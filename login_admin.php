<?php
session_start();
require_once 'conexao.php';

// Se o funcionário já estiver logado, manda direto para o estoque
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header("Location: admin_estoque.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $rf   = trim($_POST['rf']);

    try {
        // Busca o funcionário pelo registro físico (RF) e nome
        $stmt = $conn->prepare("SELECT nome, rf FROM funcionarios WHERE nome = ? AND rf = ?");
        $stmt->execute([$nome, $rf]);
        $funcionario = $stmt->fetch();

        if ($funcionario) {
            // Cria uma sessão exclusiva para o administrador
            $_SESSION['admin_logged'] = true;
            $_SESSION['user_nome']    = $funcionario['nome'];
            $_SESSION['user_rf']      = $funcionario['rf'];
            
            header("Location: admin_estoque.php");
            exit;
        } else {
            $erro = "Funcionário não encontrado ou RF incorreto.";
        }
    } catch (PDOException $e) {
        $erro = "Erro no servidor: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStorys - Login Administrativo</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="card-auth" style="border-top: 4px solid #4f46e5;">
        <div class="logo-icon" style="background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <h2>Painel do Funcionário</h2>
        <p class="subtitle">Insira suas credenciais para gerenciar o estoque</p>

        <?php if(!empty($erro)): ?> 
            <div class="msg-erro"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($erro) ?></div> 
        <?php endif; ?>

        <div class="info-box" style="background: #f8fafc; border-left: 4px solid #6366f1; padding: 12px; font-size: 0.8rem; color: #475569; margin-bottom: 20px; text-align: left;">
            <i class="fa-solid fa-info-circle" style="color: #6366f1;"></i> <strong>Acesso Padrão:</strong><br>
            Nome: <code style="background:#e2e8f0; padding:2px 4px; border-radius:4px;">admin</code><br>
            RF: <code style="background:#e2e8f0; padding:2px 4px; border-radius:4px;">123456</code>
        </div>

        <form action="login_admin.php" method="POST">
            <div class="input-group">
                <label>Nome do Funcionário</label>
                <input type="text" name="nome" placeholder="Ex: admin" required>
            </div>
            <div class="input-group">
                <label>RF (Registro Funcionário)</label>
                <input type="password" name="rf" placeholder="••••••" required>
            </div>
            
            <button type="submit" class="btn" style="background: linear-gradient(90deg, #4f46e5 0%, #818cf8 100%); width: 100%;">Autenticar</button>
            
            <div style="margin-top: 25px; display: flex; flex-direction: column; gap: 12px; align-items: center;">
                <a href="cadastrar_funcionario.php" class="link-bottom" style="font-size: 0.9rem; font-weight: 500; color: #6366f1; text-decoration: none; margin: 0;">
                    <i class="fa-solid fa-user-plus"></i> Cadastrar novo funcionário
                </a>
                
                <a href="biblioteca.php" class="link-bottom" style="font-size: 0.85rem; color: #64748b; text-decoration: none; margin: 0;">
                    <i class="fa-solid fa-arrow-left"></i> Voltar para a <span style="color: #6366f1; font-weight: 600;">Vitrine</span>
                </a>
            </div>
        </form>
    </div>
</body>
</html>