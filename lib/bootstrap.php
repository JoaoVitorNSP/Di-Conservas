<?php
// Base do painel: caminhos, sessão segura, proteção contra CSRF e utilitários.
declare(strict_types=1);

define('ROOT_DIR', dirname(__DIR__));
define('DATA_DIR', ROOT_DIR . '/data');
define('PRODUCTS_FILE', ROOT_DIR . '/products.json');
define('PRODUCT_IMAGE_DIR', ROOT_DIR . '/assets/produtos');
define('PRODUCT_IMAGE_URL', '/assets/produtos/');
define('SESSION_IDLE_SECONDS', 3600); // sai sozinho após 1 hora parado

function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    return ($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off';
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

// Inicia a sessão e envia os cabeçalhos de segurança de toda página do painel.
function start_admin_session(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' blob:; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('dc_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();

    if (isset($_SESSION['admin_user'])) {
        if (time() - (int) ($_SESSION['last_activity'] ?? 0) > SESSION_IDLE_SECONDS) {
            $_SESSION = [];
            session_regenerate_id(true);
            flash('A sessão expirou por inatividade. Entre novamente.', 'error');
        } else {
            $_SESSION['last_activity'] = time();
        }
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION['admin_user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('/admin/login.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

// Bloqueia envios de formulário que não partiram do próprio painel.
function require_valid_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Método não permitido.');
    }
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(403);
        exit('Sessão inválida. Volte, recarregue a página e tente de novo.');
    }
}

function flash(string $message, string $type = 'ok'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function flash_take(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Grava substituindo de uma vez, para nunca deixar um arquivo pela metade.
function write_file_atomic(string $path, string $content): void
{
    $temp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (file_put_contents($temp, $content, LOCK_EX) === false || !rename($temp, $path)) {
        @unlink($temp);
        throw new RuntimeException('Não foi possível gravar o arquivo. Verifique as permissões da pasta.');
    }
}
