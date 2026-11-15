import './bootstrap';
document.addEventListener('click', e => {
 const t=e.target.closest('[data-menu-toggle]'); if(t) document.querySelector(t.dataset.menuToggle)?.classList.toggle('hidden');
 const o=e.target.closest('[data-modal-open]'); if(o) document.getElementById(o.dataset.modalOpen)?.classList.remove('hidden');
 const c=e.target.closest('[data-modal-close]'); if(c) document.getElementById(c.dataset.modalClose)?.classList.add('hidden');
});
