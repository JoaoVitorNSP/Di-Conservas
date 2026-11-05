<?php
declare(strict_types=1);

namespace App\Models;

class Setting extends BaseModel
{
    private const CART = 'cart_enabled';

    // Sem nada salvo, o carrinho fica ligado.
    public function cartEnabled(): bool
    {
        $stmt = $this->pdo->prepare('SELECT value FROM settings WHERE name = ?');
        $stmt->execute([self::CART]);
        return $stmt->fetchColumn() !== '0';
    }

    public function setCartEnabled(bool $enabled): void
    {
        $value = $enabled ? '1' : '0';
        $this->pdo->prepare('INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?')
            ->execute([self::CART, $value, $value]);
    }
}
