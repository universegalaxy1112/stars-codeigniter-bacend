(function (global) {
  // Class ------------------------------------------------
  let Const = {};

  Const.httpCodeSucceed = 200;
  Const.httpCodeFileNotFound = 404;
  Const.httpCodeSeverError = 500;
  Const.httpCodeAuthError = 503;

  Const.responsecodeSucceed = 1;
  Const.responsecodeError = 2;
  Const.resCodeLoginNoName = 1000001;
  Const.resCodeLoginNoRoomID = 1000002;
  Const.resCodeLoginNoUserID = 1000003;
  Const.resCodeUserListNoRoomID = 1000004;
  Const.resCodeMessageListNoRoomID = 1000005;
  Const.resCodeMessageListNoLastMessageID = 1000006;
  Const.resCodeSendMessageNoFile = 1000007;
  Const.resCodeSendMessageNoRoomID = 1000008;
  Const.resCodeSendMessageNoUserID = 1000009;
  Const.resCodeSendMessageNoType = 1000010;
  Const.resCodeFileUploadNoFile = 1000011;

  Const.resCodeSocketUnknownError = 1000012;
  Const.resCodeSocketDeleteMessageNoUserID = 1000013;
  Const.resCodeSocketDeleteMessageNoMessageID = 1000014;
  Const.resCodeSocketSendMessageNoRoomID = 1000015;
  Const.resCodeSocketSendMessageNoUserID = 1000016;
  Const.resCodeSocketSendMessageNoType = 1000017;
  Const.resCodeSocketSendMessageNoMessage = 1000018;
  Const.resCodeSocketSendMessageNoFileID = 1000031;
  Const.resCodeSocketSendMessageNoFile = 1000033;
  Const.resCodeSocketSendMessageNoLocation = 1000019;
  Const.resCodeSocketSendMessageFail = 1000020;
  Const.resCodeSocketSaveMessageFail = 1000032;

  Const.resCodeSocketTypingNoUserID = 1000021;
  Const.resCodeSocketTypingNoRoomID = 1000022;
  Const.resCodeSocketTypingNoType = 1000023;
  Const.resCodeSocketTypingFaild = 1000024;

  Const.resCodeSocketLoginNoUserID = 1000025;
  Const.resCodeSocketLoginNoRoomID = 1000026;
  Const.resCodeTokenError = 1000027;

  Const.resCodeStickerListFailed = 1000028;
  Const.resCodeSocketOpenMessageNoUserID = 1000029;
  Const.resCodeSocketOpenMessageNoMessageID = 1000030;

  Const.resCodeDatabaseError = 1000029;
  Const.resCodeNotSupportedType = 1000030;

  Const.resCodeInvalidUserID = 1000031;
  Const.resCodeInvalidMessageID = 1000032;
  Const.resCodeInvalidFileID = 1000033;
  Const.resCodeMessageNull = 1000034;
  Const.resCodeInvalidMessageSeenID = 1000035;
  Const.resCodeInvalidRoomID = 1000036;
  Const.resCodeInvalidRoomUserID = 1000037;

  Const.resCodeParamError = 2001;
  Const.resCodeTokenError = 2100;

  Const.MESSAGE_TYPE_TEXT = 1;
  Const.MESSAGE_TYPE_FILE = 2;
  Const.MESSAGE_TYPE_LOCATION = 3;
  Const.MESSAGE_TYPE_CONTACT = 4;
  Const.MESSAGE_TYPE_STICKER = 5;

  Const.MESSAGE_TYPE_STREAM = 0;
  Const.MESSAGE_TYPE_COMMENT = 1;

  Const.MESSAGE_NEW_USER = 1000;
  Const.MESSAGE_USER_LEAVE = 1001;

  Const.SOCKET_ERROR = "socketerror";
  Const.SOCKET_LOGIN = "login";
  Const.SOCKET_LOGOUT = "logout";
  Const.SOCKET_NEW_USER = "newUser";
  Const.SOCKET_FETCH_MESSAGE_LIST = "FetchMessageList";
  Const.SOCKET_NEW_MESSAGE = "newMessage";
  Const.SOCKET_SEND_MESSAGE = "sendMessage";
  Const.SOCKET_SEND_TYPING = "sendTyping";
  Const.SOCKET_TYPING = "typing";
  Const.SOCKET_OPEN_MESSAGE = "openMessage";
  Const.SOCKET_UPDATE_MESSAGE = "messageUpdated";
  Const.SOCKET_DELETE_MESSAGE = "deleteMessage";
  Const.SOCKET_CHANGE_MESSAGE = "MessageChanges";
  Const.SOCKET_DELETE_DURATION_MESSAGE = "deleteDurationMessage";
  Const.SOCKET_DISCONNECT = "disconnect";
  Const.SOCKET_USER_LEFT = "userLeft";
  Const.SOCKET_JOIN_STREAM = "joinStream";
  Const.SOCKET_STREAM = "stream";
  Const.SOCKET_STREAM_COMMENT = "streamComment";

  Const.NOTIFICATION_SEND_MESSAGE = "SendMessage";
  Const.NOTIFICATION_NEW_USER = "NewUser";
  Const.NOTIFICATION_USER_LEFT = "UserLeft";
  Const.NOTIFICATION_USER_TYPING = "UserTyping";
  Const.NOTIFICATION_MESSAGE_CHANGES = "MessageChanges";
  Const.NOTIFICATION_MESSAGE_DELETED = "MessageDeleted";
  Const.NOTIFICATION_SEND_STREAM = "SendStream";
  Const.NOTIFICATION_SEND_STREAM_COMMENT = "SendStreamComment";

  Const.URL_GET_ROOM = "chat/room";
  Const.URL_POST_CREATE_ROOM = "chat/create_room";
  Const.URL_POST_UPDATE_ROOM = "chat/update_room";
  Const.URL_POST_ROOMS_BY_IDS = "chat/rooms_by_ids";
  Const.URL_GET_ROOMS_BY_USERID = "chat/rooms_list";

  Const.URL_POST_CREATE_ROOM_USER = "chat/create_room_user";
  Const.URL_GET_ROOM_USER_BY_ROOM_USER_ID = "chat/room_user_by_room_user_id";
  Const.URL_POST_ROOM_USER_BY_ROOM_ID_AND_USER_ID =
    "chat/room_user_by_room_id_and_user_id";
  Const.URL_GET_ROOM_USERS_BY_ROOM_ID = "chat/room_users_by_room_id";
  Const.URL_GET_ROOM_USERS_BY_USER_ID = "chat/room_users_by_user_id";
  Const.URL_POST_ROOM_USERS_BY_ROOM_IDS = "chat/room_users_by_room_ids";
  Const.URL_POST_ROOM_USERS_BY_USER_IDS = "chat/room_users_by_user_ids";

  Const.URL_GET_USER = "user/id";
  Const.URL_API_USERS = "user/list";
  Const.URL_POST_USERS_BY_IDS = "user/users_by_ids";

  Const.URL_POST_SAVE_NEW_MESSAGE = "chat/save_new_message";
  Const.URL_POST_SEND_PUSH_TO_OFFLINE_USERS = "chat/send_push_to_offline_users";
  Const.URL_GET_MESSAGE = "chat/message";
  Const.URL_API_PAST_MESSAGE = "chat/message_list";
  Const.URL_POST_ROOM_MESSAGE = "chat/room_messages";
  Const.URL_POST_ADD_SEEN_MESSAGE_BY = "chat/add_seen_message_by";
  Const.URL_POST_DELETE_MESSAGE = "chat/delete_message";

  Const.URL_API_UPLOAD_FILE = "chat/file/upload";
  Const.URL_API_DOWNLOAD_FILE = "chat/file/download";
  Const.URL_API_SEND_FILE = "chat/message/sendFile";
  Const.URL_API_STICKER_LIST = "chat/stickers";

  Const.URL_API_SEND_SS_NOTIFICATION = "chat/send_ss_notification"; // send notification to followers about starting music stream

  Const.ERROR_CODES = {
    1000001: "Name is not provided.",
    1000002: "Room ID is not provided.",
    1000003: "User ID is not provided.",
    1000004: "Room ID is not provided.",
    1000005: "Roomo ID is not provided.",
    1000006: "Last Meesage ID is not provided.",
    1000007: "File not provided.",
    1000008: "Room ID not provided.",
    1000009: "User ID is not provided.",
    1000010: "Type is not provided.",
    1000011: "File is not provided.",
    1000012: "Unknown Error",
    1000013: "User ID is not provided.",
    1000014: "Message ID is not provided.",
    1000015: "Room ID is not provided.",
    1000016: "User ID is not provided.",
    1000017: "Type is not provided.",
    1000018: "Message is not provided.",
    1000019: "Location is not provided.",
    1000020: "Failed to send message.",
    1000027: "Invalid token",
  };

  // Exports ----------------------------------------------
  module["exports"] = Const;
})((this || 0).self || global);
