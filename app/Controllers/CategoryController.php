<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;

class CategoryController extends BaseController
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->view('admin.categories.index', [
            'title' => 'Categorias',
            'categories' => (new Category())->allWithCounts(),
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $name = clean_line($_POST['name'] ?? '');
        $categories = new Category();

        if ($error = $this->nameProblem($name)) {
            flash($error, 'error');
        } elseif ($categories->findByName($name)) {
            flash("Já existe a categoria “{$name}”.", 'error');
        } else {
            $categories->create($name);
            flash("Categoria “{$name}” criada.");
        }
        $this->redirect('/admin/categories');
    }

    public function update(int $id): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $name = clean_line($_POST['name'] ?? '');
        $categories = new Category();
        if (!$categories->find($id)) {
            $this->notFound();
        }

        $existing = $categories->findByName($name);
        if ($error = $this->nameProblem($name)) {
            flash($error, 'error');
        } elseif ($existing && (int) $existing['id'] !== $id) {
            flash("Já existe a categoria “{$name}”.", 'error');
        } else {
            $categories->rename($id, $name);
            flash("Categoria renomeada para “{$name}”.");
        }
        $this->redirect('/admin/categories');
    }

    public function destroy(int $id): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $categories = new Category();
        $category = $categories->find($id);
        if (!$category) {
            $this->notFound();
        }

        $total = (int) $category['total'];
        if ($total > 0) {
            flash("“{$category['description']}” tem {$total} produto(s). Mude a categoria deles antes de excluir.", 'error');
        } else {
            $categories->delete($id);
            flash("Categoria “{$category['description']}” excluída.");
        }
        $this->redirect('/admin/categories');
    }

    // Ocultar ou mostrar uma ou várias categorias. Só oculta as que não têm produto visível.
    public function status(): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $ids = $this->postedIds();
        $status = $_POST['status'] ?? '';
        if (!$ids || !in_array($status, ['active', 'inactive'], true)) {
            $this->finish('Selecione ao menos uma categoria.', '/admin/categories', [], 'error', 422);
        }

        $categories = new Category();
        if ($status === 'active') {
            $categories->setStatus($ids, 'active');
            $what = count($ids) === 1 ? '1 categoria voltou a ficar ativa' : count($ids) . ' categorias voltaram a ficar ativas';
            $this->finish("{$what}. Os produtos continuam como estavam.", '/admin/categories', ['status' => 'active', 'changed' => $ids, 'blocked' => []]);
        }

        $blocked = [];
        foreach ($categories->visibleProducts($ids) as $row) {
            $id = (int) $row['category_id'];
            $blocked[$id] ??= ['id' => $id, 'name' => $row['category_name'], 'products' => []];
            $blocked[$id]['products'][] = $row['name'];
        }
        $free = array_values(array_diff($ids, array_keys($blocked)));
        $categories->setStatus($free, 'inactive');

        $parts = [];
        if ($free) {
            $parts[] = count($free) === 1 ? '1 categoria ocultada.' : count($free) . ' categorias ocultadas.';
        }
        foreach ($blocked as $item) {
            $total = count($item['products']);
            $parts[] = "“{$item['name']}” não foi ocultada: ainda tem {$total} " . ($total === 1 ? 'produto visível' : 'produtos visíveis') . '.';
        }
        $this->finish(implode(' ', $parts), '/admin/categories', ['status' => 'inactive', 'changed' => $free, 'blocked' => array_values($blocked)], $free ? 'ok' : 'error');
    }

    private function nameProblem(string $name): ?string
    {
        return $name === '' || mb_strlen($name) > 60 ? 'Informe o nome da categoria (até 60 caracteres).' : null;
    }
}
