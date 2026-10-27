/**
 * Botões [data-magnetic] seguem levemente o cursor.
 */

export function initMagnetic(root = document) {
  if (!window.matchMedia('(pointer: fine)').matches || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return () => {};
  }
  const cleanups = [...root.querySelectorAll('[data-magnetic]')].map((el) => {
    const strength = 0.25;
    const move = (e) => {
      const r = el.getBoundingClientRect();
      const x = (e.clientX - r.left - r.width / 2) * strength;
      const y = (e.clientY - r.top - r.height / 2) * strength;
      el.style.transform = `translate(${x}px, ${y}px)`;
    };
    const reset = () => {
      el.style.transform = '';
    };
    el.style.transition = 'transform .6s cubic-bezier(.16,1,.3,1), background-color .3s';
    el.addEventListener('pointermove', move);
    el.addEventListener('pointerleave', reset);
    return () => {
      el.removeEventListener('pointermove', move);
      el.removeEventListener('pointerleave', reset);
    };
  });
  return () => cleanups.forEach((fn) => fn());
}
