<?php
// admin-dashboard.php - Dashboard administrativo protegido

require_once 'auth.php';
checkAdminAuth();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Área Administrativa - Di Conservas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: Arial, sans-serif; background: #f7f7f7; }
    .container { max-width: 600px; margin: 80px auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
    h2 { text-align: center; margin-bottom: 24px; }
    .welcome { text-align: center; font-size: 18px; margin-bottom: 24px; }
    .admin-info { text-align: center; margin-bottom: 24px; color: #666; }
    .actions { display: flex; flex-direction: column; gap: 16px; margin: 24px 0; }
    .btn { display: block; padding: 12px 20px; background: #2d7a2d; color: #fff; text-decoration: none; border-radius: 4px; text-align: center; }
    .btn:hover { background: #256325; }
    .btn-danger { background: #c00; }
    .btn-danger:hover { background: #a00; }
    .session-info { background: #e8f5e8; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Área Administrativa</h2>
    <div class="welcome">Bem-vindo, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</div>
    
    <div class="session-info">
      <strong>Sessão ativa:</strong> Expira em 10 minutos de inatividade<br>
      <strong>Última atividade:</strong> <?php echo date('H:i:s', $_SESSION['admin_last_activity']); ?>
    </div>
    
    <div class="actions">
      <a href="cadastro.php" class="btn">📝 Cadastrar Produto</a>
      <a href="produtos.php" class="btn">📋 Listar Produtos</a>
      <a href="logout.php" class="btn btn-danger">🚪 Sair</a>
    </div>
  </div>
  
  <script>
    // Auto-refresh para manter sessão ativa a cada 5 minutos
    setInterval(function() {
      fetch('session-refresh.php')
        .then(response => response.text())
        .then(data => {
          if (data.includes('expired')) {
            alert('Sessão expirada! Redirecionando para login...');
            window.location.href = 'admin.php';
          }
        });
    }, 300000); // 5 minutos
  </script>
</body>
</html>
