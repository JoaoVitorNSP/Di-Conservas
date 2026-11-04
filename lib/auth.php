<?php
// Login do painel: a senha fica apenas como hash, fora do código, e há limite de tentativas.
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const CREDENTIALS_FILE = DATA_DIR . '/credentials.php';
const ATTEMPTS_FILE = DATA_DIR . '/login-attempts.json';
const MAX_LOGIN_ATTEMPTS = 5;
const LOGIN_LOCK_SECONDS = 900;
const MIN_PASSWORD_LENGTH = 10;

function admin_credentials(): ?array
{
    if (!is_file(CREDENTIALS_FILE)) {
        return null;
    }
    $data = require CREDENTIALS_FILE;
    return is_array($data) && isset($data['username'], $data['password_hash']) ? $data : null;
}

function password_problem(string $password): ?string
{
    if (mb_strlen($password) < MIN_PASSWORD_LENGTH) {
        return 'A senha precisa ter pelo menos ' . MIN_PASSWORD_LENGTH . ' caracteres.';
    }
    return null;
}

function save_credentials(string $username, string $password): void
{
    $data = ['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)];
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0750, true);
    }
    write_file_atomic(CREDENTIALS_FILE, "<?php\nreturn " . var_export($data, true) . ";\n");
    @chmod(CREDENTIALS_FILE, 0640);
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate(CREDENTIALS_FILE, true);
    }
}

function verify_credentials(string $username, string $password): bool
{
    $credentials = admin_credentials();
    if ($credentials === null) {
        return false;
    }
    // Sempre confere a senha, para que o tempo de resposta não revele se o usuário existe.
    $passwordOk = password_verify($password, $credentials['password_hash']);
    return hash_equals($credentials['username'], $username) && $passwordOk;
}

// Abre o registro de tentativas com trava, aplica $change e salva.
function with_login_attempts(callable $change): mixed
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0750, true);
    }
    $handle = fopen(ATTEMPTS_FILE, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Não foi possível registrar a tentativa de login.');
    }
    try {
        flock($handle, LOCK_EX);
        $attempts = json_decode((string) stream_get_contents($handle), true);
        $attempts = is_array($attempts) ? $attempts : [];

        // Esquece o que já venceu.
        $now = time();
        $attempts = array_filter($attempts, fn ($a) => $now - (int) ($a['last'] ?? 0) < LOGIN_LOCK_SECONDS);

        $result = $change($attempts);

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, (string) json_encode($attempts));
        return $result;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function login_client_key(): string
{
    return hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido'));
}

// Segundos que faltam de bloqueio para este IP (0 = pode tentar).
function login_lock_remaining(): int
{
    $key = login_client_key();
    return with_login_attempts(function (array &$attempts) use ($key): int {
        $entry = $attempts[$key] ?? null;
        if (!$entry || (int) $entry['count'] < MAX_LOGIN_ATTEMPTS) {
            return 0;
        }
        return max(0, LOGIN_LOCK_SECONDS - (time() - (int) $entry['last']));
    });
}

function login_register_failure(): void
{
    $key = login_client_key();
    with_login_attempts(function (array &$attempts) use ($key): void {
        $attempts[$key] = ['count' => (int) ($attempts[$key]['count'] ?? 0) + 1, 'last' => time()];
    });
}

function login_clear_failures(): void
{
    $key = login_client_key();
    with_login_attempts(function (array &$attempts) use ($key): void {
        unset($attempts[$key]);
    });
}
