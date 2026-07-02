import { rmSync, readdir, existsSync, mkdirSync, appendFileSync } from 'fs'
import { join } from 'path'
import https from "https"
import pino from 'pino'
import makeWASocketModule, {
    useMultiFileAuthState,
    makeCacheableSignalKeyStore,
    DisconnectReason,
    delay,
    downloadMediaMessage,
    getAggregateVotesInPollMessage,
    fetchLatestBaileysVersion,
    WAMessageStatus,
} from 'baileys'

import proto from 'baileys'

import makeInMemoryStore from './store/memory-store.js'

import { toDataURL } from 'qrcode'
import __dirname from './dirname.js'
import response from './response.js'
import { downloadImage } from './utils/download.js'
import axios from 'axios'
import NodeCache from 'node-cache'

const msgRetryCounterCache = new NodeCache()
const autoReplyCache = new NodeCache()

const sessions = new Map()
const sessionMetas = new Map()
const retries = new Map()

const APP_WEBHOOK_ALLOWED_EVENTS = ['CONNECTION_UPDATE','MESSAGES_UPSERT']
const UPSERT_TYPES_TO_PROCESS = new Set(['notify', 'append'])

const parseBool = (value, fallback = false) => {
    if (value === undefined || value === null) {
        return fallback
    }

    return ['1', 'true', 'yes', 'on'].includes(String(value).toLowerCase())
}

const leadCaptureDebug = (message, data = {}) => {
    if (!parseBool(process.env.WA_DEBUG_LEAD_CAPTURE, false)) {
        return
    }

    const line = JSON.stringify({
        at: new Date().toISOString(),
        message,
        ...data,
    })

    try {
        appendFileSync(join(__dirname, 'storage', 'logs', 'wa-lead-capture.log'), `${line}\n`)
    } catch {
    }
}

const cleanPhoneFromJid = (jid = '') => {
    const head = String(jid).split('@')[0] || ''
    return head.replace(/\D/g, '')
}

const getChatRemoteJidAlt = (chat) => {
    const messages = chat?.messages
    if (!Array.isArray(messages) || messages.length === 0) {
        return ''
    }

    const first = messages[0]
    const node = first?.message
    const key = node?.key
    return String(key?.remoteJidAlt || '')
}

const getChatPushName = (chat) => {
    const messages = chat?.messages
    if (!Array.isArray(messages) || messages.length === 0) {
        return ''
    }

    for (const row of messages) {
        const candidate = String(row?.pushName || row?.message?.pushName || '').trim()
        if (candidate) {
            return candidate
        }
    }

    return ''
}

const buildContactIndexes = (store) => {
    const byJid = new Map()
    const byLid = new Map()
    const contactValues = [...(store?.contacts?.values?.() || [])]

    for (const contact of contactValues) {
        const jid = String(contact?.id || '').trim()
        const lid = String(contact?.lid || '').trim()
        if (jid) {
            byJid.set(jid, contact)
        }
        if (lid) {
            byLid.set(lid, contact)
        }
    }

    return { byJid, byLid }
}

const resolveDirectChatMeta = (contactIndexes, chat) => {
    const rawJid = String(chat?.id || '').trim()
    const pnJid = String(chat?.pnJid || '').trim()
    const altJid = getChatRemoteJidAlt(chat)
    const accountLid = String(chat?.accountLid || '').trim()
    const contact =
        contactIndexes.byJid.get(pnJid) ||
        contactIndexes.byJid.get(altJid) ||
        contactIndexes.byLid.get(rawJid) ||
        contactIndexes.byLid.get(accountLid) ||
        null

    const resolvedPhoneJid =
        [pnJid, altJid, String(contact?.id || '').trim(), rawJid].find((jid) => String(jid || '').endsWith('@s.whatsapp.net')) || ''
    const resolvedNumber = cleanPhoneFromJid(resolvedPhoneJid)
    const displayName =
        [
            String(chat?.name || '').trim(),
            String(chat?.notify || '').trim(),
            String(contact?.name || '').trim(),
            getChatPushName(chat),
            resolvedNumber ? `+${resolvedNumber}` : '',
        ].find((value) => Boolean(String(value || '').trim())) || ''

    return {
        resolvedPhoneJid,
        resolvedNumber,
        displayName,
        contactName: String(contact?.name || '').trim(),
        altJid,
    }
}

const buildCandidateRemoteJids = (session, remoteJid = '') => {
    const candidateJids = []
    const pushCandidate = (value) => {
        const jid = String(value || '').trim()
        if (!jid) {
            return
        }
        if (!candidateJids.includes(jid)) {
            candidateJids.push(jid)
        }
    }

    const remote = String(remoteJid || '').trim()
    if (!remote) {
        return candidateJids
    }

    pushCandidate(remote)

    const phoneDigits = cleanPhoneFromJid(remote)
    const chatValues = [...(session?.store?.chats?.values?.() || [])]

    if (remote.endsWith('@lid')) {
        const lidChat = chatValues.find((chat) => String(chat?.id || '') === remote)
        pushCandidate(lidChat?.pnJid)
        pushCandidate(getChatRemoteJidAlt(lidChat))
    } else if (remote.endsWith('@s.whatsapp.net')) {
        const pnChat = chatValues.find((chat) => String(chat?.pnJid || '') === remote)
        pushCandidate(pnChat?.id)
        pushCandidate(getChatRemoteJidAlt(pnChat))
    }

    if (phoneDigits) {
        const relatedChats = chatValues.filter((chat) => {
            const id = String(chat?.id || '')
            const pnJid = String(chat?.pnJid || '')
            const altJid = getChatRemoteJidAlt(chat)
            return id.includes(phoneDigits) || pnJid.includes(phoneDigits) || altJid.includes(phoneDigits)
        })

        for (const chat of relatedChats) {
            pushCandidate(chat?.pnJid)
            pushCandidate(chat?.id)
            pushCandidate(getChatRemoteJidAlt(chat))
        }
    }

    return candidateJids
}

