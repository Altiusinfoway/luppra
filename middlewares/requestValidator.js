import { validationResult } from 'express-validator'
import response from './../response.js'

const validate = (req, res, next) => {
    console.log('--- middleware -> request validator js file=>validate() => line=5');
    const errors = validationResult(req)

    if (!errors.isEmpty()) {
        return response(res, 400, false, 'Please fill out all required input.')
    }

    next()
}

export default validate
