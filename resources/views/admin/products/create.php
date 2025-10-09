<?php
use App\Controllers\BaseController;
$successMessage = BaseController::getFlashMessage('success');
$errorMessage = BaseController::getFlashMessage('error');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($title); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: Arial, sans-serif; background: #f7f7f7; }
    .container { max-width: 800px; margin: 20px auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
    h2 { text-align: center; margin-bottom: 24px; }
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: bold; }
    input[type="text"], input[type="number"], textarea, select { 
      width: 100%; 
      padding: 12px; 
      border: 1px solid #ddd; 
      border-radius: 4px; 
      box-sizing: border-box;
      font-size: 16px;
    }
    
    textarea { resize: vertical; min-height: 100px; }
    
    .btn { padding: 12px 24px; background: #2d7a2d; color: #fff; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 16px; }
    .btn:hover { background: #256325; }
    .btn-secondary { background: #666; }
    .btn-secondary:hover { background: #555; }
    
    .form-actions { display: flex; gap: 12px; justify-content: center; margin-top: 30px; }
    
    .flash-message { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    
    .error { color: #c00; font-size: 14px; margin-top: 5px; }
    .field-error input, .field-error textarea, .field-error select { border-color: #c00; }
    
    .image-preview { max-width: 200px; max-height: 200px; margin-top: 10px; border-radius: 4px; }
    
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    
    @media (max-width: 768px) {
      .container { margin: 10px; padding: 20px; }
      .grid { grid-template-columns: 1fr; }
      .form-actions { flex-direction: column; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-actions">
      <h2><?php echo htmlspecialchars($title); ?></h2>
      <a href="/admin/products" class="btn btn-secondary">← Voltar</a>
    </div>
    
    <?php 
    // Exibe mensagens flash usando helper seguro
    
    
    if ($successMessage): 
    ?>
      <div class="flash-message flash-success">
        <?php echo htmlspecialchars($successMessage); ?>
      </div>
    <?php endif; ?>
    
    <?php 
    if ($errorMessage): 
    ?>
      <div class="flash-message flash-error">
        <?php echo htmlspecialchars($errorMessage); ?>
      </div>
    <?php endif; ?>
    
    <?php 
    $isEdit = isset($product);
    $action = $isEdit ? "/admin/products/{$product['id']}" : "/admin/products";
    $currentData = $isEdit ? $product : ($data ?? []);
    ?>
    
    <form method="POST" action="<?php echo $action; ?>" enctype="multipart/form-data">
      <div class="grid">
        <div>
          <div class="form-group <?php echo isset($errors['name']) ? 'field-error' : ''; ?>">
            <label for="name">Nome do Produto *</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="<?php echo htmlspecialchars($currentData['name'] ?? ''); ?>" 
                   required>
            <?php if (isset($errors['name'])): ?>
              <div class="error"><?php echo htmlspecialchars($errors['name']); ?></div>
            <?php endif; ?>
          </div>
          
          <div class="form-group <?php echo isset($errors['category_id']) ? 'field-error' : ''; ?>">
            <label for="category_id">Categoria *</label>
            <select id="category_id" name="category_id" required>
              <option value="">Selecione uma categoria</option>
              <?php if (isset($categories) && !empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo $cat['id']; ?>" 
                          <?php echo ($currentData['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['description']); ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
            <?php if (isset($errors['category_id'])): ?>
              <div class="error"><?php echo htmlspecialchars($errors['category_id']); ?></div>
            <?php endif; ?>
          </div>
          
          <div class="form-group-row">
            <div class="form-group <?php echo isset($errors['weight']) ? 'field-error' : ''; ?>">
              <label for="weight">Peso</label>
              <input type="number" 
                     id="weight" 
                     name="weight" 
                     step="0.001"
                     value="<?php echo htmlspecialchars($currentData['weight'] ?? ''); ?>" 
                     placeholder="0.300">
              <?php if (isset($errors['weight'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['weight']); ?></div>
              <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['unit_id']) ? 'field-error' : ''; ?>">
              <label for="unit_id">Unidade</label>
              <select id="unit_id" name="unit_id">
                <option value="">Selecione uma unidade</option>
                <?php var_dump($units); ?>
                <?php if (isset($units) && !empty($units)): ?>
                  <?php foreach ($units as $unit): ?>
                    <option value="<?php echo $unit['id']; ?>" 
                            <?php echo ($currentData['unit_id'] ?? '') == $unit['id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($unit['description']); ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <?php if (isset($errors['unit_id'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['unit_id']); ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <div>
          <div class="form-group <?php echo isset($errors['retail_price']) ? 'field-error' : ''; ?>">
            <label for="retail_price">Preço Varejo (R$) *</label>
            <input type="number" 
                   id="retail_price" 
                   name="retail_price" 
                   step="0.01" 
                   min="0"
                   value="<?php echo htmlspecialchars($currentData['retail_price'] ?? ''); ?>" 
                   required>
            <?php if (isset($errors['retail_price'])): ?>
              <div class="error"><?php echo htmlspecialchars($errors['retail_price']); ?></div>
            <?php endif; ?>
          </div>
          
          <div class="form-group <?php echo isset($errors['wholesale_price']) ? 'field-error' : ''; ?>">
            <label for="wholesale_price">Preço Atacado (R$) *</label>
            <input type="number" 
                   id="wholesale_price" 
                   name="wholesale_price" 
                   step="0.01" 
                   min="0"
                   value="<?php echo htmlspecialchars($currentData['wholesale_price'] ?? ''); ?>" 
                   required>
            <?php if (isset($errors['wholesale_price'])): ?>
              <div class="error"><?php echo htmlspecialchars($errors['wholesale_price']); ?></div>
            <?php endif; ?>
          </div>
          
          <div class="form-group">
            <label for="image">Imagem do Produto</label>
            <input type="file" 
                   id="image" 
                   name="image" 
                   accept="image/*"
                   onchange="previewImage(this)">
            <small style="color: #666;">Formatos aceitos: JPG, PNG, GIF (máximo 5MB)</small>
            
            <?php if ($isEdit && !empty($product['image'])): ?>
              <div style="margin-top: 10px;">
                <strong>Imagem atual:</strong><br>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="Imagem atual" 
                     class="image-preview"
                     id="currentImage">
              </div>
            <?php endif; ?>
            
            <img id="imagePreview" 
                 class="image-preview" 
                 style="display: none;" 
                 alt="Preview da nova imagem">
          </div>
        </div>
      </div>
      
      <div class="form-group <?php echo isset($errors['description']) ? 'field-error' : ''; ?>">
        <label for="description">Descrição *</label>
        <textarea id="description" 
                  name="description" 
                  required 
                  placeholder="Descreva o produto..."><?php echo htmlspecialchars($currentData['description'] ?? ''); ?></textarea>
        <?php if (isset($errors['description'])): ?>
          <div class="error"><?php echo htmlspecialchars($errors['description']); ?></div>
        <?php endif; ?>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="btn">
          <?php echo $isEdit ? 'Atualizar Produto' : 'Cadastrar Produto'; ?>
        </button>
        <a href="/admin/products" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
  
  <script>
    function previewImage(input) {
      const preview = document.getElementById('imagePreview');
      const currentImage = document.getElementById('currentImage');
      
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
          
          // Esconde a imagem atual se houver uma nova
          if (currentImage) {
            currentImage.style.display = 'none';
          }
        };
        
        reader.readAsDataURL(input.files[0]);
      } else {
        preview.style.display = 'none';
        
        // Mostra a imagem atual novamente se cancelar a seleção
        if (currentImage) {
          currentImage.style.display = 'block';
        }
      }
    }
  </script>
</body>
</html>
