<?php
declare(strict_types=1);

namespace App\Models;

class Product extends BaseModel
{
    private const SELECT = "SELECT p.*, c.description AS category_name, u.description AS unit_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN measurement_units u ON p.unit_id = u.id";

    // Só o que aparece no site, na ordem de cadastro.
    public function visible(): array
    {
        return $this->pdo->query(self::SELECT . " WHERE p.status = 'active' ORDER BY p.id")->fetchAll();
    }

    // Tudo, para o painel: os mais recentes primeiro.
    public function all(): array
    {
        return $this->pdo->query(self::SELECT . ' ORDER BY p.id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE p.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO products
            (name, category_id, description, weight, unit_id, retail_price, wholesale_price, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['name'], $data['category_id'], $data['description'], $data['weight'], $data['unit_id'],
            $data['retail_price'], $data['wholesale_price'], $data['image'], $data['status'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE products SET
            name = ?, category_id = ?, description = ?, weight = ?, unit_id = ?,
            retail_price = ?, wholesale_price = ?, image = ?, status = ?
            WHERE id = ?');
        $stmt->execute([
            $data['name'], $data['category_id'], $data['description'], $data['weight'], $data['unit_id'],
            $data['retail_price'], $data['wholesale_price'], $data['image'], $data['status'], $id,
        ]);
    }

    public function setStatus(int $id, string $status): void
    {
        $this->pdo->prepare('UPDATE products SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    }

    public function imageInUse(string $image): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM products WHERE image = ?');
        $stmt->execute([$image]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function units(): array
    {
        return $this->pdo->query('SELECT id, description FROM measurement_units ORDER BY id')->fetchAll();
    }
}
