<?php
session_start();
require_once 'conexao.php'; // Conecta com o banco de dados PostgreSQL

// Validação de segurança para o funcionário/Admin
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login_admin.php");
    exit;
}

// Captura mensagens via parâmetros de URL enviados pelas ações do sistema
$mensagem = $_GET['mensagem'] ?? ''; 
$tipo_mensagem = $_GET['tipo'] ?? '';

//parte de excluir o livro do estoque
if (isset($_GET['excluir'])) { 
    $id_excluir = (int)$_GET['excluir']; 
    try {
        $stmt_busca = $conn->prepare("SELECT url_capa FROM livros WHERE id = ?");
        $stmt_busca->execute([$id_excluir]);
        $livro_img = $stmt_busca->fetch();

        $stmt_del = $conn->prepare("DELETE FROM livros WHERE id = ?"); 
        if ($stmt_del->execute([$id_excluir])) {
            if ($livro_img && !empty($livro_img['url_capa']) && file_exists($livro_img['url_capa']) && $livro_img['url_capa'] !== 'img/default-cover.jpg') {
                unlink($livro_img['url_capa']);
            }
            header("Location: admin_estoque.php?mensagem=Livro excluído com sucesso do catálogo!&tipo=sucesso");
            exit;
        }
    } catch (PDOException $e) { 
        header("Location: admin_estoque.php?mensagem=Erro ao excluir o livro: " . urlencode($e->getMessage()) . "&tipo=erro");
        exit;
    }
}

if (isset($_GET['devolver'])) {
    $id_aluguel = (int)$_GET['devolver'];
    try {
        $conn->beginTransaction();

        // Busca o livro_id associado a este aluguel para poder devolver ao estoque correto
        $stmt_aluguel = $conn->prepare("SELECT livro_id FROM alugueis WHERE id = ? AND status = 'Pendente'");
        $stmt_aluguel->execute([$id_aluguel]);
        $aluguel_info = $stmt_aluguel->fetch();

        if ($aluguel_info) {
            $livro_id_devolver = $aluguel_info['livro_id'];

            // Atualiza o status do aluguel para Devolvido
            $stmt_up_aluguel = $conn->prepare("UPDATE alugueis SET status = 'Devolvido' WHERE id = ?");
            $stmt_up_aluguel->execute([$id_aluguel]);

            // Devolve +1 unidade para o estoque físico deste livro
            $stmt_up_livro = $conn->prepare("UPDATE livros SET estoque = estoque + 1 WHERE id = ?");
            $stmt_up_livro->execute([$livro_id_devolver]);

            $conn->commit();
            header("Location: admin_estoque.php?mensagem=Devolução recebida e estoque atualizado (+1)!&tipo=sucesso");
            exit;
        } else {
            $conn->rollBack();
            header("Location: admin_estoque.php?mensagem=Empréstimo não localizado ou já devolvido.&tipo=erro");
            exit;
        }
    } catch (PDOException $e) {
        if ($conn->inTransaction()) { $conn->rollBack(); }
        header("Location: admin_estoque.php?mensagem=Erro ao processar devolução: " . urlencode($e->getMessage()) . "&tipo=erro");
        exit;
    }
}

