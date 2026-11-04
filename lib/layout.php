<?php
// Cabeçalho e rodapé das páginas do painel.
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function admin_page_start(string $title, bool $withNav = true): void
{
    $flash = flash_take();
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> | Painel Di Conservas</title>
    <link rel="icon" type="image/png" href="/assets/logo.png">
    <link rel="stylesheet" href="/admin/admin.css?v=1">
</head>
<body>
<?php if ($withNav): ?>
    <header class="top">
        <a class="top__brand" href="/admin/">
            <img src="/assets/logo.png" alt="" width="36" height="36">
            <span>Painel</span>
        </a>
        <nav class="top__nav" aria-label="Painel">
            <a href="/admin/">Produtos</a>
            <a href="/admin/senha.php">Senha</a>
            <a href="/" target="_blank" rel="noopener">Ver site</a>
            <form method="post" action="/admin/logout.php">
                <?= csrf_field() ?>
                <button type="submit" class="link-btn">Sair</button>
            </form>
        </nav>
    </header>
<?php endif; ?>
    <main class="page">
<?php if ($flash): ?>
        <p class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
<?php endif; ?>
    <?php
}

function admin_page_end(): void
{
    ?>
    </main>
    <script src="/admin/admin.js?v=1" defer></script>
</body>
</html>
    <?php
}
