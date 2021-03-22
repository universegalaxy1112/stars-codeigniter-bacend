let _ = require("lodash");
let Utils = require("../lib/Utils");
let Const = require("../const");
let Settings = require("../lib/Settings");
let SocketHandlerBase = require("./SocketHandlerBase");
let MessageModel = require("../models/MessageModel");

let DeleteMessageActionHandler = function () {};
_.extend(DeleteMessageActionHandler.prototype, SocketHandlerBase.prototype);

DeleteMessageActionHandler.prototype.attach = function (io, socket) {
  socket.on(Const.SOCKET_DELETE_MESSAGE, function (param) {
    console.log(
      "-***- DeleteMessageHandler.js:14 - on() - socketId: ",
      socket.id
    );

    if (Utils.isEmpty(param.user_id)) {
      console.error("DeleteMessageActionHandler.js:24 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketDeleteMessageNoUserID,
      });
      return;
    }

    if (Utils.isEmpty(param.message_id)) {
      console.error("DeleteMessageActionHandler.js:30 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketDeleteMessageNoMessageID,
      });
      return;
    }

    MessageModel.deleteMessageById(param.message_id, function (err, result) {
      if (err) {
        console.error("DeleteMessageActionHandler.js:31 - ", err);
        socket.emit(Const.SOCKET_ERROR, {
          code: Const.resCodeSocketUnknownError,
        });
        return;
      }

      io.of(Settings.options.socketNameSpace)
        .in(param.room_id)
        .emit(Const.SOCKET_DELETE_MESSAGE, param.message_id);
      socket.emit(Const.SOCKET_DELETE_MESSAGE, param.message_id);
      console.log(
        "-***- DeleteMessageActionHandler.js:39 - room_id: %s, message_id: %s",
        param.room_id,
        param.message_id
      );
    });
  });

  socket.on(Const.SOCKET_DELETE_DURATION_MESSAGE, function (param) {
    console.log(
      "-*- DeleteMessageActionHandler.js:51 on_delete_duration_message() - ",
      param
    );

    if (Utils.isEmpty(param.user_id)) {
      console.error("-*- DeleteMessageActionHandler.js:54 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketDeleteMessageNoUserID,
      });
      return;
    }

    if (Utils.isEmpty(param.delete_type)) {
      console.error("-*- DeleteMessageActionHandler.js:60 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketDeleteMessageNoMessageID,
      });
      return;
    }

    if (Utils.isEmpty(param.room_id)) {
      console.error("-*- DeleteMessageActionHandler.js:66 - ", param);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketDeleteMessageNoUserID,
      });
      return;
    }

    MessageModel.deleteMessageByDuration(
      param.delete_type,
      param.room_id,
      param.user_id,
      function (err, result) {
        if (err) {
          console.error("-*- DeleteMessageActionHandler.js:74 - ", err);
          socket.emit(Const.SOCKET_ERROR, {
            code: Const.resCodeSocketUnknownError,
          });
          return;
        }

        if (result) {
          // send notification message deleted
          // io.of(Settings.options.socketNameSpace).in(param.room_id).emit(Const.emitKeyWordMessageDeleted, 0);
          socket.emit(Const.SOCKET_DELETE_MESSAGE, 0);
          console.log(
            "-*- DeleteMessageActionHandler.js:82 - room_id: %s, message_duration: %d",
            param.room_id,
            param.delete_type
          );
        } else {
          console.error(
            "-*- DeleteMessageActionHandler.js:87 - ",
            Const.resCodeSocketDeleteMessageNoMessageID
          );
          socket.emit(Const.SOCKET_ERROR, {
            code: Const.resCodeSocketDeleteMessageNoMessageID,
          });
        }
      }
    );
  });
};

module["exports"] = new DeleteMessageActionHandler();
