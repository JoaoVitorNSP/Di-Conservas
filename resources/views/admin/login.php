<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
        }

        .container {
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 2px 8px #0001;
        }

        h2 {
            text-align: center;
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #2d7a2d;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #256325;
        }

        .error {
            color: #c00;
            text-align: center;
            margin-bottom: 12px;
        }

        .session-info {
            background: #e8f5e8;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            color: #2d7a2d;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }

        .back-link:hover {
            color: #2d7a2d;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="session-info">
                Logout realizado com sucesso!
            </div>
        <?php endif; ?>
        
        <h2>Login do Administrador</h2>
        
        <?php if (isset($error) && !empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $field => $message): ?>
                    <div><?php echo htmlspecialchars($message); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/admin/login">
            <label for="username">Usuário</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>" required>
            
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Entrar</button>
        </form>
        
        <a href="/" class="back-link">← Voltar ao site</a>
    </div>
</body>

</html>
