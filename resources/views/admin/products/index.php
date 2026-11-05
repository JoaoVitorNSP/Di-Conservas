<?php
include __DIR__ . '/../_header.php';
$hidden = count(array_filter($products, fn ($p) => $p['status'] !== 'active'));
$withoutPhoto = count(array_filter($products, fn ($p) => empty($p['image'])));
?>
        <div class="page__head">
            <h1>Produtos</h1>
            <a class="btn btn--primary" href="/admin/products/create">+ Novo produto</a>
        </div>

        <ul class="stats">
            <li><strong><?= count($products) ?></strong> cadastrados</li>
            <li><a href="/admin/categories"><strong><?= count($categories) ?></strong> categorias</a></li>
            <li><strong id="statHidden"><?= $hidden ?></strong> ocultos</li>
            <li><strong><?= $withoutPhoto ?></strong> sem foto</li>
        </ul>

<?php if (!$products): ?>
        <div class="panel empty">
            <p>Nenhum produto cadastrado ainda.</p>
            <a class="btn btn--primary" href="/admin/products/create">Cadastrar o primeiro</a>
        </div>
<?php else: ?>
        <div class="list-filters">
            <label class="grow">
                <span class="sr-only">Buscar produto</span>
                <input type="search" id="listSearch" placeholder="Buscar por nome…">
            </label>
            <label>
                <span class="sr-only">Filtrar por categoria</span>
                <select id="listCategory">
                    <option value="">Categorias: todas</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category) ?>"><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span class="sr-only">Filtrar por situação</span>
                <select id="listStatus">
                    <option value="">Visíveis e ocultos</option>
                    <option value="active">Só visíveis</option>
                    <option value="inactive">Só ocultos</option>
                </select>
            </label>
        </div>

        <label class="pick-all">
            <input type="checkbox" id="pickAll">
            <span>Selecionar todos os exibidos (<span id="shownCount"><?= count($products) ?></span>)</span>
        </label>

        <ul class="plist" id="productList">
            <?php foreach ($products as $product):
                $active = $product['status'] === 'active';
                $id = (int) $product['id'];
                $toggleText = $active ? 'Ocultar' : 'Mostrar';
                ?>
                <li class="pitem<?= $active ? '' : ' pitem--hidden' ?>" data-item data-id="<?= $id ?>" data-status="<?= e($product['status']) ?>" data-name="<?= e($product['name']) ?>" data-category="<?= e($product['category_name']) ?>">
                    <label class="pick" title="Selecionar">
                        <input type="checkbox" class="js-pick" value="<?= $id ?>" aria-label="Selecionar <?= e($product['name']) ?>">
                    </label>
                    <div class="pitem__thumb">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= e($product['image']) ?>" alt="" width="88" height="88" loading="lazy">
                        <?php else: ?>
                            <span>Sem foto</span>
                        <?php endif; ?>
                    </div>
                    <div class="pitem__info">
                        <h2><?= e($product['name']) ?> <span class="tag js-tag"<?= $active ? ' hidden' : '' ?>>Oculto</span></h2>
                        <p class="muted"><?= e(implode(' · ', array_filter([$product['category_name'], format_weight($product['weight'], $product['unit_name'])]))) ?></p>
                        <p>
                            <strong><?= e(format_price($product['retail_price'])) ?></strong>
                            <?php if ((float) $product['wholesale_price'] > 0): ?>
                                <span class="muted">· atacado <?= e(format_price($product['wholesale_price'])) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="pitem__actions">
                        <a class="btn btn--small btn--act" href="/admin/products/<?= $id ?>/edit" title="Editar">
                            <svg class="icon" aria-hidden="true"><use href="#i-edit"/></svg>
                            <span class="btn__text">Editar</span>
                        </a>
                        <form method="post" action="/admin/products/<?= $id ?>/toggle" class="js-toggle">
                            <?= csrf_field() ?>
                            <button class="btn btn--small btn--act" type="submit" title="<?= $toggleText ?>">
                                <svg class="icon" aria-hidden="true"><use href="#<?= $active ? 'i-eye-off' : 'i-eye' ?>"/></svg>
                                <span class="btn__text"><?= $toggleText ?></span>
                            </button>
                        </form>
                        <form method="post" action="/admin/products/<?= $id ?>/delete" data-confirm="Excluir “<?= e($product['name']) ?>”? A foto também será apagada.">
                            <?= csrf_field() ?>
                            <button class="btn btn--small btn--act btn--danger" type="submit" title="Excluir">
                                <svg class="icon" aria-hidden="true"><use href="#i-trash"/></svg>
                                <span class="btn__text">Excluir</span>
                            </button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="panel empty" id="listEmpty" hidden>Nenhum produto com esse filtro.</p>

        <form class="bulk" id="bulkBar" action="/admin/products/status" data-one="1 produto selecionado" data-many="produtos selecionados" hidden>
            <?= csrf_field() ?>
            <strong id="bulkCount" role="status"></strong>
            <div class="bulk__actions">
                <button class="btn btn--small" type="button" data-bulk-status="inactive">Ocultar</button>
                <button class="btn btn--small" type="button" data-bulk-status="active">Mostrar</button>
                <button class="link-btn" type="button" id="bulkClear">Limpar</button>
            </div>
        </form>
<?php endif; ?>
<?php include __DIR__ . '/../_footer.php'; ?>
