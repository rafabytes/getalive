/**
 * Textos do JS no idioma da página. O português é o próprio texto do código; as
 * traduções vêm do PHP (window.GA.i18n, montado em ga_js_i18n() no inc/i18n.php).
 * Todo texto novo usado aqui precisa entrar naquela lista e nos dicionários.
 */

const strings = window.GA?.i18n || {};

/** __('Oi, %s!', nome): troca cada %s/%d, na ordem, pelos argumentos. */
export function __(text, ...args) {
  let out = strings[text] ?? text;
  args.forEach((arg) => {
    out = out.replace(/%[sd]/, String(arg));
  });
  return out;
}

/** Singular ou plural (português, inglês e espanhol seguem a mesma regra). */
export const _n = (singular, plural, n, ...args) => __(n === 1 ? singular : plural, ...args);

/** Locale para Intl (números, ordenação). */
export const locale = window.GA?.locale || 'pt-BR';
