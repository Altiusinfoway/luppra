import { isSessionExists, isSessionConnected, resolveSessionStatus } from '../whatsapp.js'
import response from './../response.js'

const validate = (req, res, next) => {
    console.log('--- middleware->session validator js file=>validate() => line=>5');
    const sessionId = req.query.id ?? req.params.id

    console.log('--- session validator js file=>sessionId => line =>8');
    console.log(sessionId);
    if (!isSessionExists(sessionId)) {
         console.log('--- session validator js file=> if isSessionExists => line =>11');
        const sessionStatus = resolveSessionStatus(sessionId)
        return response(res, 404, false, sessionStatus.message, sessionStatus)
    }

    if (req.baseUrl !== '/sessions' && !isSessionConnected(sessionId)) {
        console.log('--- session validator js file=> if req baseurl => line =>16');
        const sessionStatus = resolveSessionStatus(sessionId)
        return response(res, 400, false, sessionStatus.message, sessionStatus)
    }

    res.locals.sessionId = sessionId
    next()
}

export default validate
