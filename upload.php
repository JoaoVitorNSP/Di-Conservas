<?php
// upload.php - Processa o upload de imagens e cadastro de produtos

// Verifica autenticação
require_once 'auth.php';
checkAdminAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dados do formulário
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $retailPrice = $_POST['retailPrice'] ?? '';
    $wholesalePrice = $_POST['wholesalePrice'] ?? '';
    
    $uploadDir = 'assets/';
    $imageName = null;
    
    // Processa upload da imagem
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = time() . '-' . $_FILES['image']['name'];
        $uploadPath = $uploadDir . $imageName;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            die('Erro ao fazer upload da imagem.');
        }
    }
    
    // Salva em products.json
    $productsFile = 'products.json';
    $products = [];
    
    if (file_exists($productsFile)) {
        $products = json_decode(file_get_contents($productsFile), true) ?? [];
    }
    
    // Gera ID único para o novo produto
    $maxId = 0;
    foreach ($products as $existingProduct) {
        if (isset($existingProduct['id']) && $existingProduct['id'] > $maxId) {
            $maxId = $existingProduct['id'];
        }
    }
    $newId = $maxId + 1;
    
    // Cria objeto do produto com ID
    $product = [
        'id' => $newId,
        'name' => $name,
        'category' => $category,
        'description' => $description,
        'weight' => $weight,
        'retailPrice' => (float)$retailPrice,
        'wholesalePrice' => (float)$wholesalePrice,
        'image' => $imageName
    ];
    
    $products[] = $product;
    file_put_contents($productsFile, json_encode($products, JSON_PRETTY_PRINT));
    
    echo '<script>alert("Produto cadastrado com sucesso!"); window.location.href="admin-dashboard.php";</script>';
} else {
    http_response_code(405);
    echo 'Método não permitido';
}
?>
