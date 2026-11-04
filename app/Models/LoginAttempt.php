<?php
declare(strict_types=1);

namespace App\Models;

// Limite de tentativas de login por IP. O IP é guardado apenas como hash.
class LoginAttempt extends BaseModel
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 900;

    private function key(): string
    {
        return hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido'));
    }

    // Segundos de bloqueio restantes (0 = pode tentar).
    public function lockRemaining(): int
    {
        $stmt = $this->pdo->prepare('SELECT attempts, last_attempt FROM login_attempts WHERE client_hash = ?');
        $stmt->execute([$this->key()]);
        $row = $stmt->fetch();
        if (!$row || (int) $row['attempts'] < self::MAX_ATTEMPTS) {
            return 0;
        }
        return max(0, self::LOCK_SECONDS - (time() - (int) $row['last_attempt']));
    }

    public function registerFailure(): void
    {
        $now = time();
        $expired = $now - self::LOCK_SECONDS;
        $this->pdo->prepare('DELETE FROM login_attempts WHERE last_attempt < ?')->execute([$expired]);
        $this->pdo->prepare('INSERT INTO login_attempts (client_hash, attempts, last_attempt) VALUES (?, 1, ?)
            ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = VALUES(last_attempt)')
            ->execute([$this->key(), $now]);
    }

    public function clear(): void
    {
        $this->pdo->prepare('DELETE FROM login_attempts WHERE client_hash = ?')->execute([$this->key()]);
    }
}
