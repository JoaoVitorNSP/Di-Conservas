<?php
// Dados de conexão, lidos do arquivo .env (veja .env.example).
return [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_NAME', 'di_conservas'),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASS', ''),
];
