/**
 * Provides the basic core functionality.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Core (alias)
 * @module  WoltLabSuite/Core/Core
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.interactWithRedactor = exports.getXsrfToken = exports.enableLegacyInheritance = exports.debounce = exports.stringToBool = exports.getStoragePrefix = exports.triggerEvent = exports.serialize = exports.getUuid = exports.getType = exports.isPlainObject = exports.inherit = exports.extend = exports.convertLegacyUrl = exports.clone = void 0;
    const _clone = function (variable) {
        if (typeof variable === "object" && (Array.isArray(variable) || isPlainObject(variable))) {
            return _cloneObject(variable);
        }
        return variable;
    };
    const _cloneObject = function (obj) {
        if (!obj) {
            return null;
        }
        if (Array.isArray(obj)) {
            return obj.slice();
        }
        const newObj = {};
        Object.keys(obj).forEach((key) => (newObj[key] = _clone(obj[key])));
        return newObj;
    };
    const _prefix = "cfw" + window.CFW_PATH + "-";
    let _blockFocusIn = false;
    document.addEventListener("focusin", (event) => {
        if (_blockFocusIn) {
            event.stopImmediatePropagation();
        }
    }, true);
    /**
     * Deep clones an object.
     */
    function clone(obj) {
        return _clone(obj);
    }
    exports.clone = clone;
    /**
     * Converts WCF 2.0-style URLs into the default URL layout.
     */
    function convertLegacyUrl(url) {
        return url.replace(/^index\.php\/(.*?)\/\?/, (match, controller) => {
            const parts = controller.split(/([A-Z][a-z0-9]+)/);
            controller = "";
            for (let i = 0, length = parts.length; i < length; i++) {
                const part = parts[i].trim();
                if (part.length) {
                    if (controller.length) {
                        controller += "-";
                    }
                    controller += part.toLowerCase();
                }
            }
            return `index.php?${controller}/&`;
        });
    }
    exports.convertLegacyUrl = convertLegacyUrl;
    /**
     * Merges objects with the first argument.
     *
     * @param  {object}  out    destination object
     * @param  {...object}  args  variable number of objects to be merged into the destination object
     * @return  {object}  destination object with all provided objects merged into
     */
    function extend(out, ...args) {
        out = out || {};
        const newObj = clone(out);
        for (let i = 0, length = args.length; i < length; i++) {
            const obj = args[i];
            if (!obj) {
                continue;
            }
            Object.keys(obj).forEach((key) => {
                if (!Array.isArray(obj[key]) && typeof obj[key] === "object") {
                    if (isPlainObject(obj[key])) {
                        // object literals have the prototype of Object which in return has no parent prototype
                        newObj[key] = extend(out[key], obj[key]);
                    }
                    else {
                        newObj[key] = obj[key];
                    }
                }
                else {
                    newObj[key] = obj[key];
                }
            });
        }
        return newObj;
    }
    exports.extend = extend;
    /**
     * Inherits the prototype methods from one constructor to another
     * constructor.
     *
     * Usage:
     *
     * function MyDerivedClass() {}
     * Core.inherit(MyDerivedClass, TheAwesomeBaseClass, {
     *      // regular prototype for `MyDerivedClass`
     *
     *      overwrittenMethodFromBaseClass: function(foo, bar) {
     *              // do stuff
     *
     *              // invoke parent
     *              MyDerivedClass._super.prototype.overwrittenMethodFromBaseClass.call(this, foo, bar);
     *      }
     * });
     *
     * @see  https://github.com/nodejs/node/blob/7d14dd9b5e78faabb95d454a79faa513d0bbc2a5/lib/util.js#L697-L735
     * @deprecated 5.4 Use the native `class` and `extends` keywords instead.
     */
    function inherit(constructor, superConstructor, propertiesObject) {
        if (constructor === undefined || constructor === null) {
            throw new TypeError("The constructor must not be undefined or null.");
        }
        if (superConstructor === undefined || superConstructor === null) {
            throw new TypeError("The super constructor must not be undefined or null.");
        }
        if (superConstructor.prototype === undefined) {
            throw new TypeError("The super constructor must have a prototype.");
        }
        constructor._super = superConstructor;
        constructor.prototype = extend(Object.create(superConstructor.prototype, {
            constructor: {
                configurable: true,
                enumerable: false,
                value: constructor,
                writable: true,
            },
        }), propertiesObject || {});
    }
    exports.inherit = inherit;
    /**
     * Returns true if `obj` is an object literal.
     */
    function isPlainObject(obj) {
        if (typeof obj !== "object" || obj === null) {
            return false;
        }
        return Object.getPrototypeOf(obj) === Object.prototype;
    }
    exports.isPlainObject = isPlainObject;
    /**
     * Returns the object's class name.
     */
    function getType(obj) {
        return Object.prototype.toString.call(obj).replace(/^\[object (.+)]$/, "$1");
    }
    exports.getType = getType;
    /**
     * Returns a RFC4122 version 4 compilant UUID.
     *
     * @see    http://stackoverflow.com/a/2117523
     */
    function getUuid() {
        return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
            const r = (Math.random() * 16) | 0, v = c == "x" ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }
    exports.getUuid = getUuid;
    /**
     * Recursively serializes an object into an encoded URI parameter string.
     */
    function serialize(obj, prefix) {
        if (obj === null) {
            return "";
        }
        const parameters = [];
        Object.keys(obj).forEach((key) => {
            const parameterKey = prefix ? prefix + "[" + key + "]" : key;
            const value = obj[key];
            if (typeof value === "object") {
                parameters.push(serialize(value, parameterKey));
            }
            else {
                parameters.push(encodeURIComponent(parameterKey) + "=" + encodeURIComponent(value));
            }
        });
        return parameters.join("&");
    }
    exports.serialize = serialize;
    /**
     * Triggers a custom or built-in event.
     */
    function triggerEvent(element, eventName) {
        if (eventName === "click" && element instanceof HTMLElement) {
            element.click();
            return;
        }
        const event = new Event(eventName, {
            bubbles: true,
            cancelable: true,
        });
        element.dispatchEvent(event);
    }
    exports.triggerEvent = triggerEvent;
    /**
     * Returns the unique prefix for the localStorage.
     */
    function getStoragePrefix() {
        return _prefix;
    }
    exports.getStoragePrefix = getStoragePrefix;
    /**
     * Interprets a string value as a boolean value similar to the behavior of the
     * legacy functions `elAttrBool()` and `elDataBool()`.
     */
    function stringToBool(value) {
        return value === "1" || value === "true";
    }
    exports.stringToBool = stringToBool;

    function enableLegacyInheritance(legacyClass) {
        legacyClass.call = function (thisValue, ...args) {
            if (window.ENABLE_DEVELOPER_TOOLS) {
                console.log("Relying on legacy inheritance for ", legacyClass, thisValue);
            }
            const constructed = Reflect.construct(legacyClass, args, thisValue.constructor);
            Object.entries(constructed).forEach(([key, value]) => {
                if (typeof value === "function") {
                    value = value.bind(thisValue);
                }
                thisValue[key] = value;
            });
            for (const key in thisValue) {
                if (typeof thisValue[key] === "function") {
                    constructed[key] = thisValue[key].bind(thisValue);
                }
            }
        };
    }
    exports.enableLegacyInheritance = enableLegacyInheritance;
    /**
     * A function that emits a side effect and does not return anything.
     *
     * @see https://github.com/chodorowicz/ts-debounce/blob/62f30f2c3379b7b5e778fb1793e1fbfa17354894/src/index.ts
     */
    function debounce(func, waitMilliseconds = 50, options = {
        isImmediate: false,
    }) {
        let timeoutId;
        return function (...args) {
            const doLater = () => {
                timeoutId = undefined;
                if (!options.isImmediate) {
                    func.apply(this, args);
                }
            };
            const shouldCallNow = options.isImmediate && timeoutId === undefined;
            if (timeoutId !== undefined) {
                clearTimeout(timeoutId);
            }
            timeoutId = setTimeout(doLater, waitMilliseconds);
            if (shouldCallNow) {
                func.apply(this, args);
            }
        };
    }
    exports.debounce = debounce;

    function getXsrfToken() {
        const cookies = document.cookie.split(";").map((c) => c.trim());
        const xsrfToken = cookies.find((c) => c.startsWith("XSRF_TOKEN="));
        if (xsrfToken === undefined) {
            return "COOKIE_NOT_FOUND";
        }
        const [_key, value] = xsrfToken.split(/=/, 2);
        return decodeURIComponent(value.trim());
    }
    exports.getXsrfToken = getXsrfToken;
});