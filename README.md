# README - Migração para Apache/PHP

## Arquivos convertidos para PHP:

### Scripts PHP criados:
- `upload.php` - Processa upload de imagens e cadastro de produtos
- `admin-login.php` - Processa login do administrador

### Páginas HTML atualizadas:
- `admin.html` - Formulário de login aponta para `admin-login.php`
- `admin-dashboard.html` - Página de sucesso após login
- `cadastro.html` - Formulário para cadastro de produtos
- `admin-login.html` - Atualizado para usar links relativos

### Configuração do servidor:
- `.htaccess` - Configurações de segurança e performance
- `apache-config.conf` - Configuração do VirtualHost

## Como usar:

1. **Copie todos os arquivos para o diretório do Apache** (ex: `/var/www/html/di-conservas/`)

2. **Configure o Apache:**
   - Ative mod_rewrite: `a2enmod rewrite`
   - Ative mod_expires: `a2enmod expires`
   - Ative mod_deflate: `a2enmod deflate`
   - Ative mod_headers: `a2enmod headers`

3. **Permissões necessárias:**
   ```bash
   chmod 755 assets/
   chmod 666 products.json
   ```

4. **Acesso:**
   - Página principal: `http://localhost/`
   - Admin: `http://localhost/admin.html`
   - Cadastro: `http://localhost/cadastro.html`

## Credenciais de admin:
- Usuário: `admin`
- Senha: `1234`

## Funcionalidades:
- ✅ Upload de imagens para pasta `assets/`
- ✅ Cadastro de produtos em JSON
- ✅ Login de administrador
- ✅ Proteção de arquivos sensíveis
- ✅ Otimizações de performance
- ✅ Suporte a MySQL
- ✅ Framework Laravel (opcional)

## Executando com Docker

### Uso básico:
```bash
docker-compose up --build
```

### Instalando Laravel automaticamente:
```bash
# No Windows PowerShell:
$env:INSTALL_LARAVEL="1"; docker-compose up --build

# No Linux/Mac:
INSTALL_LARAVEL=1 docker-compose up --build
```

### Configuração do banco:
O sistema inclui um serviço MySQL configurado com:
- **Host:** mysql
- **Porta:** 3306
- **Database:** di_conservas
- **Usuário:** di_user
- **Senha:** di_pass

### Comandos úteis após instalação do Laravel:
```bash
# Gerar chave da aplicação
docker exec -it di-conservas-php-app php laravel/artisan key:generate

# Executar migrações
docker exec -it di-conservas-php-app php laravel/artisan migrate

# Acessar Laravel
# http://localhost/laravel/public
```
