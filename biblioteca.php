<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: index.php");
    exit;
}

// Resgata o catálogo atualizado
$stmt = $conn->query("SELECT * FROM livros ORDER BY titulo ASC");
$livros = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStorys - Biblioteca</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="body-dashboard">
    <nav class="navbar">
    <div class="nav-logo">
        <i class="fa-solid fa-book-open"></i> BookStorys
    </div>
    <div class="nav-actions" style="display: flex; gap: 12px; align-items: center;">
        <a href="login_admin.php" class="btn-alt" style="background: #6366f1; color: white; border: none; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-user-gear"></i> Área do Funcionário
        </a>
        
        <a href="logout.php" class="btn-alt" style="background: transparent; color: #dc2626; border: 1px solid #fecaca; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
            <i class="fa-solid fa-right-from-bracket"></i> Sair
        </a>
    </div>
</nav>

    <div class="container" style="margin-top: 30px;">
        <h1 style="font-size: 1.6rem; font-weight: 600;">Livros Disponíveis</h1>
        <p style="color:#64748b; font-size:0.9rem; margin-bottom: 20px;">Escolha um livro para alugar</p>

        <div class="books-grid">
            <?php foreach($livros as $livro): ?>
                <div class="book-card">
                    <div class="book-cover-wrapper">
                        <img src="<?= htmlspecialchars($livro['url_capa']) ?>" class="book-cover-img" alt="Capa do Livrow">
                    </div>
                    <div class="book-info">
                        <div>
                            <div class="book-title"><?= htmlspecialchars($livro['titulo']) ?></div>
                            <div class="book-author"><?= htmlspecialchars($livro['autor']) ?></div>
                        </div>
                        <div>
                            <div class="stock-row">
                                <span>Estoque:</span>
                                <?php if($livro['estoque'] > 0): ?>
                                    <span class="badge-stock available"><?= $livro['estoque'] ?> disponíveis</span>
                                <?php else: ?>
                                    <span class="badge-stock empty">Esgotado</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($livro['estoque'] > 0): ?>
                                <button class="btn" style="margin:0;" onclick="efetuarAluguel(<?= $livro['id'] ?>)">Alugar</button>
                            <?php else: ?>
                                <button class="btn btn-alt" style="margin:0; width:100%; cursor:not-allowed;" disabled>Indisponível</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="modalAlugado" class="modal">
        <div class="modal-content" style="max-width:440px;">
            <button class="close-modal" onclick="fecharModalSucesso()">&times;</button>
            <h3 style="font-size:1.4rem; color:#1e293b; margin-bottom:15px; text-align:left;">Livro Alugado!</h3>
            <div style="text-align:left; color:#475569; font-size:0.95rem; display:flex; flex-direction:column; gap:8px;">
                <p><i class="fa-regular fa-calendar-check" style="color:#8b5cf6;"></i> Você tem <strong style="color:#8b5cf6;">7 dias</strong> para devolver o livro.</p>
                <p style="font-size:0.85rem; color:#64748b; background:#f8fafc; padding:10px; border-radius:8px;" id="textoDataDevolucao"></p>
            </div>
            <button class="btn" style="margin-top:25px;" onclick="fecharModalSucesso()">Confirmar</button>
        </div>
    </div>

    <script>
        function efetuarAluguel(livroId) { // Função para enviar a requisição de aluguel
            fetch('alugar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + livroId
            })
            .then(response => response.json()) // Espera a resposta do servidor e converte para JSON
            .then(data => {
                if (data.sucesso) {
                    // Atualiza dinamicamente o texto contendo a data calculada pelo servidor PHP
                    document.getElementById('textoDataDevolucao').innerText = "A data de devolução será até " + data.data_devolucao + ".";
                    document.getElementById('modalAlugado').style.display = 'flex';
                } else {
                    alert(data.mensagem || "Erro ao processar aluguel."); // Exibe mensagem de erro caso a requisição falhe
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert("Falha na comunicação com o servidor.");
            });
        }

        function fecharModalSucesso() {
            document.getElementById('modalAlugado').style.display = 'none';
            window.location.reload(); // Recarrega para exibir o estoque atualizado
        }
    </script>
</body>
</html>