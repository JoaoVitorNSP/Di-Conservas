<?php $title = 'Entrar'; include __DIR__ . '/_header.php'; ?>
        <div class="login">
            <img src="/assets/logo.png" alt="" width="64" height="64">
            <h1>Painel Di Conservas</h1>
<?php if (!empty($error)): ?>
            <p class="flash flash--error" role="alert"><?= e($error) ?></p>
<?php endif; ?>
            <form method="post" action="/admin/login" class="panel form">
                <?= csrf_field() ?>
                <label>
                    <span>E-mail</span>
                    <input type="email" name="email" value="<?= e($email ?? '') ?>" autocomplete="username" autocapitalize="none" required>
                </label>
                <label>
                    <span>Senha</span>
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <button type="submit" class="btn btn--primary btn--block">Entrar</button>
            </form>
        </div>
<?php include __DIR__ . '/_footer.php'; ?>
