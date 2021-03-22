let _ = require("lodash");
let async = require("async");

let Const = require("../const");
let Utils = require("../lib/Utils");
let Settings = require("../lib/Settings");

let SocketHandlerBase = require("./SocketHandlerBase");
let MessageModel = require("../models/MessageModel");

let OpenMessageActionHandler = function () {};

_.extend(OpenMessageActionHandler.prototype, SocketHandlerBase.prototype);

OpenMessageActionHandler.prototype.attach = function (io, socket) {
  socket.on(Const.SOCKET_OPEN_MESSAGE, function (param) {
    if (Utils.isEmpty(param.user_id)) {
      console.error("OpenMessageActionHandler.js:22 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketOpenMessageNoUserID,
      });
      return;
    }

    if (Utils.isEmpty(param.message_ids) || !_.isArray(param.message_ids)) {
      console.error("OpenMessageActionHandler.js:28 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketOpenMessageNoMessageID,
      });
      return;
    }
  });
};

module["exports"] = new OpenMessageActionHandler();