const toStoreMessageRows = (messages) => {
    if (!messages) {
        return []
    }

    if (messages instanceof Map) {
        return [...messages.values()]
    }

    if (Array.isArray(messages)) {
        return messages
    }

    if (typeof messages?.values === 'function') {
        try {
            return [...messages.values()]
        } catch {
        }
    }

    if (typeof messages === 'object') {
        return Object.values(messages)
    }

    return []
}

const getTextFromMessageNode = (messageNode) => {
    if (!messageNode || typeof messageNode !== 'object') {
        return ''
    }

    if (messageNode.conversation) {
        return String(messageNode.conversation)
    }
    if (messageNode.extendedTextMessage?.text) {
        return String(messageNode.extendedTextMessage.text)
    }
    if (messageNode.imageMessage?.caption) {
        return String(messageNode.imageMessage.caption)
    }
    if (messageNode.videoMessage?.caption) {
        return String(messageNode.videoMessage.caption)
    }
    if (messageNode.documentMessage?.caption) {
        return String(messageNode.documentMessage.caption)
    }
    if (messageNode.buttonsResponseMessage?.selectedDisplayText) {
        return String(messageNode.buttonsResponseMessage.selectedDisplayText)
    }
    if (messageNode.listResponseMessage?.title) {
        return String(messageNode.listResponseMessage.title)
    }
    if (messageNode.templateButtonReplyMessage?.selectedDisplayText) {
        return String(messageNode.templateButtonReplyMessage.selectedDisplayText)
    }
    if (messageNode.ephemeralMessage?.message) {
        return getTextFromMessageNode(messageNode.ephemeralMessage.message)
    }
    if (messageNode.viewOnceMessage?.message) {
        return getTextFromMessageNode(messageNode.viewOnceMessage.message)
    }
    if (messageNode.viewOnceMessageV2?.message) {
        return getTextFromMessageNode(messageNode.viewOnceMessageV2.message)
    }

    return ''
}

const resolveMessageNode = (messageNode) => {
    let currentNode = messageNode && typeof messageNode === 'object' ? messageNode : {}
    let maxDepth = 6

    while (maxDepth-- > 0 && currentNode && typeof currentNode === 'object') {
        const keys = Object.keys(currentNode)
        const messageType = keys[0]

        if (!messageType) {
            break
        }

        if (['ephemeralMessage', 'viewOnceMessage', 'viewOnceMessageV2', 'viewOnceMessageV2Extension'].includes(messageType)) {
            const nextNode = currentNode?.[messageType]?.message
            if (nextNode && typeof nextNode === 'object') {
                currentNode = nextNode
                continue
            }
        }

        if (messageType === 'documentWithCaptionMessage') {
            const nextNode = currentNode?.[messageType]?.message
            if (nextNode && typeof nextNode === 'object') {
                currentNode = nextNode
                continue
            }
        }

        return { messageNode: currentNode, messageType }
    }

    return { messageNode: currentNode, messageType: null }
}

const buildHistoryFromStore = async (wa, remoteJid, limit = 8) => {
    try {
        const messages = await wa.store.loadMessages(remoteJid, null, {
            limit,
            sortOrder: 'desc',
        })

        const rows = [...messages.values()]
            .reverse()
            .map((row) => {
                const text = getTextFromMessageNode(row.message).trim()
                if (!text) {
                    return null
                }

                return {
                    role: row.key?.fromMe ? 'assistant' : 'user',
                    content: text.slice(0, 700),
                }
            })
            .filter(Boolean)

        return rows
    } catch {
        return []
    }
}

const fetchDynamicBotConfig = async ({ sessionId, remoteJid, incomingText }) => {
    const url = process.env.WA_AI_CONFIG_URL || ''
    const token = process.env.AUTHENTICATION_GLOBAL_AUTH_TOKEN || ''

    if (!url || !token) {
        return null
    }

    try {
        const response = await axios.post(
            url,
            {
                session_id: sessionId,
                remote_jid: remoteJid,
                message: incomingText,
            },
            {
                headers: {
                    'X-Internal-Token': token,
                    'Content-Type': 'application/json',
                },
                timeout: 12000,
            },
        )

        return response?.data ?? null
    } catch {
        return null
    }
}

const resolveLeadCaptureUrl = () => {
    const explicitUrl = process.env.WA_LEAD_CAPTURE_URL || ''
    if (explicitUrl) {
        return explicitUrl
    }

    const configUrl = process.env.WA_AI_CONFIG_URL || ''
    if (!configUrl || !/\/whatsapp-bot\/config\/?$/.test(configUrl)) {
        return ''
    }

    return configUrl.replace(/\/whatsapp-bot\/config\/?$/, '/whatsapp-bot/lead-message')
}

