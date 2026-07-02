import { isSessionExists, createSession, getSession, getListSessions, deleteSession, resolveSessionStatus, getSessionMeta } from './../whatsapp.js'
import response from '../response.js'

const find = (req, res) => {
    response(res, 200, true, 'Session found.')
}

console.log('-- sessionController.js --');
const status = (req, res) => {
    const sessionId = req.params.id
    const data = resolveSessionStatus(sessionId)
    response(res, 200, true, data.message || '', data)
}

const add = (req, res) => {
    const { id, typeAuth, phoneNumber, tenant_id: tenantId, tenant_slug: tenantSlug } = req.body
    const usePairingCode = typeAuth === 'code'

      console.log('-- sessionController.js => add => line=32');


    if (isSessionExists(id)) {
          console.log('-- sessionController.js => isSessionExists(id) => line=36');
        const sessionStatus = resolveSessionStatus(id)
        const sessionMeta = getSessionMeta(id) || {}

        if (sessionStatus.status === 'qr_required' && sessionStatus.qr_available && sessionMeta.qrcode) {
            return response(res, 200, true, sessionStatus.message, {
                qrcode: sessionMeta.qrcode,
                ...sessionStatus,
            })
        }

        if (sessionStatus.status === 'qr_required' && sessionStatus.qr_available === false) {
            deleteSession(id)
            return createSession(id, res, { usePairingCode, phoneNumber, tenantId, tenantSlug })
        }

        return response(res, 409, false, 'Session already exists, please use another id.', sessionStatus)
    }

    if (!['qr', 'code'].includes(typeAuth) && typeAuth !== undefined) {
        return response(res, 400, false, 'typeAuth must be qr or code.')
    }

    if (usePairingCode && !phoneNumber) {
        return response(res, 400, false, 'phoneNumber is required.')
    }

    createSession(id, res, { usePairingCode, phoneNumber, tenantId, tenantSlug })
}

const del = async (req, res) => {
       console.log('-- sessionController.js => del() => line=55');
    const { id } = req.params
    const session = getSession(id)
    try {
        await session.logout()
        session.end()
        session.ws.close()
    } catch {
    } finally {
        deleteSession(id)
    }

    response(res, 200, true, 'The session has been successfully deleted.')
}

const list = (req, res) => {
       console.log('-- sessionController.js => list() => line=71');
    response(res, 200, true, 'Session list', getListSessions())
}

export { find, status, add, del, list }
