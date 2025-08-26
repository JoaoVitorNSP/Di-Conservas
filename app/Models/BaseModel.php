<?php

namespace App\Models;

/**
 * Classe base para acesso ao banco de dados
 */
class BaseModel
{
    protected $connection;
    protected $table;
    
    public function __construct()
    {
        $this->connection = $this->getConnection();
    }
    
    /**
     * Estabelece conexão com o banco de dados
     */
    private function getConnection()
    {
        $config = require_once __DIR__ . '/../../config/database.php';
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
            $pdo = new \PDO($dsn, $config['username'], $config['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (\PDOException $e) {
            throw new \Exception("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    /**
     * Busca todos os registros da tabela
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca um registro por ID
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Insere um novo registro
     */
    public function create($data)
    {
        $fields = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->connection->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Atualiza um registro por ID
     */
    public function update($id, $data)
    {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "$key = :$key";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$this->table} SET $setClause WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Remove um registro por ID
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    /**
     * Executa uma query personalizada
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
