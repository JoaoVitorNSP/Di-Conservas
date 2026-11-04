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
            <li><strong><?= $hidden ?></strong> ocultos</li>
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
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category) ?>"><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <ul class="plist" id="productList">
            <?php foreach ($products as $product):
                $active = $product['status'] === 'active';
                $id = (int) $product['id'];
                ?>
                <li class="pitem<?= $active ? '' : ' pitem--hidden' ?>" data-name="<?= e($product['name']) ?>" data-category="<?= e($product['category_name']) ?>">
                    <div class="pitem__thumb">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= e($product['image']) ?>" alt="" width="72" height="72" loading="lazy">
                        <?php else: ?>
                            <span>Sem foto</span>
                        <?php endif; ?>
                    </div>
                    <div class="pitem__info">
                        <h2><?= e($product['name']) ?><?php if (!$active): ?> <span class="tag">Oculto</span><?php endif; ?></h2>
                        <p class="muted"><?= e(implode(' · ', array_filter([$product['category_name'], format_weight($product['weight'], $product['unit_name'])]))) ?></p>
                        <p>
                            <strong><?= e(format_price($product['retail_price'])) ?></strong>
                            <?php if ((float) $product['wholesale_price'] > 0): ?>
                                <span class="muted">· atacado <?= e(format_price($product['wholesale_price'])) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="pitem__actions">
                        <a class="btn btn--small" href="/admin/products/<?= $id ?>/edit">Editar</a>
                        <form method="post" action="/admin/products/<?= $id ?>/toggle">
                            <?= csrf_field() ?>
                            <button class="btn btn--small" type="submit"><?= $active ? 'Ocultar' : 'Mostrar' ?></button>
                        </form>
                        <form method="post" action="/admin/products/<?= $id ?>/delete" data-confirm="Excluir “<?= e($product['name']) ?>”? A foto também será apagada.">
                            <?= csrf_field() ?>
                            <button class="btn btn--small btn--danger" type="submit">Excluir</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="panel empty" id="listEmpty" hidden>Nenhum produto com esse filtro.</p>
<?php endif; ?>
<?php include __DIR__ . '/../_footer.php'; ?>
