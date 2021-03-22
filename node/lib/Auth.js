let _ = require("lodash");
let async = require("async");

let Const = require("../const");
let Utils = require("./Utils");

let DatabaseManager = require("./DatabaseManager");

function checkToken(request, response, next) {
  /*let token = request.headers['access-token'];
    let userModel = DatabaseManager.userModel;

    if(_.isEmpty(token)) {
        response.json({
            code : Const.resCodeTokenError
        });
        
        return;
    }*/

  next();

  /*userModel.findOne({token:token},function(err,findResult){
        if(_.isEmpty(findResult)){
            response.json({
                code : Const.resCodeTokenError
            });
        
            return;
        }
        
        let tokenGenerated = findResult.tokenGeneratedAt;
        let diff = Utils.now() - tokenGenerated;
        if(diff > Const.tokenValidInteval){
            response.json({
                code : Const.resCodeTokenError
            });
        
            return;
        }
        
        request.user = findResult;
        next();
    });*/
}

module.exports = checkToken;
