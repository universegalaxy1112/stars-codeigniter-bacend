let socket = require("socket.io");
let express = require("express");
let http = require("http");
let init = require("./init.js");

let app = express();
let server = http.createServer(app);
let io = socket.listen(server);

// start chat stream as stand alone server
let stream = require("./index.js");
let streamServer = new stream(app, io, init);

server.listen(init.port, function () {
  console.log("--- crn_dev --- server_listening_port:", +init.port);
});
