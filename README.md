# Di Conservas

Catálogo web da loja, com carrinho que envia o pedido pelo WhatsApp (sem pagamento online), e um painel para cadastrar produtos.

Feito em HTML, CSS e JavaScript no site, e PHP 8.1 ou mais novo no painel. Os produtos ficam em `products.json`; não há banco de dados.

## Como rodar no computador

```bash
php -S 127.0.0.1:8000
```

Abra http://127.0.0.1:8000. Esse servidor de teste **não aplica o `.htaccess`**: as proteções de pastas só valem no Apache.

Com Docker: `docker compose up --build`, depois http://localhost.

## Criar o acesso ao painel

O projeto não vem com senha. Na pasta do projeto, rode:

```bash
php scripts/criar-senha.php
```

Com Docker: `docker compose exec -u www-data app php scripts/criar-senha.php`.

O painel fica em `/admin/`. O mesmo comando redefine a senha, se você a esquecer. Com o painel aberto, dá para trocá-la em **Senha**.

## Publicar

1. Use Apache com `AllowOverride All` e os módulos `headers`, `expires` e `deflate`.
2. Ative HTTPS. Sem ele, a senha do painel trafega sem proteção.
3. PHP precisa das extensões `gd` (com WEBP), `fileinfo` e `mbstring`.
4. O usuário do servidor precisa poder escrever em `products.json`, `assets/produtos/` e `data/`.
5. Rode `php scripts/criar-senha.php` no servidor.

Ao atualizar um site já no ar, **não sobrescreva** `products.json`, `assets/produtos/` nem `data/`: é lá que estão os cadastros.

## Onde mexer

| O que | Onde |
|---|---|
| Número do WhatsApp, regra e quantidade mínima do atacado | início de `assets/js/site.js` (`WHATSAPP`, `WHOLESALE_SCOPE`, `WHOLESALE_MIN`) |
| Textos, endereço e horário | `index.html` |
| Visual do site | `assets/css/site.css` |

Ao alterar `site.css` ou `site.js`, aumente o `?v=` no `index.html`. O navegador guarda esses arquivos por 1 mês.

## Estrutura

```
index.html            site público
products-api.php      entrega os produtos visíveis ao site
products.json         catálogo
assets/css, assets/js visual e comportamento do site
assets/produtos/      fotos dos produtos
admin/                páginas do painel
lib/                  regras do painel (autenticação, produtos, imagens)
scripts/              comandos de linha de comando
data/                 privado: senha (hash), tentativas de login e backups
```

## Backups

Cada alteração no catálogo guarda uma cópia do arquivo anterior em `data/backups/` (as 30 mais recentes). Para restaurar, copie a versão desejada por cima de `products.json`. Essas cópias ficam no próprio servidor e não substituem um backup externo, que deve incluir também `assets/produtos/`.
