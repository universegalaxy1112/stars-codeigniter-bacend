let init = require('./init.js');
let express = require('express');
let _ = require('lodash');
let Settings = require('./lib/Settings');

let stream = function(app, io, options) {
    Settings.options = _.merge(init, options.config);
    Settings.listeners = options.listeners;

    let DatabaseManager = require('./lib/DatabaseManager');
    DatabaseManager.init();

    let WebAPIHandlerV1 = require('./web_api/WebAPIHandlerV1');
    WebAPIHandlerV1.init(app, express);

    let SocketAPIHandler = require('./socket_api/SocketAPIHandler');
    SocketAPIHandler.init(io);
};

stream.prototype.options = {};
module.exports = stream;