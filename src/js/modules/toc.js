/**
 * Índice das páginas legais: destaca a seção visível.
 */

export function initToc(root = document) {
  const links = [...root.querySelectorAll('[data-toc-link]')];
  if (!links.length) return () => {};

  const byId = new Map(links.map((a) => [decodeURIComponent(a.hash.slice(1)), a]));
  const setActive = (id) =>
    links.forEach((a) => {
      const on = byId.get(id) === a;
      a.classList.toggle('!text-white', on);
      a.classList.toggle('!border-lime', on);
    });

  const io = new IntersectionObserver(
    (entries) => {
      const visible = entries.filter((e) => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
      if (visible[0]) setActive(visible[0].target.id);
    },
    { rootMargin: '-20% 0px -70% 0px' }
  );
  byId.forEach((_, id) => {
    const heading = document.getElementById(id);
    if (heading) io.observe(heading);
  });
  return () => io.disconnect();
}
