/**
 * Formulários da conta: perfil e exclusão.
 */

import { api } from '../lib/api';
import { toast } from './toast';
import { __ } from '../lib/i18n';

function setStatus(form, message, ok) {
  const el = form.querySelector('[data-form-status]');
  if (!el) return;
  el.textContent = message;
  el.className = `text-sm ${ok ? 'text-lime' : 'text-live'}`;
}

function setFieldError(form, field) {
  form.querySelectorAll('.field').forEach((f) => f.classList.remove('has-error'));
  if (field) form.elements[field]?.closest('.field')?.classList.add('has-error');
  if (field) form.elements[field]?.focus();
}

const handlers = {
  async profile(form) {
    const data = Object.fromEntries(new FormData(form));
    const res = await api('account/profile', data);
    form.querySelectorAll('input[type="password"]').forEach((i) => (i.value = ''));
    document.querySelectorAll('[data-user-menu-toggle] .truncate').forEach((el) => (el.textContent = res.name.split(' ')[0]));
    // Senha trocada = sessão nova: o link de sair precisa do nonce novo.
    if (res.logout) document.querySelectorAll('a[href*="action=logout"]').forEach((a) => (a.href = res.logout));
    toast(res.message);
    return res.message;
  },
  async delete(form) {
    const res = await api('account/delete', Object.fromEntries(new FormData(form)));
    window.location.href = res.redirect;
    return __('Conta excluída. Até logo!');
  },
};

export function initAccount(root = document) {
  const forms = [...root.querySelectorAll('[data-account-form]')];
  const onSubmit = async (e) => {
    const form = e.currentTarget;
    e.preventDefault();
    const button = form.querySelector('[type="submit"]');
    button.disabled = true;
    setFieldError(form, null);
    try {
      setStatus(form, await handlers[form.dataset.accountForm](form), true);
    } catch (err) {
      setStatus(form, err.message, false);
      setFieldError(form, err.field);
    } finally {
      button.disabled = false;
    }
  };
  forms.forEach((form) => form.addEventListener('submit', onSubmit));
  return () => forms.forEach((form) => form.removeEventListener('submit', onSubmit));
}
