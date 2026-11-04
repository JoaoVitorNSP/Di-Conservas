<?php
// Cadastro e edição de produto.
declare(strict_types=1);

require_once __DIR__ . '/../lib/layout.php';
require_once __DIR__ . '/../lib/products.php';
require_once __DIR__ . '/../lib/images.php';

start_admin_session();
require_login();

$products = products_all();
$categories = product_categories($products);

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$isEdit = $id !== false && $id !== null;
$product = $isEdit ? product_find($products, $id) : null;

if ($isEdit && !$product) {
    flash('Produto não encontrado.', 'error');
    redirect('/admin/');
}

$errors = [];
$form = $product ?? ['name' => '', 'category' => '', 'description' => '', 'weight' => '', 'retailPrice' => null, 'wholesalePrice' => null, 'image' => '', 'active' => true];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Envio maior que o limite do PHP chega sem nenhum campo.
    if (!$_POST && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        flash('O envio passou do limite do servidor. Use uma foto menor.', 'error');
        redirect($_SERVER['REQUEST_URI']);
    }
    require_valid_post();

    [$data, $errors] = product_validate($_POST, $categories);
    $file = $_FILES['image'] ?? null;
    $newImage = null;

    if (!$errors && image_was_sent($file)) {
        try {
            $newImage = image_save_upload($file);
        } catch (ImageUploadError $error) {
            $errors['image'] = $error->getMessage();
        }
    }

    if (!$errors) {
        $removeImage = !empty($_POST['remove_image']);
        $remaining = [];
        $oldImage = null;
        try {
            products_update(function (array $current) use ($data, $id, $isEdit, $newImage, $removeImage, &$remaining, &$oldImage): array {
                if (!$isEdit) {
                    $current[] = ['id' => products_next_id($current)] + $data + ['image' => $newImage ?? ''];
                } else {
                    foreach ($current as $index => $item) {
                        if ((int) ($item['id'] ?? 0) === $id) {
                            $image = (string) ($item['image'] ?? '');
                            if ($newImage !== null) {
                                $oldImage = $image;
                                $image = $newImage;
                            } elseif ($removeImage) {
                                $oldImage = $image;
                                $image = '';
                            }
                            $current[$index] = ['id' => $id] + $data + ['image' => $image];
                            break;
                        }
                    }
                }
                $remaining = $current;
                return $current;
            });
            image_delete_if_unused($oldImage, $remaining);

            flash("“{$data['name']}” foi salvo.");
            redirect(($_POST['next'] ?? '') === 'another' ? '/admin/produto.php' : '/admin/');
        } catch (RuntimeException $error) {
            image_delete_if_unused($newImage, []);
            $errors['form'] = $error->getMessage();
        }
    } elseif ($newImage !== null) {
        image_delete_if_unused($newImage, []);
    }

    // Mantém o que foi digitado para corrigir sem digitar tudo de novo.
    $form = array_merge($form, $data, [
        'retailPrice' => clean_line($_POST['retailPrice'] ?? ''),
        'wholesalePrice' => clean_line($_POST['wholesalePrice'] ?? ''),
    ]);
}

// Mostra o preço no formato brasileiro, ex.: 25,50
function price_input(mixed $value): string
{
    if ($value === null || $value === '' || $value === 0 || $value === 0.0) {
        return '';
    }
    return is_string($value) ? $value : number_format((float) $value, 2, ',', '');
}

function field_error(array $errors, string $field): string
{
    return isset($errors[$field]) ? '<small class="error">' . e($errors[$field]) . '</small>' : '';
}

admin_page_start($isEdit ? 'Editar produto' : 'Novo produto');
?>
        <div class="page__head">
            <h1><?= $isEdit ? 'Editar produto' : 'Novo produto' ?></h1>
            <a class="btn" href="/admin/">Voltar</a>
        </div>

