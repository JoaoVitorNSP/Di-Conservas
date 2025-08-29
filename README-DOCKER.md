# 🐳 Di Conservas MVC - Docker Setup

Documentação para executar o projeto Di Conservas usando Docker e a nova arquitetura MVC.

## 🚀 Início Rápido

### Pré-requisitos
- Docker Desktop instalado
- Docker Compose instalado

### Setup Automático (Recomendado)

**Linux/Mac:**
```bash
chmod +x docker-setup.sh
./docker-setup.sh setup
```

**Windows PowerShell:**
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\docker-setup.ps1 setup
```

### Setup Manual

1. **Clone e navegue para o projeto:**
   ```bash
   cd Di-Conservas-Joaozinho
   ```

2. **Configure o ambiente:**
   ```bash
   cp .env.example .env
   ```

3. **Construa e inicie os containers:**
   ```bash
   docker-compose up --build -d
   ```

4. **Acesse a aplicação:**
   - Site: http://localhost
   - Admin: http://localhost/admin
   - API: http://localhost/api/products

## 🐳 Containers

### Aplicação PHP (app)
- **Imagem:** PHP 8.2 com Apache
- **Porta:** 80
- **DocumentRoot:** `/var/www/html/public` (estrutura MVC)
- **Extensões:** PDO MySQL, ZIP, mbstring, bcmath

### Banco de Dados MySQL (mysql)
- **Imagem:** MySQL 8.0
- **Porta:** 3306
- **Database:** di_conservas
- **Usuário:** di_user
- **Senha:** di_pass

## 📁 Estrutura no Container

```
/var/www/html/
├── public/                # DocumentRoot (acessível via web)
│   ├── index.php         # Front Controller
│   └── assets/           # Imagens e arquivos estáticos
├── app/                  # Lógica da aplicação
│   ├── Controllers/      # Controladores
│   └── Models/          # Modelos
├── resources/           # Templates e recursos
│   └── views/           # Views (templates)
├── routes/              # Definição de rotas
├── config/              # Configurações
└── storage/             # Logs e cache
```

## 🛠️ Comandos Úteis

### Gerenciamento de Containers
```bash
# Iniciar containers
docker-compose up -d

# Parar containers
docker-compose down

# Reconstruir containers
docker-compose up --build -d

# Ver logs
docker-compose logs -f

# Ver logs de um serviço específico
docker-compose logs -f app
docker-compose logs -f mysql
```

### Acesso aos Containers
```bash
# Acessar container da aplicação
docker-compose exec app bash

# Acessar MySQL
docker-compose exec mysql mysql -u di_user -p di_conservas
```

### Desenvolvimento
```bash
# Instalar dependências Composer
docker-compose exec app composer install

# Verificar status dos containers
docker-compose ps

# Limpar containers e volumes
docker-compose down -v
```

## 🔧 Configuração

### Variáveis de Ambiente (.env)
```bash
# Banco de Dados
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=di_conservas
DB_USERNAME=di_user
DB_PASSWORD=di_pass

# Aplicação
APP_NAME="Di Conservas MVC"
APP_ENV=development
APP_DEBUG=true
```

### Volumes Montados
- `./public:/var/www/html/public` - Pasta pública
- `./app:/var/www/html/app` - Código da aplicação
- `./resources:/var/www/html/resources` - Templates
- `./routes:/var/www/html/routes` - Rotas
- `./config:/var/www/html/config` - Configurações
- `./products.json:/var/www/html/products.json` - Dados temporários

## 🌐 URLs da Aplicação

### Frontend Público
- **Homepage:** http://localhost
- **Catálogo:** http://localhost/products
- **Produto:** http://localhost/products/{id}

### API REST
- **Produtos:** http://localhost/api/products
- **Busca:** http://localhost/api/products/search?q=termo
- **Categoria:** http://localhost/api/products/category/{categoria}

### Área Administrativa
- **Login:** http://localhost/admin
- **Dashboard:** http://localhost/admin/dashboard
- **Produtos:** http://localhost/admin/products
- **Criar:** http://localhost/admin/products/create
- **Editar:** http://localhost/admin/products/{id}/edit

### Credenciais Admin
- **Usuário:** admin
- **Senha:** password

## 🔍 Troubleshooting

### Container não inicia
```bash
# Verificar logs
docker-compose logs app

# Reconstruir sem cache
docker-compose build --no-cache
docker-compose up -d
```

### Problemas de permissão
```bash
# Corrigir permissões no container
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 755 /var/www/html/public/assets
```

### MySQL não conecta
```bash
# Verificar se MySQL está rodando
docker-compose exec mysql mysql -u root -p

# Verificar logs do MySQL
docker-compose logs mysql
```

### Resetar tudo
```bash
# Remove containers, volumes e redes
docker-compose down -v
docker system prune -f

# Reconstruir do zero
docker-compose up --build -d
```

## 📊 Monitoramento

### Verificar Status
```bash
# Status dos containers
docker-compose ps

# Uso de recursos
docker stats

# Logs em tempo real
docker-compose logs -f
```

### Arquivos de Log
- **Apache:** Logs disponíveis via `docker-compose logs app`
- **MySQL:** Logs disponíveis via `docker-compose logs mysql`
- **Aplicação:** `storage/logs/` (se configurado)

## 🚀 Deploy em Produção

Para produção, considere:

1. **Usar imagem otimizada:**
   ```dockerfile
   FROM php:8.2-apache-slim
   ```

2. **Configurar variáveis de ambiente:**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   ```

3. **Usar volumes nomeados para dados persistentes**
4. **Configurar proxy reverso (Nginx)**
5. **Habilitar HTTPS**
6. **Configurar backup do banco de dados**

## 📝 Desenvolvimento Local

Para desenvolvimento com hot-reload:

1. **Use volumes para código:**
   ```yaml
   volumes:
     - ./:/var/www/html
   ```

2. **Configure Xdebug (opcional):**
   ```dockerfile
   RUN pecl install xdebug && docker-php-ext-enable xdebug
   ```

3. **Use composer local:**
   ```bash
   docker-compose exec app composer install
   ```

---

## 🎯 Próximos Passos

1. **Migrar dados JSON para MySQL**
2. **Implementar migrations de banco**
3. **Configurar Redis para cache**
4. **Adicionar testes automatizados**
5. **Configurar CI/CD**

Para mais informações, consulte `README-MVC.md`.
