/**
 * Ilhas React: <div data-react="Nome" data-props='{...}'>.
 * Cada componente vira um bundle separado, carregado só nas páginas que o usam.
 */

const loaders = {
  TemplateExplorer: () => import('./TemplateExplorer'),
  AuthApp: () => import('./AuthApp'),
  RequestForm: () => import('./RequestForm'),
};

const roots = new Set();

export async function mountReact(root = document) {
  const nodes = [...root.querySelectorAll('[data-react]')];
  if (!nodes.length) return;

  const [{ createRoot }, { createElement }] = await Promise.all([import('react-dom/client'), import('react')]);

  await Promise.all(
    nodes.map(async (node) => {
      const load = loaders[node.dataset.react];
      if (!load || node.dataset.mounted) return;
      node.dataset.mounted = 'true';
      const { default: Component } = await load();
      let props = {};
      try {
        props = JSON.parse(node.dataset.props || '{}');
      } catch {
        // props inválidas: monta com o padrão do componente
      }
      const reactRoot = createRoot(node);
      reactRoot.render(createElement(Component, props));
      roots.add(reactRoot);
    })
  );
}

export function unmountReact() {
  roots.forEach((r) => r.unmount());
  roots.clear();
}
