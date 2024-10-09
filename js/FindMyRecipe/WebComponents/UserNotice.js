define(function() {
    class UserNotice extends HTMLElement {

        #_notice;
        constructor() {
            super();
            this.#_notice = document.createElement('div');
            this.#_notice.classList.add('ui-notice');
        }

        connectedCallback() {
            const type = this.getAttribute('type') || 'info';
            this.#_notice.classList.add(type);
            this.#_notice.innerHTML = this.innerHTML;
            this.parentNode.insertBefore(this.#_notice, this.nextSibling);
            this.remove();
        }
    }

    customElements.define('ui-notice', UserNotice);
});