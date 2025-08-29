<?php

/**
 * Front Controller - Ponto de entrada da aplicação
 */

// Define o diretório raiz da aplicação
define('ROOT_PATH', dirname(__DIR__));

// Carrega as variáveis de ambiente
require_once ROOT_PATH . '/app/EnvLoader.php';
EnvLoader::load(ROOT_PATH . '/.env');

// Carrega o autoloader
require_once ROOT_PATH . '/app/Autoloader.php';

// Força o carregamento das classes base primeiro
require_once ROOT_PATH . '/app/Models/BaseModel.php';

// Carregamento manual das classes principais para garantir funcionamento
$coreFiles = [
    ROOT_PATH . '/app/Models/Product.php',
    ROOT_PATH . '/app/Models/Admin.php',
    ROOT_PATH . '/app/Controllers/BaseController.php',
    ROOT_PATH . '/app/Controllers/ProductController.php',
    ROOT_PATH . '/app/Controllers/AdminController.php',
    ROOT_PATH . '/routes/Router.php'
];

foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// Trata requisições de assets (imagens, CSS, JS)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/i', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath)) {
        $mimeType = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentType = $mimeType[$extension] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $contentType);
        readfile($filePath);
        exit;
    }
}

// Carrega as rotas da aplicação
require_once ROOT_PATH . '/routes/web.php';
