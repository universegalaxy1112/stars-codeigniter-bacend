let _ = require("lodash");
let UsersManager = require("../lib/UsersManager");
let StreamManager = require("../lib/StreamManager");
let Utils = require("../lib/Utils");
let Const = require("../const");
let SocketHandlerBase = require("./SocketHandlerBase");
let Settings = require("../lib/Settings");

let DisconnectActionHandler = function () {};
_.extend(DisconnectActionHandler.prototype, SocketHandlerBase.prototype);

DisconnectActionHandler.prototype.attach = function (io, socket) {
  socket.on(Const.SOCKET_DISCONNECT, function () {
    let room_id = UsersManager.getRoomBySocketID(socket.id);
    if (Utils.isEmpty(room_id)) {
      room_id = StreamManager.getRoomBySocketID(socket.id);
      let user = StreamManager.getUserBySocketID(socket.id);
      console.log("DisconnectActionHandler.js:20 - user:", user);
      if (!_.isNull(user)) {
        StreamManager.removeUser(room_id, user.user_id);
        sendMessageUserLeft(io, socket, user);
      } else {
        console.log("DisconnectActionHandler.js:25 - socket_id:", socket.id);
      }
    } else {
      let user = UsersManager.getUserBySocketID(socket.id);
      console.log("DisconnectActionHandler.js:29 - user:", user);
      if (!_.isNull(user)) {
        UsersManager.removeUser(room_id, user.user_id);
        sendMessageUserLeft(io, socket, user);
      } else {
        console.log("DisconnectActionHandler.js:34 - socket_id:", socket.id);
      }
    }
  });
};

function sendMessageUserLeft(io, socket, user) {
  io.of(Settings.options.socketNameSpace)
    .in(user.room_id)
    .emit(Const.SOCKET_USER_LEFT, user);

  if (Settings.options.sendAttendanceMessage) {
    let message = {
      user_id: user.user_id,
      room_id: user.room_id,
      message: "",
      type: Const.MESSAGE_USER_LEAVE,
      created_id: Utils.now(),
    };
    io.of(Settings.options.socketNameSpace)
      .in(user.room_id)
      .emit(Const.SOCKET_NEW_MESSAGE, message);
  }
  socket.leave(user.room_id);
}

module["exports"] = new DisconnectActionHandler();
