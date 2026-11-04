<?php include __DIR__ . '/../_header.php'; ?>
        <div class="page__head">
            <h1>Usuários</h1>
            <a class="btn btn--primary" href="/admin/users/create">+ Novo usuário</a>
        </div>
        <p class="muted page__intro">Todo usuário é administrador e pode fazer tudo no painel.</p>

        <ul class="clist">
            <?php foreach ($users as $user):
                $id = (int) $user['id'];
                $isSelf = $id === (int) $currentUser['id'];
                ?>
                <li class="citem">
                    <div class="citem__name citem__name--text">
                        <strong><?= e($user['name']) ?><?php if ($isSelf): ?> <span class="tag tag--soft">Você</span><?php endif; ?></strong>
                        <span class="muted"><?= e($user['email']) ?></span>
                        <span class="muted small">Último acesso: <?= $user['last_login'] ? e(date('d/m/Y H:i', strtotime($user['last_login']))) : 'nunca' ?></span>
                    </div>
                    <a class="btn btn--small" href="/admin/users/<?= $id ?>/edit">Editar</a>
                    <?php if (!$isSelf): ?>
                        <form method="post" action="/admin/users/<?= $id ?>/delete" data-confirm="Excluir o usuário “<?= e($user['name']) ?>”? O acesso dele será encerrado.">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--small btn--danger">Excluir</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
<?php include __DIR__ . '/../_footer.php'; ?>
