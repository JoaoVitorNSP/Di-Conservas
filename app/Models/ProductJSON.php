<?php

namespace App\Models;

/**
 * Modelo para produtos - temporariamente usando products.json
 */
class Product extends BaseModel
{
    protected $table = 'products';
    private $jsonFile;
    
    public function __construct()
    {
        // Por enquanto, usando JSON até migrar para banco de dados
        $this->jsonFile = __DIR__ . '/../../products.json';
    }
    
    /**
     * Busca todos os produtos do arquivo JSON
     */
    public function all()
    {
        if (!file_exists($this->jsonFile)) {
            return [];
        }
        
        $jsonContent = file_get_contents($this->jsonFile);
        $products = json_decode($jsonContent, true);
        
        return $products ?: [];
    }
    
    /**
     * Busca um produto por ID
     */
    public function find($id)
    {
        $products = $this->all();
        
        foreach ($products as $product) {
            if ($product['id'] == $id) {
                return $product;
            }
        }
        
        return null;
    }
    
    /**
     * Busca produtos por categoria
     */
    public function getByCategory($category)
    {
        $products = $this->all();
        
        return array_filter($products, function($product) use ($category) {
            return $product['category'] === $category;
        });
    }
    
    /**
     * Busca produtos por nome (busca parcial)
     */
    public function searchByName($name)
    {
        $products = $this->all();
        
        return array_filter($products, function($product) use ($name) {
            return stripos($product['name'], $name) !== false;
        });
    }
    
    /**
     * Adiciona um novo produto
     */
    public function create($data)
    {
        $products = $this->all();
        
        // Gera novo ID
        $maxId = 0;
        foreach ($products as $product) {
            if ($product['id'] > $maxId) {
                $maxId = $product['id'];
            }
        }
        $data['id'] = $maxId + 1;
        
        $products[] = $data;
        
        return $this->saveToJson($products);
    }
    
    /**
     * Atualiza um produto
     */
    public function update($id, $data)
    {
        $products = $this->all();
        
        foreach ($products as $key => $product) {
            if ($product['id'] == $id) {
                $products[$key] = array_merge($product, $data);
                $products[$key]['id'] = $id; // Mantém o ID original
                return $this->saveToJson($products);
            }
        }
        
        return false;
    }
    
    /**
     * Remove um produto
     */
    public function delete($id)
    {
        $products = $this->all();
        
        foreach ($products as $key => $product) {
            if ($product['id'] == $id) {
                unset($products[$key]);
                $products = array_values($products); // Reindexar array
                return $this->saveToJson($products);
            }
        }
        
        return false;
    }
    
    /**
     * Obtém todas as categorias únicas
     */
    public function getCategories()
    {
        $products = $this->all();
        $categories = [];
        
        foreach ($products as $product) {
            if (!in_array($product['category'], $categories)) {
                $categories[] = $product['category'];
            }
        }
        
        return $categories;
    }
    
    /**
     * Salva os produtos no arquivo JSON
     */
    private function saveToJson($products)
    {
        $jsonContent = json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($this->jsonFile, $jsonContent) !== false;
    }
}
