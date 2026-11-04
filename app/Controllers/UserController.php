<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;

// Todo usuário é administrador: quem entra pode fazer tudo.
class UserController extends BaseController
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->view('admin.users.index', ['title' => 'Usuários', 'users' => (new User())->all()]);
    }

    public function create(): void
    {
        $this->requireAdmin();
        $this->view('admin.users.form', ['title' => 'Novo usuário', 'user' => null, 'form' => ['name' => '', 'email' => ''], 'errors' => []]);
    }

    public function edit(int $id): void
    {
        $this->requireAdmin();
        $user = (new User())->find($id);
        if (!$user) {
            $this->notFound();
        }
        $this->view('admin.users.form', ['title' => 'Editar usuário', 'user' => $user, 'form' => $user, 'errors' => []]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $this->save(null);
    }

    public function update(int $id): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $user = (new User())->find($id);
        if (!$user) {
            $this->notFound();
        }
        $this->save($user);
    }

    public function destroy(int $id): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $users = new User();
        $user = $users->find($id);
        if (!$user) {
            $this->notFound();
        }
        if ($id === (int) $this->currentUser['id']) {
            flash('Você não pode excluir o seu próprio usuário.', 'error');
        } else {
            $users->delete($id);
            flash("“{$user['name']}” foi excluído.");
        }
        $this->redirect('/admin/users');
    }

    private function save(?array $user): void
    {
        $users = new User();
        $name = clean_line($_POST['name'] ?? '');
        $email = clean_line($_POST['email'] ?? '');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $repeat = is_string($_POST['repeat'] ?? null) ? $_POST['repeat'] : '';
        $errors = [];

        if ($name === '' || mb_strlen($name) > 200) {
            $errors['name'] = 'Informe o nome.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Informe um e-mail válido.';
        } else {
            $sameEmail = $users->findByEmail($email);
            if ($sameEmail && (int) $sameEmail['id'] !== (int) ($user['id'] ?? 0)) {
                $errors['email'] = 'Este e-mail já está em uso.';
            }
        }

        $changingPassword = $user === null || $password !== '';
        if ($changingPassword) {
            $problem = User::passwordProblem($password);
            if ($problem !== null) {
                $errors['password'] = $problem;
            } elseif ($password !== $repeat) {
                $errors['repeat'] = 'A confirmação não é igual à senha.';
            }
        }

        // Quem troca a própria senha precisa confirmar a atual; erros contam no limite de tentativas.
        $editingSelf = $user !== null && (int) $user['id'] === (int) $this->currentUser['id'];
        $attempts = new LoginAttempt();
        if ($editingSelf && $changingPassword && !$errors) {
            $lock = $attempts->lockRemaining();
            $current = is_string($_POST['current'] ?? null) ? $_POST['current'] : '';
            if ($lock > 0) {
                $errors['current'] = 'Muitas tentativas. Aguarde ' . max(1, (int) ceil($lock / 60)) . ' minuto(s).';
            } elseif (!password_verify($current, $user['password'])) {
                $attempts->registerFailure();
                $errors['current'] = 'A senha atual está incorreta.';
            }
        }

        if ($errors) {
            $this->view('admin.users.form', [
                'title' => $user ? 'Editar usuário' : 'Novo usuário',
                'user' => $user,
                'form' => ['name' => $name, 'email' => $email],
                'errors' => $errors,
            ]);
            return;
        }

        if ($user === null) {
            $users->create($name, $email, $password);
        } else {
            $users->update((int) $user['id'], $name, $email, $changingPassword ? $password : null);
            if ($editingSelf && $changingPassword) {
                $attempts->clear();
                // Mantém esta sessão; as demais sessões deste usuário são encerradas.
                $this->beginLogin($users->find((int) $user['id']));
            }
        }
        flash("“{$name}” foi salvo.");
        $this->redirect('/admin/users');
    }
}
