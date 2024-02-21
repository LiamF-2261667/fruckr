/* Close button functionality for custom modals */
let modalCloseButtons = document.getElementsByClassName('close-button');
for (let i = 0; i < modalCloseButtons.length; i++) {
    modalCloseButtons[i].addEventListener('click', (event) => {
        event.preventDefault();

        let modal = event.target.closest('.custom-modal');
        closeModalDirect(modal);
    }, false);
}

/* Close modal if user clicks outside of it */
let modalWrappers = document.getElementsByClassName('custom-modal-wrapper');
for (let i = 0; i < modalWrappers.length; i++) {
    modalWrappers[i].addEventListener('mousedown', (event) => {
        if (event.target === event.currentTarget) {
            event.target.style.display = 'none';
            enableScroll();
        }
    }, false);
}

/* Close modal if user presses escape key */
window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape')
        closeModalDirect(document.querySelector('.custom-modal'));
}, false);

/* Functions to be used by other scripts */
function openModal(modalId) {
    openModalDirect(document.getElementById(modalId));
}

function openModalDirect(modalElement) {
    modalElement.parentElement.style.display = 'flex';

    // Scroll to top
    modalElement.scrollTop = 0;

    disableScroll();
}

function closeModal(modalId) {
    closeModalDirect(document.getElementById(modalId));
}

function closeModalDirect(modalElement) {
    modalElement.parentElement.style.display = 'none';
    enableScroll();
}