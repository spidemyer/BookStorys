<?php
session_start();
require_once 'conexao.php'; //conecta com o banco de dados

if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login_admin.php");
    exit;
}

$mensagem = ''; // mensagem de para o usuário (sucesso ou erro)
$tipo_mensagem = ''; // tipo da mensagem

if (isset($_GET['excluir'])) { //se o funcionário clicar para excluir
    $id_excluir = (int)$_GET['excluir']; //garante que o ID seja inteiro 
    try {
        $stmt_del = $conn->prepare("DELETE FROM livros WHERE id = ?"); //prepara para excluir
        $stmt_del->execute([$id_excluir]); //executa a exclusão
        $mensagem = "Livro excluído com sucesso do catálogo!"; //mensagem para o usuário
        $tipo_mensagem = "sucesso";
    } catch (PDOException $e) { //mostra erro caso não consiga excluir do sistema
        $mensagem = "Erro ao excluir o livro: " . $e->getMessage();
        $tipo_mensagem = "erro";
    }
}

try {
    // Busca os livros ordenados pelo título para manter a tabela organizada
    $stmt = $conn->query("SELECT id, titulo, autor, estoque FROM livros ORDER BY titulo ASC");
    $livros = $stmt->fetchAll(PDO::FETCH_ASSOC); // armazena os livros em um array 
} catch (PDOException $e) { //caso de erro para carregar o estoque mostra a mensagem de erro
    die("Erro ao carregar o estoque: " . $e->getMessage());
}
?>
<!DOCTYPE html> <!--html da tela -->
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStorys - Estoque </title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="body-dashboard">

    <nav class="navbar">
        <div class="nav-logo">
            <i class="fa-solid fa-book-open"></i> BookStorys Admin
        </div>
        <div class="nav-actions">
            <span class="user-name"><i class="fa-regular fa-circle-user"></i> <?= htmlspecialchars($_SESSION['user_nome'] ?? 'Painel') ?></span> <!-- Exibe o nome do usuário logado ou 'Painel'-->
            <a href="biblioteca.php" class="btn-alt"><i class="fa-solid fa-store"></i> Ver Vitrine</a>
            <a href="logout.php" class="btn-alt" style="color: #dc2626;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
    </nav>

    <main class="container" style="margin-top: 40px;">
        
        <?php if (!empty($mensagem)): ?> <!-- Exibe a mensagem de sucesso ou erro, se houver -->
            <div class="msg-erro" style="<?= $tipo_mensagem === 'sucesso' ? 'background: #f0fdf4; color: #166534; border-color: #bbf7d0;' : '' ?>">
                <i class="fa-solid <?= $tipo_mensagem === 'sucesso' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i> 
                <?= htmlspecialchars($mensagem) ?> 
            </div>
        <?php endif; ?>

        <div class="admin-header"> <!-- Cabeçalho do painel de estoque -->
            <div>
                <h1 class="admin-title">Estoque de Livros</h1>
                <p class="admin-subtitle">Gerencie o catálogo da biblioteca com controle integrado</p>
            </div>
            <button class="btn-add" onclick="abrirModalCadastro()"> <!-- Botão para abrir a janela de cadasro de novo livro -->
                <i class="fa-solid fa-plus"></i> Adicionar Livro
            </button>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Título do Livro</th>
                        <th>Autor / Escritor</th>
                        <th style="text-align: center;">Quantidade</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($livros) > 0): ?>
                        <?php foreach($livros as $livro): ?>
                            <tr>
                                <td>
                                    <span class="book-title-cell"><?= htmlspecialchars($livro['titulo']) ?></span> <!-- Exibe o título do livro -->
                               </td>
                               
                               <td>
                                    <span class="book-author-cell"><?= htmlspecialchars($livro['autor']) ?></span>
                               </td>
                               
                               <td style="text-align: center;">
                                    <?php if($livro['estoque'] > 0): ?>
                                        <span class="badge-qty positive"><?= $livro['estoque'] ?> un.</span>
                                    <?php else: ?>
                                        <span class="badge-qty zero">Esgotado</span>
                                    <?php endif; ?>
                               </td>
                               
                               <td style="text-align: right;">
                                    <div class="actions-cell"> <!-- Botões de ação para editar ou excluir o livro -->
                                        <button class="btn-action edit" onclick="editarLivro(<?= $livro['id'] ?>, '<?= addslashes($livro['titulo']) ?>', '<?= addslashes($livro['autor']) ?>', <?= $livro['estoque'] ?>)" title="Editar dados">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="btn-action delete" onclick="confirmarExclusao(<?= $livro['id'] ?>, '<?= addslashes($livro['titulo']) ?>')" title="Remover livro">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                               </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b; padding: 40px;">
                                <i class="fa-solid fa-box-open" style="font-size: 2rem; display: block; margin-bottom: 10px; color: #cbd5e1;"></i>
                                Nenhum livro cadastrado no momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modalCadastro" class="modal"> <!-- Modal para cadastro de novo livro -->
        <div class="modal-content">
            <button class="close-modal" onclick="fecharModalCadastro()">&times;</button>
            <h2>Novo Livro</h2>
            <p class="subtitle">Insira as informações básicas para atualizar a vitrine</p>
            
            <form action="cadastrar_livro_action.php" method="POST">
                <div class="input-group">
                    <label>Título do Livro</label>
                    <input type="text" name="titulo" placeholder="Ex: O Pequeno Príncipe" required>
                </div>
                <div class="input-group">
                    <label>Autor</label>
                    <input type="text" name="autor" placeholder="Ex: Antoine de Saint-Exupéry" required>
                </div>
                <div class="input-group">
                    <label>Quantidade em Estoque</label>
                    <input type="number" name="estoque" min="0" placeholder="Ex: 5" required>
                </div>
                <div class="input-group">
                    <label>Caminho Local da Capa (Opcional)</label>
                    <input type="text" name="url_capa" placeholder="Ex: img/nome-da-capa.jpg">
                </div>
                <button type="submit" class="btn" style="margin-top: 10px;">Salvar no Estoque</button>
            </form>
        </div>
    </div>

