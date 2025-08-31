<?php

// Teste de conexão com banco
require_once '/var/www/html/app/EnvLoader.php';
EnvLoader::load('/var/www/html/.env');

require_once '/var/www/html/app/Models/BaseModel.php';
require_once '/var/www/html/app/Models/User.php';

use App\Models\User;

try {
    echo "Testando conexão com banco...\n";
    
    $user = new User();
    echo "Modelo User criado com sucesso.\n";
    
    // Testa se consegue buscar usuários
    $users = $user->all();
    echo "Usuários encontrados: " . count($users) . "\n";
    
    if (!empty($users)) {
        echo "Primeiro usuário:\n";
        print_r($users[0]);
    }
    
    // Testa login
    echo "\nTestando login...\n";
    $loginUser = $user->findByEmail('admin@diconservas.com');
    if ($loginUser) {
        echo "Usuário encontrado por email.\n";
        
        // Testa verificação de senha
        $validPassword = $user->verifyPassword('admin123', $loginUser['password']);
        echo "Senha válida: " . ($validPassword ? 'SIM' : 'NÃO') . "\n";
    } else {
        echo "Usuário não encontrado por email.\n";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
