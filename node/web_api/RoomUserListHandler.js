let express = require("express");
let router = express.Router();
let _ = require("lodash");
let RequestHandlerBase = require("./RequestHandlerBase");
let UsersManager = require("../lib/UsersManager");
let Utils = require("../lib/Utils");
let Const = require("../const");
let tokenChecker = require("../lib/Auth");
let UserModel = require("../models/UserModel");

let UserListHandler = function () {};

_.extend(UserListHandler.prototype, RequestHandlerBase.prototype);

UserListHandler.prototype.attach = function (router) {
  let self = this;

  router.get("/:room_id", tokenChecker, function (request, response) {
    let room_id = request.params.room_id;

    if (_.isEmpty(room_id)) {
      self.successResponse(response, Const.resCodeUserListNoRoomID);
      return;
    }

    let roomUsers = UsersManager.getUsers(room_id);
    if (_.size(roomUsers) > 0) {
      let user_ids = [];
      _.forEach(roomUsers, function (row, key) {
        user_ids.push(row.id);
      });
      UserModel.findUsersByIds(user_ids, function (err, data) {
        self.successResponse(
          response,
          Const.responsecodeSucceed,
          Utils.stripPrivacyParamsFromArray(data)
        );
      });
    } else {
      self.successResponse(
        response,
        Const.responsecodeSucceed,
        Utils.stripPrivacyParamsFromArray(roomUsers)
      );
    }
  });
};

new UserListHandler().attach(router);
module["exports"] = router;
