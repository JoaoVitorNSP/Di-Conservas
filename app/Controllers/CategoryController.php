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

    private function nameProblem(string $name): ?string
    {
        return $name === '' || mb_strlen($name) > 60 ? 'Informe o nome da categoria (até 60 caracteres).' : null;
    }
}
