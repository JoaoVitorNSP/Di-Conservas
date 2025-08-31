<?php

require_once __DIR__ . '/Router.php';

// Inicializa o roteador
$router = new Router();

// Rotas públicas
$router->get('/', 'ProductController', 'index');
$router->get('/products', 'ProductController', 'index');
$router->get('/products/{id}', 'ProductController', 'show');

// API de produtos
$router->get('/api/products', 'ProductController', 'api');
$router->get('/api/products/search', 'ProductController', 'search');
$router->get('/api/products/category/{category}', 'ProductController', 'getByCategory');

// Rotas de administração - Login
$router->get('/admin', 'AuthController', 'showLogin');
$router->post('/admin/login', 'AuthController', 'login');
$router->get('/admin/logout', 'AuthController', 'logout');

// Rotas de administração - Dashboard
$router->get('/admin/dashboard', 'AdminController', 'dashboard');
$router->post('/admin/session-refresh', 'AdminController', 'refreshSession');

// Rotas de administração - Produtos
$router->get('/admin/products', 'AdminController', 'products');
$router->get('/admin/products/create', 'ProductController', 'create');
$router->post('/admin/products', 'ProductController', 'store');
$router->get('/admin/products/{id}/edit', 'ProductController', 'edit');
$router->post('/admin/products/{id}', 'ProductController', 'update');
$router->post('/admin/products/{id}/delete', 'ProductController', 'destroy');

// Rotas de administração - Usuários
$router->get('/admin/users', 'UserController', 'index');
$router->get('/admin/users/create', 'UserController', 'create');
$router->post('/admin/users/store', 'UserController', 'store');
$router->get('/admin/users/{uuid}/edit', 'UserController', 'edit');
$router->post('/admin/users/{uuid}/update', 'UserController', 'update');
$router->post('/admin/users/{uuid}/delete', 'UserController', 'destroy');

// Resolve a rota atual
$router->resolve();
