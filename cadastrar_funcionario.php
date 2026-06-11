<?php
session_start();
require_once 'conexao.php';

// Exige que um funcionário esteja logado para cadastrar outro
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login_admin.php");
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $rf   = trim($_POST['rf'] ?? '');

    if (!empty($nome) && !empty($rf)) {
        try {
            // Verifica se o RF já está cadastrado no sistema para evitar duplicidade
            $stmt_check = $conn->prepare("SELECT rf FROM funcionarios WHERE rf = ?");
            $stmt_check->execute([$rf]);
            
            if ($stmt_check->rowCount() > 0) {
                $mensagem = "Este número de RF já está cadastrado!";
                $tipo_mensagem = "erro";
            } else {
                // Insere o novo funcionário de forma segura
                $stmt_ins = $conn->prepare("INSERT INTO funcionarios (nome, rf) VALUES (?, ?)");
                $stmt_ins->execute([$nome, $rf]);
                
                $mensagem = "Funcionário cadastrado com sucesso!";
                $tipo_mensagem = "sucesso";
                
                // Redireciona para o painel de estoque após 2 segundos para dar tempo de ler o sucesso
                header("Refresh: 2; url=admin_estoque.php");
            }
        } catch (PDOException $e) { //em caso de erro no banco mostra a mensagem
            $mensagem = "Erro no banco de dados: " . $e->getMessage();
            $tipo_mensagem = "erro";
        }
    } else { //pede para preencher todos os campos 
        $mensagem = "Por favor, preencha todos os campos.";
        $tipo_mensagem = "erro";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStorys - Cadastrar Funcionário</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="card-auth" style="border-top: 4px solid #8b5cf6;">
        <div class="logo-icon">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h2>Novo Funcionário</h2>
        <p class="subtitle">Registre uma nova credencial de acesso ao estoque</p>

        <?php if (!empty($mensagem)): ?>
            <div class="msg-erro" style="<?= $tipo_mensagem === 'sucesso' ? 'background: #f0fdf4; color: #166534; border-color: #bbf7d0;' : '' ?>">
                <i class="fa-solid <?= $tipo_mensagem === 'sucesso' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i> 
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form action="cadastrar_funcionario.php" method="POST">
            <div class="input-group">
                <label><i class="fa-regular fa-user"></i> Nome Completo</label>
                <input type="text" name="nome" placeholder="Ex: João Silva" required>
            </div>
            
            <div class="input-group">
                <label><i class="fa-solid fa-id-card"></i> RF (Registro do Funcionário)</label>
                <input type="text" name="rf" placeholder="Ex: 123456" maxlength="20" required>
            </div>

            <button type="submit" class="btn">Concluir Cadastro</button>
            
            <div style="margin-top: 20px; display: flex; justify-content: space-between; font-size: 0.85rem;">
                <a href="login_admin.php" class="link-bottom" style="margin: 0;"><i class="fa-solid fa-right-to-bracket"></i> Ir para <span>Login</span></a>
                <a href="admin_estoque.php" class="link-bottom" style="margin: 0;"><i class="fa-solid fa-boxes-stacked"></i> Ver <span>Estoque</span></a>
            </div>
        </form>
    </div>

</body>
</html>