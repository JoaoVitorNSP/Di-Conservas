<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Página não encontrada'); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .error-container {
            text-align: center;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        
        .error-code {
            font-size: 6em;
            font-weight: bold;
            color: #E63946;
            margin-bottom: 20px;
        }
        
        .error-message {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
        }
        
        .error-description {
            color: #666;
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #2d7a2d;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin: 0 10px;
        }
        
        .btn:hover {
            background: #256325;
        }
        
        .btn-secondary {
            background: #666;
        }
        
        .btn-secondary:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-message">Página não encontrada</div>
        <div class="error-description">
            A página que você está procurando não existe ou foi movida.
        </div>
        <div>
            <a href="/" class="btn">Voltar ao Início</a>
            <a href="/products" class="btn btn-secondary">Ver Produtos</a>
        </div>
    </div>
</body>
</html>
