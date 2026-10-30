/**
 * Entrada do tema: Swup (transições entre páginas) + módulos por página + ilhas React.
 */

import '../css/main.css';

import Swup from 'swup';
import SwupHeadPlugin from '@swup/head-plugin';
import SwupBodyClassPlugin from '@swup/body-class-plugin';
import SwupPreloadPlugin from '@swup/preload-plugin';
import SwupScrollPlugin from '@swup/scroll-plugin';
import SwupA11yPlugin from '@swup/a11y-plugin';

import { initHeader, updateHeaderForPage, closeMobileMenu } from './modules/header';
import { initReveal } from './modules/reveal';
import { initStages } from './modules/stage';
import { initCards } from './modules/cards';
import { initFavorites } from './modules/favorites';
import { initConsent } from './modules/consent';
import { initAccount } from './modules/account';
import { initToc } from './modules/toc';
import { toast } from './modules/toast';
import { trackView } from './modules/activity';
import { mountReact, unmountReact } from './react/mount';
import { __ } from './lib/i18n';

const pageModules = [initReveal, initStages, initCards, initAccount, initToc];
let cleanups = [];

function pageView() {
  const container = document.querySelector('#swup');
  cleanups = pageModules.map((init) => init(container));
  mountReact(container);
  updateHeaderForPage();
  trackView();
}

function pageLeave() {
  cleanups.forEach((fn) => typeof fn === 'function' && fn());
  cleanups = [];
  unmountReact();
}

/** Links que precisam de carregamento completo (painel, sair, downloads, arquivos). */
function ignoreVisit(url, { el } = {}) {
  if (el?.closest('[data-no-swup]')) return true;
  const { pathname, search } = new URL(url, window.location.origin);
  return (
    pathname.includes('/wp-admin') ||
    pathname.includes('wp-login.php') ||
    search.includes('ga_download=') ||
    search.includes('add-to-cart=') ||
    /\.(zip|pdf|png|jpe?g|webp|xml)$/i.test(pathname)
  );
}

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const swup = new Swup({
  containers: ['#swup'],
  ignoreVisit,
  animationSelector: '[class*="transition-"]',
  plugins: [
    new SwupHeadPlugin({ persistAssets: true, awaitAssets: true }),
    new SwupBodyClassPlugin(),
    new SwupPreloadPlugin({ preloadHoveredLinks: true }),
    new SwupScrollPlugin({ animateScroll: { betweenPages: false, samePageWithHash: !reducedMotion, samePage: !reducedMotion } }),
    new SwupA11yPlugin({ headingSelector: 'h1' }),
  ],
});

swup.hooks.before('content:replace', pageLeave);
swup.hooks.on('page:view', () => {
  closeMobileMenu();
  pageView();
});
swup.hooks.on('visit:start', closeMobileMenu);

// Persistentes (fora do container do Swup): rodam uma vez só.
initHeader();
initConsent();
initFavorites((url) => swup.navigate(url));

// O Swup guarda em memória as páginas visitadas e as pré-carregadas no hover. Depois de
// favoritar, baixar, pedir um template ou editar o perfil essas cópias ficam velhas
// (ex.: /conta mostrando 0 favoritos), então o cache é descartado.
document.addEventListener('ga:changed', () => swup.cache.clear());

document.addEventListener('click', (e) => {
  if (e.target.closest('[data-download]')) {
    swup.cache.clear();
    toast(__('Seu download está começando…'), 'download');
  }
});

pageView();
