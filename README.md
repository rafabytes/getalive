# get a live: tema WordPress

Catálogo de layouts de live para o OBS Studio. Stack: **WordPress + Parcel + TailwindCSS + JS + React + Swup**.

## Desenvolvimento

```bash
npm install
npm run dev     # parcel watch
npm run build   # build de produção em dist/ (limpa dist e cache antes)
```

`dist/` é versionado para o tema funcionar sem build no servidor.

| Pasta | O que tem |
|---|---|
| `inc/` | PHP do tema: tipos de post, REST, auth, Google, downloads, LGPD, premium |
| `page-templates/` | Entrar/Criar conta, Minha conta, Solicitar, Sobre, Texto legal |
| `src/js/main.js` | Swup + módulos por página (`src/js/modules/`) |
| `src/js/react/` | Ilhas React: `TemplateExplorer`, `AuthApp`, `RequestForm` (`<div data-react="Nome">`) |
| `src/css/` | Tailwind (`main.css`) e estilo do `wp-login.php` (`login.css`) |
| `tools/` | Importador de templates |

Na ativação, o tema cria as páginas (`/entrar`, `/criar-conta`, `/conta`, `/solicitar`, `/sobre`, `/politica-de-privacidade`), os menus, liga o cadastro de usuários e troca os permalinks para `/%postname%/`.

## Configuração (Configurações → get a live)

- **Google Client ID**: crie um ID do cliente OAuth do tipo "Aplicativo da Web" no Google Cloud Console e adicione a URL do site em "Origens JavaScript autorizadas". Sem ele, o botão do Google fica escondido.
- **E-mails** de contato (aparece na Política de Privacidade) e de aviso de solicitações.
- **Scripts de estatística/marketing**: só rodam depois do aceite no banner de cookies.
- **Redes sociais** do rodapé.

## Templates

Cada template é um post `ga_template` com cenas, recursos, requisitos e o ZIP. O ZIP fica em `wp-content/uploads/getalive-private/` (bloqueado por `.htaccess`) e só é entregue a usuários logados por `?ga_download=ID` com nonce.

Para importar ou atualizar a partir da pasta do template:

```bash
php tools/import-template.php tools/templates/vapo.json
```

O manifesto aponta para a pasta de origem, as imagens das cenas e o ZIP (`dist/*.zip`, usa o mais recente). Rodar de novo atualiza o mesmo post e só reenvia o que mudou.

## Templates pagos (preparado, desligado)

Marcar "Template pago" + preço no painel deixa o template **oculto** no site até que:

1. o WooCommerce esteja ativo, e
2. "Venda de templates" esteja ligada em Configurações → get a live.

Com os dois, salvar o template cria/atualiza um produto virtual oculto no catálogo, a página do template vira a vitrine ("Comprar por R$ X" → checkout) e o download é liberado para quem comprou (`wc_customer_bought_product`).
