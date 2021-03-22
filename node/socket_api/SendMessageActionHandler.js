let _ = require("lodash");

let Utils = require("../lib/Utils");
let Const = require("../const");
let SocketHandlerBase = require("./SocketHandlerBase");
let Settings = require("../lib/Settings");
let SocketAPIHandler = require("../socket_api/SocketAPIHandler");
let UsersManager = require("../lib/UsersManager");
let MessageModel = require("../models/MessageModel");

let SendMessageActionHandler = function () {};
_.extend(SendMessageActionHandler.prototype, SocketHandlerBase.prototype);

SendMessageActionHandler.prototype.attach = function (io, socket) {
  socket.on(Const.SOCKET_SEND_MESSAGE, function (param) {
    // console.log("--- crn_dev --- param:", param);

    if (
      Utils.isEmpty(param.room_id) ||
      Utils.isEmpty(param.sender_id) ||
      Utils.isEmpty(param.receiver_id) ||
      Utils.isEmpty(param.type) ||
      (param.type == Const.MESSAGE_TYPE_TEXT && Utils.isEmpty(param.message))
    ) {
      console.log("--- crn_dev --- error_param:", param);
      socket.emit(Const.SOCKET_ERROR, { code: Const.resCodeParamError });
      return;
    }

    let userID = param.sender_id;
    sendMessage(userID, param, function (err, message) {
      // console.log("--- crn_dev --- send_message:", message);

      if (err) {
        console.log("--- crn_dev --- error_send_message_err:", err);
        console.log("--- crn_dev --- error_send_message_message:", message);
        socket.emit(Const.SOCKET_ERROR, {
          code: Const.resCodeSocketSendMessageFail,
        });
      }
    });
  });

  function sendMessage(userID, param, callback) {
    // console.log("--- crn_dev --- send_message_param:", param);

    let message = param.message.replace(/^\s*$(?:\r\n?|\n)/gm, "");
    let newMessage = {
      sender_id: userID,
      receiver_id: param.receiver_id,
      room_id: param.room_id,
      message: message,
      type: param.type,
    };

    // console.log("--- crn_dev --- save_newMessage:", newMessage);
    // save to database
    MessageModel.saveNewMessage(newMessage, function (error, result) {
      // console.log('SendMessageActionHandler.js:104 - ', message);
      if (error) {
        console.error("SendMessageActionHandler.js:106 - err", error);
        callback(error, result);
        return;
      }

      if (!result) {
        console.error("SendMessageActionHandler.js:68 - error");
        callback(Const.resCodeSocketSaveMessageFail, null);
        return;
      }

      let param1 = {
        room_id: param.room_id,
        last_message_id: result + 1,
        count_per_page: 1,
      };
      MessageModel.findInitMessageList(param1, function (error, results) {
        if (error) {
          console.log("--- crn_dev --- error_get_init_message_list:", error);
          return;
        }

        if (results) {
          var newResults = [];
          results.message_list.forEach((item) => {
            newResults.push({
              message_id: item.id,
              sender_id: item.sender_id,
              sender_name: item.sender_name,
              sender_photo:
                Settings.options.avatarUrl +
                (item.sender_photo ? item.sender_photo : "profile_default.png"),
              message_type: "text",
              message: item.message,
              image: "",
              time: item.updated_at,
            });
          });

          const response = {
            status: 1,
            data: {
              total_count: results.total_count,
              message_list: newResults,
            },
          };
          io.of(Settings.options.socketNameSpace)
            .in(param.room_id)
            .emit(Const.SOCKET_NEW_MESSAGE, response);
          console.log("--- crn_dev --- response:", response);
        } else {
          const response = {
            status: 1,
            data: {
              total_count: 0,
              message_list: [],
            },
          };

          SocketAPIHandler.io
            .of(Settings.options.socketNameSpace)
            .in(param.room_id)
            .emit(Const.SOCKET_NEW_MESSAGE, response);
        }

        // send push message to offline users
        let roomUsers = UsersManager.getUsers(param.room_id);
        // console.log("--- crn_dev --- roomUsers:", roomUsers);

        if (roomUsers.length == 1) {
          let onlineUsersAndMessage = {
            sender_id: param.sender_id,
            receiver_id: param.receiver_id,
            message: param.message,
          };
          console.log(
            "--- crn_dev --- onlineUsersAndMessage:",
            onlineUsersAndMessage
          );

          MessageModel.sendPushToOfflineUsers(onlineUsersAndMessage, function (
            err,
            data
          ) {
            if (err) {
              console.error("SendMessageActionHandler.js:163 - error", err);
            }
            console.log("SendMessageActionHandler.js:165 - data", data);
          });
        }
      });
    });
  }
};

module["exports"] = new SendMessageActionHandler();