const resolveIncomingPhoneJid = (session, msg) => {
    const remoteJid = String(msg?.key?.remoteJid || '').trim()
    const remoteJidAlt = String(msg?.key?.remoteJidAlt || '').trim()
    const participant = String(msg?.key?.participant || '').trim()
    const candidates = [remoteJidAlt, participant, remoteJid]

    if (remoteJid.endsWith('@lid')) {
        const chatValues = [...(session?.store?.chats?.values?.() || [])]
        const chat = chatValues.find((row) => String(row?.id || '') === remoteJid)
        const contactIndexes = buildContactIndexes(session?.store)
        const contact = contactIndexes.byLid.get(remoteJid) || contactIndexes.byLid.get(String(chat?.accountLid || '').trim())

        candidates.push(
            String(chat?.pnJid || '').trim(),
            getChatRemoteJidAlt(chat),
            String(contact?.id || '').trim(),
        )
    }

    return candidates.find((jid) => String(jid || '').endsWith('@s.whatsapp.net')) || ''
}

const shouldCaptureLeadMessage = (msg) => {
    if (msg?.key?.fromMe) {
        return false
    }

    const remoteJid = String(msg?.key?.remoteJid || '')
    if (!remoteJid || remoteJid === 'status@broadcast' || remoteJid.endsWith('@g.us')) {
        return false
    }

    return true
}

const captureLeadFromIncomingMessage = async ({ sessionId, session, msg }) => {
    console.log("test");
    if (!shouldCaptureLeadMessage(msg)) {
        leadCaptureDebug('skip-message', {
            sessionId,
            remoteJid: String(msg?.key?.remoteJid || ''),
            fromMe: Boolean(msg?.key?.fromMe),
        })
        return null
    }

    const url = resolveLeadCaptureUrl()
    const token = process.env.AUTHENTICATION_GLOBAL_AUTH_TOKEN || ''
    if (!url || !token) {
        leadCaptureDebug('skip-missing-url-or-token', {
            sessionId,
            hasUrl: Boolean(url),
            hasToken: Boolean(token),
        })
        return null
    }

    try {
        const meta = sessionMetas.get(sessionId) || session?.meta || {}
        const resolvedPhoneJid = resolveIncomingPhoneJid(session, msg)
        const headers = {
            'X-Internal-Token': token,
            'Content-Type': 'application/json',
        }

        if (meta.tenantId) {
            headers['X-Tenant-Id'] = String(meta.tenantId)
        }
        if (meta.tenantSlug) {
            headers['X-Tenant-Slug'] = String(meta.tenantSlug)
        }

        if (parseBool(process.env.WA_DEBUG_LEAD_CAPTURE, false)) {
            console.log('[lead-capture] request', {
                sessionId,
                remoteJid: String(msg?.key?.remoteJid || ''),
                resolvedPhoneJid,
                url,
                tenantId: meta.tenantId || '',
                tenantSlug: meta.tenantSlug || '',
            })
        }
        leadCaptureDebug('request', {
            sessionId,
            remoteJid: String(msg?.key?.remoteJid || ''),
            remoteJidAlt: String(msg?.key?.remoteJidAlt || ''),
            resolvedPhoneJid,
            url,
            tenantId: meta.tenantId || '',
            tenantSlug: meta.tenantSlug || '',
            pushName: String(msg?.pushName || '').trim(),
        })

        const response = await axios.post(
            url,
            {
                session_id: sessionId,
                remote_jid: String(msg?.key?.remoteJid || ''),
                remote_jid_alt: resolvedPhoneJid || String(msg?.key?.remoteJidAlt || ''),
                participant: String(msg?.key?.participant || ''),
                message: getTextFromMessageNode(msg?.message).trim(),
                push_name: String(msg?.pushName || '').trim(),
            },
            {
                headers,
                timeout: 12000,
            },
        )

        if (parseBool(process.env.WA_DEBUG_LEAD_CAPTURE, false)) {
            console.log('[lead-capture] response', response?.data ?? null)
        }
        leadCaptureDebug('response', {
            sessionId,
            response: response?.data ?? null,
        })

        return response?.data ?? null
    } catch (error) {
        if (parseBool(process.env.WA_DEBUG_LEAD_CAPTURE, false)) {
            console.warn('[lead-capture]', error?.response?.data || error?.message || error)
        }
        leadCaptureDebug('error', {
            sessionId,
            status: error?.response?.status || null,
            response: error?.response?.data || null,
            error: error?.message || String(error),
        })
        return null
    }
}

