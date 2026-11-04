<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ImageUploadError;
use App\Services\ProductImage;

class ProductController extends BaseController
{
    // ---------- Site ----------

    public function home(): void
    {
        // A página não abre o banco: os produtos chegam por /api/products.
        $this->view('products.index');
    }

    public function api(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Content-Type-Options: nosniff');

        try {
            $products = (new Product())->visible();
        } catch (\PDOException $error) {
            error_log((string) $error);
            http_response_code(500);
            echo json_encode(['error' => 'Catálogo indisponível']);
            return;
        }

        echo json_encode(array_map(fn (array $p): array => [
            'id' => (int) $p['id'],
            'name' => $p['name'],
            'category' => $p['category_name'],
            'description' => (string) $p['description'],
            'weight' => format_weight($p['weight'], $p['unit_name']),
            'retailPrice' => (float) $p['retail_price'],
            'wholesalePrice' => (float) $p['wholesale_price'],
            'image' => (string) $p['image'],
        ], $products), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // ---------- Painel ----------

    public function index(): void
    {
        $this->requireAdmin();
        $products = (new Product())->all();
        $this->view('admin.products.index', [
            'title' => 'Produtos',
            'products' => $products,
            'categories' => array_values(array_unique(array_column((new Category())->allWithCounts(), 'description'))),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();
        $this->renderForm(null, ['status' => 'active']);
    }

    public function edit(int $id): void
    {
        $this->requireAdmin();
        $product = (new Product())->find($id);
        if (!$product) {
            $this->notFound();
        }
        $this->renderForm($product, $product);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->save(null);
    }

    public function update(int $id): void
    {
        $this->requireAdmin();
        $product = (new Product())->find($id);
        if (!$product) {
            $this->notFound();
        }
        $this->save($product);
    }

    public function toggle(int $id): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $products = new Product();
        $product = $products->find($id);
        if (!$product) {
            $this->notFound();
        }
        $status = $product['status'] === 'active' ? 'inactive' : 'active';
        $products->setStatus($id, $status);
        flash($status === 'active' ? "“{$product['name']}” voltou a aparecer no site." : "“{$product['name']}” não aparece mais no site.");
        $this->redirect('/admin/products');
    }

    public function destroy(int $id): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $products = new Product();
        $product = $products->find($id);
        if (!$product) {
            $this->notFound();
        }
        $products->delete($id);
        $this->deleteImageIfUnused($products, $product['image']);
        flash("“{$product['name']}” foi excluído.");
        $this->redirect('/admin/products');
    }

    private function save(?array $product): void
    {
        // Envio maior que o limite do PHP chega sem nenhum campo.
        if (!$_POST && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            flash('O envio passou do limite do servidor. Use uma foto menor.', 'error');
            $this->redirect('/admin/products');
        }
        $this->requireValidCsrf();

        [$data, $errors] = $this->validate($_POST);
        $file = $_FILES['image'] ?? null;
        $newImage = null;

        if (!$errors && ProductImage::wasSent($file)) {
            try {
                $newImage = ProductImage::save($file);
            } catch (ImageUploadError $error) {
                $errors['image'] = $error->getMessage();
            }
        }

        if ($errors) {
            $form = $data + ['retail_input' => clean_line($_POST['retailPrice'] ?? ''), 'wholesale_input' => clean_line($_POST['wholesalePrice'] ?? ''), 'weight_input' => clean_line($_POST['weight'] ?? ''), 'category_name' => clean_line($_POST['category'] ?? ''), 'image' => $product['image'] ?? ''];
            $this->renderForm($product, $form, $errors, ProductImage::wasSent($file));
            return;
        }

        $products = new Product();
        $data['category_id'] = (new Category())->firstOrCreate($data['category']);
        unset($data['category']);

        $oldImage = (string) ($product['image'] ?? '');
        $data['image'] = $oldImage;
        if ($newImage !== null) {
            $data['image'] = $newImage;
        } elseif (!empty($_POST['remove_image'])) {
            $data['image'] = '';
        }

        try {
            if ($product) {
                $products->update((int) $product['id'], $data);
            } else {
                $products->create($data);
            }
        } catch (\Throwable $error) {
            ProductImage::delete($newImage);
            throw $error;
        }

        if ($oldImage !== '' && $oldImage !== $data['image']) {
            $this->deleteImageIfUnused($products, $oldImage);
        }

        flash("“{$data['name']}” foi salvo.");
        $this->redirect(($_POST['next'] ?? '') === 'another' ? '/admin/products/create' : '/admin/products');
    }

    private function deleteImageIfUnused(Product $products, ?string $image): void
    {
        if ($image && !$products->imageInUse($image)) {
            ProductImage::delete($image);
        }
    }

    // Valida o formulário. Retorna [dados, erros].
    private function validate(array $input): array
    {
        $errors = [];
        $name = clean_line($input['name'] ?? '');
        $category = clean_line($input['category'] ?? '');
        $description = is_string($input['description'] ?? null) ? trim($input['description']) : '';
        $weightRaw = clean_line($input['weight'] ?? '');
        $unitId = filter_var($input['unit_id'] ?? null, FILTER_VALIDATE_INT);
        $retailRaw = clean_line($input['retailPrice'] ?? '');
        $wholesaleRaw = clean_line($input['wholesalePrice'] ?? '');

        if ($name === '' || mb_strlen($name) > 120) {
            $errors['name'] = 'Informe o nome (até 120 caracteres).';
        }
        if ($category === '' || mb_strlen($category) > 60) {
            $errors['category'] = 'Informe a categoria (até 60 caracteres).';
        }
        if (mb_strlen($description) > 1000) {
            $errors['description'] = 'Use até 1000 caracteres.';
        }

        $weight = null;
        if ($weightRaw !== '') {
            $weight = parse_decimal($weightRaw, 3);
            $validUnit = $unitId !== false && in_array($unitId, array_map('intval', array_column((new Product())->units(), 'id')), true);
            if ($weight === null || $weight <= 0) {
                $errors['weight'] = 'Informe só o número. Exemplos: 300 ou 1,8';
            } elseif (!$validUnit) {
                $errors['weight'] = 'Escolha a unidade.';
            }
        }

        $retail = parse_decimal($retailRaw);
        if ($retail === null || $retail <= 0) {
            $errors['retailPrice'] = 'Informe um preço válido. Exemplo: 25,50';
        }

        $wholesale = 0.0;
        if ($wholesaleRaw !== '') {
            $wholesale = parse_decimal($wholesaleRaw);
            if ($wholesale === null || $wholesale <= 0) {
                $errors['wholesalePrice'] = 'Informe um preço válido ou deixe em branco.';
                $wholesale = 0.0;
            } elseif ($retail !== null && $wholesale > $retail) {
                $errors['wholesalePrice'] = 'O preço de atacado não pode ser maior que o de varejo.';
            }
        }

        return [[
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'weight' => $weight,
            'unit_id' => $weight !== null && $unitId !== false ? $unitId : null,
            'retail_price' => $retail ?? 0.0,
            'wholesale_price' => $wholesale,
            'status' => empty($input['active']) ? 'inactive' : 'active',
        ], $errors];
    }

    private function renderForm(?array $product, array $form, array $errors = [], bool $photoLost = false): void
    {
        $this->view('admin.products.form', [
            'title' => $product ? 'Editar produto' : 'Novo produto',
            'product' => $product,
            'form' => $form,
            'errors' => $errors,
            'photoLost' => $photoLost,
            'categories' => array_column((new Category())->allWithCounts(), 'description'),
            'units' => (new Product())->units(),
        ]);
    }
}
