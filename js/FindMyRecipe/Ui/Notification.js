define(["require", "exports"], function(require, exports,) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.show = void 0;

    let _busy = false;
    let _callback = null;
    let _init = false;
    let _message;
    let _element;
    let _timeout;

    function init() {
        if(_init)
            return;

        _init = true;
        _element = document.createElement("div");
        _element.id = "jsNotificationToast";
        _message = document.createElement("p");
        _element.appendChild(_message);
        document.body.appendChild(_element);
        _message.addEventListener("click", hide);
    }

    function hide() {
        clearTimeout(_timeout);
        _element.classList.remove("active");
        if(_callback !== null)
            _callback();

        _busy = false;
    }

    function show(message, cssClassname, callback) {
        if(_busy)
            return;

        _busy = true;
        init();
        _callback = typeof callback === "function" ? callback : null;
        _message.className = cssClassname || "success";
        _message.textContent = message || "Die Aktion wurde ausgeführt";
        _element.classList.add("active");
        _timeout = setTimeout(hide, 3000);
    }
    exports.show = show;
});