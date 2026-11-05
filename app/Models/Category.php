<?php
declare(strict_types=1);

namespace App\Models;

class Category extends BaseModel
{
    public function allWithCounts(): array
    {
        return $this->pdo->query("SELECT c.id, c.description, c.status, COUNT(p.id) AS total,
                COALESCE(SUM(p.status = 'active'), 0) AS visible
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id, c.description, c.status
            ORDER BY c.description")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT c.id, c.description, c.status, COUNT(p.id) AS total
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            WHERE c.id = ?
            GROUP BY c.id, c.description, c.status');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Produtos ainda visíveis nas categorias indicadas: são eles que impedem ocultá-las.
    public function visibleProducts(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT c.id AS category_id, c.description AS category_name, p.id, p.name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.status = 'active' AND c.id IN ({$marks})
            ORDER BY c.description, p.name");
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    public function setStatus(array $ids, string $status): void
    {
        if (!$ids) {
            return;
        }
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $this->pdo->prepare("UPDATE categories SET status = ? WHERE id IN ({$marks})")->execute([$status, ...$ids]);
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
