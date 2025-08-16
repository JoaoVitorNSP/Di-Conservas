<?php
// session-refresh.php - Endpoint para manter sessão ativa

session_start();

// Verifica se está logado
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    echo 'session_expired';
    exit;
}

// Verifica se a sessão expirou (10 minutos = 600 segundos)
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > 600)) {
    session_destroy();
    echo 'session_expired';
    exit;
}

// Atualiza o tempo de última atividade
$_SESSION['admin_last_activity'] = time();

// Se chegou até aqui, a sessão é válida e foi atualizada
echo 'session_active';
?>
