<?php
// Cria ou redefine o acesso ao painel. Uso, na pasta do projeto: php scripts/criar-senha.php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../lib/auth.php';

function ask(string $question): string
{
    echo $question;
    $line = fgets(STDIN);
    return $line === false ? '' : rtrim($line, "\r\n");
}

$username = trim(ask('Usuário [admin]: ')) ?: 'admin';
$password = ask('Nova senha (mínimo de ' . MIN_PASSWORD_LENGTH . " caracteres; aparece na tela ao digitar): ");

$problem = password_problem($password);
if ($problem !== null) {
    fwrite(STDERR, $problem . PHP_EOL);
    exit(1);
}
if (ask('Repita a senha: ') !== $password) {
    fwrite(STDERR, 'As senhas não são iguais.' . PHP_EOL);
    exit(1);
}

save_credentials($username, $password);
echo "Pronto. Entre em /admin/ com o usuário \"{$username}\"." . PHP_EOL;
