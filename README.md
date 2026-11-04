# Di Conservas

Catálogo web da loja, com carrinho que envia o pedido pelo WhatsApp (sem pagamento online), e um painel para gerenciar produtos, categorias e usuários.

Feito em PHP 8.1 ou mais novo, MySQL e, no site, HTML, CSS e JavaScript sem frameworks. Não precisa de Composer.

## Como rodar no computador

Com o MySQL ligado (no Laragon, **Iniciar tudo**), na pasta do projeto:

```bash
copy .env.example .env             # no Linux/Mac: cp
php scripts/instalar-banco.php     # cria o banco, as tabelas e os produtos iniciais
php scripts/criar-admin.php        # cria o seu acesso ao painel
php -S 127.0.0.1:8000 -t public
```

- Site: http://127.0.0.1:8000
- Painel: http://127.0.0.1:8000/admin

Ajuste `DB_USER` e `DB_PASS` no `.env` se o seu MySQL não usar `root` sem senha.

Com Docker: crie um `.env` com `DB_PASS` e `SETUP_TOKEN`, rode `docker compose up --build` e abra http://localhost. O banco é preparado na primeira subida.

## Publicar na Hostinger

1. No hPanel, crie um banco MySQL e anote nome, usuário e senha.
2. Em **phpMyAdmin**, importe `database/schema.sql` e depois `database/produtos-iniciais.sql`.
3. Envie o projeto para `public_html`, incluindo os arquivos `.htaccess`. O `.htaccess` da raiz direciona tudo para `public/`, e o restante fica inacessível pelo navegador.
4. Crie o arquivo `.env` em `public_html` a partir do `.env.example`, com os dados do banco, `APP_DEBUG=false` e um `SETUP_TOKEN` com pelo menos 16 caracteres.
5. Ative o SSL do domínio. Sem HTTPS, a senha do painel trafega sem proteção.
6. Abra `/admin`, informe o `SETUP_TOKEN` e crie o seu acesso. Depois, apague o `SETUP_TOKEN` do `.env`.

A pasta `public/assets/produtos` precisa permitir escrita pelo PHP. Em outro servidor Apache, o ideal é apontar a raiz do site direto para `public/`.

Ao atualizar um site já no ar, **não sobrescreva** `.env` nem `public/assets/produtos/`: os dados de produtos ficam no banco, e as fotos, nessa pasta.

## Esqueci a senha

Rode `php scripts/criar-admin.php` e informe o mesmo e-mail. Isso exige acesso SSH ao servidor. Outro administrador também pode trocar a sua senha em **Usuários**.

## Onde mexer

| O que | Onde |
|---|---|
| Número do WhatsApp, regra e quantidade mínima do atacado | início de `public/assets/js/site.js` (`WHATSAPP`, `WHOLESALE_SCOPE`, `WHOLESALE_MIN`) |
| Textos, endereço e horário do site | `resources/views/products/index.php` |
| Visual do site | `public/assets/css/site.css` |
| Unidades de medida | tabela `measurement_units` |

Ao alterar CSS ou JS, aumente o `?v=` onde o arquivo é carregado. O navegador guarda esses arquivos por 1 mês.

## Estrutura

```
public/              única pasta acessível pelo navegador (index.php e assets)
app/Controllers      regras de cada área
app/Models           acesso ao banco
app/Services         processamento das fotos
routes/web.php       lista de endereços
resources/views      telas
config/database.php  conexão (lê o .env)
database/            estrutura do banco e produtos iniciais
scripts/             comandos de instalação e de senha
```

## Backups

Dados ficam no MySQL e fotos em `public/assets/produtos/`. Inclua os dois nos seus backups; excluir um produto no painel não pode ser desfeito, então use **Ocultar** para tirar algo do site temporariamente.
