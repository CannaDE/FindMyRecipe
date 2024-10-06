// modal.js
class Modal {
    constructor() {
        this.modal = null;
        this.createModal();
    }

    createModal() {
        const modalHTML = `
            <div id="customModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg p-6 max-w-lg w-full">
                    <h2 id="modalTitle" class="text-xl font-bold mb-4"></h2>
                    <p id="modalMessage" class="mb-6"></p>
                    <div class="flex justify-end space-x-2">
                        <button id="modalCancel" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors">
                            Abbrechen
                        </button>
                        <button id="modalConfirm" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
                            Bestätigen
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('customModal');
        this.bindEvents();
    }

    bindEvents() {
        document.getElementById('modalCancel').addEventListener('click', () => this.hide());
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.hide();
        });
    }

    show(title, message, onConfirm) {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMessage').innerHTML = message;
        document.getElementById('modalConfirm').onclick = () => {
            onConfirm();
            this.hide();
        };
        this.modal.classList.remove('hidden');
    }

    hide() {
        this.modal.classList.add('hidden');
    }
}

const modal = new Modal();