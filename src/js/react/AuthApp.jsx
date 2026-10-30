import { useEffect, useId, useRef, useState } from 'react';
import Icon, { GoogleLogo } from './Icon';
import { api } from '../lib/api';
import { __ } from '../lib/i18n';

const COPY = {
  login: { title: [__('Bem-vindo'), __('de volta.')], sub: __('Entre para baixar, favoritar e pedir templates.'), cta: __('Entrar') },
  register: { title: [__('Crie sua'), __('conta grátis.')], sub: __('Leva menos de um minuto. Só pedimos nome e e-mail.'), cta: __('Criar conta') },
  forgot: { title: [__('Esqueceu'), __('a senha?')], sub: __('Sem problema. Mandamos um link para você criar outra.'), cta: __('Enviar link') },
};

/** Texto com um elemento no lugar do %s (ex.: um link no meio da frase). */
function withElement(text, element) {
  const [before, after = ''] = text.split('%s');
  return (
    <>
      {before}
      {element}
      {after}
    </>
  );
}

/* ------------------------------------------------------------------------ */
/* Google Identity Services: carrega o script assim que a tela abre, para    */
/* que o popup seja aberto direto no clique (navegadores bloqueiam popups    */
/* abertos depois de esperas assíncronas).                                   */
/* ------------------------------------------------------------------------ */

let gisPromise = null;
function loadGis() {
  if (window.google?.accounts?.oauth2) return Promise.resolve();
  gisPromise ??= new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = 'https://accounts.google.com/gsi/client';
    s.async = true;
    s.onload = resolve;
    s.onerror = () => {
      gisPromise = null;
      reject(new Error(__('Não foi possível carregar o login do Google.')));
    };
    document.head.append(s);
  });
  return gisPromise;
}

function useGoogle(clientId, onToken, onError) {
  const client = useRef(null);
  const [ready, setReady] = useState(false);
  const handlers = useRef({ onToken, onError });
  handlers.current = { onToken, onError };

  useEffect(() => {
    if (!clientId) return;
    let alive = true;
    loadGis()
      .then(() => {
        if (!alive) return;
        client.current = window.google.accounts.oauth2.initTokenClient({
          client_id: clientId,
          scope: 'openid email profile',
          callback: (res) => {
            if (res.error) handlers.current.onError(res.error_description || __('Login com Google cancelado.'));
            else handlers.current.onToken(res.access_token);
          },
          error_callback: (err) => {
            if (err?.type !== 'popup_closed') handlers.current.onError(__('Não foi possível abrir o login do Google.'));
            else handlers.current.onError(null);
          },
        });
        setReady(true);
      })
      .catch((err) => alive && handlers.current.onError(err.message));
    return () => {
      alive = false;
    };
  }, [clientId]);

  return { ready, open: () => client.current?.requestAccessToken() };
}

/* ------------------------------------------------------------------------ */
/* Campos                                                                    */
/* ------------------------------------------------------------------------ */

function Field({ label, error, action, inputRef, ...input }) {
  const id = useId();
  return (
    <div>
      <div className={`field${error ? ' has-error' : ''}`}>
        <input
          ref={inputRef}
          id={id}
          className={`field__input${action ? ' pr-14' : ''}`}
          placeholder=" "
          aria-invalid={Boolean(error)}
          aria-describedby={error ? `${id}-err` : undefined}
          {...input}
        />
        <label htmlFor={id} className="field__label">
          {label}
        </label>
        {action}
      </div>
      {error && (
        <p id={`${id}-err`} className="field__error auth-form-enter">
          <Icon name="alert" className="size-4 shrink-0" />
          {error}
        </p>
      )}
    </div>
  );
}

function strength(pw) {
  if (!pw) return 0;
  let score = 0;
  if (pw.length >= 8) score++;
  if (pw.length >= 12) score++;
  if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
  if (/\d/.test(pw) && /[^A-Za-z0-9]/.test(pw)) score++;
  return pw.length < 8 ? Math.min(score, 1) : Math.max(score, 1);
}

