let _ = require("lodash");
let Utils = require("../lib/Utils");
let Const = require("../const");
let SocketHandlerBase = require("./SocketHandlerBase");
let UserModel = require("../models/UserModel");
let Settings = require("../lib/Settings");

let SendTypingActionHandler = function () {};
_.extend(SendTypingActionHandler.prototype, SocketHandlerBase.prototype);

SendTypingActionHandler.prototype.attach = function (io, socket) {
  socket.on(Const.SOCKET_SEND_TYPING, function (param) {
    // console.log('-*- SendTypingActionHandler.js:15 - on() - socketId: ', socket.id);

    if (Utils.isEmpty(param.user_id)) {
      console.error("SendTypingActionHandler.js:20 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketTypingNoUserID,
      });
      return;
    }

    if (Utils.isEmpty(param.room_id)) {
      console.error("SendTypingActionHandler.js:26 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketTypingNoRoomID,
      });
      return;
    }

    if (Utils.isEmpty(param.type)) {
      console.error("SendTypingActionHandler.js:32 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketTypingNoType,
      });
      return;
    }

    UserModel.findUserById(param.user_id, function (err, user) {
      if (err) {
        console.error("SendTypingActionHandler.js:41 - ", err);
        socket.emit(Const.SOCKET_ERROR, {
          code: Const.resCodeSocketTypingFaild,
        });
        return;
      }

      param.user = user;
      io.of(Settings.options.socketNameSpace)
        .in(param.room_id)
        .emit(Const.SOCKET_SEND_TYPING, param);      
    });
  });
};

module["exports"] = new SendTypingActionHandler();
