<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json'); 

// Verifica se o usuário comum/cliente está autenticado
if (!isset($_SESSION['user_logged'])) { 
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado. Usuário não autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) { 
    $livro_id = intval($_POST['id']);
    $usuario_id = $_SESSION['user_id']; // Resgata o ID do usuário logado na sessão

    try {
        $conn->beginTransaction(); // Garante a consistência 

        // Verifica se o livro ainda tem estoque disponível para aluguel
        $stmtCheck = $conn->prepare("SELECT estoque FROM livros WHERE id = ? FOR UPDATE"); 
        $stmtCheck->execute([$livro_id]);
        $livro = $stmtCheck->fetch();

        if ($livro && $livro['estoque'] > 0) {
            
            // reduz o estoque do livro em 1 unidade
            $stmtUpdate = $conn->prepare("UPDATE livros SET estoque = estoque - 1 WHERE id = ?");
            $stmtUpdate->execute([$livro_id]);

            // Define e calcula as regras de fuso horário e prazo de 7 dias
            date_default_timezone_set('America/Sao_Paulo');
            $dataDevolucaoPrevista = date('Y-m-d', strtotime('+7 days'));
            $dataDevolucaoExibicao = date('d/m/Y', strtotime('+7 days'));

            // Insere o registro do alguel como Pendente, quando devolver vai ter atualização do status.
            $sqlAluguel = "INSERT INTO alugueis (usuario_id, livro_id, data_devolucao_prevista, status) 
                           VALUES (?, ?, ?, 'Pendente')";
            $stmtAluguel = $conn->prepare($sqlAluguel);
            $stmtAluguel->execute([$usuario_id, $livro_id, $dataDevolucaoPrevista]);

            // Confirma todas as operações simultaneamente no PostgreSQL
            $conn->commit();

            echo json_encode([ 
                'sucesso' => true,
                'data_devolucao' => $dataDevolucaoExibicao
            ]);
            exit;
        } else {
            $conn->rollBack(); // Reverte alterações se o livro acabou de esgotar
            echo json_encode(['sucesso' => false, 'mensagem' => 'Desculpe, este livro acabou de esgotar no estoque!']);
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