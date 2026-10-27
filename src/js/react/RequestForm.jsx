import { useId, useState } from 'react';
import Icon from './Icon';
import { api } from '../lib/api';

const EMPTY = { title: '', description: '', vibes: [], scenes: [], platforms: [], references: '', website: '' };
const MIN_DESC = 20;

function Section({ n, title, hint, children, error }) {
  return (
    <fieldset className="grid gap-4 border-t border-white/[0.07] pt-8 first:border-0 first:pt-0">
      <legend className="contents">
        <span className="flex items-baseline gap-3">
          <span className="font-mono text-xs text-ink-500">{String(n).padStart(2, '0')}</span>
          <span className="font-display text-2xl font-semibold tracking-tight text-white">{title}</span>
        </span>
      </legend>
      {hint && <p className="-mt-2 text-sm text-ink-400">{hint}</p>}
      {children}
      {error && (
        <p className="field__error auth-form-enter">
          <Icon name="alert" className="size-4 shrink-0" />
          {error}
        </p>
      )}
    </fieldset>
  );
}

function Chips({ options, value, onChange }) {
  const toggle = (opt) => onChange(value.includes(opt) ? value.filter((v) => v !== opt) : [...value, opt]);
  return (
    <div className="flex flex-wrap gap-2">
      {options.map((opt) => (
        <button key={opt} type="button" className="chip" aria-pressed={value.includes(opt)} onClick={() => toggle(opt)}>
          {value.includes(opt) && <Icon name="check" className="size-3.5" strokeWidth="2.5" />}
          {opt}
        </button>
      ))}
    </div>
  );
}

export default function RequestForm({ options, accountUrl, name }) {
  const [values, setValues] = useState(EMPTY);
  const [errors, setErrors] = useState({});
  const [message, setMessage] = useState('');
  const [busy, setBusy] = useState(false);
  const [sent, setSent] = useState(false);
  const titleId = useId();
  const descId = useId();
  const refsId = useId();

  const set = (key) => (val) => {
    setValues((v) => ({ ...v, [key]: val && val.target ? val.target.value : val }));
    if (errors[key]) setErrors((e) => ({ ...e, [key]: null }));
  };

  const submit = async (e) => {
    e.preventDefault();
    const found = {};
    if (values.title.trim().length < 3) found.title = 'Dê um nome para a sua ideia.';
    if (values.description.trim().length < MIN_DESC) found.description = `Conte um pouco mais (pelo menos ${MIN_DESC} caracteres).`;
    setErrors(found);
    setMessage('');
    if (Object.keys(found).length) {
      document.querySelector(`[name="${Object.keys(found)[0]}"]`)?.focus();
      return;
    }
    setBusy(true);
    try {
      await api('requests', values);
      setSent(true);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (err) {
      if (err.field) setErrors({ [err.field]: err.message });
      else setMessage(err.message);
    } finally {
      setBusy(false);
    }
  };

  if (sent) {
    return (
      <div className="auth-form-enter flex flex-col items-center py-12 text-center" role="status">
        <span className="relative inline-flex size-20 items-center justify-center rounded-full bg-lime text-ink-950">
          <span className="absolute inset-0 animate-ping rounded-full bg-lime/40" />
          <Icon name="check" className="relative size-9" strokeWidth="2.5" />
        </span>
        <h2 className="mt-8 font-display text-4xl font-semibold tracking-tight text-white">Pedido enviado{name ? `, ${name}` : ''}!</h2>
        <p className="mt-3 max-w-md text-ink-300">
          Recebemos “{values.title}”. Você acompanha o status em Minha conta e fica sabendo quando virar template.
        </p>
        <div className="mt-10 flex flex-wrap justify-center gap-3">
          <a href={accountUrl} className="btn btn--primary">
            <span>Ver minhas solicitações</span>
            <Icon name="arrow-right" className="btn__arrow" />
          </a>
          <button
            type="button"
            className="btn btn--outline"
            onClick={() => {
              setValues(EMPTY);
              setSent(false);
            }}
          >
            <span>Mandar outra ideia</span>
          </button>
        </div>
      </div>
    );
  }

  const descLen = values.description.trim().length;

  return (
    <form onSubmit={submit} noValidate className="grid gap-10">
      <Section n={1} title="A ideia" error={errors.title}>
        <div className={`field${errors.title ? ' has-error' : ''}`}>
          <input id={titleId} name="title" className="field__input" placeholder=" " maxLength={120} value={values.title} onChange={set('title')} />
          <label htmlFor={titleId} className="field__label">
            Dê um nome (ex.: “Café lo-fi chuvoso”)
          </label>
        </div>
      </Section>

      <Section n={2} title="Estilo" hint="Escolha quantos quiser.">
        <Chips options={options.vibes} value={values.vibes} onChange={set('vibes')} />
      </Section>

      <Section n={3} title="Cenas que você usa" hint="Assim o template já vem com tudo que a sua live precisa.">
        <Chips options={options.scenes} value={values.scenes} onChange={set('scenes')} />
      </Section>

      <Section n={4} title="Onde você faz live">
        <Chips options={options.platforms} value={values.platforms} onChange={set('platforms')} />
      </Section>

      <Section n={5} title="Descreva" error={errors.description}>
        <div className={`field${errors.description ? ' has-error' : ''}`}>
          <textarea
            id={descId}
            name="description"
            className="field__input"
            placeholder=" "
            rows={6}
            maxLength={4000}
            value={values.description}
            onChange={set('description')}
          />
          <label htmlFor={descId} className="field__label">
            Cores, clima, elementos, o tipo de conteúdo da live…
          </label>
          <span className={`pointer-events-none absolute bottom-3 right-4 font-mono text-[11px] ${descLen >= MIN_DESC ? 'text-lime' : 'text-ink-500'}`}>
            {descLen < MIN_DESC ? `${descLen}/${MIN_DESC}` : <Icon name="check" className="size-4" />}
          </span>
        </div>
      </Section>

      <Section n={6} title="Referências" hint="Opcional. Links de lives, imagens ou vídeos que mostram o que você imagina (um por linha).">
        <div className="field">
          <textarea id={refsId} name="references" className="field__input !min-h-[6rem]" placeholder=" " rows={3} value={values.references} onChange={set('references')} />
          <label htmlFor={refsId} className="field__label">
            https://
          </label>
        </div>
      </Section>

      <input type="text" name="website" value={values.website} onChange={set('website')} tabIndex={-1} autoComplete="off" className="hidden" aria-hidden="true" />

      {message && (
        <div className="notice notice--error auth-form-enter" role="alert">
          <Icon name="alert" className="mt-0.5 size-4 shrink-0" />
          <span>{message}</span>
        </div>
      )}

      <div className="flex flex-col items-start gap-4 border-t border-white/[0.07] pt-8 sm:flex-row sm:items-center sm:justify-between">
        <p className="text-sm text-ink-400">Você recebe atualizações do pedido em Minha conta.</p>
        <button type="submit" className="btn btn--primary btn--lg" disabled={busy}>
          {busy ? <span className="spinner" /> : <span>Enviar pedido</span>}
          {!busy && <Icon name="wand" className="btn__arrow" />}
        </button>
      </div>
    </form>
  );
}