const STRENGTH = [
  { label: '', color: '' },
  { label: __('Senha fraca'), color: 'bg-live' },
  { label: __('Senha razoável'), color: 'bg-orange-400' },
  { label: __('Senha boa'), color: 'bg-signal-400' },
  { label: __('Senha forte'), color: 'bg-lime' },
];

function PasswordField({ value, showMeter, ...props }) {
  const [visible, setVisible] = useState(false);
  const score = strength(value);
  return (
    <div>
      <Field
        {...props}
        value={value}
        type={visible ? 'text' : 'password'}
        action={
          <button
            type="button"
            className="field__action"
            onClick={() => setVisible((v) => !v)}
            aria-label={visible ? __('Esconder senha') : __('Mostrar senha')}
            aria-pressed={visible}
          >
            <Icon name={visible ? 'eye-off' : 'eye'} className="size-5" />
          </button>
        }
      />
      {showMeter && value && (
        <div className="auth-form-enter" aria-live="polite">
          <div className="strength" aria-hidden="true">
            {[1, 2, 3, 4].map((n) => (
              <span key={n} className={n <= score ? STRENGTH[score].color : ''} />
            ))}
          </div>
          <p className="mt-1.5 text-xs text-ink-400">
            {STRENGTH[score].label}
            {value.length < 8 && ` · ${8 - value.length === 1 ? __('falta 1 caractere') : __('faltam %d caracteres', 8 - value.length)}`}
          </p>
        </div>
      )}
    </div>
  );
}

function Checkbox({ checked, onChange, children, error }) {
  return (
    <div>
      <label className="checkbox">
        <input type="checkbox" checked={checked} onChange={(e) => onChange(e.target.checked)} />
        <span className="checkbox__box">
          <Icon name="check" className="size-3.5" strokeWidth="3" />
        </span>
        <span>{children}</span>
      </label>
      {error && (
        <p className="field__error auth-form-enter">
          <Icon name="alert" className="size-4 shrink-0" />
          {error}
        </p>
      )}
    </div>
  );
}

/* ------------------------------------------------------------------------ */
/* Tela                                                                      */
/* ------------------------------------------------------------------------ */

