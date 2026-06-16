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

    // Validações
    if ($senha !== $confirmar_senha) { 
        $erro = "As senhas não coincidem!";
    } elseif (strlen($senha) < 6 || strlen($senha) > 10) { // Validação do tamanho da senha
        $erro = "A senha deve conter entre 6 e 10 caracteres!";
    } elseif ($idade < 0 || $idade > 100) { // Validação da idade
        $erro = "A idade deve ser entre 0 e 100 anos!";
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
                
                $sucesso = "Novo cliente cadastrado com sucesso!";
                // Após cadastrar, limpa os campos e permanece na página para novos cadastros se necessário, ou volta pro estoque após 2 segundos
                header("Refresh: 2; url=biblioteca.php");
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
    <title>BookStorys - Cadastrar Cliente</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="card-auth">
        <div class="logo-icon"><i class="fa-solid fa-user-shield"></i></div>
        <h2>BookStorys</h2>
        <p class="subtitle">Painel de Cadastro de Novo Cliente</p>

        <?php if($erro): ?> <div class="msg-erro"><?= $erro ?></div> <?php endif; ?>
        <?php if($sucesso): ?> <div class="msg-erro" style="background:#ecfdf5; color:#059669; border-color:#059669;"><?= $sucesso ?></div> <?php endif; ?>

        <form action="cadastro.php" method="POST">
            <div class="input-group">
                <label>Nome do Cliente</label>
                <input type="text" name="nome" placeholder="Nome completo do cliente" required>
            </div>
            <div class="input-group">
                <label>Idade</label>
                <input type="number" name="idade" placeholder="Idade" min="0" max="100" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="nome.sobrenome@gmail.com" required>
            </div>
            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="••••••••" minlength="6" maxlength="10" required>
            </div>
            <div class="input-group">
                <label>Confirmar Senha</label>
                <input type="password" name="confirmar_senha" placeholder="••••••••" minlength="6" maxlength="10" required>
            </div>  
            <button type="submit" class="btn">Cadastrar Cliente</button>
            <a href="biblioteca.php" class="link-bottom"><i class="fa-solid fa-arrow-left"></i> Voltar para o <span>Login</span></a>
        </form>
    </div>
</body>
</html>