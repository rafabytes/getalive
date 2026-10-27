/**
 * Aviso flutuante curto no rodapé da tela.
 */

let current = null;
let timer = null;

const ICONS = {
  heart: '<path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/>',
  check: '<path d="M20 6 9 17l-5-5"/>',
  download: '<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/>',
  alert: '<circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 16h.01"/>',
};

const TONES = {
  heart: 'bg-live text-white',
  check: 'bg-lime text-ink-950',
  download: 'bg-lime text-ink-950',
  alert: 'bg-live text-white',
};

export function toast(message, icon = 'check') {
  if (current) current.remove();
  clearTimeout(timer);

  const el = document.createElement('div');
  el.className = 'toast';
  el.setAttribute('role', 'status');
  el.innerHTML = `<span class="inline-flex size-7 items-center justify-center rounded-full ${TONES[icon] || TONES.check}">
      <svg viewBox="0 0 24 24" class="size-3.5" fill="${icon === 'heart' ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">${ICONS[icon] || ICONS.check}</svg>
    </span>`;
  const text = document.createElement('span');
  text.textContent = message;
  el.append(text);
  document.body.append(el);
  current = el;

  timer = setTimeout(() => {
    el.classList.add('is-leaving');
    el.addEventListener('animationend', () => el.remove(), { once: true });
    if (current === el) current = null;
  }, 2600);
}
