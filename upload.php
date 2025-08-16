<?php
// upload.php - Processa o upload de imagens e cadastro de produtos

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
    
    // Cria objeto do produto
    $product = [
        'name' => $name,
        'category' => $category,
        'description' => $description,
        'weight' => $weight,
        'retailPrice' => $retailPrice,
        'wholesalePrice' => $wholesalePrice,
        'image' => $imageName
    ];
    
    // Salva em products.json
    $productsFile = 'products.json';
    $products = [];
    
    if (file_exists($productsFile)) {
        $products = json_decode(file_get_contents($productsFile), true) ?? [];
    }
    
    $products[] = $product;
    file_put_contents($productsFile, json_encode($products, JSON_PRETTY_PRINT));
    
    echo 'Produto cadastrado com sucesso!';
} else {
    http_response_code(405);
    echo 'Método não permitido';
}
?>
