import { useEffect, useRef, useState } from 'react';
import Icon from './Icon';
import { toggleFavorite, burst } from '../modules/favorites';
import { GA } from '../lib/api';

const STEP = 1200;
// Igual a GA_IMAGE_SIZES['card'] em inc/template-data.php.
const SIZES = '(min-width: 1280px) 432px, (min-width: 768px) 50vw, 100vw';

function FavoriteButton({ id, on, loginUrl }) {
  const ref = useRef(null);
  const onClick = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!GA.loggedIn) {
      window.location.href = loginUrl;
      return;
    }
    if (!on) burst(ref.current);
    toggleFavorite(id, !on);
  };
  return (
    <button
      ref={ref}
      type="button"
      className={`fav-btn fav-btn--icon${on ? ' is-on' : ''}`}
      aria-pressed={on}
      aria-label={on ? 'Remover dos favoritos' : 'Favoritar'}
      onClick={onClick}
    >
      <Icon name="heart" className="fav-btn__icon" />
    </button>
  );
}

export default function TemplateCard({ item, index = 0, loginUrl }) {
  const [active, setActive] = useState(0);
  const [hover, setHover] = useState(false);
  const images = item.images.slice(0, 6);

  useEffect(() => {
    if (!hover || images.length < 2) return undefined;
    const t = setInterval(() => setActive((i) => (i + 1) % images.length), STEP);
    return () => clearInterval(t);
  }, [hover, images.length]);

  return (
    <article
      className="tcard group auth-form-enter"
      style={{ '--card-accent': item.accent, animationDelay: `${Math.min(index, 8) * 60}ms` }}
      onPointerEnter={() => {
        setActive(0);
        setHover(true);
      }}
      onPointerLeave={() => {
        setHover(false);
        setActive(0);
      }}
    >
      <span className="tcard__glow" aria-hidden="true" />
      <div className="relative">
        <a href={item.url} className="tcard__media block" tabIndex={-1} aria-hidden="true">
          {images.map((img, i) => (
            <img
              key={img.src}
              src={img.src}
              srcSet={img.srcset || undefined}
              sizes={img.srcset ? SIZES : undefined}
              width={960}
              height={540}
              alt=""
              loading={i === 0 || hover ? 'eager' : 'lazy'}
              decoding="async"
              className={i === active ? 'is-active' : ''}
            />
          ))}
          {images.length > 1 && (
            <span className="tcard__progress">
              {images.map((img, i) => (
                <span key={img.src + active} className={hover && i === active ? 'is-active' : hover && i < active ? 'is-done' : ''} />
              ))}
            </span>
          )}
        </a>
        <div className="absolute left-3 top-3 flex gap-1.5">
          <span className="tag tag--live">
            <span className="size-1.5 rounded-full bg-white" />
            Live
          </span>
          {item.price && <span className="tag bg-lime text-ink-950">{item.price}</span>}
        </div>
        <div className="absolute right-3 top-3 z-10">
          <FavoriteButton id={item.id} on={item.favorite} loginUrl={loginUrl} />
        </div>
      </div>
      <div className="mt-5 flex items-start justify-between gap-4">
        <div className="min-w-0">
          <h3 className="tcard__title">
            <a href={item.url} className="after:absolute after:inset-0 after:content-['']">
              {item.title}
            </a>
          </h3>
          {item.tagline && <p className="mt-1.5 line-clamp-2 text-ink-400">{item.tagline}</p>}
        </div>
        <span
          className="mt-1 inline-flex size-10 shrink-0 items-center justify-center rounded-full border border-white/10 transition duration-500 ease-expo group-hover:rotate-45 group-hover:border-lime group-hover:bg-lime group-hover:text-ink-950"
          aria-hidden="true"
        >
          <Icon name="arrow-up-right" className="size-4" />
        </span>
      </div>
      <div className="mt-4 flex flex-wrap gap-1.5">
        <span className="tag">
          {item.scenes} {item.scenes === 1 ? 'cena' : 'cenas'}
        </span>
        {item.styles.map((s) => (
          <span key={s.slug} className="tag">
            {s.name}
          </span>
        ))}
      </div>
    </article>
  );
}
