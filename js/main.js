/* ============================================================
   QULLQA — main.js
   Los formularios (soporte, login) y el buscador ya envían datos
   de verdad por POST/GET a sus páginas PHP, así que este archivo
   ya NO intercepta esos submits. Solo aporta mejoras visuales:
   menú móvil y un filtrado en vivo opcional para el home.
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  initMobileNavbar();
  initDropdownMobile();
  initLivePreviewFilter();
});

/**
 * Control del botón hamburguesa para el menú de navegación en móviles.
 */
function initMobileNavbar() {
  const menuBtn = document.getElementById('mobileMenuBtn');
  const mainNav = document.getElementById('mainNav');

  if (!menuBtn || !mainNav) return;

  menuBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    menuBtn.classList.toggle('is-active');
    mainNav.classList.toggle('is-open');
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('#mainNav') && !e.target.closest('#mobileMenuBtn')) {
      menuBtn.classList.remove('is-active');
      mainNav.classList.remove('is-open');
    }
  });
}

/**
 * En pantallas angostas el submenú "Investigaciones" se abre con click
 * en lugar de hover.
 */
function initDropdownMobile() {
  const dropdownParents = document.querySelectorAll('.has-dropdown');

  dropdownParents.forEach((parent) => {
    const trigger = parent.querySelector('.nav-link');

    trigger.addEventListener('click', (event) => {
      if (window.innerWidth > 768) return; // en desktop se usa :hover
      event.preventDefault();
      const isOpen = parent.classList.contains('is-open');

      dropdownParents.forEach((p) => p.classList.remove('is-open'));
      if (!isOpen) parent.classList.add('is-open');
    });
  });
}

/**
 * Solo en el home: mientras el usuario escribe en el buscador,
 * oculta/muestra las 4 tarjetas destacadas como vista previa.
 */
function initLivePreviewFilter() {
  const searchInput = document.querySelector('[data-search-input]');
  const cards = document.querySelectorAll('[data-card]');
  if (!searchInput || !cards.length) return;

  searchInput.addEventListener('input', () => {
    const term = searchInput.value.trim().toLowerCase();

    cards.forEach((card) => {
      const title = (card.dataset.title || '').toLowerCase();
      const desc = (card.dataset.desc || '').toLowerCase();
      const show = !term || title.includes(term) || desc.includes(term);
      card.style.display = show ? '' : 'none';
    });
  });
}
