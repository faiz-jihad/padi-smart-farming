document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-password-toggle]');
    const password = document.querySelector('#password');

    if (!toggle || !password) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isVisible = password.type === 'text';

        password.type = isVisible ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!isVisible));
        toggle.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
        password.focus({ preventScroll: true });
    });
});
