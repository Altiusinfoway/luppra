import response from './../response.js'

const validate = (req, res, next) => {
   console.log('---middleware authenticate-validator.js --');
    next()
}

export default validate
