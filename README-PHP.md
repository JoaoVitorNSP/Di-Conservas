# README - Sistema Di Conservas com PHP/Apache

## Arquivos convertidos para PHP:

### Scripts PHP criados:
- `upload.php` - Processa upload de imagens e cadastro de produtos (gera ID único)
- `admin-login.php` - Processa login do administrador com sessões
- `auth.php` - Sistema de autenticação e controle de sessões
- `logout.php` - Logout seguro
- `session-refresh.php` - Mantém sessão ativa via AJAX
- `products.php` - API para listar produtos em formato JSON

### Páginas HTML atualizadas:
- `admin.php` - Página de login com verificação automática de sessão
- `admin.html` - Versão estática da página de login (mantida para compatibilidade)
- `admin-dashboard.php` - Dashboard administrativo protegido
- `admin-dashboard.html` - Página de sucesso após login (estática)
- `cadastro.php` - Formulário para cadastro de produtos (protegido)
- `cadastro.html` - Versão estática do formulário
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
   - Admin: `http://localhost/admin.php` (redireciona automaticamente se logado)
   - Dashboard: `http://localhost/admin-dashboard.php` (apenas se logado)
   - Cadastro: `http://localhost/cadastro.php` (apenas se logado)

## Credenciais de admin:
- Usuário: `admin`
- Senha: `1234`

## Funcionalidades:
- ✅ Upload de imagens para pasta `assets/`
- ✅ Cadastro de produtos em JSON com ID único
- ✅ Carregamento dinâmico de produtos via JSON/API
- ✅ Sistema de sessões com duração de 10 minutos
- ✅ Login automático se sessão ativa
- ✅ Redirecionamento inteligente para dashboard
- ✅ Login de administrador protegido
- ✅ Proteção de páginas administrativas
- ✅ API REST para produtos
- ✅ Proteção de arquivos sensíveis
- ✅ Otimizações de performance
- ✅ Container Docker pronto para deploy
