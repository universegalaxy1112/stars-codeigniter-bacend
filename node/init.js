(function (global) {
  "use strict;";

  // Class ------------------------------------------------
  let Config = {};

  // crn_dev
  Config.isLocal = 0;
  if (Config.isLocal) {
    console.log("--- crn_dev --- environment is LOCAL");
    Config.hostUrl = "http://192.168.1.77/";
    Config.basePath = "";
  } else {
    console.log("--- crn_dev --- environment is SERVER");
    Config.hostUrl = "http://107.180.73.164/";
    Config.basePath = "videoupload_backend/";
  }

  Config.baseUrl = Config.hostUrl + Config.basePath;
  Config.port = 5000;
  Config.urlPrefix = "/ChatStream";
  Config.urlAdminPrefix = Config.urlPrefix + "/admin";
  Config.socketNameSpace = "/ChatStream";
  Config.apiBaseUrl = Config.baseUrl + "api/";

  Config.sendAttendanceMessage = false;
  Config.assetsUrl = Config.baseUrl + "assets/uploads/";
  Config.avatarUrl = Config.assetsUrl + "profile_photos/";
  Config.roomAvatarUrl = Config.avatarUrl + "chat_room/";

  Config.dbHost = "localhost";
  Config.dbName = "stars";
  Config.dbUser = "root";
  if (Config.isLocal) {
    Config.dbPassword = "";
  } else {
    Config.dbPassword = "StarWork123!@#";
  }

  // Exports ----------------------------------------------
  module["exports"] = Config;
})((this || 0).self || global);
