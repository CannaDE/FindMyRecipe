/**
 * Allows to be informed when a click event bubbled up to the document's body.
 */
define(["require", "exports", "tslib", "CallbackList"], function (require, exports, tslib, CallbackList) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.execute = exports.remove = exports.add = exports.Origin = void 0;
    CallbackList = tslib.__importDefault(CallbackList);
    const _callbackList = new CallbackList.default();
    var Origin;
    (function (Origin) {
        Origin["Document"] = "document";
        Origin["DropDown"] = "dropdown";
        Origin["Search"] = "search";
    })(Origin = exports.Origin || (exports.Origin = {}));
    let hasGlobalListener = false;
    function add(identifier, callback) {
        _callbackList.add(identifier, callback);
        if (!hasGlobalListener) {
            document.body.addEventListener("click", () => {
                execute(Origin.Document);
            });
            hasGlobalListener = true;
        }
    }
    exports.add = add;
    function remove(identifier) {
        _callbackList.remove(identifier);
    }
    exports.remove = remove;
    function execute(origin, identifier) {
        _callbackList.forEach(null, (callback) => callback(origin, identifier));
    }
    exports.execute = execute;
});