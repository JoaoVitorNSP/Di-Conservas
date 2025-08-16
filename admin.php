<?php
// admin.php - Página de login com verificação de sessão

session_start();

// Se já estiver logado e sessão válida, redireciona para dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    // Verifica se a sessão não expirou (10 minutos = 600 segundos)
    if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] <= 600)) {
        // Atualiza o tempo de última atividade
        $_SESSION['admin_last_activity'] = time();
        header('Location: admin-dashboard.php');
        exit;
    } else {
        // Sessão expirada, destroi e continua para o login
        session_destroy();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Admin Login - Di Conservas</title>
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
        <form method="POST" action="admin-login.php">
            <label for="username">Usuário</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Entrar</button>
        </form>
        <script>
            // Mostra mensagem de erro se houver
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            if (error === '1') {
                alert('Usuário ou senha inválidos!');
            } else if (error === 'session') {
                alert('Você precisa estar logado para acessar esta página!');
            } else if (error === 'expired') {
                alert('Sua sessão expirou! Faça login novamente.');
            }
        </script>
    </div>
</body>

</html>
