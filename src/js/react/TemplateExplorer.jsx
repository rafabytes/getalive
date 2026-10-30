import { useDeferredValue, useEffect, useMemo, useRef, useState } from 'react';
import Icon from './Icon';
import TemplateCard from './TemplateCard';
import { __, _n, locale } from '../lib/i18n';

const SORTS = [
  { key: 'order', label: __('Destaques') },
  { key: 'popular', label: __('Populares') },
  { key: 'az', label: 'A–Z' },
];

/** Busca sem acento e sem diferenciar maiúsculas. */
const normalize = (s) =>
  (s || '')
    .toString()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase();

export default function TemplateExplorer({ items: initial = [], styles = [], style: initialStyle = '', request, loggedIn, loginUrl }) {
  // rank guarda a ordem em que o servidor mandou: a definida arrastando no painel.
  const [items, setItems] = useState(() => initial.map((it, rank) => ({ ...it, rank })));
  const [query, setQuery] = useState('');
  const [style, setStyle] = useState(initialStyle);
  const [sort, setSort] = useState('order');
  const [onlyFavs, setOnlyFavs] = useState(false);
  const deferredQuery = useDeferredValue(query);
  const searchRef = useRef(null);

  // Mantém os corações sincronizados com cliques feitos em outros lugares da página.
  useEffect(() => {
    const onFav = (e) => setItems((list) => list.map((it) => (it.id === e.detail.id ? { ...it, favorite: e.detail.on } : it)));
    document.addEventListener('ga:favorite', onFav);
    return () => document.removeEventListener('ga:favorite', onFav);
  }, []);

  // Atalho "/" para buscar.
  useEffect(() => {
    const onKey = (e) => {
      if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) {
        e.preventDefault();
        searchRef.current?.focus();
      }
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, []);

  const results = useMemo(() => {
    const q = normalize(deferredQuery.trim());
    const list = items.filter((it) => {
      if (style && !it.styles.some((s) => s.slug === style)) return false;
      if (onlyFavs && !it.favorite) return false;
      if (!q) return true;
      return normalize([it.title, it.tagline, ...it.styles.map((s) => s.name), ...it.platforms].join(' ')).includes(q);
    });
    const sorters = {
      order: (a, b) => a.rank - b.rank,
      popular: (a, b) => b.downloads - a.downloads,
      az: (a, b) => a.title.localeCompare(b.title, locale),
    };
    return [...list].sort(sorters[sort]);
  }, [items, deferredQuery, style, sort, onlyFavs]);

  const favCount = items.filter((it) => it.favorite).length;
  const filtered = Boolean(query || style || onlyFavs);
  const resultKey = `${deferredQuery}|${style}|${sort}|${onlyFavs}`;

  const clear = () => {
    setQuery('');
    setStyle('');
    setOnlyFavs(false);
  };

  return (
    <div>
      <div className="explorer-bar">
        <div className="flex flex-col gap-2 lg:flex-row lg:items-center">
          <label className="relative flex-1">
            <span className="sr-only">{__('Buscar templates')}</span>
            <Icon name="search" className="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-ink-400" />
            <input
              ref={searchRef}
              type="search"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder={__('Buscar por nome, estilo ou plataforma')}
              className="h-12 w-full rounded-full bg-white/[0.04] pl-12 pr-12 text-[15px] text-white outline-none ring-signal-400 transition placeholder:text-ink-500 focus:bg-white/[0.07] focus:ring-2 [&::-webkit-search-cancel-button]:hidden"
            />
            {query ? (
              <button
                type="button"
                onClick={() => setQuery('')}
                className="absolute right-2 top-1/2 inline-flex size-8 -translate-y-1/2 items-center justify-center rounded-full text-ink-400 hover:bg-white/10 hover:text-white"
                aria-label={__('Limpar busca')}
              >
                <Icon name="close" className="size-4" />
              </button>
            ) : (
              <kbd className="absolute right-4 top-1/2 hidden -translate-y-1/2 rounded-md border border-white/15 px-1.5 font-mono text-[11px] text-ink-500 md:block">/</kbd>
            )}
          </label>

          <div className="explorer-bar__sorts" role="group" aria-label={__('Ordenar')}>
            {SORTS.map((s) => (
              <button
                key={s.key}
                type="button"
                aria-pressed={sort === s.key}
                onClick={() => setSort(s.key)}
                className={`h-10 shrink-0 rounded-full px-3 text-[13px] transition sm:px-4 sm:text-sm ${sort === s.key ? 'bg-white text-ink-950' : 'text-ink-300 hover:text-white'}`}
              >
                {s.label}
              </button>
            ))}
            {loggedIn && (
              <button
                type="button"
                aria-pressed={onlyFavs}
                onClick={() => setOnlyFavs((v) => !v)}
                className={`ml-auto inline-flex h-10 shrink-0 items-center gap-2 rounded-full px-3 text-[13px] transition sm:px-4 sm:text-sm lg:ml-1 ${onlyFavs ? 'bg-live text-white' : 'text-ink-300 hover:text-white'}`}
              >
                <Icon name="heart" className="size-4" fill={onlyFavs ? 'currentColor' : 'none'} />
                <span className="max-sm:sr-only">{__('Favoritos')}</span>
                <span className="font-mono text-xs opacity-70">{favCount}</span>
              </button>
            )}
          </div>
        </div>
      </div>

      {styles.length > 0 && (
        <div className="-mx-1 mb-6 flex gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:none]" role="group" aria-label={__('Estilos')}>
          <button type="button" className="chip shrink-0" aria-pressed={!style} onClick={() => setStyle('')}>
            {__('Todos')}
          </button>
          {styles.map((s) => (
            <button key={s.slug} type="button" className="chip shrink-0" aria-pressed={style === s.slug} onClick={() => setStyle(style === s.slug ? '' : s.slug)}>
              {s.name}
            </button>
          ))}
        </div>
      )}

      <p className="mb-8 font-mono text-xs uppercase tracking-[0.16em] text-ink-500" aria-live="polite">
        {results.length} {_n('template', 'templates', results.length)}
        {filtered && (
          <button type="button" onClick={clear} className="ml-3 text-ink-300 underline underline-offset-4 hover:text-white">
            {__('limpar filtros')}
          </button>
        )}
      </p>

      {results.length > 0 ? (
        <div key={resultKey} className="grid gap-x-8 gap-y-14 md:grid-cols-2 xl:grid-cols-3">
          {results.map((item, i) => (
            <TemplateCard key={item.id} item={item} index={i} loginUrl={loginUrl} />
          ))}
          {!filtered && <SoonCard href={request} />}
        </div>
      ) : (
        <div className="auth-form-enter flex flex-col items-center rounded-[2rem] border border-dashed border-white/15 px-6 py-24 text-center">
          <span className="inline-flex size-16 items-center justify-center rounded-full bg-white/[0.05] text-ink-300">
            <Icon name={onlyFavs ? 'heart' : 'search'} className="size-7" />
          </span>
          <h2 className="mt-6 font-display text-3xl font-semibold tracking-tight text-white">
            {onlyFavs && !query ? __('Nenhum favorito ainda') : __('Nada encontrado')}
          </h2>
          <p className="mt-2 max-w-sm text-ink-400">
            {query
              ? __('Não achamos templates para “%s”. Que tal pedir um com essa cara?', query)
              : __('Tente outro filtro ou peça um template novo.')}
          </p>
          <div className="mt-8 flex flex-wrap justify-center gap-3">
            <button type="button" className="btn btn--outline" onClick={clear}>
              <span>{__('Limpar filtros')}</span>
            </button>
            <a href={request} className="btn btn--primary">
              <span>{__('Solicitar template')}</span>
              <Icon name="arrow-right" className="btn__arrow" />
            </a>
          </div>
        </div>
      )}
    </div>
  );
}

function SoonCard({ href }) {
  return (
    <a href={href} className="tcard group auth-form-enter">
      <span className="tcard__media flex flex-col items-center justify-center gap-4 border border-dashed border-white/15 !bg-transparent text-center transition duration-500 group-hover:border-lime/60">
        <span className="inline-flex size-14 items-center justify-center rounded-full border border-white/15 transition duration-500 ease-expo group-hover:rotate-90 group-hover:border-lime group-hover:bg-lime group-hover:text-ink-950">
          <Icon name="plus" className="size-6" />
        </span>
        <span className="eyebrow">{__('Próximo template')}</span>
      </span>
      <span className="mt-5 block">
        <span className="tcard__title block">{__('Pode ser o seu')}</span>
        <span className="mt-1.5 block text-ink-400">{__('Novos templates saem a partir dos pedidos. Conte a sua ideia.')}</span>
      </span>
    </a>
  );
}
