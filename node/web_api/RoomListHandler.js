let express = require("express");
let router = express.Router();
let _ = require("lodash");

let RequestHandlerBase = require("./RequestHandlerBase");
let UsersManager = require("../lib/UsersManager");
let Const = require("../const");
let tokenChecker = require("../lib/Auth");
let RoomModel = require("../models/RoomModel");

let RoomListHandler = function () {};

_.extend(RoomListHandler.prototype, RequestHandlerBase.prototype);

RoomListHandler.prototype.attach = function (router) {
  let self = this;

  router.get("/", tokenChecker, function (request, response) {
    let roomIds = UsersManager.getRoomIdsWhichHasUsers();
    if (roomIds.length == 0) {
      self.successResponse(response, Const.responsecodeSucceed, []);
      return;
    }

    RoomModel.findRoomsByIds(roomIds, function (error, data) {
      if (error) {
        console.error("RoomsUserListHandler.js:56 - error", error);
        self.successResponse(response, Const.responsecodeError);
      } else {
        self.successResponse(response, Const.responsecodeSucceed, data);
      }
    });
  });
};

new RoomListHandler().attach(router);
module["exports"] = router;
