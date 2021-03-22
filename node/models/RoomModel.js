var _ = require("lodash");
var Settings = require("../lib/Settings");
var Util = require("../lib/Utils");
var Const = require("../const");
var mysql = require("mysql");

var RoomModel = function () {};

RoomModel.prototype.connection = null;
RoomModel.prototype.init = function (connection) {
  this.connection = connection;
  return this.connection;
};

RoomModel.prototype.findRoomById = function (id, callback) {
  //console.log("RoomModel.js:14", id);
  var query = "SELECT * FROM chat_rooms WHERE id = " + id;
  this.connection.query(query, function (error, results, fields) {
    if (error) {
      console.error("RoomModel.js:18 - error", error);
      callback(error, results);
      return;
    }

    var room = results[0];
    if (room.avatar_url && !_.includes(room.avatar_url, "http://")) {
      room.avatar_url = Settings.options.roomAvatarPath + room.avatar_url;
    }
    callback(error, room);
  });
};

RoomModel.prototype.createRoom = function (roomData, callback) {
  //console.log("RoomModel.js:25", roomData);
  var query = "INSERT INTO chat_rooms SET ?";
  this.connection.query(query, roomData, function (error, results, fields) {
    if (error) {
      console.error("RoomModel.js:29 - error", error);
      callback(error, null);
      return;
    }

    roomData.id = results.insertId;
    if (roomData.avatar_url && !_.includes(roomData.avatar_url, "http://")) {
      roomData.avatar_url =
        Settings.options.roomAvatarPath + roomData.avatar_url;
    }
    callback(error, roomData);
  });
};

RoomModel.prototype.validateRoom = function (roomData, callback) {
  //console.log("RoomModel.js:25", roomData);
  var query = "INSERT IGNORE INTO chat_rooms SET ?";
  let savedConnection = this.connection;
  savedConnection.query(query, roomData, function (error, results, fields) {
    if (error) {
      console.error("RoomModel.js:57 - error", error);
      callback(error, null);
      return;
    }

    query = "SELECT * FROM chat_rooms WHERE ?";
    savedConnection.query(query, roomData, function (error, results, fields) {
      if (error) {
        console.error("RoomModel.js:38 - error", error);
        callback(error, results);
        return;
      }

      if (results.length > 0) {
        var room = results[0];
        callback(error, room);
        return;
      }

      callback(error, null);
    });
  });
};

RoomModel.prototype.updateRoom = function (roomData, callback) {
  //console.log("RoomModel.js:39", roomData);
  var query = "UPDATE SET ? FROM chat_rooms WHERE id = ? ";
  var post = {
    name: roomData.name,
    avatar_url: roomData.avatar_url ? roomData.avatar_url : "",
  };

  this.connection.query(query, [post, roomData.room_id], function (
    error,
    results,
    fields
  ) {
    if (error) {
      console.error("RoomModel.js:47 - error", error);
      callback(error, null);
      return;
    }

    roomData.id = results.insertId;
    if (roomData.avatar_url && !_.includes(roomData.avatar_url, "http://")) {
      roomData.avatar_url =
        Settings.options.roomAvatarPath + roomData.avatar_url;
    }
    callback(error, roomData);
  });
};

RoomModel.prototype.findRoomsByIds = function (aryId, callback) {
  var roomIDs = "";
  //console.log("UserModel.js:29", aryId);
  if (aryId.length == 0) {
    callback(Const.resCodeLoginNoRoomID, {});
    return;
  }

  console.log("RoomModel.js:83 room ids - ", aryId);
  aryId.forEach(function (roomID) {
    if (Util.isEmpty(roomIDs)) {
      roomIDs = roomID;
    } else {
      roomIDs += "," + roomID;
    }
  });

  //console.log("UserModel.js:43", {ids: userIDs});
  var query = "SELECT * FROM chat_rooms WHERE id IN (" + roomIDs + ")";
  if (!this.connection) {
    var init = require("../init.js");
    this.connection = mysql.createConnection({
      host: init.dbHost,
      user: init.dbUser,
      password: init.dbPassword,
      database: init.dbName,
    });
  }
  this.connection.query(query, function (error, results, fields) {
    if (error) {
      console.error("RoomModel.js:102 - error", error);
      callback(true, null);
      return;
    }

    //console.log('UserModel.js:50 - ', results);
    var rooms = [];
    _.forEach(results, function (row) {
      if (row.avatar_url && !_.includes(row.avatar_url, "http://")) {
        row.avatar_url = Settings.options.roomAvatarPath + row.avatar_url;
      }
      rooms.push(row);
    });
    callback(false, rooms);
  });
};

module["exports"] = new RoomModel();
