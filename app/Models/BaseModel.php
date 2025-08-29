<?php

namespace App\Models;

/**
 * Classe base para todos os modelos
 */
abstract class BaseModel
{
    protected $pdo;
    protected $table;

    public function __construct()
    {
        $this->pdo = $this->getConnection();
    }

    /**
     * Obtém a conexão com o banco de dados
     */
    private function getConnection()
    {
        $config = require_once __DIR__ . '/../../config/database.php';
        
        // Verifica se a configuração foi carregada corretamente
        if (!is_array($config)) {
            throw new \Exception("Erro ao carregar configuração do banco de dados");
        }
        
        // Valida os parâmetros obrigatórios
        $required = ['host', 'database', 'username', 'password'];
        foreach ($required as $key) {
            if (!isset($config[$key])) {
                throw new \Exception("Parâmetro de configuração obrigatório não encontrado: {$key}");
            }
        }
        
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
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Busca um registro por ID
     */
    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Cria um novo registro
     */
    public function create($data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute($data);
    }

    /**
     * Atualiza um registro
     */
    public function update($id, $data)
    {
        $setClause = '';
        foreach (array_keys($data) as $key) {
            $setClause .= "{$key} = :{$key}, ";
        }
        $setClause = rtrim($setClause, ', ');
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Remove um registro
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Executa uma query customizada
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Gera um UUID
     */
    protected function generateUuid()
    {
        $stmt = $this->pdo->query("SELECT UUID() as uuid");
        $result = $stmt->fetch();
        return $result['uuid'];
    }

    /**
     * Inicia uma transação
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirma uma transação
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Desfaz uma transação
     */
    public function rollback()
    {
        return $this->pdo->rollback();
    }

    /**
     * Retorna o último ID inserido
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Busca registros com condições
     */
    public function where($column, $operator, $value)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$column} {$operator} ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    /**
     * Conta registros na tabela
     */
    public function count()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM {$this->table}");
        $result = $stmt->fetch();
        return $result['count'];
    }
}
