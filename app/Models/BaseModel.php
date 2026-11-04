<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

abstract class BaseModel
{
    private static ?PDO $connection = null;
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = self::connection();
    }

    // Uma única conexão por requisição, aberta só quando alguém precisa do banco.
    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = require ROOT_PATH . '/config/database.php';
            self::$connection = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }
        return self::$connection;
    }
}
