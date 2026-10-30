/**
 * Favoritos: clique em qualquer [data-favorite] (HTML do servidor) ou chamada de
 * toggleFavorite() (componentes React). Os dois disparam "ga:favorite" para manter
 * todos os botões do mesmo template sincronizados.
 */

import { api, GA } from '../lib/api';
import { toast } from './toast';
import { __ } from '../lib/i18n';

export function burst(button) {
  button.classList.remove('is-pop');
  void button.offsetWidth;
  button.classList.add('is-pop');
  for (let i = 0; i < 8; i++) {
    const dot = document.createElement('span');
    dot.className = 'fav-burst';
    dot.style.setProperty('--r', `${i * 45}deg`);
    button.append(dot);
    dot.addEventListener('animationend', () => dot.remove(), { once: true });
  }
}

function paint(id, on) {
  document.querySelectorAll(`[data-favorite="${id}"]`).forEach((btn) => {
    btn.classList.toggle('is-on', on);
    btn.setAttribute('aria-pressed', String(on));
    btn.setAttribute('aria-label', on ? __('Remover dos favoritos') : __('Favoritar'));
    const label = btn.querySelector('.fav-btn__label');
    if (label) label.textContent = on ? __('Favoritado') : __('Favoritar');
  });
}

export async function toggleFavorite(id, on) {
  paint(id, on);
  document.dispatchEvent(new CustomEvent('ga:favorite', { detail: { id, on } }));
  try {
    const res = await api(`favorites/${id}`, { on });
    const counter = document.querySelector('[data-fav-count]');
    if (counter) counter.textContent = res.count;
    toast(on ? __('Salvo nos favoritos') : __('Removido dos favoritos'), on ? 'heart' : 'check');
    return res.favorite;
  } catch (err) {
    paint(id, !on);
    document.dispatchEvent(new CustomEvent('ga:favorite', { detail: { id, on: !on } }));
    toast(err.message, 'alert');
    return !on;
  }
}

export function initFavorites(navigate) {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-favorite]');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();

    if (!GA.loggedIn) {
      navigate(btn.dataset.login || GA.pages.login);
      return;
    }
    const on = btn.getAttribute('aria-pressed') !== 'true';
    if (on) burst(btn);
    toggleFavorite(Number(btn.dataset.favorite), on);
  });
}
