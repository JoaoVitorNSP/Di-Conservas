<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

if ($conn) {
    echo "Conexão com o banco Hostinger realizada com sucesso!";
} else {
    echo "Falha na conexão.";
}
