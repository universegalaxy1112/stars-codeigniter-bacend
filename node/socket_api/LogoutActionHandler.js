let _ = require("lodash");

let UsersManager = require("../lib/UsersManager");
let Utils = require("../lib/Utils");
let Const = require("../const");
let SocketHandlerBase = require("./SocketHandlerBase");
let Settings = require("../lib/Settings");

let LogoutActionHandler = function () {};
_.extend(LogoutActionHandler.prototype, SocketHandlerBase.prototype);

LogoutActionHandler.prototype.attach = function (io, socket) {
  socket.on(Const.SOCKET_LOGOUT, function (param) {
    console.log("-*- LogOutActionHandler.js:18 - on() - socketId: ", socket.id);

    if (Utils.isEmpty(param.user_id)) {
      console.error("LogoutActionHandler.js:19 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketLoginNoUserID,
      });
      return;
    }

    if (Utils.isEmpty(param.room_id)) {
      console.error("LogoutActionHandler.js:25 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketLoginNoRoomID,
      });
      return;
    }

    let user = UsersManager.getUserBySocketID(socket.id);
    if (!user) {
      user = {
        user_id: param.user_id,
        room_id: param.room_id,
        socket_id: socket.id,
      };
    }

    socket.leave(param.room_id);
    io.of(Settings.options.socketNameSpace)
      .in(param.room_id)
      .emit(Const.SOCKET_USER_LEFT, user);
    UsersManager.removeUser(param.room_id, param.user_id);
  });
};

module["exports"] = new LogoutActionHandler();
