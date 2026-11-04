<?php
declare(strict_types=1);

namespace App\Models;

class Category extends BaseModel
{
    public function allWithCounts(): array
    {
        return $this->pdo->query('SELECT c.id, c.description, COUNT(p.id) AS total
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id, c.description
            ORDER BY c.description')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT c.id, c.description, COUNT(p.id) AS total
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            WHERE c.id = ?
            GROUP BY c.id, c.description');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // A comparação do banco ignora maiúsculas e acentos: "conservas" encontra "Conservas".
    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, description FROM categories WHERE description = ?');
        $stmt->execute([$name]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name): int
    {
        $this->pdo->prepare('INSERT INTO categories (description) VALUES (?)')->execute([$name]);
        return (int) $this->pdo->lastInsertId();
    }

    public function firstOrCreate(string $name): int
    {
        return (int) ($this->findByName($name)['id'] ?? $this->create($name));
    }

    public function rename(int $id, string $name): void
    {
        $this->pdo->prepare('UPDATE categories SET description = ? WHERE id = ?')->execute([$name, $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    }
}
