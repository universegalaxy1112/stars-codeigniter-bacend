let Settings = require("./Settings");
let CONST = require("../const");

(function (global) {
  let UrlGenerator = {
    sendPushToOfflineUsers: function () {
      return (
        Settings.options.apiBaseUrl + CONST.URL_POST_SEND_PUSH_TO_OFFLINE_USERS
      );
    },
    sendSSNotification: function () {
      return Settings.options.apiBaseUrl + CONST.URL_API_SEND_SS_NOTIFICATION;
    },
    saveNewMessage: function () {
      return Settings.options.apiBaseUrl + CONST.URL_POST_SAVE_NEW_MESSAGE;
    },
  };

  // Exports ----------------------------------------------
  module["exports"] = UrlGenerator;
})((this || 0).self || global);
