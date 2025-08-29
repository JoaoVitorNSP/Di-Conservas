<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Models\Product;

/**
 * Controller para área administrativa
 */
class AdminController extends BaseController
{
    private $adminModel;
    private $productModel;
    
    public function __construct()
    {
        $this->adminModel = new Admin();
        $this->productModel = new Product();
    }
    
    /**
     * Exibe página de login
     */
    public function login()
    {
        $this->startSession();
        
        // Se já estiver logado, redireciona para dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/admin/dashboard');
            return;
        }
        
        $error = $this->getParam('error');
        $errorMessage = '';
        
        switch ($error) {
            case 'invalid':
                $errorMessage = 'Credenciais inválidas';
                break;
            case 'session':
                $errorMessage = 'Acesso negado. Faça login primeiro.';
                break;
            case 'expired':
                $errorMessage = 'Sessão expirada. Faça login novamente.';
                break;
        }
        
        $this->view('admin.login', [
            'title' => 'Admin Login - Di Conservas',
            'error' => $errorMessage
        ]);
    }
    
    /**
     * Processa login
     */
    public function authenticate()
    {
        $data = $this->getPostData();
        
        $errors = $this->validate($data, [
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->view('admin.login', [
                'title' => 'Admin Login - Di Conservas',
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }
        
        $admin = $this->adminModel->validateCredentials($data['username'], $data['password']);
        
        if ($admin) {
            $this->startSession();
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_last_activity'] = time();
            
            $this->redirect('/admin/dashboard');
        } else {
            $this->view('admin.login', [
                'title' => 'Admin Login - Di Conservas',
                'error' => 'Credenciais inválidas',
                'data' => $data
            ]);
        }
    }
    
    /**
     * Dashboard administrativo
     */
    public function dashboard()
    {
        $this->requireAuth();
        
        $totalProducts = count($this->productModel->all());
        $categories = $this->productModel->getCategories();
        $totalCategories = count($categories);
        
        $this->view('admin.dashboard', [
            'title' => 'Área Administrativa - Di Conservas',
            'totalProducts' => $totalProducts,
            'totalCategories' => $totalCategories,
            'categories' => $categories
        ]);
    }
    
    /**
     * Lista produtos no admin
     */
    public function products()
    {
        $this->requireAuth();
        
        $products = $this->productModel->all();
        
        $this->view('admin.products.index', [
            'title' => 'Gerenciar Produtos',
            'products' => $products
        ]);
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        $this->startSession();
        session_destroy();
        $this->redirect('/');
    }
    
    /**
     * Atualiza sessão (via AJAX)
     */
    public function refreshSession()
    {
        $this->startSession();
        
        if ($this->isAuthenticated()) {
            $_SESSION['admin_last_activity'] = time();
            $this->json(['status' => 'success']);
        } else {
            $this->json(['status' => 'expired'], 401);
        }
    }
    
    /**
     * Verifica se o usuário está autenticado
     */
    private function isAuthenticated()
    {
        $this->startSession();
        
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            return false;
        }
        
        // Verifica se a sessão expirou (10 minutos = 600 segundos)
        if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > 600)) {
            session_destroy();
            return false;
        }
        
        // Atualiza o tempo de última atividade
        $_SESSION['admin_last_activity'] = time();
        return true;
    }
    
    /**
     * Middleware para páginas que requerem autenticação
     */
    private function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/admin?error=session');
        }
    }
}
