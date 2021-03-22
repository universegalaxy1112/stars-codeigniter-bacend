let _ = require("lodash");
let UsersManager = require("../lib/UsersManager");
let Utils = require("../lib/Utils");
let Const = require("../const");
let SocketHandlerBase = require("./SocketHandlerBase");
let Settings = require("../lib/Settings");
let RoomModel = require("../models/RoomModel");
let MessageModel = require("../models/MessageModel");

let LoginActionHandler = function () {};
_.extend(LoginActionHandler.prototype, SocketHandlerBase.prototype);

LoginActionHandler.prototype.attach = function (io, socket) {
  socket.on(Const.SOCKET_LOGIN, function (param) {
    if (Utils.isEmpty(param.user_id)) {
      console.log("LoginActionHandler.js:19 ", Const.SOCKET_ERROR);
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketLoginNoUserID,
      });
      return;
    }
    if (Utils.isEmpty(param.room_id)) {
      console.log(
        "LoginActionHandler.js:25 ",
        Const.SOCKET_ERROR + param.room_id
      );
      socket.emit(Const.SOCKET_ERROR, {
        code: Const.resCodeSocketLoginNoRoomID,
      });
      return;
    }

    socket.join(param.room_id);
    param.id = param.user_id;
    param.socket_id = socket.id;
    io.of(Settings.options.socketNameSpace)
      .in(param.room_id)
      .emit(Const.SOCKET_NEW_USER, param);

    console.log("LoginActionHandler.js:44 user login param - ", param);

    UsersManager.addUser(param.user_id, param.room_id, param.socket_id);
    UsersManager.pairSocketIDandUserID(param.user_id, socket.id);

    if (Settings.options.sendAttendanceMessage) {
      // save to database
      let message = {
        user_id: param.user_id,
        room_id: param.room_id,
        message: "",
        type: Const.MESSAGE_NEW_USER,
        created_at: Utils.now(),
      };
      io.of(Settings.options.socketNameSpace)
        .in(param.room_id)
        .emit(Const.SOCKET_NEW_MESSAGE, message);
    }

    let param1 = {
      room_id: param.room_id,
      last_message_id: 0xfffffffe,
      count_per_page: 20,
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
          .to(socket.id)
          .emit(Const.SOCKET_FETCH_MESSAGE_LIST, response);
      } else {
        const response = {
          status: 1,
          data: {
            total_count: 0,
            message_list: [],
          },
        };
        io.of(Settings.options.socketNameSpace)
          .to(socket.id)
          .emit(Const.SOCKET_FETCH_MESSAGE_LIST, response);
      }
    });
  });
};

module["exports"] = new LoginActionHandler();
