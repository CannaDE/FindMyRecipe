define(["require", "exports", "tslib", "Ajax/Request", "Core", "Ajax/DbAction"], function (require, exports, tslib, Request, Core, DbAction) {
    "use strict";
    Object.defineProperty(exports, "__esModule", {value: true});
    exports.dboAction = exports.getRequestObject = exports.apiOnce = exports.api = void 0;
    Request = tslib.__importDefault(Request);
    Core = tslib.__importStar(Core);
    const _cache = new WeakMap();

    /**
     * Shorthand function to perform a request against the WCF-API with overrides
     * for success and failure callbacks.
     */
    function api(callbackObject, data, success, failure) {
        if (typeof data !== "object")
            data = {};
        let request = _cache.get(callbackObject);
        if (request === undefined) {
            if (typeof callbackObject._ajaxSetup !== "function") {
                throw new TypeError("Callback object must implement at least _ajaxSetup().");
            }
            const options = callbackObject._ajaxSetup();
            options.pinData = true;
            options.callbackObject = callbackObject;
            if (!options.url) {
                options.url = "/ajax-proxy/&x=" + Core.getXsrfToken();
                options.withCredentials = true;
            }
            request = new Request.default(options);
            _cache.set(callbackObject, request);
        }
        let oldSuccess = null;
        let oldFailure = null;
        if (typeof success === "function") {
            oldSuccess = request.getOption("success");
            request.setOption("success", success);
        }
        if (typeof failure === "function") {
            oldFailure = request.getOption("failure");
            request.setOption("failure", failure);
        }
        request.setData(data);
        request.sendRequest();
        // restore callbacks
        if (oldSuccess !== null)
            request.setOption("success", oldSuccess);
        if (oldFailure !== null)
            request.setOption("failure", oldFailure);

        return request;
    }

    exports.api = api;

    /**
     * Shorthand function to perform a single request against the WCF-API.
     *
     * Please use `Ajax.api` if you're about to repeatedly send requests because this
     * method will spawn an new and rather expensive `AjaxRequest` with each call.
     */
    function apiOnce(options) {
        options.pinData = false;
        options.callbackObject = null;
        if (!options.url) {
            options.url = "/ajax-proxy/&x=" + Core.getXsrfToken();
            options.withCredentials = true;
        }
        const request = new Request.default(options);
        request.sendRequest(false);
    }

    exports.apiOnce = apiOnce;

    /**
     * Returns the request object used for an earlier call to `api()`.
     */
    function getRequestObject(callbackObject) {
        if (!_cache.has(callbackObject)) {
            throw new Error("Expected a previously used callback object, provided object is unknown.");
        }
        return _cache.get(callbackObject);
    }

    exports.getRequestObject = getRequestObject;

    function dbAction(actionName, className) {
        return dbAction.default.prepare(actionName, className);
    }

    exports.dbAction = dbAction;

});