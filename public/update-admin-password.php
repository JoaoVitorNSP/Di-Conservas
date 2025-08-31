<?php

// Script para atualizar senha do admin
require_once '/var/www/html/app/EnvLoader.php';
EnvLoader::load('/var/www/html/.env');

require_once '/var/www/html/app/Models/BaseModel.php';
require_once '/var/www/html/app/Models/User.php';

use App\Models\User;

try {
    $userModel = new User();
    
    // Gera nova senha
    $newPassword = 'admin123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    echo "Nova senha hash: $hashedPassword\n";
    
    // Atualiza no banco
    $result = $userModel->update('28898a6c-866f-11f0-a7ac-7a19700dea65', [
        'password' => $newPassword  // O método update já faz o hash
    ]);
    
    if ($result) {
        echo "Senha atualizada com sucesso!\n";
        
        // Testa login
        $user = $userModel->findByEmail('admin@diconservas.com');
        if ($user) {
            $validPassword = $userModel->verifyPassword($newPassword, $user['password']);
            echo "Teste de login - Senha válida: " . ($validPassword ? 'SIM' : 'NÃO') . "\n";
        }
    } else {
        echo "Erro ao atualizar senha\n";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
