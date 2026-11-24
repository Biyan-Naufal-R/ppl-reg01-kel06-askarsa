import './bootstrap';

const initSidebar = () => {
 const sidebar = document.getElementById('activity-sidebar');
 const toggles = document.querySelectorAll('[data-sidebar-toggle]');
 if (!sidebar || !toggles.length) return;
 const key = 'askarsa.sidebar.open';
 const desktop = () => window.matchMedia('(min-width: 1024px)').matches;
 const apply = (open) => {
  if (desktop()) {
   sidebar.classList.toggle('lg:block', open);
   sidebar.classList.toggle('lg:hidden', !open);
   sidebar.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-40', 'hidden');
  } else {
   sidebar.classList.toggle('hidden', !open);
   sidebar.classList.toggle('fixed', open);
   sidebar.classList.toggle('inset-y-0', open);
   sidebar.classList.toggle('left-0', open);
   sidebar.classList.toggle('z-40', open);
   if (open) sidebar.classList.remove('lg:block', 'lg:hidden');
  }
  toggles.forEach(t => t.setAttribute('aria-expanded', String(open)));
 };
 let open = localStorage.getItem(key);
 open = open === null ? true : open === 'true';
 apply(open);
 toggles.forEach(t => t.addEventListener('click', () => { open = !open; localStorage.setItem(key, String(open)); apply(open); }));
 window.addEventListener('resize', () => apply(open));
};

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

initSidebar();
document.querySelectorAll('#status-toast').forEach(t => window.setTimeout(() => t.remove(), 5000));
