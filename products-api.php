<?php
// products-api.php: entrega ao site os produtos visíveis (somente leitura).

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$raw = @file_get_contents(__DIR__ . '/products.json');
$products = $raw === false ? null : json_decode($raw, true);

if (!is_array($products)) {
    http_response_code(500);
    echo json_encode(['error' => 'Catálogo indisponível']);
    exit;
}

$visible = array_values(array_filter($products, fn ($p) => ($p['active'] ?? true) !== false));
echo json_encode($visible, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
