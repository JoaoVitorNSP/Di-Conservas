<?php
include __DIR__ . '/../_header.php';

$fieldError = fn (string $field): string => isset($errors[$field]) ? '<small class="error">' . e($errors[$field]) . '</small>' : '';

// Número salvo => texto no formato brasileiro: 25.50 => 25,50; 300.000 => 300
$priceInput = fn ($value): string => (float) $value > 0 ? number_format((float) $value, 2, ',', '') : '';
$weightInput = fn ($value): string => (float) $value > 0 ? rtrim(rtrim(number_format((float) $value, 3, ',', ''), '0'), ',') : '';

$categoryValue = $form['category_name'] ?? '';
$weightValue = $form['weight_input'] ?? $weightInput($form['weight'] ?? null);
$retailValue = $form['retail_input'] ?? $priceInput($form['retail_price'] ?? null);
$wholesaleValue = $form['wholesale_input'] ?? $priceInput($form['wholesale_price'] ?? null);
$image = $form['image'] ?? '';
$selectedUnit = (int) ($form['unit_id'] ?? 0);
$action = $product ? '/admin/products/' . (int) $product['id'] : '/admin/products';
?>
        <div class="page__head">
            <h1><?= e($title) ?></h1>
            <a class="btn" href="/admin/products">Voltar</a>
        </div>

<?php if ($errors): ?>
        <p class="flash flash--error" role="alert">
            Confira os campos marcados.
            <?php if ($photoLost): ?>Selecione a foto novamente antes de salvar.<?php endif; ?>
        </p>
<?php endif; ?>

        <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="panel form form--product" novalidate>
            <?= csrf_field() ?>

            <div class="form__photo">
                <span class="label">Foto</span>
                <div class="photo-box" id="photoBox">
                    <?php if ($image !== ''): ?>
                        <img id="photoCurrent" src="<?= e($image) ?>" alt="Foto atual" width="400" height="400">
                    <?php endif; ?>
                    <span id="photoEmpty" class="photo-box__empty"<?= $image !== '' ? ' hidden' : '' ?>>Sem foto</span>
                    <canvas id="photoCanvas" width="1200" height="1200" hidden></canvas>
                </div>
                <label class="range" id="photoZoomBox" hidden>
                    <span>Zoom</span>
                    <input type="range" id="photoZoom" min="1" max="4" step="0.01" value="1">
                </label>
                <p class="muted small" id="photoHint" hidden>Arraste a foto para enquadrar o produto no quadrado.</p>
                <label class="btn btn--block file-btn">
                    <span id="photoButtonText"><?= $image !== '' ? 'Trocar foto' : 'Escolher foto' ?></span>
                    <input type="file" id="photoInput" name="image" accept="image/jpeg,image/png,image/webp">
                </label>
                <?= $fieldError('image') ?>
                <p class="error small" id="photoError" hidden></p>
                <input type="hidden" name="remove_image" id="photoRemoveFlag" value="">
                <button type="button" class="btn btn--block btn--danger" id="photoRemove"<?= $image === '' ? ' hidden' : '' ?>>Remover foto</button>
                <p class="muted small" id="photoRemoved" hidden>A foto será removida quando você salvar.</p>
            </div>

            <div class="form__fields">
                <label>
                    <span>Nome *</span>
                    <input type="text" name="name" value="<?= e($form['name'] ?? '') ?>" maxlength="120" required>
                    <?= $fieldError('name') ?>
                </label>
                <label>
                    <span>Categoria *</span>
                    <input type="text" name="category" value="<?= e($categoryValue) ?>" maxlength="60" list="categoryOptions" autocomplete="off" required>
                    <datalist id="categoryOptions">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <small class="muted">Escolha uma existente ou digite um nome novo para criar.</small>
                    <?= $fieldError('category') ?>
                </label>
                <div class="form__row">
                    <label>
                        <span>Peso / volume</span>
                        <input type="text" inputmode="decimal" name="weight" value="<?= e($weightValue) ?>" placeholder="300">
                        <?= $fieldError('weight') ?>
                    </label>
                    <label>
                        <span>Unidade</span>
                        <select name="unit_id">
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= (int) $unit['id'] ?>"<?= (int) $unit['id'] === $selectedUnit ? ' selected' : '' ?>><?= e($unit['description']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="form__row">
                    <label>
                        <span>Preço de varejo (R$) *</span>
                        <input type="text" inputmode="decimal" name="retailPrice" value="<?= e($retailValue) ?>" placeholder="25,50" required>
                        <?= $fieldError('retailPrice') ?>
                    </label>
                    <label>
                        <span>Preço de atacado (R$)</span>
                        <input type="text" inputmode="decimal" name="wholesalePrice" value="<?= e($wholesaleValue) ?>" placeholder="20,40">
                        <?= $fieldError('wholesalePrice') ?>
                    </label>
                </div>
                <label>
                    <span>Descrição</span>
                    <textarea name="description" rows="4" maxlength="1000"><?= e($form['description'] ?? '') ?></textarea>
                    <?= $fieldError('description') ?>
                </label>
                <label class="check">
                    <input type="checkbox" name="active" value="1"<?= ($form['status'] ?? 'active') === 'active' ? ' checked' : '' ?>>
                    <span>Mostrar este produto no site</span>
                </label>

                <div class="form__actions">
                    <button type="submit" class="btn btn--primary" name="next" value="list">Salvar</button>
                    <?php if (!$product): ?>
                        <button type="submit" class="btn" name="next" value="another">Salvar e cadastrar outro</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
<?php include __DIR__ . '/../_footer.php'; ?>
