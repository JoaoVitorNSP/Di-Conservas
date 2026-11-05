<?php $flash = flash_take(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Painel') ?> | Painel Di Conservas</title>
    <link rel="icon" type="image/png" href="/assets/logo.png">
    <!-- Sem defer: define o tema antes de desenhar a página, para não piscar. -->
    <script src="/assets/admin/theme.js?v=1"></script>
    <link rel="stylesheet" href="/assets/admin/admin.css?v=3">
</head>
<body>
<?php if (!empty($currentUser)): ?>
    <svg class="sprite" aria-hidden="true" focusable="false">
        <symbol id="i-edit" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 20h4L19.500 8.500a2.120 2.120 0 0 0-3-3L5 17v3zM14.500 7.500l3 3"/></symbol>
        <symbol id="i-eye" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.600-7 10-7 10 7 10 7-3.600 7-10 7-10-7-10-7zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></symbol>
        <symbol id="i-eye-off" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.600 5.100A10 10 0 0 1 12 5c6.400 0 10 7 10 7a17 17 0 0 1-3.200 4M6.500 6.600A17 17 0 0 0 2 12s3.600 7 10 7a10 10 0 0 0 5-1.400M9.900 9.900a3 3 0 0 0 4.200 4.200"/></symbol>
        <symbol id="i-trash" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v5M14 11v5"/></symbol>
        <symbol id="i-sun" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM12 2v2M12 20v2M4.900 4.900l1.400 1.400M17.700 17.700l1.400 1.400M2 12h2M20 12h2M4.900 19.100l1.400-1.400M17.700 6.300l1.400-1.400"/></symbol>
        <symbol id="i-moon" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 13.500A9 9 0 1 1 10.500 3a7 7 0 0 0 10.500 10.500z"/></symbol>
    </svg>
    <header class="top">
        <a class="top__brand" href="/admin/products">
            <img src="/assets/logo.png" alt="" width="36" height="36">
            <span>Painel</span>
        </a>
        <nav class="top__nav" aria-label="Painel">
            <a href="/admin/products">Produtos</a>
            <a href="/admin/categories">Categorias</a>
            <a href="/admin/users">Usuários</a>
            <a href="/admin/settings">Configurações</a>
            <a href="/" target="_blank" rel="noopener">Ver site</a>
        </nav>
        <div class="top__tools">
            <button type="button" class="theme-btn" id="themeToggle">
                <svg class="icon theme-btn__sun" aria-hidden="true"><use href="#i-sun"/></svg>
                <svg class="icon theme-btn__moon" aria-hidden="true"><use href="#i-moon"/></svg>
                <span class="sr-only" id="themeLabel">Trocar tema</span>
            </button>
            <form method="post" action="/admin/logout">
                <?= csrf_field() ?>
                <button type="submit" class="link-btn">Sair</button>
            </form>
        </div>
    </header>
<?php endif; ?>
    <main class="page<?= !empty($wide) ? ' page--wide' : '' ?>">
<?php if ($flash): ?>
        <p class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
<?php endif; ?>
