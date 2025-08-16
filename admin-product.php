<?php
// admin-product.php - Edita produto individual

require_once 'auth.php';
checkAdminAuth();

$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: admin-products.php');
    exit;
}

// Carrega produtos do JSON
$productsFile = 'products.json';
$products = [];
$product = null;

if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true) ?? [];
    
    // Encontra o produto pelo ID
    foreach ($products as $p) {
        if ($p['id'] == $productId) {
            $product = $p;
            break;
        }
    }
}

if (!$product) {
    header('Location: admin-products.php?error=not_found');
    exit;
}

// Processa formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $retailPrice = $_POST['retailPrice'] ?? '';
    $wholesalePrice = $_POST['wholesalePrice'] ?? '';
    
    $uploadDir = 'assets/';
    $imageName = $product['image']; // Mantém imagem atual por padrão
    
    // Processa nova imagem se enviada
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = time() . '-' . $_FILES['image']['name'];
        $uploadPath = $uploadDir . $imageName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            // Remove imagem antiga se era local (não placeholder)
            if ($product['image'] && strpos($product['image'], 'assets/') === 0 && file_exists($product['image'])) {
                unlink($product['image']);
            }
        } else {
            $imageName = $product['image']; // Mantém imagem atual se upload falhou
        }
    }
    
    // Atualiza produto no array
    for ($i = 0; $i < count($products); $i++) {
        if ($products[$i]['id'] == $productId) {
            $products[$i] = [
                'id' => (int)$productId,
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'weight' => $weight,
                'retailPrice' => (float)$retailPrice,
                'wholesalePrice' => (float)$wholesalePrice,
                'image' => $imageName
            ];
            break;
        }
    }
    
    // Salva no arquivo
    file_put_contents($productsFile, json_encode($products, JSON_PRETTY_PRINT));
    
    header('Location: admin-products.php?updated=' . $productId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto - Di Conservas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        h2 { text-align: center; margin-bottom: 24px; color: #333; }
        .session-info { background: #e8f5e8; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .product-info { background: #f8f9fa; padding: 16px; border-radius: 4px; margin-bottom: 24px; }
        
        .form-row { display: flex; gap: 16px; margin-bottom: 16px; }
        .form-group { flex: 1; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        textarea { resize: vertical; min-height: 100px; }
        
        .image-section { margin-bottom: 24px; }
        .current-image { text-align: center; margin-bottom: 16px; }
        .current-image img { max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px; border: 2px solid #ddd; }
        
        .actions { display: flex; gap: 16px; justify-content: center; margin-top: 24px; }
        .btn { padding: 12px 24px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; }
        .btn-primary { background: #2d7a2d; color: #fff; }
        .btn-primary:hover { background: #256325; }
        .btn-secondary { background: #6c757d; color: #fff; }
        .btn-secondary:hover { background: #545b62; }
        
        .price-info { font-size: 12px; color: #666; margin-top: 4px; }
        
        @media (max-width: 768px) {
            .form-row { flex-direction: column; }
            .actions { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="session-info">
            Logado como: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong> | 
            Sessão expira às: <strong><?php echo date('H:i:s', $_SESSION['admin_last_activity'] + 600); ?></strong>
        </div>
        
        <h2>Editar Produto</h2>
        
        <div class="product-info">
            <strong>ID:</strong> <?php echo htmlspecialchars($product['id']); ?> | 
            <strong>Produto:</strong> <?php echo htmlspecialchars($product['name']); ?>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nome do Produto</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Categoria</label>
                    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="weight">Peso</label>
                    <input type="text" id="weight" name="weight" value="<?php echo htmlspecialchars($product['weight']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="retailPrice">Preço de Varejo (R$)</label>
                    <input type="number" step="0.01" id="retailPrice" name="retailPrice" value="<?php echo $product['retailPrice']; ?>" required>
                    <div class="price-info">Valor atual: R$ <?php echo number_format($product['retailPrice'], 2, ',', '.'); ?></div>
                </div>
                <div class="form-group">
                    <label for="wholesalePrice">Preço de Atacado (R$)</label>
                    <input type="number" step="0.01" id="wholesalePrice" name="wholesalePrice" value="<?php echo $product['wholesalePrice']; ?>" required>
                    <div class="price-info">Valor atual: R$ <?php echo number_format($product['wholesalePrice'], 2, ',', '.'); ?></div>
                </div>
            </div>
            
            <div class="image-section">
                <label>Imagem do Produto</label>
                <div class="current-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.src='https://placehold.co/200x200/cccccc/ffffff?text=Sem+Imagem'">
                    <p>Imagem atual</p>
                </div>
                <input type="file" id="image" name="image" accept="image/*">
                <div class="price-info">Deixe em branco para manter a imagem atual</div>
            </div>
            
            <div class="actions">
                <button type="submit" class="btn btn-primary">💾 Salvar Alterações</button>
                <a href="admin-products.php" class="btn btn-secondary">❌ Cancelar</a>
            </div>
        </form>
    </div>
    
    <script>
        // Auto-refresh para manter sessão ativa
        setInterval(function() {
            fetch('session-refresh.php');
        }, 300000); // 5 minutos
        
        // Preview da nova imagem
        document.getElementById('image').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.current-image img').src = e.target.result;
                    document.querySelector('.current-image p').textContent = 'Nova imagem (prévia)';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>
