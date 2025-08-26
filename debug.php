<?php

// Debug script para verificar se os arquivos estão sendo carregados

echo "<h1>Debug do Autoloader</h1>";

// Carrega o autoloader
require_once __DIR__ . '/app/Autoloader.php';

echo "<h2>1. Testando se o autoloader está carregado</h2>";
echo class_exists('Autoloader') ? "✅ Autoloader carregado" : "❌ Autoloader não carregado";
echo "<br>";

echo "<h2>2. Verificando arquivos</h2>";
$files = [
    'app/Controllers/BaseController.php',
    'app/Controllers/ProductController.php',
    'app/Models/BaseModel.php',
    'app/Models/Product.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo $file . ": " . (file_exists($path) ? "✅ Existe" : "❌ Não existe") . "<br>";
}

echo "<h2>3. Tentando carregar classes manualmente</h2>";

// Tenta carregar BaseController
echo "BaseController: ";
try {
    require_once __DIR__ . '/app/Controllers/BaseController.php';
    echo "✅ Carregado<br>";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

// Tenta carregar ProductController
echo "ProductController: ";
try {
    require_once __DIR__ . '/app/Controllers/ProductController.php';
    echo "✅ Carregado<br>";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Testando class_exists</h2>";
echo "App\\Controllers\\BaseController: " . (class_exists('App\\Controllers\\BaseController') ? "✅ Existe" : "❌ Não existe") . "<br>";
echo "App\\Controllers\\ProductController: " . (class_exists('App\\Controllers\\ProductController') ? "✅ Existe" : "❌ Não existe") . "<br>";

echo "<h2>5. Testando instanciação</h2>";
try {
    $controller = new \App\Controllers\ProductController();
    echo "✅ ProductController instanciado com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro ao instanciar: " . $e->getMessage() . "<br>";
}

?>
