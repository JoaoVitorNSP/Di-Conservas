<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($title); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: Arial, sans-serif; background: #f7f7f7; }
    .container { max-width: 1200px; margin: 20px auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
    h2 { text-align: center; margin-bottom: 24px; }
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .btn { padding: 10px 20px; background: #2d7a2d; color: #fff; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
    .btn:hover { background: #256325; }
    .btn-small { padding: 6px 12px; font-size: 14px; }
    .btn-danger { background: #c00; }
    .btn-danger:hover { background: #a00; }
    .btn-secondary { background: #666; }
    .btn-secondary:hover { background: #555; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #f9f9f9; font-weight: bold; }
    tr:hover { background: #f5f5f5; }
    
    .product-image { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
    .actions { display: flex; gap: 8px; }
    
    .flash-message { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    
    .empty-state { text-align: center; padding: 40px; color: #666; }
    
    @media (max-width: 768px) {
      .container { margin: 10px; padding: 20px; }
      table { font-size: 14px; }
      .actions { flex-direction: column; }
      .btn-small { padding: 8px; margin-bottom: 4px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-actions">
      <h2>Gerenciar Produtos</h2>
      <div>
        <a href="/admin/products/create" class="btn">+ Adicionar Produto</a>
        <a href="/admin/dashboard" class="btn btn-secondary">← Voltar</a>
      </div>
    </div>
    
    <?php 
    // Exibe mensagens flash usando helper seguro
    use App\Controllers\BaseController;
    
    $successMessage = BaseController::getFlashMessage('success');
    if ($successMessage): 
    ?>
      <div class="flash-message flash-success">
        <?php echo htmlspecialchars($successMessage); ?>
      </div>
    <?php endif; ?>
    
    <?php 
    $errorMessage = BaseController::getFlashMessage('error');
    if ($errorMessage): 
    ?>
      <div class="flash-message flash-error">
        <?php echo htmlspecialchars($errorMessage); ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($products)): ?>
      <table>
        <thead>
          <tr>
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
              <td>
                <?php if (!empty($product['image'])): ?>
                  <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                       alt="<?php echo htmlspecialchars($product['name']); ?>" 
                       class="product-image"
                       onerror="this.src='https://placehold.co/60x60/e0e0e0/000000?text=Sem+Imagem'">
                <?php else: ?>
                  <img src="https://placehold.co/60x60/e0e0e0/000000?text=Sem+Imagem" 
                       alt="Sem imagem" 
                       class="product-image">
                <?php endif; ?>
              </td>
              <td>
                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                <br>
                <small style="color: #666;">
                  <?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>
                  <?php if (strlen($product['description']) > 50) echo '...'; ?>
                </small>
              </td>
              <td><?php echo htmlspecialchars($product['category']); ?></td>
              <td><?php echo htmlspecialchars($product['weight']); ?></td>
              <td>R$ <?php echo number_format($product['retailPrice'], 2, ',', '.'); ?></td>
              <td>R$ <?php echo number_format($product['wholesalePrice'], 2, ',', '.'); ?></td>
              <td>
                <div class="actions">
                  <a href="/admin/products/<?php echo $product['id']; ?>/edit" 
                     class="btn btn-small">Editar</a>
                  <form method="POST" 
                        action="/admin/products/<?php echo $product['id']; ?>/delete" 
                        style="display: inline;" 
                        onsubmit="return confirm('Tem certeza que deseja excluir este produto?')">
                    <button type="submit" class="btn btn-small btn-danger">Excluir</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="empty-state">
        <h3>Nenhum produto cadastrado</h3>
        <p>Comece adicionando seu primeiro produto.</p>
        <a href="/admin/products/create" class="btn">+ Adicionar Produto</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