const generateAiReply = async ({ incomingText, history, pushName, dynamicConfig = null, knowledgeText = '', rulePrompt = '' }) => {
    const apiKey = process.env.OPENAI_API_KEY
    const model = dynamicConfig?.model || process.env.WA_AI_BOT_MODEL || 'gpt-4o-mini'
    const businessName = dynamicConfig?.business_name || process.env.WA_AI_BOT_BUSINESS_NAME || 'our company'
    const businessContext =
        dynamicConfig?.business_context ||
        process.env.WA_AI_BOT_BUSINESS_CONTEXT ||
        'We sell industrial products and handle lead enquiries in India.'
    const tone = dynamicConfig?.tone || process.env.WA_AI_BOT_TONE || 'professional, concise and helpful'
    const systemPrompt =
        dynamicConfig?.system_prompt ||
        process.env.WA_AI_BOT_SYSTEM_PROMPT ||
        `You are a WhatsApp sales assistant for ${businessName}. ${businessContext} Reply in ${tone}. Keep replies short (max 80 words), action-oriented, and ask one useful follow-up question when needed.`

    if (!apiKey) {
        return null
    }

    const payloadMessages = [
        { role: 'system', content: systemPrompt },
        ...history,
        {
            role: 'user',
            content: `Customer name: ${pushName || 'Customer'}\nPhone: ${dynamicConfig?.lead?.phone || ''}\nStage: ${dynamicConfig?.lead?.stage_name || 'Unknown'}\nRule hint: ${rulePrompt}\nKnowledge:\n${knowledgeText}\nCustomer message: ${incomingText}`,
        },
    ]

    const response = await axios.post(
        'https://api.openai.com/v1/chat/completions',
        {
            model,
            messages: payloadMessages,
            temperature: 0.4,
            max_tokens: 180,
        },
        {
            headers: {
                Authorization: `Bearer ${apiKey}`,
                'Content-Type': 'application/json',
            },
            timeout: 20000,
        },
    )

    const text = response?.data?.choices?.[0]?.message?.content

    if (!text || typeof text !== 'string') {
        return null
    }

    return text.trim().slice(0, 900)
}

const handleAutoReply = async (wa, sessionId, msg) => {
    if (msg?.key?.fromMe) {
        return
    }

    const remoteJid = msg?.key?.remoteJid || ''
    if (!remoteJid || remoteJid === 'status@broadcast' || !remoteJid.endsWith('@s.whatsapp.net')) {
        return
    }

    const incomingText = getTextFromMessageNode(msg.message).trim()
    if (!incomingText) {
        return
    }

    const dynamicConfig = await fetchDynamicBotConfig({ sessionId, remoteJid, incomingText })
    const isDynamicEnabled = Boolean(dynamicConfig?.enabled)
    const enabled = isDynamicEnabled || parseBool(process.env.WA_AI_BOT_ENABLED, false)
    if (!enabled) {
        return
    }

    const cooldownSeconds = Number.parseInt(
        dynamicConfig?.config?.cooldown_seconds || process.env.WA_AI_BOT_COOLDOWN_SECONDS || '45',
        10,
    )
    const cacheKey = `ai-reply:${sessionId}:${remoteJid}`
    if (autoReplyCache.get(cacheKey)) {
        return
    }

    const lowerText = incomingText.toLowerCase()
    const stopWords = ['stop', 'unsubscribe', 'dnd', 'do not message']
    if (stopWords.includes(lowerText)) {
        autoReplyCache.set(`ai-stop:${sessionId}:${remoteJid}`, true, 60 * 60 * 24 * 30)
        return
    }

    if (autoReplyCache.get(`ai-stop:${sessionId}:${remoteJid}`)) {
        if (lowerText === 'start') {
            autoReplyCache.del(`ai-stop:${sessionId}:${remoteJid}`)
        } else {
            return
        }
    }

    autoReplyCache.set(cacheKey, true, Math.max(cooldownSeconds, 5))

    try {
        const rule = dynamicConfig?.rule || null
        if (rule?.mode === 'handoff') {
            return
        }

        if (rule?.mode === 'template' && rule?.template_text) {
            await sendMessage(
                wa,
                remoteJid,
                { text: String(rule.template_text).trim().slice(0, 900) },
                {},
                parseInt(dynamicConfig?.config?.reply_delay_ms || process.env.WA_AI_BOT_REPLY_DELAY_MS || '600', 10),
            )
            return
        }

        const knowledgeRows = Array.isArray(dynamicConfig?.knowledge) ? dynamicConfig.knowledge : []
        const messagePhone = cleanPhoneFromJid(remoteJid)
        const relevantKnowledge = knowledgeRows
            .filter((row) => {
                const keywords = String(row?.keywords || '').toLowerCase()
                if (!keywords) {
                    return true
                }
                return keywords.split(',').some((kw) => {
                    const key = kw.trim().toLowerCase()
                    return key && incomingText.toLowerCase().includes(key)
                })
            })
            .slice(0, 8)
            .map((row) => `Q: ${row?.title || ''}\nA: ${row?.answer || ''}`)
            .join('\n\n')

        const history = await buildHistoryFromStore(wa, remoteJid, 8)
        let replyText = await generateAiReply({
            incomingText,
            history,
            pushName: msg?.pushName || '',
            dynamicConfig: {
                ...(dynamicConfig?.config || {}),
                lead: dynamicConfig?.lead || null,
                customer_phone: messagePhone,
            },
            knowledgeText: relevantKnowledge,
            rulePrompt: rule?.prompt_hint || '',
        })

        if (!replyText) {
            replyText = dynamicConfig?.config?.fallback_text ||
                process.env.WA_AI_BOT_FALLBACK_TEXT ||
                'Thanks for your message. Our team will connect with you shortly.'
        }

        await sendMessage(
            wa,
            remoteJid,
            { text: replyText },
            {},
            parseInt(dynamicConfig?.config?.reply_delay_ms || process.env.WA_AI_BOT_REPLY_DELAY_MS || '600', 10),
        )
    } catch (error) {
        console.error('AI auto-reply error', error?.message || error)
    }
}

 console.log('--- whatsapp.js -----');
