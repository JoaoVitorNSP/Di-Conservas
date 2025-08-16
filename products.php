<?php
// products.php - API para listar produtos

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $productsFile = 'products.json';
    
    if (file_exists($productsFile)) {
        $products = file_get_contents($productsFile);
        echo $products;
    } else {
        echo json_encode([]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
