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
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erro ao buscar usuários: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca usuário por email
     */
    public function findByEmail($email)
    {
        try {
            $sql = "SELECT 
                        u.*,
                        h.description as hole_name
                    FROM users u
                    LEFT JOIN holes h ON u.hole_id = h.id
                    WHERE u.email = ? AND u.status = 'active'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erro ao buscar usuário por email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se a senha está correta
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Cria um novo usuário
     */
    public function create($data)
    {
        try {
            // Hash da senha
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $sql = "INSERT INTO users (id, name, email, password, phone, hole_id, status, created_at, updated_at)
                    VALUES (UUID(), ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['name'],
                $data['email'],
                $data['password'],
                $data['phone'] ?? null,
                $data['hole_id'],
                $data['status'] ?? 'active'
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza um usuário
     */
    public function update($id, $data)
    {
        try {
            $fields = [];
            $values = [];
            
            if (isset($data['name'])) {
                $fields[] = "name = ?";
                $values[] = $data['name'];
            }
            
            if (isset($data['email'])) {
                $fields[] = "email = ?";
                $values[] = $data['email'];
            }
            
            if (isset($data['password']) && !empty($data['password'])) {
                $fields[] = "password = ?";
                $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            if (isset($data['phone'])) {
                $fields[] = "phone = ?";
                $values[] = $data['phone'];
            }
            
            if (isset($data['hole_id'])) {
                $fields[] = "hole_id = ?";
                $values[] = $data['hole_id'];
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $fields[] = "updated_at = NOW()";
            $values[] = $id;
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (\Exception $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove um usuário (soft delete)
     */
    public function delete($id)
    {
        try {
            $sql = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            error_log("Erro ao remover usuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se email já existe
     */
    public function emailExists($email, $excludeId = null)
    {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = ? AND status = 'active'";
            $params = [$email];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            error_log("Erro ao verificar email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca papéis/roles disponíveis
     */
    public function getHoles()
    {
        try {
            $sql = "SELECT * FROM holes WHERE status = 'active' ORDER BY id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erro ao buscar papéis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Conta usuários ativos
     */
    public function countActive()
    {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE status = 'active'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("Erro ao contar usuários: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Atualiza último login
     */
    public function updateLastLogin($id)
    {
        try {
            $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            error_log("Erro ao atualizar último login: " . $e->getMessage());
            return false;
        }
    }
}
