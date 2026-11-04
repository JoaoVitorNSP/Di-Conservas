<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Erro temporário | Di Conservas</title>
    <link rel="icon" type="image/png" href="/assets/logo.png">
    <link rel="stylesheet" href="/assets/admin/admin.css?v=2">
</head>
<body>
    <main class="page login">
        <img src="/assets/logo.png" alt="" width="64" height="64">
        <h1>Tivemos um problema</h1>
        <p class="muted">Tente de novo em instantes. Se continuar, fale com a gente pelo WhatsApp: (41) 99612-2750.</p>
        <?php if (!empty($message)): ?>
            <pre><?= e($message) ?></pre>
        <?php endif; ?>
        <a class="btn" href="/">Voltar ao início</a>
    </main>
</body>
</html>
