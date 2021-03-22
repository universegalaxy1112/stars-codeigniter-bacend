let _ = require("lodash");
let Const = require("../const");
let init = require("../init.js");
let mysql = require("mysql");
let Util = require("../lib/Utils");
let WebAPIManager = require("../lib/WebAPIManager");
let UrlGenerator = require("../lib/UrlGenerator");
let UserModel = require("./UserModel");

let MessageModel = function () {};
MessageModel.prototype.connection = null;
MessageModel.prototype.init = function (connection) {
  this.connection = connection;
  return this.connection;
};

MessageModel.prototype.initConnection = function () {
  this.connection = mysql.createConnection({
    host: init.dbHost,
    user: init.dbUser,
    password: init.dbPassword,
    database: init.dbName,
    charset: "utf8mb4",
  });
};

MessageModel.prototype.saveNewMessage = function (param, callback) {  
  let query = "INSERT INTO chat_messages SET ?";
  let savedConnection = this.connection;
  this.connection.query(query, param, function (error, results, fields) {
    if (error) {
      callback(error, null);
    } else {      
      callback(error, results.insertId);
    }
  });
};

MessageModel.prototype.sendPushToOfflineUsers = function (message, callback) {
  //console.log("MessageModel.js:32", UrlGenerator.sendPushToOfflineUsers());
  console.log("MessageModel.js:41", message);
  WebAPIManager.post(
    UrlGenerator.sendPushToOfflineUsers(),
    message,
    function (data) {
      callback(null, data);
    },
    function (error) {
      callback(error, null);
    }
  );
};

MessageModel.prototype.findMessageById = function (id, callback) {
  let query = "SELECT * FROM chat_messages WHERE id = " + id;
  if (!this.connection) {
    this.initConnection();
  }
  this.connection.query(query, function (error, results, fields) {
    if (error) {
      callback(error, null);
    } else {
      if (results.length > 0) {
        let message = results[0];
        UserModel.findUserById(message.user_id, function (error1, user) {
          if (error1) console.error("MessageModel.js:61 - error - ", error1);
          message.user = user;
          MessageSeenModel.findByMessageId(id, function (error2, seenBy) {
            if (error2) console.error("MessageModel.js:64 - error - ", error2);
            message.seenBy = seenBy;
            if (message.type == 2) {
              FileModel.findFileById(message.message, function (error3, file) {
                if (error3)
                  console.error("MessageModel.js:68 - error - ", error3);
                if (file) {
                  if (file.thumb_id > 0) {
                    FileModel.findFileById(file.thumb_id, function (
                      error4,
                      file1
                    ) {
                      if (error4)
                        console.error("MessageModel.js:72 - error - ", error4);
                      message.file = { file: file, thumb: file1 };
                      callback(null, message);
                    });
                  } else {
                    message.file = { file: file };
                    callback(null, message);
                  }
                } else {
                  callback(null, message);
                }
              });
            } else {
              callback(null, message);
            }
          });
        });
      } else {
        callback(Const.resCodeInvalidMessageID, null);
      }
    }
  });
};

MessageModel.prototype.deleteMessageById = function (id, callback) {
  let query = "UPDATE chat_messages SET ? WHERE id = ? ";
  let post = {
    message_status: 0,
    deleted_at: Util.now(),
  };
  if (!this.connection) {
    this.initConnection();
  }
  this.connection.query(query, [post, id], function (error, results, fields) {
    callback(error, results);
  });
};

MessageModel.prototype.populateMessages = function (messages, callback) {
  if (Utils.isEmpty(messages)) {
    callback(Const.resCodeMessageNull, messages);
    return;
  }

  if (!_.isArray(messages)) {
    messages = [messages];
  }

  let ids = [];
  messages.forEach(function (row) {
    _.forEach(row.seenBy, function (row2) {
      ids.push(row2.user_id);
    });

    if (_.indexOf(ids, row.user_id) == -1) {
      ids.push(row.user_id);
    }
  });

  //console.log("MessageModel.js:147", ids);
  UserModel.findUsersByIds(ids, function (err, users) {
    let resultAry = [];
    _.forEach(messages, function (
      messageElement,
      messageIndex,
      messagesEntity
    ) {
      let obj = messageElement;
      _.forEach(users, function (userElement, userIndex) {
        // replace user to userObj
        if (messageElement.user_id.toString() == userElement.id.toString()) {
          //console.log("MessageModel.js:155", userElement);
          obj.user = userElement;
        }
      });

      let seenByAry = [];
      // replace seenby.user to userObj
      _.forEach(messageElement.seenBy, function (seenByRow) {
        _.forEach(users, function (userElement, userIndex) {
          // replace user to userObj
          if (seenByRow.user_id.toString() == userElement.id.toString()) {
            seenByAry.push({
              user_id: userElement.id,
              user: userElement,
              created_at: seenByRow.created_at,
              seen_status: 1,
            });
          }
        });
      });

      obj.seenBy = seenByAry;
      resultAry.push(obj);
    });

    callback(err, resultAry);
  });
};

MessageModel.prototype.findInitMessageList = function (param, callback) {
  var query = `SELECT
                  t1.*,
                  t2.username AS sender_name,
                  t2.photo AS sender_photo,
                  t3.username AS receiver_name,
                  t3.photo AS receiver_photo
                FROM
                  chat_messages AS t1
                  LEFT JOIN users AS t2 ON t1.sender_id = t2.id
                  LEFT JOIN users AS t3 ON t1.receiver_id = t3.id
                WHERE
                  t1.room_id = '${param.room_id}'
                  AND t1.id < ${param.last_message_id}
                ORDER BY
                  t1.created_at DESC
                LIMIT
                  ${param.count_per_page}`;
  let savedConnection = this.connection;
  this.connection.query(query, function (error, results, fields) {
    if (error) {
      callback(error, null);
      return;
    } else {
      if (results.length > 0) {
        query = `SELECT COUNT(*) AS total_count FROM chat_messages 
        WHERE room_id = '${param.room_id}' AND id < ${param.last_message_id} 
        ORDER BY created_at DESC LIMIT ${param.count_per_page}`;
        savedConnection.query(query, function (error1, results1, fields) {
          if (error1) {
            callback(error1, null);
            return;
          } else {
            newResults = {
              message_list: results,
              total_count: results1[0].total_count,
            };

            callback(error, newResults);
          }
        });
      } else {
        callback(error, null);
      }
    }
  });
};

module["exports"] = new MessageModel();
