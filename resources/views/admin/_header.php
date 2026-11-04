<?php $flash = flash_take(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Painel') ?> | Painel Di Conservas</title>
    <link rel="icon" type="image/png" href="/assets/logo.png">
    <link rel="stylesheet" href="/assets/admin/admin.css?v=2">
</head>
<body>
<?php if (!empty($currentUser)): ?>
    <header class="top">
        <a class="top__brand" href="/admin/products">
            <img src="/assets/logo.png" alt="" width="36" height="36">
            <span>Painel</span>
        </a>
        <nav class="top__nav" aria-label="Painel">
            <a href="/admin/products">Produtos</a>
            <a href="/admin/categories">Categorias</a>
            <a href="/admin/users">Usuários</a>
            <a href="/" target="_blank" rel="noopener">Ver site</a>
            <form method="post" action="/admin/logout">
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
