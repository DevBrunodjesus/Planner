<?php
require 'conexao.php';

// Se já logado, vai direto
if (estaLogado()) {
    header('Location: dashboard.php');
    exit();
}

$erro = '';
if ($_POST) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    if (strlen($senha) < 6) {
        $erro = "A senha precisa ter no mínimo 6 caracteres";
    } else {
        try {
            // Verifica se email já existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $erro = "Este e-mail já está em uso";
            } else {
                // Cria usuário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $email, $senha_hash]);
                
                // Login automático
                $_SESSION['usuario_id'] = $pdo->lastInsertId();
                $_SESSION['usuario_nome'] = $nome;
                
                header('Location: dashboard.php');
                exit();
            }
        } catch(PDOException $e) {
            $erro = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cadastro - Planner</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-box">
        <h1>Criar Conta</h1>
        
        <?php if ($erro): ?>
            <div class="erro"><?= $erro ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="nome" placeholder="Seu nome completo" required>
            <input type="email" name="email" placeholder="Seu melhor e-mail" required>
            <input type="password" name="senha" placeholder="Senha (mínimo 6 caracteres)" required minlength="6">
            <button type="submit">Criar Conta</button>
        </form>
        
        <p class="link">Já tem conta? <a href="index.php">Faça login</a></p>
    </div>
</body>
</html>