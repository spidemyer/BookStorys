<?php
session_start();
require_once 'conexao.php'; 

// Verifica se o usuário está logado. Se não, redireciona para o login 
if (!isset($_SESSION['user_logged']) || $_SESSION['user_logged'] !== true) { 
    header("Location: index.php");
    exit;
}

// Busca todos os livros do catálogo para exibir na vitrine
try {
    $stmt = $conn->query("SELECT id, titulo, autor, estoque, url_capa FROM livros ORDER BY titulo ASC"); // Consulta para obter os livros ordenados por título
    $livros = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch (PDOException $e) { // Em caso de erro na consulta, exibe uma mensagem de erro
    die("Erro ao carregar os livros da biblioteca: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStorys - Biblioteca</title>
    <link class="link-css" rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="body-dashboard">

    <nav class="navbar">
        <div class="nav-logo">
            <i class="fa-solid fa-book-open"></i> BookStorys
        </div>
        <div class="nav-actions">
            <span class="user-name"><i class="fa-regular fa-circle-user"></i> Olá, <?= htmlspecialchars($_SESSION['user_nome'] ?? 'Leitor') ?></span>
            
            <?php if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true): ?>
                <a href="admin_estoque.php" class="btn-alt"><i class="fa-solid fa-sliders"></i> Painel Admin</a>
            <?php else: ?>
                <a href="login_admin.php" class="btn-alt" style="background: #475569; color: #fff;"><i class="fa-solid fa-user-shield"></i> Área do Funcionário</a>
            <?php endif; ?>
            
            <a href="logout.php" class="btn-alt" style="color: #dc2626;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
    </nav>

    <main class="container" style="margin-top: 40px; padding-bottom: 60px;">
        
        <div class="admin-header">
            <div>
                <h1 class="admin-title">Vitrine de Livros</h1>
                <p class="admin-subtitle">Escolha sua próxima leitura. Lembre-se de verificar os prazos de devolução.</p>
            </div>
        </div>

        <div class="books-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; margin-top: 30px;">
            <?php if (count($livros) > 0): ?>
                <?php foreach ($livros as $livro): ?>
                    <div class="book-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: transform 0.2s;">
                        
                        <div style="text-align: center; margin-bottom: 15px;">
                            <img src="<?= htmlspecialchars(!empty($livro['url_capa']) ? $livro['url_capa'] : 'img/default-cover.jpg') ?>" alt="Capa de <?= htmlspecialchars($livro['titulo']) ?>" style="width: 100%; max-height: 260px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        </div>

                        <div style="flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h3 style="font-size: 1.05rem; color: #1e293b; margin: 0 0 5px 0; font-weight: 600; line-height: 1.3;"><?= htmlspecialchars($livro['titulo']) ?></h3>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 12px 0;"><i class="fa-regular fa-user"></i> <?= htmlspecialchars($livro['autor']) ?></p>
                            </div>

                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <span style="font-size: 0.8rem; color: #475569; font-weight: 500;">Disponível:</span>
                                    <?php if ($livro['estoque'] > 0): ?>
                                        <span class="badge-qty positive" style="padding: 2px 8px; font-size: 0.75rem;"><?= $livro['estoque'] ?> un.</span>
                                    <?php else: ?>
                                        <span class="badge-qty zero" style="padding: 2px 8px; font-size: 0.75rem;">Esgotado</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($livro['estoque'] > 0): ?>
                                    <button class="btn" style="width: 100%; padding: 10px; font-size: 0.9rem; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); margin: 0;" onclick="alugarLivro(<?= $livro['id'] ?>, '<?= addslashes($livro['titulo']) ?>')">
                                        <i class="fa-solid fa-book-bookmark"></i> Alugar Livro
                                    </button>
                                <?php else: ?>
                                    <button class="btn" style="width: 100%; padding: 10px; font-size: 0.9rem; background: #cbd5e1; color: #64748b; border: none; cursor: not-allowed; margin: 0;" disabled>
                                        <i class="fa-solid fa-ban"></i> Indisponível
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: #64748b; padding: 60px;">
                    <i class="fa-solid fa-boxes-empty" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <p>Nenhum livro disponível na biblioteca no momento.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <script>
        function alugarLivro(idLivro, tituloLivro) { // Função para lidar com o processo de aluguel do livro
            let termoCompromisso = "📋 TERMOS DE EMPRÉSTIMO\n\n" +
                                   "Livro: \"" + tituloLivro + "\"\n\n" +
                                   "1. O prazo máximo para leitura e devolução é de 7 dias.\n" +
                                   "2. Caso o prazo expire, será aplicada uma multa automática de R$ 5,00 por CADA DIA de atraso.\n\n" +
                                   "Você concorda com as condições e confirma a retirada do livro?";

            if (confirm(termoCompromisso)) {
                
                // Mudado o destino para alugar.php para corresponder ao script criado
                fetch('alugar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + idLivro
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Erro HTTP no servidor (Status " + response.status + ")");
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.sucesso) {
                        alert("🎉 Empréstimo realizado com sucesso!\n\n📅 Data limite para devolução: " + data.data_devolucao + "\nBoa leitura!");
                        window.location.reload(); 
                    } else {
                        alert("⚠️ " + data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro detalhado na requisição:', error);
                    alert("Erro interno na comunicação com o sistema:\n" + error.message);
                });
            }
        }
    </script>
</body>
</html>