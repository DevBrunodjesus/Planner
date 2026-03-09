<?php
// conexao.php - Único arquivo de configuração
session_start();

$host = 'localhost';
$db   = 'planner_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
}

// Funções essenciais
function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

function requerLogin() {
    if (!estaLogado()) {
        header('Location: index.php');
        exit();
    }
}
?>