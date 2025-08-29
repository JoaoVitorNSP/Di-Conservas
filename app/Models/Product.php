<?php

namespace App\Models;

/**
 * Modelo para produtos - usando banco de dados MySQL
 */
class Product extends BaseModel
{
    protected $table = 'products';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Busca todos os produtos ativos com suas categorias e unidades
     */
    public function all()
    {
        $sql = "SELECT 
                    p.*,
                    c.description as category_name,
                    u.description as unit_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN measurement_units u ON p.unit_id = u.id
                WHERE p.status = 'active'
                ORDER BY p.name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca um produto por ID
     */
    public function find($id)
    {
        $sql = "SELECT 
                    p.*,
                    c.description as category_name,
                    u.description as unit_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN measurement_units u ON p.unit_id = u.id
                WHERE p.id = ? AND p.status = 'active'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca um produto por UUID
     */
    public function findByUuid($uuid)
    {
        $sql = "SELECT 
                    p.*,
                    c.description as category_name,
                    u.description as unit_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN measurement_units u ON p.unit_id = u.id
                WHERE p.uuid = ? AND p.status = 'active'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$uuid]);
        return $stmt->fetch();
    }
    
    /**
     * Cria um novo produto
     */
    public function create($data)
    {
        // Gera UUID se não fornecido
        if (empty($data['uuid'])) {
            $data['uuid'] = $this->generateUuid();
        }
        
        $sql = "INSERT INTO products (uuid, name, category_id, description, weight, unit_id, retail_price, wholesale_price, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['uuid'],
            $data['name'],
            $data['category_id'],
            $data['description'] ?? null,
            $data['weight'] ?? null,
            $data['unit_id'] ?? null,
            $data['retail_price'] ?? 0.00,
            $data['wholesale_price'] ?? 0.00,
            $data['image'] ?? null
        ]);
        
        if ($result) {
            return $this->pdo->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Atualiza um produto
     */
    public function update($id, $data)
    {
        $sql = "UPDATE products SET 
                    name = ?, 
                    category_id = ?, 
                    description = ?, 
                    weight = ?, 
                    unit_id = ?, 
                    retail_price = ?, 
                    wholesale_price = ?, 
                    image = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND status = 'active'";
        $product = $this->find($id);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['category_id'],
            $data['description'] ?? $product['description'],
            $data['weight'] ?? $product['weight'],
            $data['unit_id'] ?? $product['unit_id'],
            $data['retail_price'] ?? $product['retail_price'],
            $data['wholesale_price'] ?? $product['wholesale_price'],
            $data['image'] ?? $product['image'],
            $id
        ]);
    }
    
    /**
     * Remove um produto (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE products SET status = 'deleted', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Busca produtos por categoria
     */
    public function getByCategory($categoryId)
    {
        $sql = "SELECT 
                    p.*,
                    c.description as category_name,
                    u.description as unit_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN measurement_units u ON p.unit_id = u.id
                WHERE p.category_id = ? AND p.status = 'active'
                ORDER BY p.name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca produtos por termo de pesquisa
     */
    public function search($term)
    {
        $sql = "SELECT 
                    p.*,
                    c.description as category_name,
                    u.description as unit_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN measurement_units u ON p.unit_id = u.id
                WHERE (p.name LIKE ? OR p.description LIKE ? OR c.description LIKE ?) 
                AND p.status = 'active'
                ORDER BY p.name";
        
        $searchTerm = "%{$term}%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém todas as categorias ativas
     */
    public function getCategories()
    {
        $sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY description";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém todas as unidades de medida ativas
     */
    public function getMeasurementUnits()
    {
        $sql = "SELECT * FROM measurement_units WHERE status = 'active' ORDER BY description";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Gera um UUID simples
     */
    protected function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
