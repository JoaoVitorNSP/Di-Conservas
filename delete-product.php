<?php
// delete-product.php - Deleta produto via AJAX

require_once 'auth.php';
checkAdminAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['id'] ?? null;

if (!$productId) {
    echo json_encode(['success' => false, 'error' => 'ID do produto não fornecido']);
    exit;
}

// Carrega produtos do JSON
$productsFile = 'products.json';
$products = [];

if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true) ?? [];
}

// Encontra e remove o produto
$productFound = false;
$imageToDelete = null;

for ($i = 0; $i < count($products); $i++) {
    if ($products[$i]['id'] == $productId) {
        $imageToDelete = $products[$i]['image'];
        array_splice($products, $i, 1);
        $productFound = true;
        break;
    }
}

if (!$productFound) {
    echo json_encode(['success' => false, 'error' => 'Produto não encontrado']);
    exit;
}

// Remove imagem do servidor se for local
if ($imageToDelete && strpos($imageToDelete, 'assets/') === 0 && file_exists($imageToDelete)) {
    unlink($imageToDelete);
}

// Salva produtos atualizados
file_put_contents($productsFile, json_encode($products, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
?>
