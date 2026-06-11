<?php
session_start();
require_once 'conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rf = trim($_POST['rf'] ?? '');

    if (!empty($rf)) {
        try {
            // Busca o funcionário pelo Registro Geral (RF) no banco de dados
            $stmt = $conn->prepare("SELECT id, nome FROM funcionarios WHERE rf = ?");
            $stmt->execute([$rf]);
            $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($funcionario) {
                // Configura as variáveis de sessão administrativa
                $_SESSION['admin_logged'] = true;
                $_SESSION['user_id'] = $funcionario['id'];
                $_SESSION['user_nome'] = $funcionario['nome'];
                
                // Redireciona direto para o painel de gerenciamento
                header("Location: admin_estoque.php");
                exit;
            } else {
                $erro = "RF inválido ou não cadastrado no sistema!";
            }
        } catch (PDOException $e) {
            $erro = "Erro no servidor: " . $e->getMessage();
        }
    } else {
        $erro = "Por favor, digite o seu número de RF.";
    }
}
?> 
<!DOCTYPE html>
<html lang="pt-BR"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStorys - Login Funcionário</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; font-family: system-ui, -apple-system, sans-serif;">

    <div class="card-auth" style="background: #ffffff; border-radius: 16px; padding: 40px; width: 100%; max-width: 450px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); border-top: 4px solid #8d1010; box-sizing: border-box;">
        
        <div class="logo-icon" style="background: #ffe0ea; color: #8d1010; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; font-size: 1.5rem;">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        
        <h2 style="text-align: center; color: #8d1010; margin: 0 0 8px 0; font-size: 1.8rem; font-weight: 600;">Painel do Funcionário</h2>
        <p class="subtitle" style="text-align: center; color: #64748b; margin: 0 0 30px 0; font-size: 0.95rem;">Insira suas credenciais para gerenciar o estoque</p>

        <?php if (!empty($erro)): ?>
            <div class="msg-erro" style="background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form action="login_admin.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            
            <div class="input-group" style="display: flex; flex-direction: column; gap: 8px;">
                <label style="color: #3b1e23; font-weight: 500; font-size: 0.9rem;"><i class="fa-solid fa-id-card" style="color: #8d1010; margin-right: 4px;"></i> RF (Registro Funcionário)</label>
                <input type="password" name="rf" placeholder="••••••" required autocomplete="off" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 1rem; outline: none; transition: border-color 0.2s; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #f16382 0%, #a50c32 100%); color: #ffffff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2); transition: opacity 0.2s; margin-top: 10px;">
                Autenticar
            </button>
            
            <div style="margin-top: 10px; text-align: center;">
                <a href="biblioteca.php" class="link-bottom" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px; transition: color 0.2s;">
                    <i class="fa-solid fa-arrow-left"></i> Voltar para a <span style="color: #8d1010; font-weight: 500;">Vitrine</span>
                </a>
            </div>
        </form>
    </div>

</body>
</html>