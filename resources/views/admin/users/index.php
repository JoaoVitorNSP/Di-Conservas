<?php
// Inicia a sessão para acessar os dados do usuário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser = $_SESSION['user'] ?? null;
$userName = $currentUser ? $currentUser['name'] : 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Gerenciar Usuários' ?> - Di Conservas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 0.375rem;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .table-card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .status-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <h4><i class="bi bi-shield-check"></i> Admin</h4>
                        <small>Olá, <?= htmlspecialchars($userName) ?>!</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/products">
                                <i class="bi bi-box-seam"></i> Produtos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/users">
                                <i class="bi bi-people"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-light" href="/admin/logout">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-people"></i> Gerenciar Usuários
                    </h1>
                </div>

                <!-- Alertas -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Cabeçalho com estatísticas e botão de novo usuário -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card table-card">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col">
                                        <h3 class="text-primary mb-0"><?= $totalUsers ?? count($users ?? []) ?></h3>
                                        <small class="text-muted">Usuários Ativos</small>
                                    </div>
                                    <div class="col">
                                        <h3 class="text-success mb-0"><?= count(array_filter($users ?? [], fn($u) => $u['hole_id'] == 1)) ?></h3>
                                        <small class="text-muted">Administradores</small>
                                    </div>
                                    <div class="col">
                                        <h3 class="text-info mb-0"><?= count(array_filter($users ?? [], fn($u) => $u['hole_id'] == 2)) ?></h3>
                                        <small class="text-muted">Gerentes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/admin/users/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Novo Usuário
                        </a>
                    </div>
                </div>

                <!-- Tabela de usuários -->
                <div class="card table-card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-table"></i> Lista de Usuários
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($users)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Telefone</th>
                                            <th>Papel</th>
                                            <th>Status</th>
                                            <!-- <th>Último Login</th> -->
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($user['name']) ?></strong>
                                                </td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                                                <td>
                                                    <?php
                                                    $badgeClass = match($user['hole_id']) {
                                                        1 => 'bg-danger',
                                                        2 => 'bg-warning text-dark',
                                                        3 => 'bg-info',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?> status-badge">
                                                        <?= htmlspecialchars($user['hole_name']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($user['status'] === 'active'): ?>
                                                        <span class="badge bg-success status-badge">Ativo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary status-badge">Inativo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <!-- <td>
                                                    <?php if ($user['last_login']): ?>
                                                        <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Nunca</span>
                                                    <?php endif; ?>
                                                </td> -->
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="/admin/users/<?= $user['id'] ?>/edit" 
                                                           class="btn btn-outline-primary btn-action" 
                                                           title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        
                                                        <?php if ($user['id'] !== $currentUser['id']): ?>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger btn-action" 
                                                                    title="Excluir"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#deleteModal"
                                                                    data-user-id="<?= $user['id'] ?>"
                                                                    data-user-name="<?= htmlspecialchars($user['name']) ?>">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-people display-1 text-muted"></i>
                                <h4 class="text-muted mt-3">Nenhum usuário encontrado</h4>
                                <p class="text-muted">Clique em "Novo Usuário" para começar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário <strong id="userName"></strong>?</p>
                    <p class="text-danger small">
                        <i class="bi bi-info-circle"></i>
                        Esta ação não pode ser desfeita.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configurar modal de exclusão
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteForm');
            const userNameSpan = document.getElementById('userName');
            
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const userName = button.getAttribute('data-user-name');
                
                userNameSpan.textContent = userName;
                deleteForm.action = `/admin/users/${userId}/delete`;
            });
        });
    </script>
</body>
</html>
