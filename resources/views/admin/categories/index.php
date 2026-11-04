<?php include __DIR__ . '/../_header.php'; ?>
        <div class="page__head">
            <h1>Categorias</h1>
        </div>
        <p class="muted page__intro">São estas as categorias que aparecem nos filtros do site. As que estão vazias ficam escondidas lá.</p>

        <form method="post" action="/admin/categories" class="panel inline-form">
            <?= csrf_field() ?>
            <label class="grow">
                <span class="sr-only">Nome da nova categoria</span>
                <input type="text" name="name" maxlength="60" placeholder="Nova categoria" required>
            </label>
            <button type="submit" class="btn btn--primary">Adicionar</button>
        </form>

        <ul class="clist">
            <?php foreach ($categories as $category):
                $id = (int) $category['id'];
                $total = (int) $category['total'];
                ?>
                <li class="citem">
                    <form method="post" action="/admin/categories/<?= $id ?>" class="citem__name">
                        <?= csrf_field() ?>
                        <label class="grow">
                            <span class="sr-only">Nome da categoria</span>
                            <input type="text" name="name" value="<?= e($category['description']) ?>" maxlength="60" required>
                        </label>
                        <button type="submit" class="btn btn--small">Renomear</button>
                    </form>
                    <span class="citem__count muted"><?= $total === 1 ? '1 produto' : $total . ' produtos' ?></span>
                    <?php if ($total === 0): ?>
                        <form method="post" action="/admin/categories/<?= $id ?>/delete" data-confirm="Excluir a categoria “<?= e($category['description']) ?>”?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--small btn--danger">Excluir</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
<?php include __DIR__ . '/../_footer.php'; ?>
