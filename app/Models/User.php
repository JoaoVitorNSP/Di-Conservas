<?php
declare(strict_types=1);

namespace App\Models;

class User extends BaseModel
{
    public const MIN_PASSWORD_LENGTH = 10;

    public function all(): array
    {
        return $this->pdo->query('SELECT id, name, email, last_login FROM users ORDER BY name')->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, string $email, ?string $password): void
    {
        $this->pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')->execute([$name, $email, $id]);
        if ($password !== null) {
            $this->pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
        }
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    }

    public function touchLastLogin(int $id): void
    {
        $this->pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$id]);
    }

    public static function passwordProblem(string $password): ?string
    {
        return mb_strlen($password) < self::MIN_PASSWORD_LENGTH
            ? 'A senha precisa ter pelo menos ' . self::MIN_PASSWORD_LENGTH . ' caracteres.'
            : null;
    }
}
