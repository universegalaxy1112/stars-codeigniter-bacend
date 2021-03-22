let _ = require("lodash");

let StreamManager = {
  rooms: {},

  addUser: function (param) {
    let user = {
      user_id: param.user_id,
      room_id: param.room_id,
      socket_id: param.socket_id,
    };

    if (_.isUndefined(this.rooms[param.room_id])) {
      this.rooms[param.room_id] = {};
    }

    if (_.isEmpty(this.rooms[param.room_id])) {
      this.rooms[param.room_id] = {
        users: {},
      };
    }

    if (_.isUndefined(this.rooms[param.room_id].users[param.user_id]))
      this.rooms[param.room_id].users[param.user_id] = user;

    this.rooms[param.room_id].users[param.user_id] = user;
  },

  removeUser: function (room_id, user_id) {
    if (room_id.startsWith(user_id + "_")) {
      delete this.rooms[room_id];
      return;
    }
    delete this.rooms[room_id].users[user_id];
  },

  getRoomIds: function () {
    let roomIds = [];
    _.forEach(this.rooms, function (row, key) {
      roomIds.push(key);
    });
    return roomIds;
  },

  getRoomIdsWhichHasUsers: function () {
    let roomIds = [];
    _.forEach(this.rooms, function (row, key) {
      if (_.size(row.users) > 0) {
        roomIds.push(key);
      }
    });
    return roomIds;
  },

  getUsers: function (room_id) {
    if (!this.rooms[room_id]) this.rooms[room_id] = {};

    let users = this.rooms[room_id].users;

    // change to array
    let usersAry = [];
    _.forEach(users, function (row, key) {
      usersAry.push(row);
    });

    return usersAry;
  },

  getRoomByUserID: function (user_id) {
    let roomsAry = [];
    _.forEach(this.rooms, function (room, room_id) {
      _.forEach(room.users, function (user, key) {
        if (user.user_id == user_id) roomsAry.push(room_id);
      });
    });
    return roomsAry;
  },

  pairSocketIDandUserID: function (user_id, socket_id) {
    _.forEach(this.rooms, function (room, room_id) {
      _.forEach(room.users, function (user) {
        if (user.user_id == user_id) user.socket_id = socket_id;
      });
    });
  },

  getUserBySocketID: function (socket_id) {
    let userResult = null;
    
    _.forEach(this.rooms, function (room, room_id) {
      _.forEach(room.users, function (user) {
        if (user.socket_id == socket_id) userResult = user;
      });
    });

    return userResult;
  },

  getRoomBySocketID: function (socket_id) {
    let roomResult = null;
    _.forEach(this.rooms, function (room, room_id) {
      _.forEach(room.users, function (user) {
        if (user.socket_id == socket_id) roomResult = room_id;
      });
    });
    return roomResult;
  },
};

module["exports"] = StreamManager;