<div id="modalEditar" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="fecharModalEditar()">&times;</button> <!-- Botão para fechar a janela de edição -->
            <h2>Editar Livro</h2>
            <p class="subtitle">Modifique os dados necessários do exemplar</p>
            
            <form action="editar_livro_action.php" method="POST">
                <input type="hidden" id="editar_id" name="id">

                <div class="input-group">
                    <label>Título do Livro</label>
                    <input type="text" id="editar_titulo" name="titulo" required>
                </div>
                <div class="input-group">
                    <label>Autor / Escritor</label>
                    <input type="text" id="editar_autor" name="autor" required>
                </div>
                <div class="input-group">
                    <label>Quantidade em Estoque</label>
                    <input type="number" id="editar_estoque" name="estoque" min="0" required>
                </div>
                
                <div class="input-group">
                    <label>Caminho da Imagem da Capa</label>
                    <input type="text" id="editar_url_capa" name="url_capa" placeholder="Ex: img/nova-capa.jpg">
                </div>

                <button type="submit" class="btn" style="margin-top: 10px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">Atualizar Livro</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModalCadastro() { 
            document.getElementById('modalCadastro').style.display = 'flex';
        }
        function fecharModalCadastro() {
            document.getElementById('modalCadastro').style.display = 'none';
        }

        function editarLivro(id, titulo, autor, estoque, urlCapa) {
            document.getElementById('editar_id').value = id;
            document.getElementById('editar_titulo').value = titulo;
            document.getElementById('editar_autor').value = autor;
            document.getElementById('editar_estoque').value = estoque;
            document.getElementById('editar_url_capa').value = urlCapa; 
            
            document.getElementById('modalEditar').style.display = 'flex';
        }
        
        function fecharModalEditar() {
            document.getElementById('modalEditar').style.display = 'none'; 
        }

        function confirmarExclusao(id, titulo) {
            if (confirm("Tem certeza que deseja remover o livro '" + titulo + "' permanentemente do sistema?")) {
                window.location.href = "admin_estoque.php?excluir=" + id;
            }
        }

        window.onclick = function(event) {
            let modalCad = document.getElementById('modalCadastro');
            let modalEdi = document.getElementById('modalEditar');
            if (event.target == modalCad) { modalCad.style.display = 'none'; }
            if (event.target == modalEdi) { modalEdi.style.display = 'none'; }
        }
    </script>