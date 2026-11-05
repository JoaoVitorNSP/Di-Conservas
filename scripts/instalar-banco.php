<?php
// Cria as tabelas e, se o catálogo estiver vazio, cadastra os produtos iniciais.
// Uso, na pasta do projeto: php scripts/instalar-banco.php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/EnvLoader.php';
EnvLoader::load(ROOT_PATH . '/.env');
require ROOT_PATH . '/app/helpers.php';

$config = require ROOT_PATH . '/config/database.php';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
$server = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";

$pdo = new PDO($server, $config['username'], $config['password'], $options);
$dbName = '`' . str_replace('`', '``', $config['database']) . '`';
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException) {
    // Em hospedagem compartilhada o banco é criado pelo painel: basta existir.
}
$pdo->exec("USE {$dbName}");

function run_sql_file(PDO $pdo, string $path): void
{
    $statements = preg_split('/;\s*(?:\r?\n|$)/', (string) file_get_contents($path));
    foreach ($statements as $statement) {
        if (trim($statement) !== '') {
            $pdo->exec($statement);
        }
    }
}

run_sql_file($pdo, ROOT_PATH . '/database/schema.sql');
// Bancos criados antes de as categorias poderem ser ocultadas.
if (!$pdo->query("SHOW COLUMNS FROM categories LIKE 'status'")->fetch()) {
    $pdo->exec("ALTER TABLE categories ADD COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER description");
}
echo "Tabelas prontas." . PHP_EOL;

$total = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
if ($total > 0) {
    echo "O catálogo já tem {$total} produtos: nada foi importado." . PHP_EOL;
    exit;
}
run_sql_file($pdo, ROOT_PATH . '/database/produtos-iniciais.sql');
$total = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
echo "{$total} produtos cadastrados." . PHP_EOL;
