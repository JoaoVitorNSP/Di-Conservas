<?php
// Cria um administrador ou redefine a senha de um existente, informando o mesmo e-mail.
// Uso, na pasta do projeto: php scripts/criar-admin.php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/EnvLoader.php';
EnvLoader::load(ROOT_PATH . '/.env');
require ROOT_PATH . '/app/helpers.php';
require ROOT_PATH . '/app/Autoloader.php';

use App\Models\User;

function ask(string $question): string
{
    echo $question;
    $line = fgets(STDIN);
    return $line === false ? '' : trim($line, "\r\n");
}

$email = trim(ask('E-mail: '));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, 'E-mail inválido.' . PHP_EOL);
    exit(1);
}

$users = new User();
$existing = $users->findByEmail($email);
$name = $existing['name'] ?? trim(ask('Nome: '));
if ($name === '') {
    fwrite(STDERR, 'Informe o nome.' . PHP_EOL);
    exit(1);
}

$password = ask('Nova senha (mínimo de ' . User::MIN_PASSWORD_LENGTH . ' caracteres; aparece na tela ao digitar): ');
$problem = User::passwordProblem($password);
if ($problem !== null) {
    fwrite(STDERR, $problem . PHP_EOL);
    exit(1);
}
if (ask('Repita a senha: ') !== $password) {
    fwrite(STDERR, 'As senhas não são iguais.' . PHP_EOL);
    exit(1);
}

if ($existing) {
    $users->update((int) $existing['id'], $existing['name'], $email, $password);
    echo "Senha de {$email} redefinida." . PHP_EOL;
} else {
    $users->create($name, $email, $password);
    echo "Administrador {$email} criado. Entre em /admin." . PHP_EOL;
}
