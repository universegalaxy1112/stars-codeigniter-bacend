let express = require("express");
let router = express.Router();
let bodyParser = require("body-parser");
let Settings = require("../lib/Settings");

let WebAPIHandler = {
  init: function (app, express) {
    app.set("port", 5000);
    app.use(
      Settings.options.urlPrefix,
      express.static(__dirname + "/../../../public/client")
    );
    app.use(bodyParser.json());

    // HTTP Api Routes
    router.use("/v1/user/rooms", require("./RoomsUserListHandler"));
    router.use("/v1/user/room", require("./RoomUserListHandler"));    
    

    WebAPIHandler.router = router;
    app.use(Settings.options.urlPrefix, router);
  },
};

module["exports"] = WebAPIHandler;
