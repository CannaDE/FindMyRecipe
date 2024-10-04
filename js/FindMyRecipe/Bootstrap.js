define(["require", "exports", "tslib", "Environment", "Ui/Notification", "Ui/Dialog", "Ui/Tooltip"], function(require, exports, tslib, Environment, Notification, Dialog, Tooltip) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Dialog = tslib.__importDefault(Dialog);
    Tooltip = tslib.__importDefault(Tooltip);

    function setup() {
        document.querySelectorAll("form[method=get]").forEach((form) => {
            form.method = "post";
        });

        Environment.setup();
        Notification.show("Das ist ein Test");
        Dialog.default.setup();
        Tooltip.setup();

        

        if(Environment.browser() === "microsoft") {
            window.onbeforeunload = () => {
                /* Prevent "Back navigation caching" (http://msdn.microsoft.com/en-us/library/ie/dn265017%28v=vs.85%29.aspx) */
            };
        }
    }
    exports.setup = setup;   

    function setThemeBasedOnSystemPreference() {
        let prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");
        const htmlElement = document.documentElement;
        if(prefersDarkScheme)
            htmlElement.dataset.colorSheme = "dark";
        else
            htmlElement.dataset.colorSheme = "light";
    }
});
