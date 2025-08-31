<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\User;

/**
 * Controlador de autenticação
 */
class AuthController extends BaseController
{
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new \App\Models\User();
    }
    
    /**
     * Exibe página de login
     */
    public function showLogin()
    {
        // Se já está logado, redireciona para o painel
        if ($this->isLoggedIn()) {
            $this->redirect('/admin/dashboard');
            return;
        }
        
        $this->view('admin.login', [
            'title' => 'Login Admin'
        ]);
    }
    
    /**
     * Processa o login
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin');
            return;
        }
        
        $this->startSession(); // Garantir que a sessão esteja iniciada
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Email e senha são obrigatórios';
            $this->redirect('/admin');
            return;
        }
        
        // Busca usuário por email
        $user = $this->userModel->findByEmail($email);
        
        if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
            // Verifica se o usuário está ativo
            if ($user['status'] !== 'active') {
                $_SESSION['error'] = 'Sua conta está inativa. Entre em contato com o administrador.';
                $this->redirect('/admin');
                return;
            }
            
            // Login bem-sucedido
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'hole_id' => $user['hole_id'],
                'hole_name' => $user['hole_name']
            ];
            $_SESSION['last_activity'] = time();
            $_SESSION['success'] = 'Login realizado com sucesso!';
            
            // Atualiza último login
            $this->userModel->updateLastLogin($user['id']);
            
            $this->redirect('/admin/dashboard');
        } else {
            // Login falhou
            $_SESSION['error'] = 'Email ou senha incorretos';
            $this->redirect('/admin');
        }
    }
    
    /**
     * Processa o logout
     */
    public function logout()
    {
        // Limpa todas as variáveis de sessão
        $_SESSION = [];
        
        // Destrói a sessão
        session_destroy();
        
        // Remove o cookie de sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Redireciona para a página inicial
        $this->redirect('/');
    }
}
