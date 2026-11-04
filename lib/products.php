<?php
// Leitura, validação e gravação do catálogo (products.json).
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const MAX_PRODUCT_BACKUPS = 30;

function products_decode(string $json): array
{
    if (trim($json) === '') {
        return [];
    }
    $products = json_decode($json, true);
    // Nunca trata arquivo corrompido como lista vazia: salvar por cima apagaria o catálogo.
    if (!is_array($products)) {
        throw new RuntimeException('O arquivo products.json está corrompido. Restaure uma cópia de data/backups.');
    }
    return $products;
}

function products_all(): array
{
    return is_file(PRODUCTS_FILE) ? products_decode((string) file_get_contents(PRODUCTS_FILE)) : [];
}

function product_find(array $products, int $id): ?array
{
    foreach ($products as $product) {
        if ((int) ($product['id'] ?? 0) === $id) {
            return $product;
        }
    }
    return null;
}

function product_categories(array $products): array
{
    $names = array_filter(array_map(fn ($p) => (string) ($p['category'] ?? ''), $products));
    $names = array_values(array_unique($names));
    sort($names, SORT_NATURAL | SORT_FLAG_CASE);
    return $names;
}

function products_next_id(array $products): int
{
    return array_reduce($products, fn ($max, $p) => max($max, (int) ($p['id'] ?? 0)), 0) + 1;
}

// Altera o catálogo com trava (dois envios ao mesmo tempo não se atropelam) e guarda uma cópia antes.
function products_update(callable $change): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0750, true);
    }
    $lock = fopen(DATA_DIR . '/products.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('Não foi possível travar o catálogo para gravação.');
    }
    try {
        flock($lock, LOCK_EX);
        $products = products_all();
        $updated = array_values($change($products));
        products_backup();
        $json = json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        write_file_atomic(PRODUCTS_FILE, $json . "\n");
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function products_backup(): void
{
    if (!is_file(PRODUCTS_FILE)) {
        return;
    }
    $dir = DATA_DIR . '/backups';
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    copy(PRODUCTS_FILE, $dir . '/products-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json');

    $files = glob($dir . '/products-*.json') ?: [];
    sort($files);
    foreach (array_slice($files, 0, max(0, count($files) - MAX_PRODUCT_BACKUPS)) as $old) {
        @unlink($old);
    }
}

// Aceita "25,50", "25.50" e "1.234,50". Retorna null quando não é um preço válido.
function parse_price(string $value): ?float
{
    $value = trim($value);
    if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{1,2})?$/', $value)) {
        $value = str_replace('.', '', $value);
    }
    $value = str_replace(',', '.', $value);
    if (!preg_match('/^\d{1,7}(\.\d{1,2})?$/', $value)) {
        return null;
    }
    return round((float) $value, 2);
}

function clean_line(mixed $value): string
{
    return is_string($value) ? trim((string) preg_replace('/\s+/u', ' ', $value)) : '';
}

// Valida o formulário. Retorna [dados, erros].
function product_validate(array $input, array $existingCategories): array
{
    $errors = [];

    $name = clean_line($input['name'] ?? '');
    $category = clean_line($input['category'] ?? '');
    $weight = clean_line($input['weight'] ?? '');
    $description = is_string($input['description'] ?? null) ? trim($input['description']) : '';
    $retailRaw = clean_line($input['retailPrice'] ?? '');
    $wholesaleRaw = clean_line($input['wholesalePrice'] ?? '');

    if ($name === '' || mb_strlen($name) > 120) {
        $errors['name'] = 'Informe o nome (até 120 caracteres).';
    }
    if ($category === '' || mb_strlen($category) > 60) {
        $errors['category'] = 'Informe a categoria (até 60 caracteres).';
    }
    if (mb_strlen($weight) > 30) {
        $errors['weight'] = 'Use até 30 caracteres.';
    }
    if (mb_strlen($description) > 1000) {
        $errors['description'] = 'Use até 1000 caracteres.';
    }

    $retail = parse_price($retailRaw);
    if ($retail === null || $retail <= 0) {
        $errors['retailPrice'] = 'Informe um preço válido. Exemplo: 25,50';
    }

    $wholesale = null;
    if ($wholesaleRaw !== '') {
        $wholesale = parse_price($wholesaleRaw);
        if ($wholesale === null || $wholesale <= 0) {
            $errors['wholesalePrice'] = 'Informe um preço válido ou deixe em branco.';
        } elseif ($retail !== null && $wholesale > $retail) {
            $errors['wholesalePrice'] = 'O preço de atacado não pode ser maior que o de varejo.';
        }
    }

    // "conservas" vira "Conservas" se a categoria já existe: evita categorias duplicadas.
    foreach ($existingCategories as $existing) {
        if (mb_strtolower($existing) === mb_strtolower($category)) {
            $category = $existing;
            break;
        }
    }

    return [[
        'name' => $name,
        'category' => $category,
        'description' => $description,
        'weight' => $weight,
        'retailPrice' => $retail ?? 0.0,
        'wholesalePrice' => $wholesale,
        'active' => !empty($input['active']),
    ], $errors];
}

function format_price(float|int|null $value): string
{
    return $value ? 'R$ ' . number_format((float) $value, 2, ',', '.') : '';
}
