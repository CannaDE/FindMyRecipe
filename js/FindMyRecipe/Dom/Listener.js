define(["require", "exports", "tslib", "CallbackList"], function (require, exports, tslib_1, CallbackList_1) {
    "use strict";
    CallbackList_1 = tslib_1.__importDefault(CallbackList_1);
    const _callbackList = new CallbackList_1.default();
    let _hot = false;
    return {
        /**
         * @see CallbackList.add
         */
        add: _callbackList.add.bind(_callbackList),
        /**
         * @see CallbackList.remove
         */
        remove: _callbackList.remove.bind(_callbackList),
        /**
         * Triggers the execution of all the listeners.
         * Use this function when you added new elements to the DOM that might
         * be relevant to others.
         * While this function is in progress further calls to it will be ignored.
         */
        trigger() {
            if (_hot)
                return;
            try {
                _hot = true;
                _callbackList.forEach(null, (callback) => callback());
            } finally {
                _hot = false;
            }
        },
    };
});