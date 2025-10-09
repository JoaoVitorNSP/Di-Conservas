<?php
/**
 * Arquivo de compatibilidade para products-api.php
 * Redireciona para a nova API na estrutura MVC
 */

// Redireciona para a nova API
header('Location: /api/products', true, 301);
exit();
