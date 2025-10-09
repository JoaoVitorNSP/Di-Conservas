<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\User;

/**
 * Controller para área administrativa
 */
class AdminController extends BaseController
{
    private $productModel;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->userModel = new User();
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
     * Atualiza sessão (via AJAX)
     */
    public function refreshSession()
    {
        $this->startSession();
        
        if ($this->isLoggedIn()) {
            $_SESSION['last_activity'] = time();
            $this->json(['status' => 'success']);
        } else {
            $this->json(['status' => 'expired'], 401);
        }
    }
}

