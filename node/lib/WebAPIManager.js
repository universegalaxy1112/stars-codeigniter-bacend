let CONST = require("../const");
let _ = require("lodash");
let request = require("request");

(function (global) {
  let WebAPIManager = {
    post: function (url, data, onSuccess, onError) {
      // console.log("WebAPIManager.js:8 - post_data: ", data);
      request(
        {
          uri: url,
          method: "POST",
          // agent: true,
          // pool: {maxSockets: 500},
          // timeout: 10000,
          followRedirect: true,
          maxRedirects: 10,
          json: data,
        },
        function (error, response, body) {
          if (!error) {
            //console.log('WebAPIManager.js:18 - body', body);
            let resObj = {};
            if (typeof body === "object") {
              resObj = body;
            } else {
              console.log("WebAPIManager.js:23 - url", url);
              console.log("WebAPIManager.js:25 - body", body);
              resObj = JSON.parse(body);
            }
            let errorCode = resObj.status;
            //console.log('WebAPIManager.js:26 - errorCode', errorCode);

            // server handled error
            if (errorCode != 1) {
              let message = CONST.ERROR_CODES[errorCode];
              console.log("WebAPIManager.js:35", message);
              if (!_.isUndefined(onError)) {
                console.log("WebAPIManager.js:37", error);
                onError();
              }
            } else {
              if (!_.isUndefined(onSuccess)) onSuccess(resObj.data);
            }
          } else {
            console.log("WebAPIManager.js:43 - url: ", url);
            console.log("WebAPIManager.js:44", error);
            if (!_.isUndefined(onError)) {
              onError();
            }
          }
        }
      );
    },

    get: function (url, onSuccess, onError) {
      request(
        {
          uri: url,
          method: "GET",
          // timeout: 10000,
          followRedirect: true,
          maxRedirects: 10,
        },
        function (error, response, body) {
          if (!error) {
            //console.log('WebAPIManager.js:59 - body', body);
            try {
              let resObj = JSON.parse(body);
              let errorCode = resObj.status;
              // server handled error
              if (errorCode != 1) {
                console.log("WebAPIManager.js:64 - errorCode", errorCode);
                let message = CONST.ERROR_CODES[errorCode];
                console.log("WebAPIManager.js:66 - ", url);
                console.log("WebAPIManager.js:67 - ", body);
              }

              if (errorCode == 1) {
                if (!_.isUndefined(onSuccess)) onSuccess(resObj.data);
              }
            } catch (err) {
              console.error("WebAPIManager.js:76 - ", url);
              console.error("WebAPIManager.js:77 - body", body);
              if (!_.isUndefined(onError)) {
                onError();
              }
            }
          } else {
            console.log("WebAPIManager.js:83 - ", url);
            console.log("WebAPIManager.js:84 - ", error);
            if (!_.isUndefined(onError)) {
              onError();
            }
          }
        }
      );
    },
  };

  // Exports ----------------------------------------------
  module["exports"] = WebAPIManager;
})((this || 0).self || global);
