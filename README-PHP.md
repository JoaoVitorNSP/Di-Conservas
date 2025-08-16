# README - Sistema Di Conservas com PHP/Apache

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

### Docker:
- `Dockerfile` - Configurado para PHP 8.2 com Apache
- `docker-compose.yml` - Orquestração do container
- `.dockerignore` - Arquivos ignorados no build

## Como usar:

### 🐳 **Com Docker (Recomendado):**
```bash
# Build e executar
docker-compose up --build

# Apenas executar (após primeiro build)
docker-compose up

# Parar
docker-compose down
```

**Acesso Docker:** http://localhost/

### 🖥️ **Instalação Manual:**

1. **Copie todos os arquivos para o diretório do Apache** (ex: `/var/www/html/di-conservas/`)

2. **Configure o Apache:**
   ```bash
   a2enmod rewrite expires deflate headers
   systemctl restart apache2
   ```

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
- ✅ Container Docker pronto para deploy
