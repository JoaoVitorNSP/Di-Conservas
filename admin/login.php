<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/layout.php';
require_once __DIR__ . '/../lib/auth.php';

start_admin_session();

if (is_logged_in()) {
    redirect('/admin/');
}

$configured = admin_credentials() !== null;
$error = null;
$username = '';

if ($configured && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_post();
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    $lock = login_lock_remaining();
    if ($lock > 0) {
        $error = 'Muitas tentativas. Aguarde ' . max(1, (int) ceil($lock / 60)) . ' minuto(s) e tente de novo.';
    } elseif (verify_credentials($username, $password)) {
        login_clear_failures();
        session_regenerate_id(true);
        unset($_SESSION['csrf']);
        $_SESSION['admin_user'] = $username;
        $_SESSION['last_activity'] = time();
        redirect('/admin/');
    } else {
        login_register_failure();
        $error = 'Usuário ou senha incorretos.';
    }
}

admin_page_start('Entrar', false);
?>
        <div class="login">
            <img src="/assets/logo.png" alt="" width="64" height="64">
            <h1>Painel Di Conservas</h1>

<?php if (!$configured): ?>
            <div class="panel">
                <p><strong>O painel ainda não tem senha.</strong></p>
                <p>Crie o acesso rodando este comando na pasta do projeto, no servidor:</p>
                <pre>php scripts/criar-senha.php</pre>
            </div>
<?php else: ?>
    <?php if ($error): ?>
            <p class="flash flash--error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>
            <form method="post" class="panel form">
                <?= csrf_field() ?>
                <label>
                    <span>Usuário</span>
                    <input type="text" name="username" value="<?= e($username) ?>" autocomplete="username" autocapitalize="none" required>
                </label>
                <label>
                    <span>Senha</span>
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <button type="submit" class="btn btn--primary btn--block">Entrar</button>
            </form>
<?php endif; ?>
        </div>
<?php
admin_page_end();