<?php if ($errors): ?>
        <p class="flash flash--error" role="alert">
            <?= e($errors['form'] ?? 'Confira os campos marcados.') ?>
            <?php if (image_was_sent($_FILES['image'] ?? null)): ?>Selecione a foto novamente antes de salvar.<?php endif; ?>
        </p>
<?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="panel form form--product" novalidate>
            <?= csrf_field() ?>

            <div class="form__photo">
                <span class="label">Foto</span>
                <div class="photo-box" id="photoBox">
                    <?php if (!empty($form['image'])): ?>
                        <img id="photoCurrent" src="<?= e($form['image']) ?>" alt="Foto atual" width="400" height="400">
                    <?php else: ?>
                        <span id="photoCurrent" class="photo-box__empty">Sem foto</span>
                    <?php endif; ?>
                    <canvas id="photoCanvas" width="1200" height="1200" hidden></canvas>
                </div>
                <label class="range" id="photoZoomBox" hidden>
                    <span>Zoom</span>
                    <input type="range" id="photoZoom" min="1" max="4" step="0.01" value="1">
                </label>
                <p class="muted small" id="photoHint" hidden>Arraste a foto para enquadrar o produto no quadrado.</p>
                <label class="btn btn--block file-btn">
                    <span id="photoButtonText"><?= !empty($form['image']) ? 'Trocar foto' : 'Escolher foto' ?></span>
                    <input type="file" id="photoInput" name="image" accept="image/jpeg,image/png,image/webp">
                </label>
                <?= field_error($errors, 'image') ?>
                <p class="error small" id="photoError" hidden></p>
                <?php if ($isEdit && !empty($form['image'])): ?>
                    <label class="check">
                        <input type="checkbox" name="remove_image" value="1">
                        <span>Remover a foto atual</span>
                    </label>
                <?php endif; ?>
            </div>

            <div class="form__fields">
                <label>
                    <span>Nome *</span>
                    <input type="text" name="name" value="<?= e($form['name']) ?>" maxlength="120" required>
                    <?= field_error($errors, 'name') ?>
                </label>
                <div class="form__row">
                    <label>
                        <span>Categoria *</span>
                        <input type="text" name="category" value="<?= e($form['category']) ?>" maxlength="60" list="categoryOptions" autocomplete="off" required>
                        <datalist id="categoryOptions">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e($category) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <?= field_error($errors, 'category') ?>
                    </label>
                    <label>
                        <span>Peso / volume</span>
                        <input type="text" name="weight" value="<?= e($form['weight']) ?>" maxlength="30" placeholder="Ex.: 300g drenado">
                        <?= field_error($errors, 'weight') ?>
                    </label>
                </div>
                <div class="form__row">
                    <label>
                        <span>Preço de varejo (R$) *</span>
                        <input type="text" inputmode="decimal" name="retailPrice" value="<?= e(price_input($form['retailPrice'])) ?>" placeholder="25,50" required>
                        <?= field_error($errors, 'retailPrice') ?>
                    </label>
                    <label>
                        <span>Preço de atacado (R$)</span>
                        <input type="text" inputmode="decimal" name="wholesalePrice" value="<?= e(price_input($form['wholesalePrice'])) ?>" placeholder="20,40">
                        <?= field_error($errors, 'wholesalePrice') ?>
                    </label>
                </div>
                <label>
                    <span>Descrição</span>
                    <textarea name="description" rows="4" maxlength="1000"><?= e($form['description']) ?></textarea>
                    <?= field_error($errors, 'description') ?>
                </label>
                <label class="check">
                    <input type="checkbox" name="active" value="1"<?= ($form['active'] ?? true) !== false ? ' checked' : '' ?>>
                    <span>Mostrar este produto no site</span>
                </label>

                <div class="form__actions">
                    <button type="submit" class="btn btn--primary" name="next" value="list">Salvar</button>
                    <?php if (!$isEdit): ?>
                        <button type="submit" class="btn" name="next" value="another">Salvar e cadastrar outro</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
<?php
admin_page_end();
