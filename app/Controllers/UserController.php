<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\User;

/**
 * Controlador para gerenciamento de usuários
 */
class UserController extends BaseController
{
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new \App\Models\User();
    }
    
    /**
     * Lista todos os usuários
     */
    public function index()
    {
        // Verifica se está logado e é admin
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        $users = $this->userModel->all();
        $totalUsers = $this->userModel->countActive();
        
        $this->view('admin.users.index', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'title' => 'Gerenciar Usuários'
        ]);
    }
    
    /**
     * Exibe formulário para criar usuário
     */
    public function create()
    {
        // Verifica se está logado e é admin
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        $holes = $this->userModel->getHoles();
        
        $this->view('admin.users.create', [
            'holes' => $holes,
            'title' => 'Criar Usuário'
        ]);
    }
    
    /**
     * Processa criação de usuário
     */
    public function store()
    {
        // Verifica se está logado e é admin
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'phone' => trim($_POST['phone'] ?? ''),
            'hole_id' => $_POST['hole_id'] ?? null,
            'status' => 'active'
        ];
        
        $errors = [];
        
        // Validações
        if (empty($data['name'])) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email é obrigatório';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        } elseif ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'Este email já está em uso';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'Senha é obrigatória';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        if (empty($data['hole_id'])) {
            $errors[] = 'Papel é obrigatório';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $data;
            $this->redirect('/admin/users/create');
            return;
        }
        
        try {
            if ($this->userModel->create($data)) {
                $_SESSION['success'] = 'Usuário criado com sucesso!';
                $this->redirect('/admin/users');
            } else {
                $_SESSION['error'] = 'Erro ao criar usuário';
                $this->redirect('/admin/users/create');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro interno: ' . $e->getMessage();
            $this->redirect('/admin/users/create');
        }
    }
    
    /**
     * Exibe formulário para editar usuário
     */
    public function edit($id)
    {
        // Verifica se está logado e é admin
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            $this->redirect('/admin/users');
            return;
        }
        
        $holes = $this->userModel->getHoles();
        
        $this->view('admin.users.edit', [
            'user' => $user,
            'holes' => $holes,
            'title' => 'Editar Usuário'
        ]);
    }
    
    /**
     * Processa atualização de usuário
     */
    public function update($id)
    {
        // Verifica se está logado e é admin
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            $this->redirect('/admin/users');
            return;
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'hole_id' => $_POST['hole_id'] ?? null
        ];
        
        // Senha opcional na edição
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        $errors = [];
        
        // Validações
        if (empty($data['name'])) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email é obrigatório';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        } elseif ($this->userModel->emailExists($data['email'], $id)) {
            $errors[] = 'Este email já está em uso';
        }
        
        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        if (empty($data['hole_id'])) {
            $errors[] = 'Papel é obrigatório';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect("/admin/users/{$id}/edit");
            return;
        }
        
        try {
            if ($this->userModel->update($id, $data)) {
                $_SESSION['success'] = 'Usuário atualizado com sucesso!';
                $this->redirect('/admin/users');
            } else {
                $_SESSION['error'] = 'Erro ao atualizar usuário';
                $this->redirect("/admin/users/{$id}/edit");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro interno: ' . $e->getMessage();
            $this->redirect("/admin/users/{$id}/edit");
        }
    }
    
    /**
     * Remove usuário
     */
    public function destroy($id)
    {
        // Verifica se está logado e é admin
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            $this->redirect('/admin/users');
            return;
        }
        
        // Não permite deletar o próprio usuário
        if ($user['id'] === $_SESSION['user']['id']) {
            $_SESSION['error'] = 'Você não pode deletar seu próprio usuário';
            $this->redirect('/admin/users');
            return;
        }
        
        try {
            if ($this->userModel->delete($id)) {
                $_SESSION['success'] = 'Usuário removido com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao remover usuário';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro interno: ' . $e->getMessage();
        }
        
        $this->redirect('/admin/users');
    }
    
    /**
     * Verifica se o usuário logado é administrador
     */
    protected function isAdmin()
    {
        return isset($_SESSION['user']['hole_id']) && $_SESSION['user']['hole_id'] == 1;
    }
}
