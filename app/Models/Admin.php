<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Modelo para administradores
 */
class Admin extends BaseModel
{
    protected $table = 'admins';
    
    // Por enquanto, credenciais fixas até implementar banco de dados
    private static $admins = [
        [
            'id' => 1,
            'username' => 'admin',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'email' => 'admin@diconservas.com'
        ]
    ];
    
    /**
     * Valida as credenciais do administrador
     */
    public function validateCredentials($username, $password)
    {
        foreach (self::$admins as $admin) {
            if ($admin['username'] === $username) {
                if (password_verify($password, $admin['password'])) {
                    return $admin;
                }
                break;
            }
        }
        
        return false;
    }
    
    /**
     * Busca administrador por username
     */
    public function findByUsername($username)
    {
        foreach (self::$admins as $admin) {
            if ($admin['username'] === $username) {
                return $admin;
            }
        }
        
        return null;
    }
    
    /**
     * Busca administrador por ID
     */
    public function find($id)
    {
        foreach (self::$admins as $admin) {
            if ($admin['id'] == $id) {
                return $admin;
            }
        }
        
        return null;
    }
    
    /**
     * Gera hash de senha
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verifica se a senha está correta
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
