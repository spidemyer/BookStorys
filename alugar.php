<?php
session_start();
require_once 'conexao.php'; 

header('Content-Type: application/json'); // define o tipo de resposta como json

if (!isset($_SESSION['user_logged'])) {  //confirma se o usuário está logado
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado. Usuário não autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) { 
    $livro_id = intval($_POST['id']);
    $usuario_id = $_SESSION['user_id']; // Resgata o ID do usuário logado na sessão

    try {
        $conn->beginTransaction(); // Garante a consistência 

        // Verifica se o livro existe e ainda tem estoque disponível para aluguel
        $stmtCheck = $conn->prepare("SELECT estoque FROM livros WHERE id = ? FOR UPDATE"); 
        $stmtCheck->execute([$livro_id]);
        $livro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($livro && $livro['estoque'] > 0) {
            
            // Reduz o estoque do livro em 1 unidade
            $stmtUpdate = $conn->prepare("UPDATE livros SET estoque = estoque - 1 WHERE id = ?");
            $stmtUpdate->execute([$livro_id]);

            // Define e calcula as regras de fuso horário e prazos
            date_default_timezone_set('America/Sao_Paulo');
            $dataAluguel = date('Y-m-d'); // Data atual formatada para o banco
            $dataDevolucaoPrevista = date('Y-m-d', strtotime('+7 days'));
            $dataDevolucaoExibicao = date('d/m/Y', strtotime('+7 days'));

            // Insere o registro incluindo explicitamente a data_aluguel para evitar conflitos no Postgres
            $sqlAluguel = "INSERT INTO alugueis (usuario_id, livro_id, data_aluguel, data_devolucao_prevista, status) 
                           VALUES (?, ?, ?, ?, 'Pendente')";
            $stmtAluguel = $conn->prepare($sqlAluguel);
            $stmtAluguel->execute([$usuario_id, $livro_id, $dataAluguel, $dataDevolucaoPrevista]);

            // Confirma todas as operações simultaneamente
            $conn->commit();

            echo json_encode([ 
                'sucesso' => true,
                'data_devolucao' => $dataDevolucaoExibicao
            ]);
            exit;
        } else {
            $conn->rollBack(); // Reverte alterações se o livro acabou de esgotar ou não existe
            $mensagemErro = $livro ? 'Desculpe, este livro acabou de esgotar no estoque!' : 'Livro não encontrado.';
            echo json_encode(['sucesso' => false, 'mensagem' => $mensagemErro]);
            exit;
        }
    } catch (Exception $e) { 
        if ($conn->inTransaction()) {
            $conn->rollBack();
        } 
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao processar transação: ' . $e->getMessage()]);
        exit;
    }
} else { 
    echo json_encode(['sucesso' => false, 'mensagem' => 'Requisição inválida.']);
    exit;
}
?>