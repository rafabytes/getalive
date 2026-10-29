/**
 * "Monitor" estilo OBS: troca de cenas (dock), autoplay, timecode de REC,
 * inclinação 3D na tela de login e lightbox.
 */

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function pad(n) {
  return String(n).padStart(2, '0');
}

function initStage(stage) {
  const images = [...stage.querySelectorAll('.monitor__screen img')];
  const tabs = [...stage.querySelectorAll('[data-scene]')];
  const label = stage.querySelector('[data-stage-label]');
  const timecode = stage.querySelector('[data-timecode]');
  const link = stage.querySelector('a.monitor__screen');
  const interval = Number(stage.dataset.stageInterval) || 3200;
  let autoplay = stage.dataset.stageAutoplay !== 'false' && images.length > 1 && !reduced;
  let index = 0;
  let timer = null;
  let visible = true;
  const timers = [];

  const show = (i) => {
    index = (i + images.length) % images.length;
    images.forEach((img, n) => img.classList.toggle('is-active', n === index));
    tabs.forEach((tab, n) => tab.setAttribute('aria-selected', String(n === index)));
    if (label) label.textContent = images[index]?.dataset.title || tabs[index]?.textContent || '';
    // Slider da home: cada tela é um template, então o link segue a tela visível.
    if (link && images[index]?.dataset.href) {
      link.href = images[index].dataset.href;
      link.setAttribute('aria-label', `Ver o template ${tabs[index]?.textContent || ''}`);
    }
    centerTab(tabs[index]);
  };

  // Rola só a faixa de abas, na horizontal. scrollIntoView também rolaria a página, e a troca
  // automática puxava a tela de quem estava lendo mais abaixo (visível no celular).
  const centerTab = (tab) => {
    const dock = tab?.parentElement;
    if (!dock || dock.scrollWidth <= dock.clientWidth) return;
    const t = tab.getBoundingClientRect();
    const d = dock.getBoundingClientRect();
    dock.scrollTo({ left: dock.scrollLeft + t.left - d.left - (d.width - t.width) / 2, behavior: reduced ? 'auto' : 'smooth' });
  };

  const tick = () => {
    if (autoplay && visible && !stage.matches(':hover')) show(index + 1);
  };

  if (autoplay) timer = setInterval(tick, interval);

  tabs.forEach((tab, n) =>
    tab.addEventListener('click', () => {
      autoplay = false;
      show(n);
    })
  );

  // Setas do teclado na lista de cenas
  stage.querySelector('[role="tablist"]')?.addEventListener('keydown', (e) => {
    if (!['ArrowRight', 'ArrowLeft'].includes(e.key)) return;
    e.preventDefault();
    autoplay = false;
    show(index + (e.key === 'ArrowRight' ? 1 : -1));
    tabs[index]?.focus();
  });

  if (timecode) {
    const started = Date.now() - Math.floor(Math.random() * 3600) * 1000;
    const update = () => {
      const s = Math.floor((Date.now() - started) / 1000);
      timecode.textContent = `${pad(Math.floor(s / 3600))}:${pad(Math.floor(s / 60) % 60)}:${pad(s % 60)}`;
    };
    update();
    timers.push(setInterval(update, 1000));
  }

  const io = new IntersectionObserver(([entry]) => (visible = entry.isIntersecting));
  io.observe(stage);

  return () => {
    clearInterval(timer);
    timers.forEach(clearInterval);
    io.disconnect();
  };
}

function openLightbox(src, alt = '') {
  const box = document.createElement('div');
  box.className = 'lightbox';
  box.setAttribute('role', 'dialog');
  box.setAttribute('aria-modal', 'true');
  box.setAttribute('aria-label', 'Imagem ampliada');
  box.innerHTML = `<button type="button" class="icon-btn absolute right-4 top-4 bg-ink-900" aria-label="Fechar">
      <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>`;
  const img = document.createElement('img');
  img.src = src;
  img.alt = alt;
  box.append(img);

  const previous = document.activeElement;
  const close = () => {
    box.remove();
    document.removeEventListener('keydown', onKey);
    previous?.focus?.();
  };
  const onKey = (e) => e.key === 'Escape' && close();
  box.addEventListener('click', close);
  document.addEventListener('keydown', onKey);
  document.body.append(box);
  box.querySelector('button').focus();
}

function initTilt(visual) {
  const tilt = visual.querySelector('[data-auth-tilt]');
  if (!tilt || reduced || !window.matchMedia('(pointer: fine)').matches) return () => {};
  const onMove = (e) => {
    const r = visual.getBoundingClientRect();
    const x = (e.clientX - r.left) / r.width - 0.5;
    const y = (e.clientY - r.top) / r.height - 0.5;
    tilt.style.transform = `rotateY(${-14 + x * 16}deg) rotateX(${6 - y * 10}deg) rotateZ(1deg)`;
  };
  const onLeave = () => (tilt.style.transform = '');
  visual.addEventListener('pointermove', onMove);
  visual.addEventListener('pointerleave', onLeave);
  return () => {
    visual.removeEventListener('pointermove', onMove);
    visual.removeEventListener('pointerleave', onLeave);
  };
}

export function initStages(root = document) {
  const cleanups = [...root.querySelectorAll('[data-stage]')].map(initStage);
  root.querySelectorAll('[data-auth-visual]').forEach((v) => cleanups.push(initTilt(v)));

  const onClick = (e) => {
    const single = e.target.closest('[data-lightbox-src]');
    if (single) {
      openLightbox(single.dataset.lightboxSrc, single.getAttribute('aria-label') || '');
      return;
    }
    const screen = e.target.closest('[data-lightbox]');
    if (screen) {
      const img = screen.querySelector('img.is-active') || screen.querySelector('img');
      if (img) openLightbox(img.src, img.alt);
    }
  };
  root.addEventListener('click', onClick);
  cleanups.push(() => root.removeEventListener('click', onClick));

  return () => cleanups.forEach((fn) => fn && fn());
}
