<?php

namespace App\Controllers;

// Carregamento manual das dependências para garantir funcionamento
if (!class_exists('App\\Controllers\\BaseController')) {
    require_once __DIR__ . '/BaseController.php';
}

if (!class_exists('App\\Models\\BaseModel')) {
    require_once __DIR__ . '/../Models/BaseModel.php';
}

if (!class_exists('App\\Models\\Product')) {
    require_once __DIR__ . '/../Models/Product.php';
}

use App\Models\Product;

/**
 * Controller para gerenciar produtos
 */
class ProductController extends BaseController
{
    private $productModel;
    
    public function __construct()
    {
        $this->productModel = new Product();
    }
    
    /**
     * Exibe a página principal com todos os produtos
     */
    public function index()
    {
        $products = $this->productModel->all();
        $categories = $this->productModel->getCategories();
        
        $this->view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'title' => 'Di Conservas - Catálogo'
        ]);
    }
    
    /**
     * API para listar produtos (JSON)
     */
    public function api()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
        
        $products = $this->productModel->all();
        $this->json($products);
    }
    
    /**
     * Busca produtos por categoria
     */
    public function getByCategory($category)
    {
        $products = $this->productModel->getByCategory($category);
        $this->json($products);
    }
    
    /**
     * Busca produtos por nome
     */
    public function search()
    {
        $query = $this->getParam('q', '');
        
        if (empty($query)) {
            $products = $this->productModel->all();
        } else {
            $products = $this->productModel->searchByName($query);
        }
        
        $this->json($products);
    }
    
    /**
     * Exibe detalhes de um produto
     */
    public function show($id)
    {
        $product = $this->productModel->find($id);
        
        if (!$product) {
            http_response_code(404);
            $this->view('errors.404', ['title' => 'Produto não encontrado']);
            return;
        }
        
        $this->view('products.show', [
            'product' => $product,
            'title' => $product['name'] . ' - Di Conservas'
        ]);
    }
    
    /**
     * Formulário para criar novo produto
     */
    public function create()
    {
        $categories = $this->productModel->getCategories();
        
        $this->view('admin.products.create', [
            'categories' => $categories,
            'title' => 'Adicionar Produto'
        ]);
    }
    
    /**
     * Salva um novo produto
     */
    public function store()
    {
        $data = $this->getPostData();
        
        // Validação
        $errors = $this->validate($data, [
            'name' => 'required|max:255',
            'category' => 'required|max:100',
            'description' => 'required',
            'weight' => 'required|max:50',
            'retailPrice' => 'required',
            'wholesalePrice' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->setFlash('error', 'Erro de validação');
            $this->view('admin.products.create', [
                'errors' => $errors,
                'data' => $data,
                'categories' => $this->productModel->getCategories(),
                'title' => 'Adicionar Produto'
            ]);
            return;
        }
        
        // Processa upload da imagem se houver
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->handleImageUpload($_FILES['image']);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }
        
        // Converte preços para float
        $data['retailPrice'] = floatval($data['retailPrice']);
        $data['wholesalePrice'] = floatval($data['wholesalePrice']);
        
        if ($this->productModel->create($data)) {
            $this->setFlash('success', 'Produto adicionado com sucesso!');
            $this->redirect('/admin/products');
        } else {
            $this->setFlash('error', 'Erro ao adicionar produto');
            $this->redirect('/admin/products/create');
        }
    }
    
    /**
     * Formulário para editar produto
     */
    public function edit($id)
    {
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->setFlash('error', 'Produto não encontrado');
            $this->redirect('/admin/products');
            return;
        }
        
        $categories = $this->productModel->getCategories();
        
        $this->view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'title' => 'Editar Produto'
        ]);
    }
    
    /**
     * Atualiza um produto
     */
    public function update($id)
    {
        $data = $this->getPostData();
        
        // Validação
        $errors = $this->validate($data, [
            'name' => 'required|max:255',
            'category' => 'required|max:100',
            'description' => 'required',
            'weight' => 'required|max:50',
            'retailPrice' => 'required',
            'wholesalePrice' => 'required'
        ]);
        
        if (!empty($errors)) {
            $product = $this->productModel->find($id);
            $this->setFlash('error', 'Erro de validação');
            $this->view('admin.products.edit', [
                'errors' => $errors,
                'product' => array_merge($product, $data),
                'categories' => $this->productModel->getCategories(),
                'title' => 'Editar Produto'
            ]);
            return;
        }
        
        // Processa upload da imagem se houver
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->handleImageUpload($_FILES['image']);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }
        
        // Converte preços para float
        $data['retailPrice'] = floatval($data['retailPrice']);
        $data['wholesalePrice'] = floatval($data['wholesalePrice']);
        
        if ($this->productModel->update($id, $data)) {
            $this->setFlash('success', 'Produto atualizado com sucesso!');
            $this->redirect('/admin/products');
        } else {
            $this->setFlash('error', 'Erro ao atualizar produto');
            $this->redirect("/admin/products/$id/edit");
        }
    }
    
    /**
     * Remove um produto
     */
    public function destroy($id)
    {
        if ($this->productModel->delete($id)) {
            $this->setFlash('success', 'Produto removido com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao remover produto');
        }
        
        $this->redirect('/admin/products');
    }
    
    /**
     * Faz upload da imagem do produto
     */
    private function handleImageUpload($file)
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $this->setFlash('error', 'Tipo de arquivo não permitido');
            return false;
        }
        
        if ($file['size'] > $maxSize) {
            $this->setFlash('error', 'Arquivo muito grande (máximo 5MB)');
            return false;
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../../public/assets/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return '/assets/' . $filename;
        }
        
        $this->setFlash('error', 'Erro ao fazer upload da imagem');
        return false;
    }
}
