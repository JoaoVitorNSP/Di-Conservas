<?php $title = 'Primeiro acesso'; include __DIR__ . '/_header.php'; ?>
        <div class="login">
            <img src="/assets/logo.png" alt="" width="64" height="64">
            <h1>Primeiro acesso</h1>
<?php if (!empty($error)): ?>
            <p class="flash flash--error" role="alert"><?= e($error) ?></p>
<?php endif; ?>
<?php if (!$canSetup): ?>
            <div class="panel">
                <p><strong>O painel ainda não tem administrador.</strong></p>
                <p>Para liberar esta tela, defina no arquivo <code>.env</code> do servidor um código com pelo menos 16 caracteres:</p>
                <pre>SETUP_TOKEN=um-codigo-so-seu</pre>
                <p>Ou crie o acesso pela linha de comando:</p>
                <pre>php scripts/criar-admin.php</pre>
            </div>
<?php else: ?>
            <form method="post" action="/admin/setup" class="panel form">
                <?= csrf_field() ?>
                <label>
                    <span>Código de instalação (SETUP_TOKEN do .env)</span>
                    <input type="password" name="token" autocomplete="off" required>
                </label>
                <label>
                    <span>Seu nome</span>
                    <input type="text" name="name" value="<?= e($name ?? '') ?>" autocomplete="name" required>
                </label>
                <label>
                    <span>E-mail</span>
                    <input type="email" name="email" value="<?= e($email ?? '') ?>" autocomplete="username" autocapitalize="none" required>
                </label>
                <label>
                    <span>Senha (mínimo de <?= \App\Models\User::MIN_PASSWORD_LENGTH ?> caracteres)</span>
                    <input type="password" name="password" autocomplete="new-password" required>
                </label>
                <label>
                    <span>Repita a senha</span>
                    <input type="password" name="repeat" autocomplete="new-password" required>
                </label>
                <button type="submit" class="btn btn--primary btn--block">Criar acesso</button>
            </form>
<?php endif; ?>
        </div>
<?php include __DIR__ . '/_footer.php'; ?>
