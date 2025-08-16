<?php
// admin-login.php - Processa o login do administrador

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Usuário e senha fixos para exemplo
    if ($username === 'admin' && $password === '1234') {
        // Redireciona para página de sucesso
        header('Location: admin-dashboard.html');
        exit;
    } else {
        // Redireciona de volta para página de login com erro
        header('Location: admin.html?error=1');
        exit;
    }
} else {
    http_response_code(405);
    echo 'Método não permitido';
}
?>
