<?php
// Funções usadas em controllers e telas.
declare(strict_types=1);

function env(string $name, ?string $default = null): ?string
{
    if (array_key_exists($name, $_ENV)) {
        return (string) $_ENV[$name];
    }
    $value = getenv($name);
    return $value === false ? $default : $value;
}

function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function flash(string $message, string $type = 'ok'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function flash_take(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function clean_line(mixed $value): string
{
    return is_string($value) ? trim((string) preg_replace('/\s+/u', ' ', $value)) : '';
}

// Aceita "25,50", "25.50" e "1.234,50". Retorna null se não for um número válido.
function parse_decimal(string $value, int $decimals = 2): ?float
{
    $value = trim($value);
    if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $value)) {
        $value = str_replace('.', '', $value);
    }
    $value = str_replace(',', '.', $value);
    if (!preg_match('/^\d{1,7}(\.\d{1,' . $decimals . '})?$/', $value)) {
        return null;
    }
    return round((float) $value, $decimals);
}

function format_price(float|int|string|null $value): string
{
    return (float) $value > 0 ? 'R$ ' . number_format((float) $value, 2, ',', '.') : '';
}

// 300 + g => "300g"; 1.8 + kg => "1,8 kg"
function format_weight(float|int|string|null $weight, ?string $unit): string
{
    if ($weight === null || (float) $weight <= 0) {
        return '';
    }
    $number = rtrim(rtrim(number_format((float) $weight, 3, ',', ''), '0'), ',');
    return in_array($unit, ['g', 'ml'], true) ? $number . $unit : trim($number . ' ' . $unit);
}
