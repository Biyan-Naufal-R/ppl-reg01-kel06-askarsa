import './bootstrap';

document.addEventListener('click', e => {
 const target = e.target;
 const menuToggle = target.closest('[data-menu-toggle]');
 if (menuToggle) document.querySelector(menuToggle.dataset.menuToggle)?.classList.toggle('hidden');

 const accountToggle = target.closest('[data-account-toggle]');
 const accountMenu = document.getElementById('account-menu');
 if (accountToggle && accountMenu) {
  const isExpanded = accountToggle.getAttribute('aria-expanded') === 'true';
  accountMenu.classList.toggle('hidden', isExpanded);
  accountToggle.setAttribute('aria-expanded', String(!isExpanded));
 } else if (accountMenu && !accountMenu.contains(target)) {
  accountMenu.classList.add('hidden');
  document.querySelector('[data-account-toggle]')?.setAttribute('aria-expanded', 'false');
 }

 const toastClose = target.closest('[data-toast-close]');
 if (toastClose) document.getElementById('status-toast')?.remove();

 const passwordToggle = target.closest('[data-password-toggle]');
 if (passwordToggle) {
  const passwordInput = document.getElementById(passwordToggle.dataset.passwordToggle);
  if (passwordInput) {
   const isVisible = passwordInput.type === 'text';
   passwordInput.type = isVisible ? 'password' : 'text';
   passwordToggle.textContent = isVisible ? passwordToggle.dataset.showLabel : passwordToggle.dataset.hideLabel;
   passwordToggle.setAttribute('aria-label', `${isVisible ? 'Tampilkan' : 'Sembunyikan'} ${passwordToggle.getAttribute('aria-label').replace(/^(Tampilkan|Sembunyikan) /, '')}`);
   passwordToggle.setAttribute('aria-pressed', String(!isVisible));
  }
 }

 const modalOpen = target.closest('[data-modal-open]');
 if (modalOpen) document.getElementById(modalOpen.dataset.modalOpen)?.classList.remove('hidden');

 const modalClose = target.closest('[data-modal-close]');
 if (modalClose) document.getElementById(modalClose.dataset.modalClose)?.classList.add('hidden');
});

document.addEventListener('keydown', e => {
 if (e.key === 'Escape') {
  document.getElementById('account-menu')?.classList.add('hidden');
  document.querySelector('[data-account-toggle]')?.setAttribute('aria-expanded', 'false');
 }
});

const statusToast = document.getElementById('status-toast');
if (statusToast) window.setTimeout(() => statusToast.remove(), 5000);
