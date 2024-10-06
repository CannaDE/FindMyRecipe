define(["require", "exports", "tslib", "Ui/Screen", "Ui/Overlay", "Dom/Util", "Dom/Listener", "Core", "Environment", "focus-trap"], function (require, exports, tslib, UiScreen,UiOverlay, Util, Listener, Core, Environment, focus_trap_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiDialog = void 0;
    Core = tslib.__importStar(Core);
    Listener = tslib.__importDefault(Listener);
    UiScreen = tslib.__importStar(UiScreen);
    UiOverlay = tslib.__importDefault(UiOverlay);
    Util = tslib.__importStar(Util);
    Environment = tslib.__importStar(Environment);


    let _activeDialog = null;
    let _container;
    let _headerTitle;
    const _dialogs = new Map();
    let _dialogFullHeight = false;
    const _dialogObjects = new WeakMap();
    const _dialogToObject = new Map();
    let _keyupListener;
    // list of supported `input[type]` values for dialog submit
    const _validInputTypes = ["number", "password", "search", "tel", "text", "url", "email"];

    const UiDialog = {

        setup() {

            _container = document.createElement("div");
            _container.classList.add("dialogPageOverlay");
            _container.setAttribute("aria-hidden", "true");
            _container.addEventListener("mousedown", (ev) => this._closeOnBackdrop(ev));
            _container.addEventListener("wheel", (event) => {
                if (event.target === _container) {
                    event.preventDefault();
                }
            }, { passive: false });

            console.log(document.getElementById("page"))
            document.getElementById("page").appendChild(_container);

            _keyupListener = (event) => {
                if (event.key === "Escape") {
                    const target = event.target;
                    if (target.nodeName !== "INPUT" && target.nodeName !== "TEXTAREA") {
                        const data = _dialogs.get(_activeDialog);

                        if(!data.closable)
                            return true;

                        if (typeof data.onBeforeClose === "function") {
                            data.onBeforeClose(_activeDialog);
                            return false;
                        }

                        this.close(_activeDialog);
                        return false;
                    }
                }
                return true;
            };

            UiScreen.on("screen-xs", {
                match() {
                    _dialogFullHeight = true;
                },
                unmatch() {
                    _dialogFullHeight = false;
                },
                setup() {
                    _dialogFullHeight = true;
                }
            });

            this._initStaticDialogs();

            Listener.default.add("Ui/Dialog", () => {
                this._initStaticDialogs();
            });

            window.addEventListener("resize", () => {
                _dialogs.forEach((modal) => {
                    if (!Core.stringToBool(modal.modal.getAttribute("aria-hidden"))) {
                        this.rebuild(modal.modal.dataset.id || "");
                    }
                });
            });
            return this;
        },

        _initStaticDialogs() {
            document.querySelectorAll(".jsStaticModal").forEach((button) => {
                button.classList.remove("jsStaticModal");
                const id = button.dataset.dialogId || "";
                if (id) {
                    const container = document.getElementById(id);
                    if (container !== null) {
                        container.classList.remove("jsStaticModalContent");
                        container.dataset.isStaticDialog = "true";
                        Util.hide(container);
                        button.addEventListener("click", (event) => {
                            event.preventDefault();
                            this.openStatic(container.id, null, { title: _container.dataset.title || "" });
                        });
                    }
                }
            });
        },
        /**
         * Closes the current active dialog by clicks on the backdrop.
         */
        _closeOnBackdrop(event) {
            if (event.target !== _container) {
                return;
            }
            if (Core.stringToBool(_container.getAttribute("close-on-click"))) {
                event.preventDefault();
                this._close(event);
            }
            else {
                event.preventDefault();
            }
        },
        /**
         * Handles clicks on the close button or the backdrop if enabled.
         */
        _close(event) {

            event.preventDefault();
            const data = _dialogs.get(_activeDialog);
            if (typeof data.onBeforeClose === "function") {
                data.onBeforeClose(_activeDialog);
                return false;
            }
            this.close(_activeDialog);
            return true;
        },
        open(callbackObject, html) {
            let dialogData = _dialogObjects.get(callbackObject);
            let dialogElement;
            if(dialogData && Core.isPlainObject(dialogData)) {
                return this.openStatic(dialogData.id);
            }

            //initialize new dialog
            if(typeof callbackObject._dialogSetup !== "function") {
                throw Error("Callback object does not implemented the method '_dialogSetup()'.");
            }

            const setupData = callbackObject._dialogSetup();
            if(!Core.isPlainObject(setupData)) {
                throw new Error("Expected an object literal as return value of '_dialogSetup()'.");
            }
            const id = setupData.id;
            dialogData = { id };
            if(setupData.source === undefined) {
                dialogElement = document.querySelector("#"+ id);
                if(dialogElement === null) {
                    throw new Error("Element with id '" + id + "' is invalid and no source attributes was given. If you want to use the 'html' argument, please add 'sources: null' to your dialog configuration.");
                }

                setupData.source = document.createDocumentFragment();
                setupData.source.appendChild(dialogElement);
                dialogElement.removeAttribute("id");
                Util.show(dialogElement);
            } else if(setupData.source === null) {

                setupData.source = html;
            } else if(setupData.source === "function") {
                setupData.source();
            } else if(Core.isPlainObject(setupData.source)) {
                if(typeof html === "string" && html.trim() !== "") {
                    setupData.source = html;
                } else {
                    void new Promise((resolve_1, reject_1) => { require(["../Ajax"], resolve_1, reject_1); }).then((Ajax) => {
                        const source = setupData.source;
                        Ajax.api(this, source.data, (data) => {
                            if (data.returnValues && typeof data.returnValues.template === "string") {
                                this.open(callbackObject, data.returnValues.template);
                                if (typeof source.after === "function") {
                                    source.after(_dialogs.get(id).content, data);
                                }
                            }
                        });
                    });
                    return {};
                }
            } else {
                if(typeof setupData.source === "string") {
                    dialogElement = document.createElement("div");
                    dialogElement.id = id;
                    Util.setInnerHtml(dialogElement, setupData.source);
                    setupData.source = document.createDocumentFragment();
                    setupData.source.appendChild(dialogElement);
                }
                if (!setupData.source.nodeType || setupData.source.nodeType !== Node.DOCUMENT_FRAGMENT_NODE) {
                    throw new Error("Expected at least a document fragment as 'source' attribute.");
                }
            }

            _dialogObjects.set(callbackObject, dialogData);
            _dialogToObject.set(id, callbackObject);
            return this.openStatic(id, setupData.source, setupData.options);
        },
        /**
         * Open a dialog, if the dialog is already open the content container
         * will be replaced by the HTML string contained in the parameter html.
         *
         * If id is an existing element id, html will be ignored and the referenced
         * element will be appended to the content element instead.
         */
        openStatic(id, html, options) {
            if(!this.isOpen(id)) {
                UiScreen.pageOverlayOpen(id);
            }
            if(Environment.platform() !== "desktop") {
                if(!this.isOpen(id)) {
                    UiScreen.scrollDisable();
                }
            }
            if(_dialogs.has(id)) {
                console.log(options);
                this._updateDialog(id, html);
            }
            else {

                options = Core.extend({
                    backdropCloseOnClick: true,
                    closable: true,
                    closeButtonLabel: "Modal schließen",
                    closeConfirmMessage: "",
                    disableContentPadding: false,
                    title: null,
                    onBeforeClose: null,
                    onClose: null,
                    onShow: null,
                }, options || {});

                if (!options.closable)
                    options.backdropCloseOnClick = false;
                if (options.closeConfirmMessage) {
                    options.onBeforeClose = (id) => {
                        void new Promise((resolve_2, reject_2) => { require(["Ui/Dialog/Confirmation"], resolve_2, reject_2); }).then(tslib.__importStar).then((UiConfirmation) => {
                            UiConfirmation.show({
                                confirm: this.close.bind(this, id),
                                message: options.closeConfirmMessage || "",
                            });
                        });
                    };
                }
                this._createModal(id, html, options);

                const data = _dialogs.get(id);
                // iOS breaks `position: fixed` when input elements or `contenteditable`
                // are focused, this will freeze the screen and force Safari to scroll
                // to the input field
                if (Environment.platform() === "ios") {
                    window.setTimeout(() => {
                        data.content.querySelector("input, textarea")?.focus();
                    }, 200);
                }
                return data;
            }

        },
        /**
         * Creates the DOM for a new dialog and opens it.
         */
        _createModal(id, html, options) {

            let element = null;
            if (html === null) {
                element = document.getElementById(id);
                if (element === null) {
                    throw new Error("Expected either a HTML string or an existing element id.");
                }
            }
            const modal = document.createElement("div");
            modal.classList.add("modalContainer");
            modal.setAttribute("aria-hidden", "true");
            modal.setAttribute("role", "dialog");
            modal.dataset.id = id;
            const header = document.createElement("header");
            modal.appendChild(header);
            const titleId = Util.getUniqueId();
            _container.setAttribute("aria-labelledby", titleId);
            _headerTitle = document.createElement("span");
            _headerTitle.classList.add("modalTitle");
            _headerTitle.textContent = options.title;
            header.appendChild(_headerTitle);

            if (options.closable) {
                const closeButton = document.createElement("button");
                closeButton.type = "button";
                closeButton.innerHTML = '<span class="fa fa-times color-theme"></span>';
                closeButton.title = options.closeButtonLabel;
                closeButton.setAttribute("aria-label", options.closeButtonLabel);
                closeButton.addEventListener("click", (e) => this._close(e));
                header.appendChild(closeButton);
            }

            const resizeObserver = new ResizeObserver((entries) => {
                if (modal.getAttribute("aria-hidden") === "false") {
                    for (const entry of entries) {
                        const contentBoxSize = Array.isArray(entry.contentBoxSize)
                            ? entry.contentBoxSize[0]
                            : entry.contentBoxSize;
                        const offset = Math.floor(contentBoxSize.inlineSize / 2);
                        modal.style.setProperty("--translate-x", `-${offset}px`);
                    }
                }
            });
            resizeObserver.observe(modal);
            const contentContainer = document.createElement("div");
            contentContainer.classList.add("modalContent");
            if (options.disableContentPadding)
                contentContainer.classList.add("modalContentNoPadding");
            modal.appendChild(contentContainer);
            contentContainer.addEventListener("wheel", (event) => {
                let allowScroll = false;
                let element = event.target;
                let clientHeight;
                let scrollHeight;
                let scrollTop;
                for (;;) {
                    clientHeight = element.clientHeight;
                    scrollHeight = element.scrollHeight;
                    if (clientHeight < scrollHeight) {
                        scrollTop = element.scrollTop;
                        // negative value: scrolling up
                        if (event.deltaY < 0 && scrollTop > 0) {
                            allowScroll = true;
                            break;
                        }
                        else if (event.deltaY > 0 && scrollTop + clientHeight < scrollHeight) {
                            allowScroll = true;
                            break;
                        }
                    }
                    if (!element || element === contentContainer) {
                        break;
                    }
                    element = element.parentNode;
                }
                if (!allowScroll) {
                    event.preventDefault();
                }
            }, { passive: false });
            let content;
            if (element === null) {
                if (typeof html === "string") {
                    content = document.createElement("div");
                    content.id = id;
                    Util.setInnerHtml(content, html);
                }
                else if (html instanceof DocumentFragment) {
                    const children = [];
                    let node;
                    for (let i = 0, length = html.childNodes.length; i < length; i++) {
                        node = html.childNodes[i];
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            children.push(node);
                        }
                    }
                    if (children[0].nodeName !== "DIV" || children.length > 1) {
                        content = document.createElement("div");
                        content.id = id;
                        content.appendChild(html);
                    }
                    else {
                        content = children[0];
                    }
                }
                else {
                    throw new TypeError("'html' must either be a string or a DocumentFragment");
                }
            }
            else {
                content = element;
            }

            contentContainer.appendChild(content);

            if(content.style.getPropertyValue("display") === "none") {
                Util.show(content);
            }

            const focusTrap = focus_trap_1.createFocusTrap(modal, {
                allowOutsideClick: true,
                escapeDeactivates() {
                    const data = _dialogs.get(id);
                    if (data.closable) {
                        UiDialog.close(id);
                    }
                    return false;
                },
                fallbackFocus: modal,
            });
            _dialogs.set(id, {
                backdropCloseOnClick: options.backdropCloseOnClick,
                closable: options.closable,
                content,
                modal,
                focusTrap,
                header,
                _headerTitle,
                onBeforeClose: options.onBeforeClose,
                onClose: options.onClose,
                onShow: options.onShow,
                submitButton: null,
                inputFields: new Set(),
            });
            _container.insertBefore(modal, _container.firstChild);
            if (typeof options.onSetup === "function") {
                options.onSetup(content);
            }
            this._updateDialog(id, null);
        },
        /**
         * Updates the dialog's content element.
         */
        _updateDialog(id, html) {
            const data = _dialogs.get(id);
            if (data === undefined) {
                throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
            }
            if (typeof html === "string") {
                Util.setInnerHtml(data.content, html);
            }
            if (Core.stringToBool(data.modal.getAttribute("aria-hidden"))) {
                UiOverlay.default.execute();
                if (data.closable && Core.stringToBool(_container.getAttribute("aria-hidden"))) {
                    window.addEventListener("keyup", _keyupListener);
                }
                // Move the dialog to the front to prevent it being hidden behind already open dialogs
                // if it was previously visible.
                data.modal.parentNode.insertBefore(data.modal, data.modal.parentNode.firstChild);
                data.modal.setAttribute("aria-hidden", "false");
                _container.setAttribute("aria-hidden", "false");
                _container.setAttribute("close-on-click", data.backdropCloseOnClick ? "true" : "false");
                _activeDialog = id;
                // Set the focus to the first focusable child of the dialog element.
                const closeBtn = data.header.querySelector(".modalCloseButton");
                if(closeBtn)
                    closeBtn.setAttribute("inert", "true");
                if(closeBtn)
                    closeBtn.removeAttribute("inert");
                if (typeof data.onShow === "function") {
                    data.onShow(data.content);
                }
            }
            this.rebuild(id);
            Listener.default.trigger();
            data.focusTrap.activate();
        },
        /**
         * Rebuilds dialog identified by given id.
         */
        rebuild(elementId) {
            const id = this._getDialogId(elementId);
            const data = _dialogs.get(id);
            if (data === undefined) {
                throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
            }
            // ignore non-active dialogs
            if (Core.stringToBool(data.modal.getAttribute("aria-hidden"))) {
                return;
            }

            const contentContainer = data.content.parentNode;
            const formSubmit = data.content.querySelector(".formSubmit");
            let unavailableHeight = 0;

            if(formSubmit !== null) {
                contentContainer.classList.add("modalForm");
                formSubmit.classList.add("modalFormSubmit");
                console.log(formSubmit);
                unavailableHeight += Util.outerHeight(data.header);
                unavailableHeight -= 1;
            } else {
                contentContainer.classList.remove("modalForm");
                contentContainer.style.removeProperty("margin-bottom");
            }

            unavailableHeight += Util.outerHeight(data.header);
            const maximumHeight = window.innerHeight * (_dialogFullHeight ? 1 : 0.8) - unavailableHeight;
            contentContainer.style.setProperty("max-height", maximumHeight + "px", "");
            const callbackObject = _dialogToObject.get(id);

            console.log(options);
            //data._headerTitle.textContent = options.title;

            if (callbackObject !== undefined && typeof callbackObject._dialogSubmit === "function") {
                const inputFields = data.content.querySelectorAll('input[data-submit-on-enter="true"]');
                const submitButton = data.content.querySelector('.modalFooter > input[type="submit"], .modalFooter > button[data-type="submit"]');

                if (submitButton === null) {
                    // check if there is at least one input field with submit handling,
                    // otherwise we'll assume the dialog has not been populated yet
                    if (inputFields.length === 0) {
                        console.warn("Broken dialog, expected a submit button.", data.content);
                    }
                    return;
                }
                if (data.submitButton !== submitButton) {
                    data.submitButton = submitButton;
                    submitButton.addEventListener("click", (event) => {
                        event.preventDefault();
                        this._submit(id);
                    });
                    const _callbackKeydown = (event) => {
                        if (event.key === "Enter") {
                            event.preventDefault();
                            this._submit(id);
                        }
                    };
                    // bind input fields
                    let inputField;
                    for (let i = 0, length = inputFields.length; i < length; i++) {
                        inputField = inputFields[i];
                        if (data.inputFields.has(inputField))
                            continue;
                        if (_validInputTypes.indexOf(inputField.type) === -1) {
                            console.warn("Unsupported input type.", inputField);
                            continue;
                        }
                        data.inputFields.add(inputField);
                        inputField.addEventListener("keydown", _callbackKeydown);
                    }
                }
            }
        },
        /**
         * Submits the dialog with the given id.
         */
        _submit(id) {
            const data = _dialogs.get(id);
            let isValid = true;
            data.inputFields.forEach((inputField) => {
                if (inputField.required) {
                    if (inputField.value.trim() === "") {
                        Util.innerError(inputField, "Leeres Feld");
                        inputField.closest("dl")?.classList.add("formError");
                        isValid = false;
                    }
                    else {
                        Util.innerError(inputField, false);
                        inputField.closest("dl")?.classList.remove("formError");
                    }
                }
            });
            if (isValid) {
                const callbackObject = _dialogToObject.get(id);
                if (typeof callbackObject._dialogSubmit === "function") {
                    callbackObject._dialogSubmit();
                }
            }
        },


        /**
         * Sets the dialog title.
         */
        setTitle(id, title) {
            id = this._getDialogId(id);
            const data = _dialogs.get(id);
            if (data === undefined) {
                throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
            }
            const dialogTitle = data.dialog.querySelector(".modalTitle");
            if (dialogTitle) {
                dialogTitle.textContent = title;
            }
        },

        close(id) {
            id = this._getDialogId(id);
            let data = _dialogs.get(id);
            if (data === undefined) {
                throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
            }
            try {
                data.focusTrap.deactivate();
            }
            catch (e) {
                // The focus trap is unable to return the focus if
                // the origin is no longer focusable. This can happen
                // when the source is removed or is not longer visible,
                // the latter typically caused by collapsing menus
                // on mobile devices.
                const ignoreErrorMessage = "Your focus-trap must have at least one container with at least one tabbable node in it at all times";
                if (e.message !== ignoreErrorMessage) {
                    throw e;
                }
            }

            data.modal.setAttribute("aria-hidden", "true");
            // Move the keyboard focus away from a now hidden element.
            const activeElement = document.activeElement;
            if (activeElement.closest(".modalContainer") === data.modal) {
                activeElement.blur();
            }
            if (typeof data.onClose === "function") {
                data.onClose(id);
            }
            // get next active dialog
            _activeDialog = null;
            for (let i = 0; i < _container.childElementCount; i++) {
                const child = _container.children[i];
                if (!Core.stringToBool(child.getAttribute("aria-hidden"))) {
                    _activeDialog = child.dataset.id || "";
                    break;
                }
            }
            UiScreen.pageOverlayClose();
            if (_activeDialog === null) {
                _container.setAttribute("aria-hidden", "true");
                _container.dataset.closeOnClick = "false";
                if (data.closable) {
                    window.removeEventListener("keyup", _keyupListener);
                }
            }
            else {
                data = _dialogs.get(_activeDialog);
                console.log(data);
                _container.dataset.closeOnClick = data.backdropCloseOnClick ? "true" : "false";
            }
            if (Environment.platform() !== "desktop") {
                UiScreen.scrollEnable();
            }

        },

        /**
         * Destroys a dialog instance.
         *
         * @param  {Object}  callbackObject  the same object that was used to invoke `_dialogSetup()` on first call
         */
        destroy(callbackObject) {
            if (typeof callbackObject !== "object") {
                throw new TypeError("Expected the callback object as parameter.");
            }
            if (_dialogObjects.has(callbackObject)) {
                const id = _dialogObjects.get(callbackObject).id;
                if (this.isOpen(id)) {
                    this.close(id);
                }
                // If the dialog is destroyed in the close callback, this method is
                // called twice resulting in `_dialogs.get(id)` being undefined for
                // the initial call.
                if (_dialogs.has(id)) {
                    _dialogs.get(id).dialog.remove();
                    _dialogs.delete(id);
                }
                _dialogObjects.delete(callbackObject);
            }
        },

        /**
         * Returns the dialog data for given element id.
         */
        getDialog(id) {
            return _dialogs.get(this._getDialogId(id));
        },
        /**
         * Returns true for open dialogs.
         */
        isOpen(id) {
            const data = this.getDialog(id);
            return data !== undefined && data.modal.getAttribute("aria-hidden") === "false";
        },

        /**
         * Returns a dialog's id.
         *
         * @param  {(string|object)}  id  element id or callback object
         * @return      {string}
         * @protected
         */
        _getDialogId(id) {
            if (typeof id === "object") {
                const dialogData = _dialogObjects.get(id);
                if (dialogData !== undefined) {
                    return dialogData.id;
                }
            }
            return id.toString();
        },
        _ajaxSetup() {
            return {};
        },

    };
    return UiDialog;
});