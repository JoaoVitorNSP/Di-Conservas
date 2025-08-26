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
$router->get('/admin', 'AdminController', 'login');
$router->post('/admin/login', 'AdminController', 'authenticate');
$router->get('/admin/logout', 'AdminController', 'logout');

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

// Resolve a rota atual
$router->resolve();
