/**
 * Provides the AJAX status overlay.
 */
define(["require", "exports", "tslib", "Dom/Util"], function (require, exports, tslib_1, Util) {
    "use strict";
    Object.defineProperty(exports, "__esModule", {value: true});
    exports.hide = exports.show = void 0;

    class AjaxStatus {
        _activeRequests = 0;
        _overlay;
        _timer = null;

        _menuHider;

        constructor() {

            this._dialogPageOverlay = document.querySelector('.dialogPageOverlay');

            this._container = document.createElement("div");
            this._container.classList.add("ajaxRequestLoading");
            this._container.setAttribute("aria-hidden", "true");
            this._container.setAttribute("role", "status");
            this._container.id = "ajaxRequestLoading";

            const icon = document.createElement("span");
            icon.classList.add("spinner-border", "color-highlight");
            icon.style.borderWidth = "5px";
            icon.style.width = "3rem";
            icon.style.height = "3rem";

            this._container.append(icon);

            const title = document.createElement("span");
            title.classList.add();
            title.textContent = "Lädt...";
            this._container.appendChild(title);

            this._dialogPageOverlay.appendChild(this._container);


        }

        show() {
            this._activeRequests++;
            if (this._timer === null) {
                this._timer = window.setTimeout(() => {
                    if (this._activeRequests) {
                        this._dialogPageOverlay.setAttribute("aria-hidden", "false");
                        this._dialogPageOverlay.setAttribute("close-on-click", "false");
                        this._container.setAttribute("aria-hidden", "false");

                    }
                    this._timer = null;
                }, 250);
            }
        }

        hide() {
            if (--this._activeRequests === 0) {
                if (this._timer !== null) {
                    window.clearTimeout(this._timer);
                    this._timer = null;
                }
            }
            this._dialogPageOverlay.setAttribute("aria-hidden", "true");
            this._container.setAttribute("aria-hidden", "true");
        }
    }

    let status;

    function getStatus() {
        if (status === undefined) {
            status = new AjaxStatus();
        }
        return status;
    }

    /**
     * Shows the loading overlay.
     */
    function show() {
        getStatus().show();
    }

    exports.show = show;

    /**
     * Hides the loading overlay.
     */
    function hide() {
        getStatus().hide();
    }

    exports.hide = hide;
});