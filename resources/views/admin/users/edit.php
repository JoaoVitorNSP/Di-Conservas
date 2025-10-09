<?php
// Inicia a sessão para acessar os dados do usuário e mensagens
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
    <title><?= $title ?? 'Editar Usuário' ?> - Di Conservas Admin</title>
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
        .form-card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }
        .required::after {
            content: " *";
            color: #dc3545;
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
                <div class="pt-3 pb-2 mb-3 border-bottom d-flex justify-content-between align-items-center">
                    <h1 class="h2">
                        <i class="bi bi-person-gear"></i> Editar Usuário
                    </h1>
                    <a href="/admin/users" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
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

                <!-- Formulário -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card form-card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-person-gear"></i> Dados do Usuário
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="/admin/users/<?= $user['id'] ?>/update" novalidate>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label required">Nome Completo</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="name" 
                                                   name="name" 
                                                   value="<?= htmlspecialchars($user['name']) ?>"
                                                   required>
                                            <div class="invalid-feedback">
                                                Por favor, insira o nome completo.
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label required">Email</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?= htmlspecialchars($user['email']) ?>"
                                                   required>
                                            <div class="invalid-feedback">
                                                Por favor, insira um email válido.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">Nova Senha</label>
                                            <div class="input-group">
                                                <input type="password" 
                                                       class="form-control" 
                                                       id="password" 
                                                       name="password" 
                                                       minlength="6"
                                                       placeholder="Deixe em branco para manter a atual">
                                                <button class="btn btn-outline-secondary" 
                                                        type="button" 
                                                        onclick="togglePassword('password')">
                                                    <i class="bi bi-eye" id="password-icon"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback">
                                                A senha deve ter pelo menos 6 caracteres.
                                            </div>
                                            <div class="form-text">
                                                Deixe em branco para manter a senha atual. Mínimo de 6 caracteres se alterada.
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Telefone</label>
                                            <input type="tel" 
                                                   class="form-control" 
                                                   id="phone" 
                                                   name="phone" 
                                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                                   placeholder="(11) 99999-9999">
                                            <div class="form-text">
                                                Opcional. Formato: (11) 99999-9999
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="hole_id" class="form-label required">Papel/Função</label>
                                        <select class="form-select" id="hole_id" name="hole_id" required>
                                            <option value="">Selecione o papel...</option>
                                            <?php if (!empty($holes)): ?>
                                                <?php foreach ($holes as $hole): ?>
                                                    <option value="<?= $hole['id'] ?>" 
                                                            <?= $user['hole_id'] == $hole['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($hole['description']) ?>
                                                        <?php if (!empty($hole['description'])): ?>
                                                            - <?= htmlspecialchars($hole['description']) ?>
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor, selecione um papel.
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Salvar Alterações
                                        </button>
                                        <a href="/admin/users" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações adicionais -->
                    <div class="col-lg-4">
                        <div class="card form-card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-person-circle"></i> Informações do Usuário
                                </h6>
                            </div>
                            <div class="card-body">
                                <p><strong>ID:</strong> <?= htmlspecialchars($user['id']) ?></p>
                                <p><strong>Status:</strong> 
                                    <?php if ($user['status'] === 'active'): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Criado em:</strong><br>
                                    <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                </p>
                                <?php if ($user['last_login']): ?>
                                    <p><strong>Último login:</strong><br>
                                        <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                                    </p>
                                <?php else: ?>
                                    <p><strong>Último login:</strong><br>
                                        <span class="text-muted">Nunca logou</span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card form-card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-info-circle"></i> Informações
                                </h6>
                            </div>
                            <div class="card-body">
                                <h6>Papéis Disponíveis:</h6>
                                <ul class="list-unstyled">
                                    <li><span class="badge bg-danger me-2">Administrador</span> Acesso total</li>
                                    <li><span class="badge bg-warning text-dark me-2">Gerente</span> Gerenciar produtos</li>
                                    <li><span class="badge bg-info me-2">Funcionário</span> Acesso limitado</li>
                                </ul>
                                
                                <hr>
                                
                                <h6>Sobre a Senha:</h6>
                                <ul class="list-unstyled small text-muted">
                                    <li><i class="bi bi-info-circle"></i> Deixe em branco para manter a atual</li>
                                    <li><i class="bi bi-check text-success"></i> Mínimo 6 caracteres se alterada</li>
                                    <li><i class="bi bi-shield-check"></i> Será criptografada automaticamente</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        // Formatação do telefone
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            
            e.target.value = value;
        });
    </script>
</body>
</html>
