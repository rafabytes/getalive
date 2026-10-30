/**
 * Cabeçalho persistente: esconde ao rolar para baixo, menu do usuário, menu mobile,
 * link ativo e "voltar para cá" nos links de login.
 */

import { GA } from '../lib/api';
import { __ } from '../lib/i18n';

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
      setHidden(header, hide);
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
    burger.setAttribute('aria-label', open ? __('Fechar menu') : __('Abrir menu'));
    setHidden(header, false);
  });

  // Seletor de idioma (cabeçalho e tela de login): delegação, porque o da tela de login chega pelo Swup.
  const closeLangMenus = (except) =>
    document.querySelectorAll('[data-lang-menu].is-open').forEach((m) => {
      if (m === except) return;
      m.classList.remove('is-open');
      m.querySelector('[data-lang-menu-toggle]')?.setAttribute('aria-expanded', 'false');
    });
  document.addEventListener('click', (e) => {
    const langToggle = e.target.closest('[data-lang-menu-toggle]');
    const langMenu = langToggle?.closest('[data-lang-menu]');
    closeLangMenus(langMenu);
    if (!langMenu) return;
    const open = !langMenu.classList.contains('is-open');
    langMenu.classList.toggle('is-open', open);
    langToggle.setAttribute('aria-expanded', String(open));
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    closeUserMenu();
    closeLangMenus();
    closeMobileMenu();
  });

  return { closeUserMenu };
}

export function closeMobileMenu() {
  html.classList.remove('menu-open');
  const burger = document.querySelector('[data-burger]');
  burger?.setAttribute('aria-expanded', 'false');
  burger?.setAttribute('aria-label', __('Abrir menu'));
}

/**
 * Links do seletor de idioma apontam para esta mesma página nos outros idiomas,
 * lidos do <link rel="alternate" hreflang> do <head> (o Swup troca o <head> a cada página).
 * Sem versão alternativa (404, busca), vão para a página inicial do idioma.
 */
function updateLangLinks() {
  document.querySelectorAll('[data-lang-link]').forEach((link) => {
    const alt = document.head.querySelector(`link[rel="alternate"][hreflang="${link.getAttribute('hreflang')}"]`);
    link.href = alt?.href || GA.langHomes?.[link.dataset.langLink] || link.href;
  });
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

  updateLangLinks();

  const header = document.querySelector('[data-header]');
  if (header) setHidden(header, false);
}

/** A barra de busca dos templates sobe junto com o cabeçalho (via classe no <html>). */
function setHidden(header, hide) {
  header.classList.toggle('is-hidden', hide);
  html.classList.toggle('header-hidden', hide);
}
