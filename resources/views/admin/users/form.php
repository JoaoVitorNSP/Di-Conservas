<?php
include __DIR__ . '/../_header.php';

$fieldError = fn (string $field): string => isset($errors[$field]) ? '<small class="error">' . e($errors[$field]) . '</small>' : '';
$isSelf = $user !== null && (int) $user['id'] === (int) $currentUser['id'];
$action = $user ? '/admin/users/' . (int) $user['id'] : '/admin/users';
$min = \App\Models\User::MIN_PASSWORD_LENGTH;
?>
        <div class="page__head">
            <h1><?= e($title) ?></h1>
            <a class="btn" href="/admin/users">Voltar</a>
        </div>

        <form method="post" action="<?= e($action) ?>" class="panel form form--narrow">
            <?= csrf_field() ?>
            <label>
                <span>Nome</span>
                <input type="text" name="name" value="<?= e($form['name']) ?>" maxlength="200" autocomplete="off" required>
                <?= $fieldError('name') ?>
            </label>
            <label>
                <span>E-mail (usado para entrar)</span>
                <input type="email" name="email" value="<?= e($form['email']) ?>" maxlength="254" autocapitalize="none" autocomplete="off" required>
                <?= $fieldError('email') ?>
            </label>
            <label>
                <span><?= $user ? 'Nova senha (deixe em branco para manter)' : 'Senha' ?> — mínimo de <?= $min ?> caracteres</span>
                <input type="password" name="password" autocomplete="new-password"<?= $user ? '' : ' required' ?>>
                <?= $fieldError('password') ?>
            </label>
            <label>
                <span>Repita a senha</span>
                <input type="password" name="repeat" autocomplete="new-password">
                <?= $fieldError('repeat') ?>
            </label>
            <?php if ($isSelf): ?>
                <label>
                    <span>Senha atual (necessária só para trocar a senha)</span>
                    <input type="password" name="current" autocomplete="current-password">
                    <?= $fieldError('current') ?>
                </label>
            <?php endif; ?>
            <div class="form__actions">
                <button type="submit" class="btn btn--primary">Salvar</button>
            </div>
        </form>
<?php include __DIR__ . '/../_footer.php'; ?>
