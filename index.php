<?php
session_start();

$erro = '';

try {
    require_once 'conexao.php';
} catch (Exception $e) {
    $erro = "Falha no sistema de banco de dados.";
}

if (isset($_SESSION['user_logged']) && $_SESSION['user_logged'] === true) {
    header("Location: biblioteca.php");
    exit;
}

// Processa o formulário apenas se a conexão estiver ativa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($erro)) {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (isset($conn)) { // Verifica se a conexão foi estabelecida
        try {
            $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?"); // Busca o usuário pelo email
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) { // Verifica a senha 
                $_SESSION['user_logged'] = true;
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_nome'] = $usuario['nome'];
                
                header("Location: biblioteca.php"); 
                exit;
            } else {
                $erro = "E-mail ou senha incorretos."; // Mensagem genérica para evitar exposição de informações
            }
        } catch (PDOException $e) {
            $erro = "Erro na consulta: " . $e->getMessage();
        }
    } else {
        $erro = "O banco de dados não está conectado."; // Mensagem de erro para falha na conexão
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStorys - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="card-auth">
        <div class="logo-icon"><i class="fa-solid fa-book-open"></i></div>
        <h2>BookStorys</h2>
        <p class="subtitle">Faça login para acessar a biblioteca</p>

        <?php if(!empty($erro)): ?> 
            <div class="msg-erro" style="background:#fee2e2; color:#b91c1c; padding:12px; border-radius:8px; margin-bottom:15px; font-size:0.85rem; text-align:left;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($erro) ?>
            </div> 
        <?php endif; ?>

        <form action="index.php" method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="seu@email.com" required>
            </div>
            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">Entrar</button>
            
            <a href="cadastro.php" class="link-bottom">Não tem uma conta? <span>Cadastre-se</span></a>
        </form>
    </div>
</body>
</html>