<?php

/**
 * Configurações do banco de dados
 */
return [
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? 'di_conservas',
    'username' => $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
