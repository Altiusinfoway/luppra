import { getSession, formatGroup, formatPhone } from '../whatsapp.js'
import response from './../response.js'

const toMessageArray = (messages) => {
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

const getMessages = async (req, res) => {
    const session = getSession(res.locals.sessionId)

    const { jid } = req.params
    const { limit = 25, isGroup = false } = req.query

    const isGroupBool = isGroup === 'true'
    const jidRaw = String(jid || '').trim()

    try {
        const limitNum = Number.parseInt(limit, 10)
        const safeLimit = Number.isNaN(limitNum) ? 25 : Math.max(1, Math.min(limitNum, 200))
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

        if (isGroupBool) {
            pushCandidate(formatGroup(jidRaw))
        } else {
            const phoneDigits = jidRaw.replace(/\D/g, '')
            const chatValues = [...session.store.chats.values()]

            if (jidRaw.includes('@')) {
                pushCandidate(jidRaw)

                if (jidRaw.endsWith('@lid')) {
                    const lidChat = chatValues.find((chat) => String(chat?.id || '') === jidRaw)
                    pushCandidate(lidChat?.pnJid)
                    pushCandidate(getChatRemoteJidAlt(lidChat))
                } else if (jidRaw.endsWith('@s.whatsapp.net')) {
                    const pnChat = chatValues.find((chat) => String(chat?.pnJid || '') === jidRaw)
                    pushCandidate(pnChat?.id)
                    pushCandidate(getChatRemoteJidAlt(pnChat))
                }
            } else {
                pushCandidate(formatPhone(jidRaw))
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
        }

        const mergedMessages = []
        const seenMessageKeys = new Set()
        for (const candidateJid of candidateJids) {
            const loaded = await session.store.loadMessages(candidateJid, null, {
                limit: safeLimit,
                sortOrder: 'desc',
            })
            const rows = toMessageArray(loaded)
            console.log('[getMessages] candidate', {
                sessionId: res.locals.sessionId,
                requestedJid: jidRaw,
                candidateJid,
                count: rows.length,
            })
            for (const row of rows) {
                const keyId = String(row?.key?.id || '')
                const remoteJid = String(row?.key?.remoteJid || '')
                const dedupeKey = `${remoteJid}:${keyId}`
                if (!keyId || seenMessageKeys.has(dedupeKey)) {
                    continue
                }
                seenMessageKeys.add(dedupeKey)
                mergedMessages.push(row)
            }
        }

        mergedMessages.sort((a, b) => {
            const aTs = Number(a?.messageTimestamp?.low ?? a?.messageTimestamp ?? a?.timestamp ?? 0)
            const bTs = Number(b?.messageTimestamp?.low ?? b?.messageTimestamp ?? b?.timestamp ?? 0)
            return bTs - aTs
        })
        const bestMessages = mergedMessages.slice(0, safeLimit)

        console.log('[getMessages] resolved', {
            sessionId: res.locals.sessionId,
            requestedJid: jidRaw,
            tried: candidateJids,
            returned: bestMessages.length,
        })

        response(res, 200, true, '', bestMessages)
    } catch (error) {
        console.error('[getMessages] error', {
            sessionId: res.locals.sessionId,
            requestedJid: jidRaw,
            message: error?.message || error,
        })
        response(res, 500, false, 'Failed to load messages.')
    }
}

export default getMessages