const sessionsDir = (sessionId = '') => {
     console.log('--- whatsapp.js =>sessionsDir() -----');
    return join(__dirname, 'sessions', sessionId ? sessionId : '')
}

const qrTtlMs = () => {
    const configured = parseInt(process.env.WA_SERVER_QR_TTL_MS ?? 60000, 10)
    return Number.isFinite(configured) && configured > 0 ? configured : 60000
}

const isQrExpired = (meta = {}) => {
    const qrGeneratedAt = Number(meta?.qrGeneratedAt ?? 0)
    if (!qrGeneratedAt) {
        return false
    }

    return (Date.now() - qrGeneratedAt) > qrTtlMs()
}

const setSessionMeta = (sessionId, patch = {}) => {
    const existingMeta = sessionMetas.get(sessionId) || {}
    const nextMeta = { ...existingMeta, ...patch }
    sessionMetas.set(sessionId, nextMeta)

    const session = sessions.get(sessionId)
    if (session) {
        session.meta = { ...(session.meta || {}), ...patch }
    }

    return nextMeta
}

const buildNormalizedSessionStatus = (status, overrides = {}) => {
    const defaults = {
        connected: {
            can_open_chat: true,
            should_redirect_to_qr: false,
            qr_available: false,
            message: 'WhatsApp session is connected.',
        },
        connecting: {
            can_open_chat: false,
            should_redirect_to_qr: true,
            qr_available: false,
            message: 'WhatsApp session is connecting. Please wait a moment.',
        },
        disconnected: {
            can_open_chat: false,
            should_redirect_to_qr: true,
            qr_available: false,
            message: 'WhatsApp session is disconnected. Please reconnect this device.',
        },
        qr_required: {
            can_open_chat: false,
            should_redirect_to_qr: true,
            qr_available: true,
            message: 'Please scan the QR code to connect this device.',
        },
        not_ready: {
            can_open_chat: false,
            should_redirect_to_qr: true,
            qr_available: false,
            message: 'WhatsApp session is not ready yet. Please reconnect this device.',
        },
    }

    return {
        status,
        ...(defaults[status] || defaults.not_ready),
        ...overrides,
    }
}

const resolveSessionStatus = (sessionId) => {
    const session = getSession(sessionId)
    const meta = sessionMetas.get(sessionId) || session?.meta || {}
    const readyState = session?.ws?.socket?.readyState

    if (readyState === 1 && typeof session?.user !== 'undefined') {
        return buildNormalizedSessionStatus('connected')
    }

    if (meta.status === 'qr_required') {
        if (meta.qrAvailable === true && !isQrExpired(meta)) {
            return buildNormalizedSessionStatus('qr_required', {
                qr_available: true,
                message: meta.message || 'Please scan the QR code to connect this device.',
            })
        }

        return buildNormalizedSessionStatus('qr_required', {
            qr_available: false,
            message: 'QR code expired. Please refresh to generate a new QR code.',
        })
    }

    if (readyState === 0) {
        return buildNormalizedSessionStatus('connecting')
    }

    if (readyState === 2 || readyState === 3) {
        return buildNormalizedSessionStatus('disconnected')
    }

    if (meta.status && ['connected', 'connecting', 'disconnected', 'qr_required', 'not_ready'].includes(meta.status)) {
        return buildNormalizedSessionStatus(meta.status, {
            qr_available: meta.qrAvailable === true,
            message: meta.message || undefined,
        })
    }

    return buildNormalizedSessionStatus('not_ready')
}

const isSessionExists = (sessionId) => {
     console.log('--- whatsapp.js =>isSessionExists()=>line=41 -----');
     console.log(sessionId);
    return sessions.has(sessionId)
}

const isSessionConnected = (sessionId) => {
    console.log('--- whatsapp.js =>isSessionConnected()=>line=47 -----');
    console.log(sessionId);
    return sessions.get(sessionId)?.ws?.socket?.readyState === 1
}

const shouldReconnect = (sessionId) => {
    console.log('--- whatsapp.js =>shouldReconnect()=>line=53 -----');
    console.log(sessionId);
    const maxRetries = parseInt(process.env.WA_SERVER_MAX_RETRIES ?? 0)
    let attempts = retries.get(sessionId) ?? 0

    console.log('--- whatsapp.js =>maxRetries =>line=58 -----',maxRetries);

    if (attempts < maxRetries || maxRetries === -1) {
        ++attempts

        console.log('Reconnecting...', { attempts, sessionId })
        retries.set(sessionId, attempts)

        return true
    }

    return false
}



