<?php
// admin-products.php - Lista todos os produtos em formato de tabela

require_once 'auth.php';
checkAdminAuth();

// Carrega produtos do JSON
$productsFile = 'products.json';
$products = [];
if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Produtos - Di Conservas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        h2 { text-align: center; margin-bottom: 24px; color: #333; }
        .session-info { background: #e8f5e8; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { padding: 8px 16px; background: #2d7a2d; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .btn:hover { background: #256325; }
        .btn-danger { background: #c00; }
        .btn-danger:hover { background: #a00; }
        .btn-edit { background: #0066cc; }
        .btn-edit:hover { background: #0052a3; }
        
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: bold; }
        tr:hover { background-color: #f5f5f5; }
        .product-image { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
        .product-actions { display: flex; gap: 8px; }
        .no-products { text-align: center; padding: 40px; color: #666; }
        
        @media (max-width: 768px) {
            .container { padding: 16px; }
            th, td { padding: 8px; font-size: 14px; }
            .product-image { width: 40px; height: 40px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="session-info">
            Logado como: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong> | 
            Sessão expira às: <strong><?php echo date('H:i:s', $_SESSION['admin_last_activity'] + 600); ?></strong>
        </div>
        
        <h2>Gerenciar Produtos</h2>
        
        <div class="actions">
            <a href="cadastro.php" class="btn">➕ Novo Produto</a>
            <a href="admin-dashboard.php" class="btn">← Voltar ao Dashboard</a>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="no-products">
                <p>Nenhum produto cadastrado ainda.</p>
                <a href="cadastro.php" class="btn">Cadastrar Primeiro Produto</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Peso</th>
                            <th>Preço Varejo</th>
                            <th>Preço Atacado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-image"
                                         onerror="this.src='https://placehold.co/60x60/cccccc/ffffff?text=Sem+Imagem'">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td><?php echo htmlspecialchars($product['weight']); ?></td>
                                <td>R$ <?php echo number_format($product['retailPrice'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($product['wholesalePrice'], 2, ',', '.'); ?></td>
                                <td>
                                    <div class="product-actions">
                                        <a href="admin-product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">✏️ Editar</a>
                                        <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="btn btn-danger">🗑️ Excluir</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-refresh para manter sessão ativa
        setInterval(function() {
            fetch('session-refresh.php');
        }, 300000); // 5 minutos
        
        function deleteProduct(productId) {
            if (confirm('Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.')) {
                fetch('delete-product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Produto excluído com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao excluir produto: ' + (data.error || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    alert('Erro ao excluir produto: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>
