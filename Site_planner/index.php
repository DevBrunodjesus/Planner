<?php
require 'conexao.php';

// Se já logado, vai direto para o planner !
if (estaLogado()) {
    header('Location: dashboard.php');
    exit();
}

$erro = '';
if ($_POST) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    $stmt = $pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        header('Location: dashboard.php');
        exit();
    } else {
        $erro = "E-mail ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Planner</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-box">
      <div class="planner-header h1">
        <h1>Planner+</h1>
      </div>
        <p class="subtitulo">Organize sua rotina</p>
        
        <?php if ($erro): ?>
            <div class="erro"><?= $erro ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Seu e-mail" required>
            <input type="password" name="senha" placeholder="Sua senha" required>
            <button type="submit">Entrar</button>
        </form>
        
        <p class="link">Novo por aqui? <a href="cadastro.php">Crie sua conta</a></p>
    </div>
</body>
</html>