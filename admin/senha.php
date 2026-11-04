<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/layout.php';
require_once __DIR__ . '/../lib/auth.php';

start_admin_session();
require_login();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_post();
    $current = is_string($_POST['current'] ?? null) ? $_POST['current'] : '';
    $new = is_string($_POST['new'] ?? null) ? $_POST['new'] : '';
    $repeat = is_string($_POST['repeat'] ?? null) ? $_POST['repeat'] : '';

    $lock = login_lock_remaining();
    if ($lock > 0) {
        $error = 'Muitas tentativas. Aguarde ' . max(1, (int) ceil($lock / 60)) . ' minuto(s) e tente de novo.';
    } elseif (!verify_credentials($_SESSION['admin_user'], $current)) {
        login_register_failure();
        $error = 'A senha atual está incorreta.';
    } elseif ($new !== $repeat) {
        $error = 'A confirmação não é igual à nova senha.';
    } elseif (($problem = password_problem($new)) !== null) {
        $error = $problem;
    } else {
        login_clear_failures();
        save_credentials($_SESSION['admin_user'], $new);
        session_regenerate_id(true);
        flash('Senha alterada.');
        redirect('/admin/');
    }
}

admin_page_start('Alterar senha');
?>
        <div class="page__head">
            <h1>Alterar senha</h1>
        </div>
<?php if ($error): ?>
        <p class="flash flash--error" role="alert"><?= e($error) ?></p>
<?php endif; ?>
        <form method="post" class="panel form form--narrow">
            <?= csrf_field() ?>
            <label>
                <span>Senha atual</span>
                <input type="password" name="current" autocomplete="current-password" required>
            </label>
            <label>
                <span>Nova senha (mínimo de <?= MIN_PASSWORD_LENGTH ?> caracteres)</span>
                <input type="password" name="new" autocomplete="new-password" minlength="<?= MIN_PASSWORD_LENGTH ?>" required>
            </label>
            <label>
                <span>Repita a nova senha</span>
                <input type="password" name="repeat" autocomplete="new-password" required>
            </label>
            <button type="submit" class="btn btn--primary">Salvar nova senha</button>
        </form>
<?php
admin_page_end();
