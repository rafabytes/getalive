/**
 * Revelações no scroll ([data-reveal], títulos [data-split]) e contadores ([data-count]).
 */

import { locale } from '../lib/i18n';

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function countUp(el) {
  const target = Number(el.dataset.count);
  if (!target || reduced) return;
  const start = performance.now();
  const duration = 1400;
  const fmt = new Intl.NumberFormat(locale);
  const step = (now) => {
    const p = Math.min(1, (now - start) / duration);
    const eased = 1 - Math.pow(1 - p, 4);
    el.textContent = fmt.format(Math.round(target * eased));
    if (p < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}

export function initReveal(root = document) {
  root.querySelectorAll('[data-split]').forEach((el) => {
    el.querySelectorAll('.split-line > span').forEach((span, i) => {
      span.style.setProperty('--delay', `${0.05 + i * 0.09}s`);
    });
  });

  const targets = root.querySelectorAll('[data-reveal], [data-split], [data-count]');
  if (reduced || !('IntersectionObserver' in window)) {
    targets.forEach((el) => el.classList.add('is-in'));
    return () => {};
  }

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-in');
        if (entry.target.dataset.count) countUp(entry.target);
        io.unobserve(entry.target);
      });
    },
    { rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
  );
  targets.forEach((el) => io.observe(el));
  return () => io.disconnect();
}