const createSession = async (sessionId, res = null, options = { usePairingCode: false, phoneNumber: '', tenantId: '', tenantSlug: '' }) => {
    const sessionFile = 'md_' + sessionId

    const logger = pino({ level: 'silent' })
    const store = makeInMemoryStore({
        preserveDataDuringSync: true,
        backupBeforeSync: false,
        incrementalSave: true,
        maxMessagesPerChat: 150,
        autoSaveInterval: 10000,
        storeFile: sessionsDir(`${sessionId}_store.json`)
    });

    const { state, saveCreds } = await useMultiFileAuthState(sessionsDir(sessionFile))

    // Fetch latest version of WA Web
    const { version, isLatest } = await fetchLatestBaileysVersion()
    console.log(`using WA v${version.join('.')}, isLatest: ${isLatest}`)

    // Load store
    store?.readFromFile(sessionsDir(`${sessionId}_store.json`))

    // Make both Node and Bun compatible
    const makeWASocket = makeWASocketModule.default ?? makeWASocketModule;

    /**
     * @type {import('baileys').AnyWASocket}
     */
    const wa = makeWASocket({
        version,
        printQRInTerminal: false,
        mobile: false,
        auth: {
            creds: state.creds,
            keys: makeCacheableSignalKeyStore(state.keys, logger),
        },
        logger,
        msgRetryCounterCache,
        generateHighQualityLinkPreview: true,
        getMessage,
    })
    store?.bind(wa.ev)

    const existingMeta = sessionMetas.get(sessionId) || {}
    const meta = {
        tenantId: options.tenantId || existingMeta.tenantId || '',
        tenantSlug: options.tenantSlug || existingMeta.tenantSlug || '',
        status: 'connecting',
        qrAvailable: false,
        qrGeneratedAt: 0,
        qrcode: '',
        message: 'WhatsApp session is connecting. Please wait a moment.',
    }

    if (meta.tenantId || meta.tenantSlug) {
        sessionMetas.set(sessionId, meta)
    } else {
        sessionMetas.set(sessionId, meta)
    }

    sessions.set(sessionId, { ...wa, store, meta })

    if (options.usePairingCode && !wa.authState.creds.registered) {
        if (!wa.authState.creds.account) {
            await wa.waitForConnectionUpdate((update) => {
                return Boolean(update.qr)
            })
            const code = await wa.requestPairingCode(options.phoneNumber)
            if (res && !res.headersSent && code !== undefined) {
                response(res, 200, true, 'Verify on your phone and enter the provided code.', { code })
            } else {
                response(res, 500, false, 'Unable to create session.')
            }
        }
    }

    wa.ev.on('creds.update', saveCreds)



    // Automatically read incoming messages, uncomment below codes to enable this behaviour
    wa.ev.on('messages.upsert', async (m) => {
        if (!UPSERT_TYPES_TO_PROCESS.has(String(m?.type || ''))) {
            return
        }

        if (parseBool(process.env.WA_DEBUG_UPSERT, false)) {
            console.log('[messages.upsert]', {
                sessionId,
                type: m?.type,
                count: Array.isArray(m?.messages) ? m.messages.length : 0,
            })
        }

        const messages = m.messages.filter((item) => {
            const remoteJid = String(item?.key?.remoteJid || '')
            if (!remoteJid || remoteJid === 'status@broadcast') {
                return false
            }
            return item?.key?.fromMe === false
        })
        leadCaptureDebug('upsert-filtered', {
            sessionId,
            type: String(m?.type || ''),
            total: Array.isArray(m?.messages) ? m.messages.length : 0,
            incoming: messages.length,
            remoteJids: messages.map((msg) => String(msg?.key?.remoteJid || '')).slice(0, 5),
        })
        if (messages.length > 0) {
            console.log("vv");
            const messageTmp = await Promise.all(
                messages.map(async (msg) => {
                    try {
                        const typeMessage = Object.keys(msg.message)[0]
                        if (msg?.status) {
                            msg.status = WAMessageStatus[msg?.status] ?? 'UNKNOWN'
                        }

                        if (
                            ['documentMessage', 'imageMessage', 'videoMessage', 'audioMessage'].includes(typeMessage) &&
                            process.env.APP_WEBHOOK_FILE_IN_BASE64 === 'true'
                        ) {
                            const mediaMessage = await getMessageMedia(wa, msg)

                            const fieldsToConvert = [
                                'fileEncSha256',
                                'mediaKey',
                                'fileSha256',
                                'jpegThumbnail',
                                'thumbnailSha256',
                                'thumbnailEncSha256',
                                'streamingSidecar',
                            ]

                            fieldsToConvert.forEach((field) => {
                                if (msg.message[typeMessage]?.[field] !== undefined) {
                                    msg.message[typeMessage][field] = convertToBase64(msg.message[typeMessage][field])
                                }
                            })

                            return {
                                ...msg,
                                message: {
                                    [typeMessage]: {
                                        ...msg.message[typeMessage],
                                        fileBase64: mediaMessage.base64,
                                    },
                                },
                            }
                        }

                        return msg
                    } catch {
                        return {}
                    }
                }),
            )


        }

        for (const msg of messages) {
            console.log("bb");
            await captureLeadFromIncomingMessage({ sessionId, session: sessions.get(sessionId), msg })
            await handleAutoReply(wa, sessionId, msg)
        }
    })



    wa.ev.on('messages.update', async (m) => {
        for (const { key, update } of m) {
            const msg = await getMessage(key)

            if (!msg) {
                continue
            }

            update.status = WAMessageStatus[update.status]
            const messagesUpdate = [
                {
                    key,
                    update,
                    message: msg,
                },
            ]

        }
    })

    wa.ev.on('message-receipt.update', async (m) => {
        for (const { key, messageTimestamp, pushName, broadcast, update } of m) {
            if (update?.pollUpdates) {
                const pollCreation = await getMessage(key)
                if (pollCreation) {
                    const pollMessage = await getAggregateVotesInPollMessage({
                        message: pollCreation,
                        pollUpdates: update.pollUpdates,
                    })
                    update.pollUpdates[0].vote = pollMessage

                    return
                }
            }
        }


    })



    wa.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update
        const statusCode = lastDisconnect?.error?.output?.statusCode



        if (connection === 'open') {
            retries.delete(sessionId)
            setSessionMeta(sessionId, {
                status: 'connected',
                qrAvailable: false,
                qrGeneratedAt: 0,
                qrcode: '',
                message: 'WhatsApp session is connected.',
            })
        }

        if (connection === 'close') {
            const reconnecting = statusCode !== DisconnectReason.loggedOut && shouldReconnect(sessionId)
            setSessionMeta(sessionId, reconnecting
                ? {
                    status: 'connecting',
                    qrAvailable: false,
                    qrGeneratedAt: 0,
                    qrcode: '',
                    message: 'WhatsApp session is reconnecting. Please wait a moment.',
                }
                : {
                    status: 'disconnected',
                    qrAvailable: false,
                    qrGeneratedAt: 0,
                    qrcode: '',
                    message: 'WhatsApp session is disconnected. Please reconnect this device.',
                })

            if (statusCode === DisconnectReason.loggedOut || !reconnecting) {
                if (res && !res.headersSent) {
                    response(res, 500, false, 'Unable to create session.')
                }

                return deleteSession(sessionId)
            }

            setTimeout(
                () => {
                    createSession(sessionId, res, sessionMetas.get(sessionId) || {})
                },
                statusCode === DisconnectReason.restartRequired ? 0 : parseInt(process.env.WA_SERVER_RECONNECT_INTERVAL ?? 0),
            )
        }

        if (qr) {
            if (res && !res.headersSent) {


                try {
                    const qrcode = await toDataURL(qr)
                    setSessionMeta(sessionId, {
                        status: 'qr_required',
                        qrAvailable: true,
                        qrGeneratedAt: Date.now(),
                        qrcode,
                        message: 'Please scan the QR code to connect this device.',
                    })
                    response(res, 200, true, 'QR code received, please scan the QR code.', { qrcode })
                    return
                } catch {
                    response(res, 500, false, 'Unable to create QR code.')
                }
            }
        }
    })



    async function getMessage(key) {
        if (store) {
            const msg = await store.loadMessages(key.remoteJid, key.id)
            return msg?.message || undefined
        }

        // Only if store is present
        return proto.Message.fromObject({})
    }
}

