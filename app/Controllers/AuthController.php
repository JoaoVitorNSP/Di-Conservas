<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;

class AuthController extends BaseController
{
    private const MIN_SETUP_TOKEN_LENGTH = 16;

    public function showLogin(): void
    {
        $this->startAdmin();
        if ($this->currentUser !== null) {
            $this->redirect('/admin/products');
        }
        $this->renderEntry();
    }

    public function login(): void
    {
        $this->startAdmin();
        $this->requireValidCsrf();
        if ($this->currentUser !== null) {
            $this->redirect('/admin/products');
        }

        $email = clean_line($_POST['email'] ?? '');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $attempts = new LoginAttempt();

        $lock = $attempts->lockRemaining();
        if ($lock > 0) {
            $this->renderEntry(['error' => $this->lockMessage($lock), 'email' => $email]);
            return;
        }

        $users = new User();
        $user = $users->findByEmail($email);
        // Sem usuário, o hash é gerado na hora: o tempo de resposta não revela se o e-mail existe.
        $valid = password_verify($password, $user['password'] ?? password_hash($password, PASSWORD_DEFAULT));

        if (!$user || !$valid) {
            $attempts->registerFailure();
            $this->renderEntry(['error' => 'E-mail ou senha incorretos.', 'email' => $email]);
            return;
        }

        $attempts->clear();
        $users->touchLastLogin((int) $user['id']);
        $this->beginLogin($user);
        $this->redirect('/admin/products');
    }

    // Cria o primeiro administrador. Só funciona enquanto não existe nenhum usuário.
    public function setup(): void
    {
        $this->startAdmin();
        $this->requireValidCsrf();

        $users = new User();
        if ($users->count() > 0) {
            $this->redirect('/admin');
        }

        $attempts = new LoginAttempt();
        $lock = $attempts->lockRemaining();
        if ($lock > 0) {
            $this->renderEntry(['error' => $this->lockMessage($lock)]);
            return;
        }

        $token = (string) env('SETUP_TOKEN', '');
        $sent = is_string($_POST['token'] ?? null) ? $_POST['token'] : '';
        if (strlen($token) < self::MIN_SETUP_TOKEN_LENGTH || !hash_equals($token, $sent)) {
            $attempts->registerFailure();
            $this->renderEntry(['error' => 'Código de instalação incorreto.']);
            return;
        }

        $name = clean_line($_POST['name'] ?? '');
        $email = clean_line($_POST['email'] ?? '');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $repeat = is_string($_POST['repeat'] ?? null) ? $_POST['repeat'] : '';

        $error = match (true) {
            $name === '' => 'Informe seu nome.',
            !filter_var($email, FILTER_VALIDATE_EMAIL) => 'Informe um e-mail válido.',
            $password !== $repeat => 'A confirmação não é igual à senha.',
            default => User::passwordProblem($password),
        };
        if ($error !== null) {
            $this->renderEntry(['error' => $error, 'email' => $email, 'name' => $name]);
            return;
        }

        $id = $users->create($name, $email, $password);
        $attempts->clear();
        $this->beginLogin($users->find($id));
        flash('Acesso criado. Agora você já pode apagar o SETUP_TOKEN do .env.');
        $this->redirect('/admin/products');
    }

    public function logout(): void
    {
        $this->startAdmin();
        $this->requireValidCsrf();
        $this->endLogin();
        session_destroy();
        $this->redirect('/admin');
    }

    private function renderEntry(array $data = []): void
    {
        $hasUsers = (new User())->count() > 0;
        $canSetup = strlen((string) env('SETUP_TOKEN', '')) >= self::MIN_SETUP_TOKEN_LENGTH;
        $this->view($hasUsers ? 'admin.login' : 'admin.setup', $data + ['canSetup' => $canSetup]);
    }

    private function lockMessage(int $seconds): string
    {
        return 'Muitas tentativas. Aguarde ' . max(1, (int) ceil($seconds / 60)) . ' minuto(s) e tente de novo.';
    }
}
