/**
 * Consentimento de cookies (LGPD).
 *
 * Escolha guardada no cookie ga_consent por 12 meses. Scripts opcionais ficam como
 * <script type="text/plain" data-consent="analytics|marketing"> e só são ativados
 * depois do aceite da categoria. API pública: window.GAConsent.has('analytics').
 */

const COOKIE = 'ga_consent';
const MAX_AGE = 60 * 60 * 24 * 365;
const OPTIONAL = ['analytics', 'marketing'];

function read() {
  const raw = document.cookie.split('; ').find((c) => c.startsWith(COOKIE + '='));
  if (!raw) return null;
  try {
    return JSON.parse(decodeURIComponent(raw.slice(COOKIE.length + 1)));
  } catch {
    return null;
  }
}

function write(prefs, version) {
  const value = { v: version, necessary: true, ...prefs, at: Date.now() };
  const secure = location.protocol === 'https:' ? '; Secure' : '';
  document.cookie = `${COOKIE}=${encodeURIComponent(JSON.stringify(value))}; Max-Age=${MAX_AGE}; Path=/; SameSite=Lax${secure}`;
  return value;
}

function activateScripts(state) {
  document.querySelectorAll('script[type="text/plain"][data-consent]').forEach((placeholder) => {
    if (!state?.[placeholder.dataset.consent]) return;
    const script = document.createElement('script');
    [...placeholder.attributes].forEach((attr) => {
      if (attr.name !== 'type' && attr.name !== 'data-consent') script.setAttribute(attr.name, attr.value);
    });
    script.text = placeholder.text;
    placeholder.replaceWith(script);
  });
}

export function initConsent() {
  const banner = document.querySelector('[data-consent-banner]');
  if (!banner) return;

  const version = Number(banner.dataset.version) || 1;
  const prefsForm = banner.querySelector('[data-consent-prefs]');
  const btn = (name) => banner.querySelector(`[data-consent-action="${name}"]`);
  let state = read();
  if (state && state.v !== version) state = null;

  const setPrefsMode = (on) => {
    prefsForm.hidden = !on;
    btn('customize').hidden = on;
    btn('save').hidden = !on;
  };

  const syncCheckboxes = () => {
    OPTIONAL.forEach((key) => {
      prefsForm.elements[key].checked = Boolean(state?.[key]);
    });
  };

  const open = (withPrefs = false) => {
    syncCheckboxes();
    setPrefsMode(withPrefs);
    banner.hidden = false;
    banner.querySelector('button')?.focus({ preventScroll: true });
  };

  const decide = (prefs) => {
    const revoked = state && OPTIONAL.some((key) => state[key] && !prefs[key]);
    state = write(prefs, version);
    banner.hidden = true;
    document.dispatchEvent(new CustomEvent('ga:consent', { detail: state }));
    // Script de terceiro já carregado não tem como ser desligado: recarrega sem ele.
    if (revoked) window.location.reload();
    else activateScripts(state);
  };

  btn('accept').addEventListener('click', () => decide({ analytics: true, marketing: true }));
  btn('reject').addEventListener('click', () => decide({ analytics: false, marketing: false }));
  btn('customize').addEventListener('click', () => setPrefsMode(true));
  btn('save').addEventListener('click', () =>
    decide(Object.fromEntries(OPTIONAL.map((key) => [key, prefsForm.elements[key].checked])))
  );

  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-consent-open]')) open(true);
  });

  if (state) activateScripts(state);
  else setTimeout(() => open(false), 900);

  window.GAConsent = {
    get: () => state,
    has: (key) => key === 'necessary' || Boolean(state?.[key]),
    open: () => open(true),
  };
}
