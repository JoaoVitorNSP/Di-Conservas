<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Modelo para usuários
 */
class User extends BaseModel
{
    protected $table = 'users';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Busca todos os usuários com suas informações de papel
     */
    public function all()
    {
        try {
            $sql = "SELECT 
                        u.*,
                        h.description as hole_name
                    FROM users u
                    LEFT JOIN holes h ON u.hole_id = h.id
                    WHERE u.status = 'active'
                    ORDER BY u.name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erro ao buscar usuários: " . $e->getMessage());
            return [];
        }
    }se App\Models\BaseModel;

/**
 * Modelo para usuários
 */
class User extends BaseModel
{
    protected $table = 'users';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Busca todos os usuários com suas informações de papel
     */
    public function all()
    {
        $sql = "SELECT 
                    u.*,
                    h.description as hole_name
                FROM users u
                LEFT JOIN holes h ON u.hole_id = h.id
                WHERE u.status = 'active'
                ORDER BY u.name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca um usuário por ID
     */
    public function find($id)
    {
        $sql = "SELECT 
                    u.*,
                    h.description as hole_name
                FROM users u
                LEFT JOIN holes h ON u.hole_id = h.id
                WHERE u.id = ? AND u.status = 'active'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca um usuário por email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT 
                    u.*,
                    h.description as hole_name
                FROM users u
                LEFT JOIN holes h ON u.hole_id = h.id
                WHERE u.email = ? AND u.status = 'active'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Cria um novo usuário
     */
    public function create($data)
    {
        // Gera UUID se não fornecido
        if (!isset($data['id']) || empty($data['id'])) {
            $data['id'] = $this->generateUuid();
        }
        
        // Hash da senha
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql = "INSERT INTO users (id, hole_id, name, email, password, phone, status) 
                VALUES (:id, :hole_id, :name, :email, :password, :phone, :status)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Atualiza um usuário
     */
    public function update($id, $data)
    {
        // Se tem nova senha, faz o hash
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Remove password do array se está vazio
            unset($data['password']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Remove um usuário (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Verifica a senha do usuário
     */
    public function verifyPassword($user, $password)
    {
        return password_verify($password, $user['password']);
    }
    
    /**
     * Busca papéis/holes disponíveis
     */
    public function getHoles()
    {
        $sql = "SELECT * FROM holes WHERE status = 'active' ORDER BY description";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Verifica se email já existe
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ? AND status != 'deleted'";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Conta usuários ativos
     */
    public function countActive()
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
}
