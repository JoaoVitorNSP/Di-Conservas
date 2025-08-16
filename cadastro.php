<?php
// cadastro.php - Página de cadastro protegida

require_once 'auth.php';
checkAdminAuth();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produto - Di Conservas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        h2 { text-align: center; margin-bottom: 24px; }
        .session-info { background: #e8f5e8; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; margin-bottom: 16px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #2d7a2d; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #256325; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #2d7a2d; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="session-info">
            Logado como: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong> | 
            Sessão expira às: <strong><?php echo date('H:i:s', $_SESSION['admin_last_activity'] + 600); ?></strong>
        </div>
        
        <h2>Cadastro de Produto</h2>
        <form method="POST" action="upload.php" enctype="multipart/form-data">
            <label for="name">Nome do Produto</label>
            <input type="text" id="name" name="name" required>
            
            <label for="category">Categoria</label>
            <input type="text" id="category" name="category" required>
            
            <label for="description">Descrição</label>
            <textarea id="description" name="description" rows="4" required></textarea>
            
            <label for="weight">Peso</label>
            <input type="text" id="weight" name="weight" required>
            
            <label for="retailPrice">Preço de Varejo</label>
            <input type="number" step="0.01" id="retailPrice" name="retailPrice" required>
            
            <label for="wholesalePrice">Preço de Atacado</label>
            <input type="number" step="0.01" id="wholesalePrice" name="wholesalePrice" required>
            
            <label for="image">Imagem do Produto</label>
            <input type="file" id="image" name="image" accept="image/*" required>
            
            <button type="submit">Cadastrar Produto</button>
        </form>
        <a href="admin-dashboard.php" class="back-link">← Voltar ao Dashboard</a>
    </div>
    
    <script>
        // Auto-refresh para manter sessão ativa
        setInterval(function() {
            fetch('session-refresh.php');
        }, 300000); // 5 minutos
    </script>
</body>
</html>
