<?php include __DIR__ . '/../_header.php'; ?>
        <div class="page__head">
            <h1>Categorias</h1>
        </div>
        <p class="muted page__intro">São estas as categorias que aparecem nos filtros do site. As que não têm produto visível ficam escondidas lá. Para ocultar uma categoria, oculte antes todos os produtos dela.</p>

        <form method="post" action="/admin/categories" class="panel inline-form">
            <?= csrf_field() ?>
            <label class="grow">
                <span class="sr-only">Nome da nova categoria</span>
                <input type="text" name="name" maxlength="60" placeholder="Nova categoria" required>
            </label>
            <button type="submit" class="btn btn--primary">Adicionar</button>
        </form>

        <?php if ($categories): ?>
        <label class="pick-all">
            <input type="checkbox" id="pickAll">
            <span>Selecionar todas (<span id="shownCount"><?= count($categories) ?></span>)</span>
        </label>
        <?php endif; ?>

        <ul class="clist">
            <?php foreach ($categories as $category):
                $id = (int) $category['id'];
                $total = (int) $category['total'];
                $visible = (int) $category['visible'];
                $active = $category['status'] === 'active';
                $count = $total === 1 ? '1 produto' : $total . ' produtos';
                if ($total > 0) {
                    $count .= ' · ' . ($visible === 1 ? '1 visível' : $visible . ' visíveis');
                }
                ?>
                <li class="citem<?= $active ? '' : ' citem--hidden' ?>" data-item data-id="<?= $id ?>" data-status="<?= e($category['status']) ?>">
                    <input type="checkbox" class="citem__pick js-pick" value="<?= $id ?>" aria-label="Selecionar <?= e($category['description']) ?>">
                    <form method="post" action="/admin/categories/<?= $id ?>" class="citem__name">
                        <?= csrf_field() ?>
                        <label class="grow">
                            <span class="sr-only">Nome da categoria</span>
                            <input type="text" name="name" value="<?= e($category['description']) ?>" maxlength="60" required>
                        </label>
                        <button type="submit" class="btn btn--small">Renomear</button>
                    </form>
                    <span class="citem__count muted"><?= e($count) ?></span>
                    <span class="tag js-tag"<?= $active ? ' hidden' : '' ?>>Oculta</span>
                    <form method="post" action="/admin/categories/status" class="js-toggle">
                        <?= csrf_field() ?>
                        <input type="hidden" name="ids[]" value="<?= $id ?>">
                        <input type="hidden" name="status" value="<?= $active ? 'inactive' : 'active' ?>">
                        <button type="submit" class="btn btn--small"><span class="btn__text"><?= $active ? 'Ocultar' : 'Mostrar' ?></span></button>
                    </form>
                    <?php if ($total === 0): ?>
                        <form method="post" action="/admin/categories/<?= $id ?>/delete" data-confirm="Excluir a categoria “<?= e($category['description']) ?>”?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn--small btn--danger">Excluir</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <form class="bulk" id="bulkBar" action="/admin/categories/status" data-one="1 categoria selecionada" data-many="categorias selecionadas" hidden>
            <?= csrf_field() ?>
            <strong id="bulkCount" role="status"></strong>
            <div class="bulk__actions">
                <button class="btn btn--small" type="button" data-bulk-status="inactive">Ocultar</button>
                <button class="btn btn--small" type="button" data-bulk-status="active">Mostrar</button>
                <button class="link-btn" type="button" id="bulkClear">Limpar</button>
            </div>
        </form>

        <dialog class="modal" id="blockedDialog" aria-labelledby="blockedTitle">
            <h2 id="blockedTitle"></h2>
            <p>Uma categoria só pode ser ocultada quando todos os produtos dela estão ocultos. Estes ainda aparecem no site:</p>
            <div class="modal__list" id="blockedList"></div>
            <div class="modal__foot">
                <button class="btn btn--primary" type="button" id="blockedClose" autofocus>Entendi</button>
            </div>
        </dialog>
<?php include __DIR__ . '/../_footer.php'; ?>
