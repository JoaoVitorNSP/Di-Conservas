<?php
// auth.php - Verificação de autenticação e sessões

session_start();

function checkAdminAuth() {
    // Verifica se está logado
    if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
        header('Location: admin.php?error=session');
        exit;
    }
    
    // Verifica se a sessão expirou (10 minutos = 600 segundos)
    if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > 600)) {
        session_destroy();
        header('Location: admin.php?error=expired');
        exit;
    }
    
    // Atualiza o tempo de última atividade
    $_SESSION['admin_last_activity'] = time();
}

function logoutAdmin() {
    session_destroy();
    header('Location: /');
    exit;
}
?>
