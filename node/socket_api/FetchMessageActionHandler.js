let _ = require("lodash");
let async = require("async");

let Const = require("../const");
let Utils = require("../lib/Utils");
let Settings = require("../lib/Settings");

let SocketHandlerBase = require("./SocketHandlerBase");
let MessageModel = require("../models/MessageModel");

let FetchMessageActionHandler = function () {};

_.extend(FetchMessageActionHandler.prototype, SocketHandlerBase.prototype);

FetchMessageActionHandler.prototype.attach = function (io, socket) {
  socket.on(Const.SOCKET_FETCH_MESSAGE_LIST, function (param) {
    console.log("--- crn_dev --- 001:");
    if (
      Utils.isEmpty(param.room_id) ||
      Utils.isEmpty(param.last_message_id) ||
      Utils.isEmpty(param.count_per_page)
    ) {
      console.log("--- crn_dev --- error_no_room_id:", param);
      socket.emit(Const.SOCKET_ERROR, { code: Const.resCodeParamError });
      return;
    }

    MessageModel.findInitMessageList(param, function (error, results) {
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

module["exports"] = new FetchMessageActionHandler();
