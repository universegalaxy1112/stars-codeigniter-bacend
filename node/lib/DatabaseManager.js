let init = require("../init.js");
let mysql = require("mysql");

let connection = mysql.createConnection({
  host: init.dbHost,
  user: init.dbUser,
  password: init.dbPassword,
  database: init.dbName,
  charset: "utf8mb4",
});

let DatabaseManager = {
  roomModel: null,
  messageModel: null,
  userModel: null,

  init: function () {
    this.roomModel = require("../models/RoomModel");
    this.roomModel.init(connection);

    this.userModel = require("../models/UserModel");
    this.userModel.init(connection);

    this.messageModel = require("../models/MessageModel");
    this.messageModel.init(connection);
  },
};

module["exports"] = DatabaseManager;