/**
 * @returns {(import('baileys').AnyWASocket|null)}
 */
const getSession = (sessionId) => {
    return sessions.get(sessionId) ?? null
}

const getSessionMeta = (sessionId) => {
    return sessionMetas.get(sessionId) ?? null
}

const getListSessions = () => {
    return [...sessions.keys()]
}

const deleteSession = (sessionId) => {
    const sessionFile = 'md_' + sessionId
    const storeFile = `${sessionId}_store.json`
    const rmOptions = { force: true, recursive: true }

    rmSync(sessionsDir(sessionFile), rmOptions)
    rmSync(sessionsDir(storeFile), rmOptions)

    sessions.delete(sessionId)
    sessionMetas.delete(sessionId)
    retries.delete(sessionId)
}

const getChatList = (sessionId, isGroup = false) => {
    const session = getSession(sessionId)
    const chats = session.store.chats
    const contactIndexes = buildContactIndexes(session.store)

    if (isGroup) {
        return [...chats.values()].filter((chat) => String(chat.id || '').endsWith('@g.us'))
    }

    return [...chats.values()]
        .filter((chat) => {
            const id = String(chat?.id || '')
            const pnJid = String(chat?.pnJid || '')
            return id.endsWith('@s.whatsapp.net') || id.endsWith('@lid') || pnJid.endsWith('@s.whatsapp.net')
        })
        .map((chat) => ({
            ...chat,
            ...resolveDirectChatMeta(contactIndexes, chat),
        }))
}

/**
 * @param {import('baileys').AnyWASocket} session
 */
const isExists = async (session, jid, isGroup = false) => {
    try {
        let result

        if (isGroup) {
            result = await session.groupMetadata(jid)

            return Boolean(result.id)
        }

        ;[result] = await session.onWhatsApp(jid)

        return result.exists
    } catch {
        return false
    }
}

/**
 * @param {import('baileys').AnyWASocket} session
 */
const sendMessage = async (session, receiver, message, options = {}, delayMs = 1000) => {
    try {
        await delay(parseInt(delayMs))
        return await session.sendMessage(receiver, message, options)
    } catch {
        return Promise.reject(null) // eslint-disable-line prefer-promise-reject-errors
    }
}

/**
 * @param {import('baileys').AnyWASocket} session
 */
const updateProfileStatus = async (session, status) => {
    try {
        return await session.updateProfileStatus(status)
    } catch {
        return Promise.reject(null) // eslint-disable-line prefer-promise-reject-errors
    }
}

const updateProfileName = async (session, name) => {
    try {
        return await session.updateProfileName(name)
    } catch {
        return Promise.reject(null) // eslint-disable-line prefer-promise-reject-errors
    }
}

const getProfilePicture = async (session, jid, type = 'image') => {
    try {
        return await session.profilePictureUrl(jid, type)
    } catch {
        return Promise.reject(null) // eslint-disable-line prefer-promise-reject-errors
    }
}

const blockAndUnblockUser = async (session, jid, block) => {
    try {
        return await session.updateBlockStatus(jid, block)
    } catch {
        return Promise.reject(null) // eslint-disable-line prefer-promise-reject-errors
    }
}

