let _ = require("lodash");
let Const = require("../const.js");
let Settings = require("../lib/Settings");
let Utils = require("../lib/Utils");
let init = require("../init.js");
let mysql = require("mysql");

let UserModel = function () {};
UserModel.prototype.connection = null;
UserModel.prototype.init = function (connection) {
  this.connection = connection;
  return this.connection;
};

UserModel.prototype.initConnection = function () {
  this.connection = mysql.createConnection({
    host: init.dbHost,
    user: init.dbUser,
    password: init.dbPassword,
    database: init.dbName,
    charset: "utf8mb4",
  });
};

UserModel.prototype.findUserById = function (id, callBack) {
  let query = "SELECT * FROM users WHERE id = " + id;
  if (!this.connection) {
    this.initConnection();
  }
  this.connection.query(query, function (error, results, fields) {
    if (error) {
      console.error("UserModel.js:22 - error", error);
      callBack(true, null);
      return;
    }

    //console.log('UserModel.js:22 - ', results[0].username);
    let user = results[0];
    if (user.photo && !_.includes(user.photo, "http://")) {
      user.avatarURL = Settings.options.avatarPath + user.photo;
    } else {
      user.avatarURL = user.photo;
    }

    Utils.stripPrivacyParams(user);
    callBack(false, user);
  });
};

UserModel.prototype.findUsersByIds = function (aryId, callBack) {
  let userIDs = "";
  //console.log("UserModel.js:29", aryId);
  if (aryId.length === 0) {
    callBack(Const.resCodeLoginNoRoomID, []);
    return;
  }

  aryId.forEach(function (userID) {
    if (Utils.isEmpty(userIDs)) {
      userIDs = userID;
    } else {
      userIDs += "," + userID;
    }
  });

  if (Utils.isEmpty(userIDs)) {
    callBack(Const.resCodeLoginNoUserID, []);
    return;
  }

  //console.log("UserModel.js:43", {ids: userIDs});
  let query = "SELECT * FROM users WHERE id IN (" + userIDs + ")";
  if (!this.connection) {
    this.initConnection();
  }
  this.connection.query(query, function (error, results, fields) {
    if (error) {
      console.error("UserModel.js:73 - error", error);
      callBack(true, null);
      return;
    }

    //console.log('UserModel.js:50 - ', results);
    let users = [];
    _.forEach(results, function (row) {
      if (row.photo && !_.includes(row.photo, "http://")) {
        row.photo = Settings.options.avatarPath + row.photo;
      }
      users.push(row);
    });
    Utils.stripPrivacyParamsFromArray(users);
    callBack(false, users);
  });
};

module["exports"] = new UserModel();
