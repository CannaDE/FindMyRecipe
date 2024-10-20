/**
 * Dispatch requests to `DatabaseObjectAction` actions with a
 * `Promise`-based API and full IDE support.
 */
define(["require", "exports", "tslib", "Ajax/Error", "Ajax/Status", "Core", "Dom/Listener"], function (require, exports, tslib_1, Error_1, AjaxStatus, Core, Listener_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", {value: true});
    exports.DboAction = void 0;

    class DboAction {
        constructor(actionName, className) {
            this._objectIDs = [];
            this._payload = {};
            this._showLoadingIndicator = true;
            this._signal = undefined;
            this.actionName = actionName;
            this.className = className;
        }

        static prepare(actionName, className) {
            return new DboAction(actionName, className);
        }

        getAbortController() {
            if (this._signal === undefined) {
                this._signal = new AbortController();
            }
            return this._signal;
        }

        objectIds(objectIds) {
            this._objectIDs = objectIds;
            return this;
        }

        payload(payload) {
            this._payload = payload;
            return this;
        }

        disableLoadingIndicator() {
            this._showLoadingIndicator = false;
            return this;
        }

        async dispatch() {
            (0, Error_1.registerGlobalRejectionHandler)();
            const url = window.API_URL + "index.php?ajax-proxy/&x=" + Core.getXsrfToken();
            const body = {
                actionName: this.actionName,
                className: this.className,
            };
            if (this._objectIDs) {
                body.objectIDs = this._objectIDs;
            }
            if (this._payload) {
                body.parameters = this._payload;
            }
            const init = {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-XSRF-TOKEN": Core.getXsrfToken(),
                },
                body: Core.serialize(body),
                mode: "same-origin",
                credentials: "same-origin",
                cache: "no-store",
                redirect: "error",
            };
            if (this._signal) {
                init.signal = this._signal.signal;
            }
            // Use a local copy to isolate the behavior in case of changes before
            // the request handling has completed.
            const showLoadingIndicator = this._showLoadingIndicator;
            if (showLoadingIndicator) {
                AjaxStatus.show();
            }
            try {
                const response = await fetch(url, init);
                if (!response.ok) {
                    throw new Error_1.StatusNotOk(response);
                }
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error_1.ExpectedJson(response);
                }
                let json;
                try {
                    json = await response.json();
                } catch (e) {
                    throw new Error_1.InvalidJson(response);
                }
                return json.returnValues;
            } catch (error) {
                if (error instanceof Error_1.ApiError) {
                    throw error;
                } else {
                    // Re-package the error for use in our global "unhandledrejection" handler.
                    throw new Error_1.ConnectionError(error);
                }
            } finally {
                if (showLoadingIndicator) {
                    AjaxStatus.hide();
                }
                Listener_1.default.trigger();
                // fix anchor tags generated through WCF::getAnchor()
                document.querySelectorAll('a[href*="#"]').forEach((link) => {
                    let href = link.href;
                    if (href.indexOf("AJAXProxy") !== -1 || href.indexOf("ajax-proxy") !== -1) {
                        href = href.substr(href.indexOf("#"));
                        link.href = document.location.toString().replace(/#.*/, "") + href;
                    }
                });
            }
        }
    }

    exports.DboAction = DboAction;
    exports.default = DboAction;
});