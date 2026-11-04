<?php
// Ações rápidas da lista: ocultar/mostrar e excluir produto.
declare(strict_types=1);

require_once __DIR__ . '/../lib/products.php';
require_once __DIR__ . '/../lib/images.php';

start_admin_session();
require_login();
require_valid_post();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

try {
    if ($id === false || !in_array($action, ['toggle', 'delete'], true)) {
        throw new RuntimeException('Pedido inválido.');
    }

    $name = null;
    $image = null;
    $remaining = [];
    $nowActive = true;

    products_update(function (array $products) use ($id, $action, &$name, &$image, &$remaining, &$nowActive): array {
        foreach ($products as $index => $product) {
            if ((int) ($product['id'] ?? 0) !== $id) {
                continue;
            }
            $name = (string) ($product['name'] ?? '');
            if ($action === 'toggle') {
                $nowActive = ($product['active'] ?? true) === false;
                $products[$index]['active'] = $nowActive;
            } else {
                $image = $product['image'] ?? null;
                unset($products[$index]);
            }
            break;
        }
        $remaining = $products;
        return $products;
    });

    if ($name === null) {
        flash('Produto não encontrado.', 'error');
    } elseif ($action === 'delete') {
        image_delete_if_unused($image, $remaining);
        flash("“{$name}” foi excluído.");
    } else {
        flash($nowActive ? "“{$name}” voltou a aparecer no site." : "“{$name}” não aparece mais no site.");
    }
} catch (RuntimeException $error) {
    flash($error->getMessage(), 'error');
}

redirect('/admin/');
