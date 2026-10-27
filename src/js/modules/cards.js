/**
 * Cards de template (HTML do servidor): ao passar o mouse, percorre as cenas
 * com barrinhas de progresso no estilo stories.
 */

import { initMagnetic } from './magnetic';

const STEP = 1200;

function initCard(media) {
  const images = [...media.querySelectorAll('img')];
  const bars = [...media.querySelectorAll('.tcard__progress span')];
  if (images.length < 2) return () => {};

  let index = 0;
  let timer = null;

  const show = (i) => {
    index = i % images.length;
    images.forEach((img, n) => img.classList.toggle('is-active', n === index));
    bars.forEach((bar, n) => {
      bar.classList.toggle('is-done', n < index);
      bar.classList.remove('is-active');
      if (n === index) {
        void bar.offsetWidth;
        bar.classList.add('is-active');
      }
    });
  };

  const enter = () => {
    // Pré-carrega as próximas cenas na primeira passada do mouse.
    images.forEach((img) => (img.loading = 'eager'));
    show(0);
    clearInterval(timer);
    timer = setInterval(() => show(index + 1), STEP);
  };
  const leave = () => {
    clearInterval(timer);
    show(0);
    bars.forEach((bar) => bar.classList.remove('is-active', 'is-done'));
  };

  const card = media.closest('.tcard') || media;
  card.addEventListener('pointerenter', enter);
  card.addEventListener('pointerleave', leave);
  return () => {
    clearInterval(timer);
    card.removeEventListener('pointerenter', enter);
    card.removeEventListener('pointerleave', leave);
  };
}

export function initCards(root = document) {
  const cleanups = [...root.querySelectorAll('[data-card-cycle]')].map(initCard);
  cleanups.push(initMagnetic(root));
  return () => cleanups.forEach((fn) => fn());
}
