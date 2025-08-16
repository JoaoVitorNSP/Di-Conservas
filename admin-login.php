<?php
// admin-login.php - Processa o login do administrador com sessões

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Usuário e senha fixos para exemplo
    if ($username === 'admin' && $password === '1234') {
        // Inicia sessão do admin
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['admin_username'] = $username;
        
        // Redireciona para página de dashboard
        header('Location: admin-dashboard.php');
        exit;
    } else {
        // Redireciona de volta para página de login com erro
        header('Location: admin.php?error=1');
        exit;
    }
} else {
    http_response_code(405);
    echo 'Método não permitido';
}
?>
