const authButtons = document.querySelectorAll('[data-auth-target]');
const authPanels = document.querySelectorAll('[data-auth-panel]');

if (authButtons.length > 0 && authPanels.length > 0) {
    const setAuthMode = (mode) => {
        authPanels.forEach((panel) => {
            panel.hidden = panel.dataset.authPanel !== mode;
        });

        authButtons.forEach((button) => {
            const active = button.dataset.authTarget === mode;

            button.classList.toggle('budget-auth-switch-button-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    };

    authButtons.forEach((button) => {
        button.addEventListener('click', () => setAuthMode(button.dataset.authTarget));
    });
}
