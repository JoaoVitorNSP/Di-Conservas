<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

abstract class BaseController
{
    private const SESSION_IDLE_SECONDS = 3600; // sai sozinho após 1 hora parado

    protected ?array $currentUser = null;

    protected function view(string $viewName, array $viewData = []): void
    {
        $viewFile = ROOT_PATH . '/resources/views/' . str_replace('.', '/', $viewName) . '.php';
        // EXTR_SKIP: um campo como "name" não pode sobrescrever as variáveis acima.
        extract($viewData, EXTR_SKIP);
        $currentUser = $this->currentUser;
        require $viewFile;
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path, true, 303);
        exit;
    }

    protected function notFound(): never
    {
        http_response_code(404);
        $title = 'Página não encontrada';
        include ROOT_PATH . '/resources/views/errors/404.php';
        exit;
    }

    private function isHttps(): bool
    {
        return ($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off';
    }

    // Inicia a sessão e envia os cabeçalhos de segurança de toda página do painel.
    protected function startAdmin(): void
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
            'secure' => $this->isHttps(),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();

        if (isset($_SESSION['user_id'])) {
            if (time() - (int) ($_SESSION['last_activity'] ?? 0) > self::SESSION_IDLE_SECONDS) {
                $this->endLogin();
                flash('A sessão expirou por inatividade. Entre novamente.', 'error');
                return;
            }
            $_SESSION['last_activity'] = time();
            $this->loadCurrentUser();
        }
    }

    // A cada pedido confere se o usuário ainda existe e se a senha não mudou desde o login.
    private function loadCurrentUser(): void
    {
        $user = (new User())->find((int) $_SESSION['user_id']);
        if (!$user || !hash_equals((string) ($_SESSION['auth'] ?? ''), self::authFingerprint($user))) {
            $this->endLogin();
            return;
        }
        $this->currentUser = $user;
    }

    protected static function authFingerprint(array $user): string
    {
        return hash('sha256', $user['id'] . '|' . $user['password']);
    }

    protected function beginLogin(array $user): void
    {
        session_regenerate_id(true);
        unset($_SESSION['csrf']);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['auth'] = self::authFingerprint($user);
        $_SESSION['last_activity'] = time();
    }

    protected function endLogin(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    protected function requireAdmin(): void
    {
        $this->startAdmin();
        if ($this->currentUser === null) {
            $this->redirect('/admin');
        }
    }

    // Bloqueia envios de formulário que não partiram do próprio painel.
    protected function requireValidCsrf(): void
    {
        $sent = $_POST['csrf'] ?? '';
        if (!is_string($sent) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
            http_response_code(403);
            exit('Sessão inválida. Volte, recarregue a página e tente de novo.');
        }
    }
}