try {
    // Busca o catálogo completo de livros
    $stmt = $conn->query("SELECT id, titulo, autor, estoque, url_capa FROM livros ORDER BY titulo ASC"); 
    $livros = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    // Busca os aluguéis ativos/pendentes com INNER JOIN para pegar dados do usuário e do livro
    $sql_emprestimos = "SELECT a.id AS aluguel_id, u.nome AS usuario_nome, u.email AS usuario_email, 
                               l.titulo AS livro_titulo, a.data_aluguel, a.data_devolucao_prevista
                        FROM alugueis a
                        INNER JOIN usuarios u ON a.usuario_id = u.id
                        INNER JOIN livros l ON a.livro_id = l.id
                        WHERE a.status = 'Pendente'
                        ORDER BY a.data_devolucao_prevista ASC";
    $stmt_emp = $conn->query($sql_emprestimos);
    $emprestimos = $stmt_emp->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { 
    die("Erro ao carregar o painel administrativo: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStorys - Painel Administrativo</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="body-dashboard">

    <nav class="navbar">
        <div class="nav-logo">
            <i class="fa-solid fa-book-open"></i> BookStorys Admin
        </div>
        <div class="nav-actions">
            <span class="user-name"><i class="fa-regular fa-circle-user"></i> <?= htmlspecialchars($_SESSION['user_nome'] ?? 'Painel') ?></span> 
            <a href="biblioteca.php" class="btn-alt"><i class="fa-solid fa-store"></i> Ver Vitrine</a>
            <a href="logout.php" class="btn-alt" style="color: #dc2626;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
    </nav>

    <main class="container" style="margin-top: 40px; padding-bottom: 60px;">
        
        <?php if (!empty($mensagem)): ?> 
            <div class="msg-erro" style="<?= $tipo_mensagem === 'sucesso' ? 'background: #f0fdf4; color: #166534; border-color: #bbf7d0;' : '' ?>">
                <i class="fa-solid <?= $tipo_mensagem === 'sucesso' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i> 
                <?= htmlspecialchars($mensagem) ?> 
            </div>
        <?php endif; ?>

        <div class="admin-header"> 
            <div>
                <h1 class="admin-title">Estoque de Livros</h1>
                <p class="admin-subtitle">Gerencie o catálogo da biblioteca com controle integrado</p>
            </div>
            <button class="btn-add" onclick="abrirModalCadastro()"> 
                <i class="fa-solid fa-plus"></i> Adicionar Livro
            </button>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Capa</th>
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
                                <td style="text-align: center;">
                                    <img src="<?= htmlspecialchars($livro['url_capa'] ?? 'img/default-cover.jpg') ?>" alt="Capa" style="width: 40px; height: 55px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                                </td>
                                <td><span class="book-title-cell"><?= htmlspecialchars($livro['titulo']) ?></span></td>
                                <td><span class="book-author-cell"><?= htmlspecialchars($livro['autor']) ?></span></td>
                                <td style="text-align: center;">
                                    <?php if($livro['estoque'] > 0): ?>
                                        <span class="badge-qty positive"><?= $livro['estoque'] ?> un.</span>
                                    <?php else: ?>
                                        <span class="badge-qty zero">Esgotado</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="actions-cell"> 
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
                            <td colspan="5" style="text-align: center; color: #64748b; padding: 40px;">Nenhum livro cadastrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-header" style="margin-top: 50px;"> 
            <div>
                <h1 class="admin-title">Livros Emprestados</h1>
                <p class="admin-subtitle">Controle de exemplares sob posse dos leitores, prazos e multas cumulativas</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Livro Alugado</th>
                        <th>Quem Emprestou</th>
                        <th style="text-align: center;">Data do Aluguel</th>
                        <th style="text-align: center;">Prazo de Devolução</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($emprestimos) > 0): ?>
                        <?php foreach($emprestimos as $emp): 
                            // Configurações de fuso horário e tratamento visual do status
                            $data_aluguel_formatada = date('d/m/Y', strtotime($emp['data_aluguel']));
                            $data_dev_formatada = date('d/m/Y', strtotime($emp['data_devolucao_prevista']));
                            
                            $hoje = date('Y-m-d');
                            $esta_atrasado = ($hoje > $emp['data_devolucao_prevista']);

                            // Cálculo matemático da multa em tempo real (R$ 5,00 por dia útil/corrido vencido)
                            $valor_multa = 0;
                            $dias_atraso = 0;

                            if ($esta_atrasado) {
                                $data_hoje_obj = new DateTime($hoje);
                                $data_prevista_obj = new DateTime($emp['data_devolucao_prevista']);
                                $diferenca = $data_hoje_obj->diff($data_prevista_obj);
                                $dias_atraso = $diferenca->days;
                                $valor_multa = $dias_atraso * 5.00;
                            }
                        ?>
                            <tr>
                                <td>
                                    <strong style="color: #1e293b;"><?= htmlspecialchars($emp['livro_titulo']) ?></strong>
                                </td>
                                <td>
                                    <div style="font-size: 0.9rem; font-weight: 500; color: #334155;"><?= htmlspecialchars($emp['usuario_nome']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b;"><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($emp['usuario_email']) ?></div>
                                </td>
                                <td style="text-align: center; font-size: 0.9rem; color: #475569;">
                                    <?= $data_aluguel_formatada ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if($esta_atrasado): ?>
                                        <span class="badge-qty zero" style="font-weight: 600; display: inline-block; margin-bottom: 4px;"><i class="fa-solid fa-clock"></i> Atrasado (<?= $data_dev_formatada ?>)</span>
                                        <div style="font-size: 0.8rem; color: #b91c1c; font-weight: bold;">Multa: R$ <?= number_format($valor_multa, 2, ',', '.') ?> (<?= $dias_atraso ?> dias)</div>
                                    <?php else: ?>
                                        <span class="badge-qty positive" style="background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe;"><i class="fa-regular fa-calendar"></i> No prazo (<?= $data_dev_formatada ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                        
                                        <form action="editar_prazo_action.php" method="POST" style="margin:0;" onsubmit="return confirm('Deseja renovar o aluguel de \'<?= addslashes($emp['livro_titulo']) ?>\' por mais 7 dias?')">
                                            <input type="hidden" name="aluguel_id" value="<?= $emp['aluguel_id'] ?>">
                                            <input type="hidden" name="acao" value="renovar">
                                            <button type="submit" class="btn-action edit" style="background: #fef3c7; color: #d97706; border: 1px solid #fde68a;" title="Renovar +7 dias automaticamente">
                                                <i class="fa-solid fa-arrows-rotate"></i>
                                            </button>
                                        </form>

                                        <button class="btn-action edit" onclick="abrirModalPrazo(<?= $emp['aluguel_id'] ?>, '<?= $emp['data_devolucao_prevista'] ?>', '<?= addslashes($emp['livro_titulo']) ?>')" title="Aumentar ou diminuir prazo manual">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </button>

                                        <button class="btn-add" style="padding: 6px 12px; font-size: 0.8rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); margin:0;" onclick="confirmarDevolucaoComMulta(<?= $emp['aluguel_id'] ?>, '<?= addslashes($emp['livro_titulo']) ?>', '<?= addslashes($emp['usuario_nome']) ?>', '<?= number_format($valor_multa, 2, ',', '.') ?>')">
                                            <i class="fa-solid fa-arrow-rotate-left"></i> Receber
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748b; padding: 40px;">
                                <i class="fa-solid fa-circle-check" style="font-size: 2rem; display: block; margin-bottom: 10px; color: #cbd5e1;"></i>
                                Excelente! Todos os livros estão guardados no estoque no momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modalCadastro" class="modal"> 
        <div class="modal-content">
            <button class="close-modal" onclick="fecharModalCadastro()">&times;</button>
            <h2>Novo Livro</h2>
            <p class="subtitle">Insira as informações básicas e envie o arquivo de imagem da capa</p>
            <form action="cadastrar_livro_action.php" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label>Título do Livro</label>
                    <input type="text" name="titulo" required>
                </div>
                <div class="input-group">
                    <label>Autor</label>
                    <input type="text" name="autor" required>
                </div>
                <div class="input-group">
                    <label>Quantidade em Estoque</label>
                    <input type="number" name="estoque" min="0" required>
                </div>
                <div class="input-group">
                    <label>Upload da Capa (JPG ou PNG)</label>
                    <input type="file" name="capa_arquivo" accept="image/jpeg, image/png" required style="border: 1px dashed #cbd5e1; padding: 8px; border-radius: 6px; background: #f8fafc;">
                </div>
                <button type="submit" class="btn" style="margin-top: 10px;">Salvar no Estoque</button>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="fecharModalEditar()">&times;</button> 
            <h2>Editar Livro</h2>
            <form action="editar_livro_action.php" method="POST" enctype="multipart/form-data">
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
                    <label>Substituir Capa (Opcional)</label>
                    <input type="file" name="capa_arquivo" accept="image/jpeg, image/png" style="border: 1px dashed #cbd5e1; padding: 8px; border-radius: 6px; background: #f8fafc;">
                </div>
                <button type="submit" class="btn" style="margin-top: 10px;">Atualizar Livro</button>
            </form>
        </div>
    </div>

    <div id="modalPrazo" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <button class="close-modal" onclick="fecharModalPrazo()">&times;</button> 
            <h2>Alterar Devolução</h2>
            <p class="subtitle" id="texto_modal_prazo">Ajuste o prazo limite para o livro</p>
            
            <form action="editar_prazo_action.php" method="POST">
                <input type="hidden" id="prazo_aluguel_id" name="aluguel_id">
                <input type="hidden" name="acao" value="salvar">
                
                <div class="input-group">
                    <label>Nova Data de Devolução</label>
                    <input type="date" id="nova_data_prazo" name="nova_data" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-family: inherit;">
                </div>
                
                <button type="submit" class="btn" style="margin-top: 15px; width: 100%;">Atualizar Prazo</button>
            </form>
        </div>
    </div>

    <script>
        // Modal de Cadastro
        function abrirModalCadastro() { document.getElementById('modalCadastro').style.display = 'flex'; }
        function fecharModalCadastro() { document.getElementById('modalCadastro').style.display = 'none'; }
        
        // Modal de Edição de Livros
        function fecharModalEditar() { document.getElementById('modalEditar').style.display = 'none'; }
        function editarLivro(id, titulo, autor, estoque) {
            document.getElementById('editar_id').value = id;
            document.getElementById('editar_titulo').value = titulo;
            document.getElementById('editar_autor').value = autor;
            document.getElementById('editar_estoque').value = estoque;
            document.getElementById('modalEditar').style.display = 'flex';
        }

        // Parte para editar o prazo de devolução dos livros
        function abrirModalPrazo(idAluguel, dataAtual, tituloLivro) {
            document.getElementById('prazo_aluguel_id').value = idAluguel;
            document.getElementById('nova_data_prazo').value = dataAtual;
            document.getElementById('texto_modal_prazo').innerText = "Ajuste o prazo limite para: " + tituloLivro;
            document.getElementById('modalPrazo').style.display = 'flex';
        }
        function fecharModalPrazo() { document.getElementById('modalPrazo').style.display = 'none'; }

        // Validações e Caixas de Diálogo de Confirmação Críticas
        function confirmarExclusao(id, titulo) {
            if (confirm("Tem certeza que deseja remover o livro '" + titulo + "' permanentemente do sistema?")) {
                window.location.href = "admin_estoque.php?excluir=" + id;
            }
        }

        // Função JavaScript adaptada para exibir notificação de multa ativa 
        function confirmarDevolucaoComMulta(idAluguel, livro, usuario, valorMulta) {
            let mensagem = "Confirmar que o leitor '" + usuario + "' devolveu o livro '" + livro + "' físico?";
            
            if (valorMulta !== "0,00") {
                mensagem += "\n\n⚠️ ATENÇÃO FINANCEIRA: Este livro está atrasado!\nCobrar o valor de R$ " + valorMulta + " referente à multa antes de confirmar.";
            }

            if (confirm(mensagem)) {
                window.location.href = "admin_estoque.php?devolver=" + idAluguel;
            }
        }

        // Fecha as modais se clicar na parte escura do fundo
        window.onclick = function(event) {
            let modalCad = document.getElementById('modalCadastro');
            let modalEdi = document.getElementById('modalEditar');
            let modalPrz = document.getElementById('modalPrazo');
            if (event.target == modalCad) { modalCad.style.display = 'none'; }
            if (event.target == modalEdi) { modalEdi.style.display = 'none'; }
            if (event.target == modalPrz) { modalPrz.style.display = 'none'; }
        }
    </script>
</body>
</html>