/**
 * Cabeçalho persistente: esconde ao rolar para baixo, menu do usuário, menu mobile,
 * link ativo e "voltar para cá" nos links de login.
 */

import { GA } from '../lib/api';

const html = document.documentElement;

export function initHeader() {
  const header = document.querySelector('[data-header]');
  if (!header) return;

  let lastY = window.scrollY;
  let ticking = false;
  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      const y = window.scrollY;
      header.classList.toggle('is-scrolled', y > 20);
      const hide = y > 160 && y > lastY && !html.classList.contains('menu-open');
      header.classList.toggle('is-hidden', hide);
      if (hide) closeUserMenu();
      lastY = y;
      ticking = false;
    });
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Menu do usuário
  const menu = document.querySelector('[data-user-menu]');
  const toggle = menu?.querySelector('[data-user-menu-toggle]');
  function closeUserMenu() {
    if (!menu) return;
    menu.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
  }
  toggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    const open = !menu.classList.contains('is-open');
    menu.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
  });
  document.addEventListener('click', (e) => {
    if (menu && !menu.contains(e.target)) closeUserMenu();
  });

  // Menu mobile
  const burger = document.querySelector('[data-burger]');
  burger?.addEventListener('click', () => {
    const open = !html.classList.contains('menu-open');
    html.classList.toggle('menu-open', open);
    burger.setAttribute('aria-expanded', String(open));
    burger.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
    header.classList.remove('is-hidden');
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    closeUserMenu();
    closeMobileMenu();
  });

  return { closeUserMenu };
}

export function closeMobileMenu() {
  html.classList.remove('menu-open');
  const burger = document.querySelector('[data-burger]');
  burger?.setAttribute('aria-expanded', 'false');
  burger?.setAttribute('aria-label', 'Abrir menu');
}

/**
 * Atualiza estados que dependem da página atual (roda a cada navegação).
 */
export function updateHeaderForPage() {
  const path = window.location.pathname.replace(/\/$/, '');

  document.querySelectorAll('[data-nav], .mobile-nav__link').forEach((link) => {
    const linkPath = new URL(link.href).pathname.replace(/\/$/, '');
    const active = linkPath && (path === linkPath || (linkPath.length > 1 && path.startsWith(linkPath + '/')));
    if (active) link.setAttribute('aria-current', 'page');
    else link.removeAttribute('aria-current');
  });

  // Login/cadastro voltam para a página em que a pessoa estava.
  const isAuthPage = [GA.pages?.login, GA.pages?.register].some((u) => u && new URL(u).pathname.replace(/\/$/, '') === path);
  if (!isAuthPage) {
    document.querySelectorAll('[data-login-link]').forEach((link) => {
      const url = new URL(link.href);
      if (url.searchParams.has('redirect_to') && link.closest('#swup')) return;
      url.searchParams.set('redirect_to', window.location.href);
      link.href = url.toString();
    });
  }

  document.querySelector('[data-header]')?.classList.remove('is-hidden');
}
