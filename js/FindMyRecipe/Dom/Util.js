/**
 * Provides helper functions to work with DOM nodes.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Dom/Util (alias)
 * @module  WoltLabSuite/Core/Dom/Util
 */
define(["require", "exports", "tslib", "../StringUtil", "jquery-ui"], function (require, exports, tslib_1, StringUtil, $) {
    "use strict";
    StringUtil = tslib_1.__importStar(StringUtil);
    function _isBoundaryNode(element, ancestor, position) {
        if (!ancestor.contains(element)) {
            throw new Error("Ancestor element does not contain target element.");
        }
        let node;
        let target = element;
        const whichSibling = position + "Sibling";
        while (target !== null && target !== ancestor) {
            if (target[position + "ElementSibling"] !== null) {
                return false;
            }
            else if (target[whichSibling]) {
                node = target[whichSibling];
                while (node) {
                    if (node.textContent.trim() !== "") {
                        return false;
                    }
                    node = node[whichSibling];
                }
            }
            target = target.parentNode;
        }
        return true;
    }
    let _idCounter = 0;
    const DomUtil = {
        /**
         * Returns a DocumentFragment containing the provided HTML string as DOM nodes.
         */
        createFragmentFromHtml(html) {
            const tmp = document.createElement("div");
            this.setInnerHtml(tmp, html);
            const fragment = document.createDocumentFragment();
            while (tmp.childNodes.length) {
                fragment.appendChild(tmp.childNodes[0]);
            }
            return fragment;
        },
        /**
         * Returns a unique element id.
         */
        getUniqueId() {
            let elementId;
            do {
                elementId = `cfw-${_idCounter++}`;
            } while (document.getElementById(elementId) !== null);
            return elementId;
        },
        /**
         * Returns the element's id. If there is no id set, a unique id will be
         * created and assigned.
         */
        identify(element) {
            if (!(element instanceof Element)) {
                throw new TypeError("Expected a valid DOM element as argument.");
            }
            let id = element.id;
            if (!id) {
                id = this.getUniqueId();
                element.id = id;
            }
            return id;
        },
        /**
         * Returns the outer height of an element including margins.
         */
        outerHeight(element, styles) {
            styles = styles || window.getComputedStyle(element);
            let height = element.offsetHeight;
            height += ~~styles.marginTop + ~~styles.marginBottom;
            return height;
        },
        /**
         * Returns the outer width of an element including margins.
         */
        outerWidth(element, styles) {
            styles = styles || window.getComputedStyle(element);
            let width = element.offsetWidth;
            width += ~~styles.marginLeft + ~~styles.marginRight;
            return width;
        },
        /**
         * Returns the outer dimensions of an element including margins.
         */
        outerDimensions(element) {
            const styles = window.getComputedStyle(element);
            return {
                height: this.outerHeight(element, styles),
                width: this.outerWidth(element, styles),
            };
        },
        /**
         * Returns the element's offset relative to the document's top left corner.
         *
         * @param  {Element}  element          element
         * @return  {{left: int, top: int}}         offset relative to top left corner
         */
        offset(element) {
            const rect = element.getBoundingClientRect();
            return {
                top: Math.round(rect.top + (window.scrollY || window.pageYOffset)),
                left: Math.round(rect.left + (window.scrollX || window.pageXOffset)),
            };
        },
        /**
         * Prepends an element to a parent element.
         *
         * @deprecated 5.3 Use `parent.insertAdjacentElement('afterbegin', element)` instead.
         */
        prepend(element, parent) {
            parent.insertAdjacentElement("afterbegin", element);
        },
        /**
         * Inserts an element after an existing element.
         *
         * @deprecated 5.3 Use `element.insertAdjacentElement('afterend', newElement)` instead.
         */
        insertAfter(newElement, element) {
            element.insertAdjacentElement("afterend", newElement);
        },
        /**
         * Applies a list of CSS properties to an element.
         */
        setStyles(element, styles) {
            let important = false;
            Object.keys(styles).forEach((property) => {
                if (/ !important$/.test(styles[property])) {
                    important = true;
                    styles[property] = styles[property].replace(/ !important$/, "");
                }
                else {
                    important = false;
                }
                // for a set style property with priority = important, some browsers are
                // not able to overwrite it with a property != important; removing the
                // property first solves this issue
                if (element.style.getPropertyPriority(property) === "important" && !important) {
                    element.style.removeProperty(property);
                }
                element.style.setProperty(property, styles[property], important ? "important" : "");
            });
        },
        /**
         * Returns a style property value as integer.
         *
         * The behavior of this method is undefined for properties that are not considered
         * to have a "numeric" value, e.g. "background-image".
         */
        styleAsInt(styles, propertyName) {
            const value = styles.getPropertyValue(propertyName);
            if (value === null) {
                return 0;
            }
            return parseInt(value, 10);
        },
        /**
         * Sets the inner HTML of given element and reinjects <script> elements to be properly executed.
         *
         * @see    http://www.w3.org/TR/2008/WD-html5-20080610/dom.html#innerhtml0
         * @param  {Element}  element    target element
         * @param  {string}  innerHtml  HTML string
         */
        setInnerHtml(element, innerHtml) {
            element.innerHTML = innerHtml;
            let container;
            if (element instanceof HTMLTemplateElement) {
                container = element.content;
            }
            else {
                container = element;
            }
            const scripts = container.querySelectorAll("script");
            for (let i = 0, length = scripts.length; i < length; i++) {
                const script = scripts[i];
                const newScript = document.createElement("script");
                if (script.src) {
                    newScript.src = script.src;
                }
                else {
                    newScript.textContent = script.textContent;
                }
                container.appendChild(newScript);
                script.remove();
            }
        },
        /**
         *
         * @param html
         * @param {Element} referenceElement
         * @param insertMethod
         */
        insertHtml(html, referenceElement, insertMethod) {
            const element = document.createElement("template");
            this.setInnerHtml(element, html);
            const fragment = document.importNode(element.content, true);
            switch (insertMethod) {
                case "append":
                    referenceElement.appendChild(fragment);
                    break;
                case "after":
                    if (referenceElement.parentNode === null) {
                        throw new Error("The reference element has no parent, but the insert position was set to 'after'.");
                    }
                    referenceElement.parentNode.insertBefore(fragment, referenceElement.nextSibling);
                    break;
                case "prepend":
                    referenceElement.insertBefore(fragment, referenceElement.firstChild);
                    break;
                case "before":
                    if (referenceElement.parentNode === null) {
                        throw new Error("The reference element has no parent, but the insert position was set to 'before'.");
                    }
                    referenceElement.parentNode.insertBefore(fragment, referenceElement);
                    break;
                default:
                    throw new Error("Unknown insert method '" + insertMethod + "'.");
            }
        },
        /**
         * Returns true if `element` contains the `child` element.
         *
         * @deprecated 5.4 Use `element.contains(child)` instead.
         */
        contains(element, child) {
            return element.contains(child);
        },
        /**
         * Retrieves all data attributes from target element, optionally allowing for
         * a custom prefix that serves two purposes: First it will restrict the results
         * for items starting with it and second it will remove that prefix.
         *
         * @deprecated 5.4 Use `element.dataset` instead.
         */
        getDataAttributes(element, prefix, camelCaseName, idToUpperCase) {
            prefix = prefix || "";
            if (prefix.indexOf("data-") !== 0) {
                prefix = "data-" + prefix;
            }
            camelCaseName = camelCaseName === true;
            idToUpperCase = idToUpperCase === true;
            const attributes = {};
            for (let i = 0, length = element.attributes.length; i < length; i++) {
                const attribute = element.attributes[i];
                if (attribute.name.indexOf(prefix) === 0) {
                    let name = attribute.name.replace(new RegExp("^" + prefix), "");
                    if (camelCaseName) {
                        const tmp = name.split("-");
                        name = "";
                        for (let j = 0, innerLength = tmp.length; j < innerLength; j++) {
                            if (name.length) {
                                if (idToUpperCase && tmp[j] === "id") {
                                    tmp[j] = "ID";
                                }
                                else {
                                    tmp[j] = StringUtil.ucfirst(tmp[j]);
                                }
                            }
                            name += tmp[j];
                        }
                    }
                    attributes[name] = attribute.value;
                }
            }
            return attributes;
        },
        /**
         * Unwraps contained nodes by moving them out of `element` while
         * preserving their previous order. Target element will be removed
         * at the end of the operation.
         */
        unwrapChildNodes(element) {
            if (element.parentNode === null) {
                throw new Error("The element has no parent.");
            }
            const parent = element.parentNode;
            while (element.childNodes.length) {
                parent.insertBefore(element.childNodes[0], element);
            }
            element.remove();
        },
        /**
         * Replaces an element by moving all child nodes into the new element
         * while preserving their previous order. The old element will be removed
         * at the end of the operation.
         */
        replaceElement(oldElement, newElement) {
            if (oldElement.parentNode === null) {
                throw new Error("The old element has no parent.");
            }
            while (oldElement.childNodes.length) {
                newElement.appendChild(oldElement.childNodes[0]);
            }
            oldElement.parentNode.insertBefore(newElement, oldElement);
            oldElement.remove();
        },
        /**
         * Returns true if given element is the most left node of the ancestor, that is
         * a node without any content nor elements before it or its parent nodes.
         */
        isAtNodeStart(element, ancestor) {
            return _isBoundaryNode(element, ancestor, "previous");
        },
        /**
         * Returns true if given element is the most right node of the ancestor, that is
         * a node without any content nor elements after it or its parent nodes.
         */
        isAtNodeEnd(element, ancestor) {
            return _isBoundaryNode(element, ancestor, "next");
        },
        /**
         * Returns the first ancestor element with position fixed or null.
         *
         * @param       {Element}               element         target element
         * @returns     {(Element|null)}        first ancestor with position fixed or null
         */
        getFixedParent(element) {
            while (element && element !== document.body) {
                if (window.getComputedStyle(element).getPropertyValue("position") === "fixed") {
                    return element;
                }
                element = element.offsetParent;
            }
            return null;
        },
        /**
         * Shorthand function to hide an element by setting its 'display' value to 'none'.
         */
        hide(element) {
            element.style.setProperty("display", "none", "");
        },
        /**
         * Shorthand function to show an element previously hidden by using `hide()`.
         */
        show(element) {
            element.style.removeProperty("display");
        },
        /**
         * Shorthand function to check if given element is hidden by setting its 'display'
         * value to 'none'.
         */
        isHidden(element) {
            return element.style.getPropertyValue("display") === "none";
        },
        /**
         * Shorthand function to toggle the element visibility using either `hide()` or `show()`.
         */
        toggle(element) {
            if (this.isHidden(element)) {
                this.show(element);
            }
            else {
                this.hide(element);
            }
        },
        /**
         * Displays or removes an error message below the provided element.
         */
        innerError(element, errorMessage, isHtml) {
            const parent = element.parentNode;
            if (parent === null) {
                throw new Error("Only elements that have a parent element or document are valid.");
            }
            if (typeof errorMessage !== "string") {
                if (!errorMessage) {
                    errorMessage = "";
                }
                else {
                    throw new TypeError("The error message must be a string; `false`, `null` or `undefined` can be used as a substitute for an empty string.");
                }
            }
            let insertTarget = parent;
            let referenceElement = element;
            if (insertTarget.classList.contains("inputAddon")) {
                insertTarget = parent.parentElement;
                referenceElement = parent;
            }
            let innerError = referenceElement.nextElementSibling;
            if (innerError === null || innerError.nodeName !== "SMALL" || !innerError.classList.contains("innerError")) {
                if (errorMessage === "") {
                    innerError = null;
                }
                else {
                    innerError = document.createElement("small");
                    innerError.className = "innerError";
                    insertTarget.insertBefore(innerError, referenceElement.nextSibling);
                }
            }
            if (errorMessage === "") {
                if (innerError !== null) {
                    innerError.remove();
                    innerError = null;
                }
            }
            else {
                if (isHtml) {
                    innerError.innerHTML = errorMessage;
                }
                else {
                    innerError.textContent = errorMessage;
                }
            }
            return innerError;
        },
        /**
         * Displays or removes an error message below the provided element.
         */
        innerSuccess(element, message, isHtml) {
            const parent = element.parentNode;
            if (parent === null) {
                throw new Error("Only elements that have a parent element or document are valid.");
            }
            if (typeof message !== "string") {
                if (!message) {
                    message = "";
                }
                else {
                    throw new TypeError("The message must be a string; `false`, `null` or `undefined` can be used as a substitute for an empty string.");
                }
            }
            let innerSuccess = element.nextElementSibling;
            if (innerSuccess === null ||
                innerSuccess.nodeName !== "SMALL" ||
                !innerSuccess.classList.contains("innerSuccess")) {
                if (message === "") {
                    innerSuccess = null;
                }
                else {
                    innerSuccess = document.createElement("small");
                    innerSuccess.className = "innerSuccess";
                    parent.insertBefore(innerSuccess, element.nextSibling);
                }
            }
            if (message === "") {
                if (innerSuccess !== null) {
                    innerSuccess.remove();
                    innerSuccess = null;
                }
            }
            else {
                if (isHtml) {
                    innerSuccess.innerHTML = message;
                }
                else {
                    innerSuccess.textContent = message;
                }
            }
            return innerSuccess;
        },
        /**
         * Finds the closest element that matches the provided selector. This is a helper
         * function because `closest()` does exist on elements only, for example, it is
         * missing on text nodes.
         */
        closest(node, selector) {
            const element = node instanceof HTMLElement ? node : node.parentElement;
            return element.closest(selector);
        },
        /**
         * Returns the `node` if it is an element or its parent. This is useful when working
         * with the range of a text selection.
         */
        getClosestElement(node) {
            return node instanceof HTMLElement ? node : node.parentElement;
        },
    };
    return DomUtil;
});