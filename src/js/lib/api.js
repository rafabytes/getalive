/**
 * Chamadas à API do tema (getalive/v1) com o nonce do WordPress.
 */

const config = window.GA || {};

export class ApiError extends Error {
  constructor(message, { field = null, code = '', status = 0 } = {}) {
    super(message);
    this.field = field;
    this.code = code;
    this.status = status;
  }
}

export async function api(path, data = {}) {
  let res;
  try {
    res = await fetch(config.rest + path, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce },
      body: JSON.stringify(data),
    });
  } catch {
    throw new ApiError('Sem conexão. Confira sua internet e tente de novo.');
  }

  let json = {};
  try {
    json = await res.json();
  } catch {
    // Resposta sem JSON (erro do servidor); cai na mensagem genérica abaixo.
  }

  // Nonce vencido (página aberta há muito tempo): recarrega para pegar um novo.
  if (json.code === 'rest_cookie_invalid_nonce') {
    window.location.reload();
    return new Promise(() => {});
  }

  if (!res.ok) {
    throw new ApiError(json.message || 'Algo deu errado. Tente de novo em instantes.', {
      field: json.field,
      code: json.code,
      status: res.status,
    });
  }
  return json;
}

export const GA = config;
