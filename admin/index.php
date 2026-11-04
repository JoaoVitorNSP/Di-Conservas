<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/layout.php';
require_once __DIR__ . '/../lib/products.php';

start_admin_session();
require_login();

$products = products_all();
$categories = product_categories($products);
$hidden = count(array_filter($products, fn ($p) => ($p['active'] ?? true) === false));
$withoutPhoto = count(array_filter($products, fn ($p) => empty($p['image'])));

// Mais recentes primeiro: quem acabou de cadastrar vê o item no topo.
usort($products, fn ($a, $b) => (int) $b['id'] <=> (int) $a['id']);

admin_page_start('Produtos');
?>
        <div class="page__head">
            <h1>Produtos</h1>
            <a class="btn btn--primary" href="/admin/produto.php">+ Novo produto</a>
        </div>

        <ul class="stats">
            <li><strong><?= count($products) ?></strong> cadastrados</li>
            <li><strong><?= count($categories) ?></strong> categorias</li>
            <li><strong><?= $hidden ?></strong> ocultos</li>
            <li><strong><?= $withoutPhoto ?></strong> sem foto</li>
        </ul>

<?php if (!$products): ?>
        <div class="panel empty">
            <p>Nenhum produto cadastrado ainda.</p>
            <a class="btn btn--primary" href="/admin/produto.php">Cadastrar o primeiro</a>
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
                $active = ($product['active'] ?? true) !== false;
                ?>
                <li class="pitem<?= $active ? '' : ' pitem--hidden' ?>" data-name="<?= e($product['name'] ?? '') ?>" data-category="<?= e($product['category'] ?? '') ?>">
                    <div class="pitem__thumb">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= e($product['image']) ?>" alt="" width="72" height="72" loading="lazy">
                        <?php else: ?>
                            <span>Sem foto</span>
                        <?php endif; ?>
                    </div>
                    <div class="pitem__info">
                        <h2><?= e($product['name'] ?? '') ?><?php if (!$active): ?> <span class="tag">Oculto</span><?php endif; ?></h2>
                        <p class="muted"><?= e(implode(' · ', array_filter([$product['category'] ?? '', $product['weight'] ?? '']))) ?></p>
                        <p>
                            <strong><?= e(format_price($product['retailPrice'] ?? 0)) ?></strong>
                            <?php if (!empty($product['wholesalePrice'])): ?>
                                <span class="muted">· atacado <?= e(format_price($product['wholesalePrice'])) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="pitem__actions">
                        <a class="btn btn--small" href="/admin/produto.php?id=<?= (int) $product['id'] ?>">Editar</a>
                        <form method="post" action="/admin/acao.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                            <button class="btn btn--small" name="action" value="toggle" type="submit"><?= $active ? 'Ocultar' : 'Mostrar' ?></button>
                        </form>
                        <form method="post" action="/admin/acao.php" data-confirm="Excluir “<?= e($product['name'] ?? '') ?>”? A foto também será apagada.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                            <button class="btn btn--small btn--danger" name="action" value="delete" type="submit">Excluir</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="panel empty" id="listEmpty" hidden>Nenhum produto com esse filtro.</p>
<?php endif; ?>
<?php
admin_page_end();
