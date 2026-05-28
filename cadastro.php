<?php
session_start();
require_once 'conexao.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') { //formulario de cadastro
    $nome = trim($_POST['nome']);
    $idade = intval($_POST['idade']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($senha !== $confirmar_senha) { //confirma se as senhas são iguais, se não mostra mensagem de erro
        $erro = "As senhas não coincidem!";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve conter no mínimo 6 caracteres!";
    } else {
        try {
            // Verifica se o e-mail já existe
            $stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmtCheck->execute([$email]);
            
            if ($stmtCheck->rowCount() > 0) {
                $erro = "Este e-mail já está cadastrado!";
            } else {
                // Criptografa a senha para salvar com segurança
                $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
                
                // Insere no PostgreSQL
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, idade, email, senha) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $idade, $email, $senhaHash]);
                
                $sucesso = "Cadastro realizado com sucesso! Redirecionando...";
                header("Refresh: 2; url=index.php");
            }
        } catch (PDOException $e) {
            $erro = "Erro no cadastro: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStorys - Cadastro</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="card-auth">
        <div class="logo-icon"><i class="fa-solid fa-book-open"></i></div>
        <h2>BookStorys</h2>
        <p class="subtitle">Crie sua conta para acessar a biblioteca</p>

        <?php if($erro): ?> <div class="msg-erro"><?= $erro ?></div> <?php endif; ?>
        <?php if($sucesso): ?> <div class="msg-erro" style="background:#ecfdf5; color:#059669; border-color:#059669;"><?= $sucesso ?></div> <?php endif; ?>

        <form action="cadastro.php" method="POST">
            <div class="input-group">
                <label>Nome Completo</label>
                <input type="text" name="nome" placeholder="Seu nome completo" required>
            </div>
            <div class="input-group">
                <label>Idade</label>
                <input type="number" name="idade" placeholder="Sua idade" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="seu@email.com" required>
            </div>
            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="••••••••" required>
            </div>
            <div class="input-group">
                <label>Confirmar Senha</label>
                <input type="password" name="confirmar_senha" placeholder="••••••••" required>
            </div>  
            <button type="submit" class="btn">Cadastrar</button>
            <a href="index.php" class="link-bottom">Já tem uma conta? <span>Faça login</span></a>
        </form>