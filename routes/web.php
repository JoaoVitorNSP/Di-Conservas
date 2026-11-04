<?php
declare(strict_types=1);

require __DIR__ . '/Router.php';

$router = new Router();

// Site
$router->get('/', 'ProductController', 'home');
$router->get('/api/products', 'ProductController', 'api');

// Acesso ao painel
$router->get('/admin', 'AuthController', 'showLogin');
$router->post('/admin/login', 'AuthController', 'login');
$router->post('/admin/setup', 'AuthController', 'setup');
$router->post('/admin/logout', 'AuthController', 'logout');

// Produtos
$router->get('/admin/products', 'ProductController', 'index');
$router->get('/admin/products/create', 'ProductController', 'create');
$router->post('/admin/products', 'ProductController', 'store');
$router->get('/admin/products/{id}/edit', 'ProductController', 'edit');
$router->post('/admin/products/{id}', 'ProductController', 'update');
$router->post('/admin/products/{id}/toggle', 'ProductController', 'toggle');
$router->post('/admin/products/{id}/delete', 'ProductController', 'destroy');

// Categorias
$router->get('/admin/categories', 'CategoryController', 'index');
$router->post('/admin/categories', 'CategoryController', 'store');
$router->post('/admin/categories/{id}', 'CategoryController', 'update');
$router->post('/admin/categories/{id}/delete', 'CategoryController', 'destroy');

// Usuários
$router->get('/admin/users', 'UserController', 'index');
$router->get('/admin/users/create', 'UserController', 'create');
$router->post('/admin/users', 'UserController', 'store');
$router->get('/admin/users/{id}/edit', 'UserController', 'edit');
$router->post('/admin/users/{id}', 'UserController', 'update');
$router->post('/admin/users/{id}/delete', 'UserController', 'destroy');

$router->resolve();
