/**
 * Registra a página vista no histórico de atividade (só para quem está logado).
 * Fica no navegador porque o Swup pré-carrega links no hover: pelo servidor,
 * passar o mouse num link contaria como visita.
 */

import { GA } from '../lib/api';

let last = '';

export function trackView() {
  if (!GA.loggedIn || !GA.rest) return;
  const path = window.location.pathname + window.location.search;
  if (path === last) return;
  last = path;

  fetch(`${GA.rest}activity/view`, {
    method: 'POST',
    credentials: 'same-origin',
    keepalive: true,
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': GA.nonce },
    body: JSON.stringify({ path, title: document.title }),
  }).catch(() => {});
}