export default function AuthApp({ mode: initialMode = 'login', redirectTo, googleClientId, registrationOpen = true, privacyUrl, urls }) {
  const [mode, setMode] = useState(initialMode === 'register' && !registrationOpen ? 'login' : initialMode);
  const [values, setValues] = useState({ name: '', email: '', password: '', remember: true, consent: false, website: '' });
  const [errors, setErrors] = useState({});
  const [message, setMessage] = useState(null);
  const [busy, setBusy] = useState(false);
  const [done, setDone] = useState(null);
  const firstField = useRef(null);
  const mounted = useRef(false);

  const set = (key) => (e) => {
    const value = e && e.target ? e.target.value : e;
    setValues((v) => ({ ...v, [key]: value }));
    if (errors[key]) setErrors((err) => ({ ...err, [key]: null }));
  };

  const switchMode = (next) => {
    setMode(next);
    setErrors({});
    setMessage(null);
    // Mantém a URL coerente com a aba (útil para compartilhar/voltar).
    const base = next === 'register' ? urls.register : urls.login;
    const url = new URL(base);
    const current = new URL(window.location.href);
    if (current.searchParams.get('redirect_to')) url.searchParams.set('redirect_to', current.searchParams.get('redirect_to'));
    if (next === 'forgot') url.searchParams.set('modo', 'recuperar');
    window.history.replaceState(window.history.state, '', url);
  };

  useEffect(() => {
    if (mounted.current && window.matchMedia('(pointer: fine)').matches) firstField.current?.focus();
    mounted.current = true;
  }, [mode]);

  const finish = (res, text) => {
    setDone({ name: res.name, text });
    setTimeout(() => {
      window.location.href = res.redirect || redirectTo;
    }, 1100);
  };

  const google = useGoogle(
    googleClientId,
    async (token) => {
      setBusy('google');
      setMessage(null);
      try {
        const res = await api('auth/google', { access_token: token, redirect_to: redirectTo });
        finish(res, res.created ? __('Conta criada com o Google.') : __('Login feito com o Google.'));
      } catch (err) {
        setMessage({ type: 'error', text: err.message });
        setBusy(false);
      }
    },
    (text) => {
      setBusy(false);
      if (text) setMessage({ type: 'error', text });
    }
  );

  const validate = () => {
    const e = {};
    if (mode === 'register' && values.name.trim().length < 2) e.name = __('Conta pra gente como você se chama.');
    if (!/^\S+@\S+\.\S+$/.test(values.email.trim())) e.email = __('Informe um e-mail válido.');
    if (mode === 'login' && !values.password) e.password = __('Informe sua senha.');
    if (mode === 'register' && values.password.length < 8) e.password = __('Use pelo menos 8 caracteres.');
    if (mode === 'register' && !values.consent) e.consent = __('Para criar a conta, aceite a Política de Privacidade.');
    return e;
  };

  const submit = async (e) => {
    e.preventDefault();
    const found = validate();
    setErrors(found);
    setMessage(null);
    if (Object.keys(found).length) {
      const first = Object.keys(found)[0];
      document.querySelector(`[name="${first}"]`)?.focus();
      return;
    }

    setBusy('form');
    try {
      if (mode === 'forgot') {
        const res = await api('auth/lost-password', { email: values.email });
        setMessage({ type: 'success', text: res.message });
        setBusy(false);
        return;
      }
      const res = await api(mode === 'login' ? 'auth/login' : 'auth/register', { ...values, redirect_to: redirectTo });
      finish(res, mode === 'login' ? __('Login feito.') : __('Conta criada.'));
    } catch (err) {
      if (err.field) {
        setErrors({ [err.field]: err.message });
        document.querySelector(`[name="${err.field}"]`)?.focus();
      } else {
        setMessage({ type: 'error', text: err.message });
      }
      setBusy(false);
    }
  };

  if (done) {
    return (
      <div className="auth-form-enter flex flex-col items-center py-10 text-center" role="status">
        <span className="relative inline-flex size-20 items-center justify-center rounded-full bg-lime text-ink-950">
          <span className="absolute inset-0 animate-ping rounded-full bg-lime/40" />
          <Icon name="check" className="relative size-9" strokeWidth="2.5" />
        </span>
        <h1 className="mt-8 font-display text-4xl font-semibold tracking-tight text-white">
          {done.name ? __('Oi, %s!', done.name) : __('Tudo certo!')}
        </h1>
        <p className="mt-2 text-ink-400">{__('%s Levando você de volta…', done.text)}</p>
      </div>
    );
  }

  const copy = COPY[mode];
  const showGoogle = googleClientId && mode !== 'forgot';

  return (
    <div>
      {mode !== 'forgot' && registrationOpen && (
        <div className="auth-tabs mb-10" role="tablist" aria-label={__('Entrar ou criar conta')}>
          <span className="auth-tabs__pill" style={{ transform: mode === 'register' ? 'translateX(100%)' : 'none' }} aria-hidden="true" />
          <button type="button" role="tab" aria-selected={mode === 'login'} onClick={() => switchMode('login')}>
            {__('Entrar')}
          </button>
          <button type="button" role="tab" aria-selected={mode === 'register'} onClick={() => switchMode('register')}>
            {__('Criar conta')}
          </button>
        </div>
      )}

      <div key={mode} className="auth-form-enter">
        {mode === 'forgot' && (
          <button type="button" onClick={() => switchMode('login')} className="mb-8 inline-flex items-center gap-2 text-sm text-ink-400 transition hover:text-white">
            <Icon name="arrow-left" className="size-4" /> {__('Voltar para o login')}
          </button>
        )}

        <h1 className="font-display text-[clamp(2.5rem,5vw,3.5rem)] font-semibold leading-[0.95] tracking-[-0.035em] text-white" style={{ fontVariationSettings: "'wdth' 85" }}>
          {copy.title[0]}
          <br />
          <span className="text-ink-500">{copy.title[1]}</span>
        </h1>
        <p className="mt-4 text-ink-400">{copy.sub}</p>

        {message && (
          <div className={`notice notice--${message.type} auth-form-enter mt-8`} role={message.type === 'error' ? 'alert' : 'status'}>
            <Icon name={message.type === 'error' ? 'alert' : 'mail'} className="mt-0.5 size-4 shrink-0" />
            <span>{message.text}</span>
          </div>
        )}

        {showGoogle && (
          <div className="mt-8">
            <button type="button" className="btn-google" onClick={() => (setBusy('google'), google.open())} disabled={!google.ready || busy}>
              {busy === 'google' ? <span className="spinner" /> : <GoogleLogo />}
              <span>{mode === 'register' ? __('Criar conta com o Google') : __('Continuar com o Google')}</span>
            </button>
            <p className="divider-or mt-8">{__('ou com e-mail')}</p>
          </div>
        )}

        <form onSubmit={submit} noValidate className={`grid gap-4 ${showGoogle ? 'mt-8' : 'mt-10'}`}>
          {mode === 'register' && (
            <Field label={__('Seu nome')} name="name" autoComplete="name" value={values.name} onChange={set('name')} error={errors.name} inputRef={firstField} required />
          )}
          <Field
            label={__('E-mail')}
            name="email"
            type="email"
            autoComplete="email"
            inputMode="email"
            value={values.email}
            onChange={set('email')}
            error={errors.email}
            inputRef={mode === 'register' ? undefined : firstField}
            required
          />
          {mode !== 'forgot' && (
            <PasswordField
              label={__('Senha')}
              name="password"
              autoComplete={mode === 'login' ? 'current-password' : 'new-password'}
              value={values.password}
              onChange={set('password')}
              error={errors.password}
              showMeter={mode === 'register'}
              required
            />
          )}

          {mode === 'register' && (
            <>
              <input type="text" name="website" value={values.website} onChange={set('website')} tabIndex={-1} autoComplete="off" className="hidden" aria-hidden="true" />
              <Checkbox checked={values.consent} onChange={set('consent')} error={errors.consent}>
                {withElement(
                  __('Li e aceito a %s. Meus dados são usados só para a conta funcionar.'),
                  <a href={privacyUrl} target="_blank" rel="noopener">
                    {__('Política de Privacidade')}
                  </a>
                )}
              </Checkbox>
            </>
          )}

          {mode === 'login' && (
            <div className="flex items-center justify-between gap-4">
              <Checkbox checked={values.remember} onChange={set('remember')}>
                {__('Lembrar de mim')}
              </Checkbox>
              <button type="button" onClick={() => switchMode('forgot')} className="text-sm text-ink-300 underline-offset-4 transition hover:text-white hover:underline">
                {__('Esqueci a senha')}
              </button>
            </div>
          )}

          <button type="submit" className="btn btn--primary btn--lg mt-4 w-full" disabled={Boolean(busy)}>
            {busy === 'form' ? <span className="spinner" /> : <span>{copy.cta}</span>}
            {busy !== 'form' && <Icon name="arrow-right" className="btn__arrow" />}
          </button>
        </form>

        {mode !== 'forgot' && (
          <p className="mt-8 text-center text-sm text-ink-400">
            {mode === 'login' ? (
              registrationOpen && (
                <>
                  {__('Ainda não tem conta?')}{' '}
                  <button type="button" onClick={() => switchMode('register')} className="font-medium text-white underline-offset-4 hover:underline">
                    {__('Crie grátis')}
                  </button>
                </>
              )
            ) : (
              <>
                {__('Já tem conta?')}{' '}
                <button type="button" onClick={() => switchMode('login')} className="font-medium text-white underline-offset-4 hover:underline">
                  {__('Entrar')}
                </button>
              </>
            )}
          </p>
        )}
      </div>
    </div>
  );
}
