# Di Conservas - Sistema MVC

Sistema de catálogo de produtos para Di Conservas, agora estruturado seguindo o padrão arquitetural MVC (Model-View-Controller) do Laravel.

## 📁 Estrutura do Projeto

```
Di-Conservas-Joaozinho/
├── app/
│   ├── Controllers/          # Controladores (regras de negócio)
│   │   ├── BaseController.php
│   │   ├── ProductController.php
│   │   └── AdminController.php
│   ├── Models/              # Modelos (acesso aos dados)
│   │   ├── BaseModel.php
│   │   ├── Product.php
│   │   └── Admin.php
│   └── Autoloader.php       # Carregador automático de classes
├── resources/
│   └── views/               # Views (interface visual)
│       ├── products/
│       │   └── index.php
│       ├── admin/
│       │   ├── login.php
│       │   ├── dashboard.php
│       │   └── products/
│       └── errors/
├── routes/
│   ├── Router.php           # Sistema de roteamento
│   └── web.php             # Definição das rotas
├── config/
│   └── database.php        # Configuração do banco de dados
├── public/                 # Pasta pública (ponto de entrada)
│   ├── index.php          # Front Controller
│   ├── assets/            # Imagens e arquivos estáticos
│   └── .htaccess
├── products.json          # Dados temporários (até migrar para BD)
└── .env.example          # Configurações de ambiente
```

## 🚀 Funcionalidades

### Área Pública
- **Catálogo de produtos** com paginação e filtros
- **Busca por nome/descrição** e filtro por categoria
- **Modal de detalhes** dos produtos
- **Design responsivo** com Tailwind CSS
- **API REST** para consulta de produtos

### Área Administrativa
- **Sistema de autenticação** com sessão temporizada
- **Dashboard** com estatísticas
- **CRUD completo** de produtos
- **Upload de imagens** dos produtos
- **Gerenciamento de categorias**

## 🛠️ Tecnologias Utilizadas

- **PHP 7.4+** - Linguagem principal
- **MVC Pattern** - Arquitetura inspirada no Laravel
- **JSON** - Armazenamento temporário de dados
- **Tailwind CSS** - Framework CSS
- **JavaScript** - Interatividade frontend
- **Apache/Nginx** - Servidor web

## ⚙️ Instalação e Configuração

### 1. Pré-requisitos
- PHP 7.4 ou superior
- Servidor web (Apache/Nginx)
- MySQL (opcional, para futuras migrações)

### 2. Configuração do Ambiente

1. **Clone ou baixe o projeto**
2. **Configure o servidor web** para apontar para a pasta `public/`
3. **Configure as permissões** da pasta `public/assets/` para escrita
4. **Copie o arquivo de ambiente** (opcional):
   ```bash
   cp .env.example .env
   ```

### 3. Configuração do Apache

Se usando Apache, certifique-se que o mod_rewrite está habilitado e configure o Virtual Host:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/caminho/para/Di-Conservas-Joaozinho/public"
    ServerName diconservas.local
    
    <Directory "C:/caminho/para/Di-Conservas-Joaozinho/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 4. Configuração do Nginx

Para Nginx, use esta configuração:

```nginx
server {
    listen 80;
    server_name diconservas.local;
    root /caminho/para/Di-Conservas-Joaozinho/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔗 Rotas da Aplicação

### Rotas Públicas
- `GET /` - Página inicial com catálogo
- `GET /products` - Lista de produtos
- `GET /products/{id}` - Detalhes do produto
- `GET /api/products` - API JSON dos produtos
- `GET /api/products/search?q=termo` - Busca produtos
- `GET /api/products/category/{categoria}` - Produtos por categoria

### Rotas Administrativas
- `GET /admin` - Login administrativo
- `POST /admin/login` - Processar login
- `GET /admin/dashboard` - Dashboard
- `GET /admin/products` - Listar produtos
- `GET /admin/products/create` - Formulário de criação
- `POST /admin/products` - Salvar produto
- `GET /admin/products/{id}/edit` - Formulário de edição
- `POST /admin/products/{id}` - Atualizar produto
- `POST /admin/products/{id}/delete` - Excluir produto
- `GET /admin/logout` - Logout

## 👤 Credenciais de Acesso

**Administrador:**
- **Usuário:** admin
- **Senha:** password

## 📊 Estrutura MVC

### Models (Modelos)
- **BaseModel:** Classe base com métodos comuns de banco de dados
- **Product:** Gerencia produtos (atualmente via JSON)
- **Admin:** Gerencia autenticação de administradores

### Views (Visões)
- **products/index:** Catálogo público de produtos
- **admin/login:** Página de login administrativo
- **admin/dashboard:** Dashboard administrativo
- **admin/products/*:** CRUD de produtos

### Controllers (Controladores)
- **BaseController:** Funcionalidades comuns (validação, redirecionamento, etc.)
- **ProductController:** Lógica dos produtos (público e admin)
- **AdminController:** Lógica administrativa (auth, dashboard)

## 🔧 Principais Classes

### Router
Sistema de roteamento que mapeia URLs para controllers/actions:
```php
$router->get('/products', 'ProductController', 'index');
$router->post('/admin/login', 'AdminController', 'authenticate');
```

### BaseModel
Classe base para modelos com métodos CRUD:
```php
$products = $model->all();
$product = $model->find($id);
$model->create($data);
$model->update($id, $data);
$model->delete($id);
```

### BaseController
Classe base para controllers com utilitários:
```php
$this->view('template', $data);
$this->json($response);
$this->redirect('/path');
$this->validate($data, $rules);
```

## 🚀 Próximos Passos

1. **Migração para Banco de Dados:** Substituir JSON por MySQL/PostgreSQL
2. **Sistema de Autenticação:** Implementar múltiplos usuários admin
3. **Cache:** Implementar sistema de cache para melhor performance
4. **API RESTful:** Expandir API para operações CRUD completas
5. **Testes:** Implementar testes unitários e de integração
6. **Deploy:** Configuração para produção

## 📝 Contribuição

Para contribuir com o projeto:

1. Faça um fork do repositório
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

---

**Di Conservas** - Sistema desenvolvido com ❤️ usando PHP e padrão MVC
