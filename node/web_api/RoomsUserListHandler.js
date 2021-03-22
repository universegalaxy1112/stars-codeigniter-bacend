let express = require("express");
let router = express.Router();
let _ = require("lodash");
let RequestHandlerBase = require("./RequestHandlerBase");
let UsersManager = require("../lib/UsersManager");
let Utils = require("../lib/Utils");
let Const = require("../const");
let tokenChecker = require("../lib/Auth");

let RoomsUserListHandler = function () {};

_.extend(RoomsUserListHandler.prototype, RequestHandlerBase.prototype);

RoomsUserListHandler.prototype.attach = function (router) {
  let self = this;

  router.get("/:user_id", tokenChecker, function (request, response) {
    let user_id = request.params.user_id;
  });
};

new RoomsUserListHandler().attach(router);
module["exports"] = router;