const formatPhone = (phone) => {
    if (phone.endsWith('@s.whatsapp.net')) {
        return phone
    }

    let formatted = phone.replace(/\D/g, '')

    return (formatted += '@s.whatsapp.net')
}

const formatGroup = (group) => {
    if (group.endsWith('@g.us')) {
        return group
    }

    let formatted = group.replace(/[^\d-]/g, '')

    return (formatted += '@g.us')
}

const cleanup = () => {
    console.log('Running cleanup before exit.')

    sessions.forEach((session, sessionId) => {
        session.store.writeToFile(sessionsDir(`${sessionId}_store.json`))
    })
}

const getGroupsWithParticipants = async (session) => {
    return session.groupFetchAllParticipating()
}

const participantsUpdate = async (session, jid, participants, action) => {
    return session.groupParticipantsUpdate(jid, participants, action)
}

const updateSubject = async (session, jid, subject) => {
    return session.groupUpdateSubject(jid, subject)
}

const updateDescription = async (session, jid, description) => {
    return session.groupUpdateDescription(jid, description)
}

const settingUpdate = async (session, jid, settings) => {
    return session.groupSettingUpdate(jid, settings)
}

const leave = async (session, jid) => {
    return session.groupLeave(jid)
}

const inviteCode = async (session, jid) => {
    return session.groupInviteCode(jid)
}

const revokeInvite = async (session, jid) => {
    return session.groupRevokeInvite(jid)
}

const metaData = async (session, req) => {
    return session.groupMetadata(req.groupId)
}

const acceptInvite = async (session, req) => {
    return session.groupAcceptInvite(req.invite)
}

const profilePicture = async (session, jid, urlImage) => {
    const image = await downloadImage(urlImage)
    return session.updateProfilePicture(jid, { url: image })
}

const readMessage = async (session, keys) => {
    return session.readMessages(keys)
}

const getStoreMessage = async (session, messageId, remoteJid) => {
    const candidateJids = buildCandidateRemoteJids(session, remoteJid)

    for (const candidateJid of candidateJids) {
        try {
            const directMessage = await session.store.loadMessages(candidateJid, messageId)
            const directRows = toStoreMessageRows(directMessage)
            const directFound = directRows.find((row) => String(row?.key?.id || '') === String(messageId || ''))
            if (directFound?.message) {
                return directFound
            }
        } catch {
        }

        try {
            const loaded = await session.store.loadMessages(candidateJid, null, {
                limit: 80,
                sortOrder: 'desc',
            })
            const rows = toStoreMessageRows(loaded)
            const found = rows.find((row) => String(row?.key?.id || '') === String(messageId || ''))
            if (found?.message) {
                return found
            }

            for (const row of rows) {
                const altJid = String(row?.key?.remoteJidAlt || '').trim()
                if (String(row?.key?.id || '') === String(messageId || '') && altJid) {
                    const altRows = toStoreMessageRows(await session.store.loadMessages(altJid, messageId))
                    const altFound = altRows.find((altRow) => String(altRow?.key?.id || '') === String(messageId || ''))
                    if (altFound?.message) {
                        return altFound
                    }
                }
            }
        } catch {
        }
    }

    // eslint-disable-next-line prefer-promise-reject-errors
    return Promise.reject(null)
}

const getMessageMedia = async (session, message) => {
    try {
        const { messageNode, messageType } = resolveMessageNode(message?.message || {})
        const mediaMessage = messageNode?.[messageType] || {}
        const buffer = await downloadMediaMessage(
            message,
            'buffer',
            {},
            { reuploadRequest: session.updateMediaMessage },
        )

        return {
            messageType,
            fileName: mediaMessage.fileName ?? '',
            caption: mediaMessage.caption ?? '',
            size: {
                fileLength: mediaMessage.fileLength,
                height: mediaMessage.height ?? 0,
                width: mediaMessage.width ?? 0,
            },
            mimetype: mediaMessage.mimetype,
            base64: buffer.toString('base64'),
        }
    } catch {
        // eslint-disable-next-line prefer-promise-reject-errors
        return Promise.reject(null)
    }
}

const convertToBase64 = (arrayBytes) => {
    const byteArray = new Uint8Array(arrayBytes)
    return Buffer.from(byteArray).toString('base64')
}

const init = () => {
    readdir(sessionsDir(), (err, files) => {
        if (err) {
            throw err
        }

        for (const file of files) {
            if ((!file.startsWith('md_') && !file.startsWith('legacy_')) || file.endsWith('_store')) {
                continue
            }

            const filename = file.replace('.json', '')
            const sessionId = filename.substring(3)
            console.log('Recovering session: ' + sessionId)
            createSession(sessionId)
        }
    })
}

export {
    isSessionExists,
    createSession,
    getSession,
    getListSessions,
    deleteSession,
    getChatList,
    getGroupsWithParticipants,
    isExists,
    sendMessage,
    updateProfileStatus,
    updateProfileName,
    getProfilePicture,
    formatPhone,
    formatGroup,
    cleanup,
    participantsUpdate,
    updateSubject,
    updateDescription,
    settingUpdate,
    leave,
    inviteCode,
    revokeInvite,
    metaData,
    acceptInvite,
    profilePicture,
    readMessage,
    init,
    isSessionConnected,
    resolveSessionStatus,
    getSessionMeta,
    getMessageMedia,
    getStoreMessage,
    blockAndUnblockUser,
}
