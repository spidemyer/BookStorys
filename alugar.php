<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json'); 

if (!isset($_SESSION['user_logged'])) { // Verifica se o usuário está autenticado
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado. Usuário não autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) { // Verifica se a requisição é POST e se o ID do livro foi enviado
    $livro_id = intval($_POST['id']);

    try {
        $conn->beginTransaction(); //garante que as verificações e atualizações sejam consistentes

        $stmtCheck = $conn->prepare("SELECT estoque FROM livros WHERE id = ? FOR UPDATE"); 
        $stmtCheck->execute([$livro_id]);
        $livro = $stmtCheck->fetch();

        if ($livro && $livro['estoque'] > 0) {
            // Decrementa em 1 a contagem de estoque físico disponível
            $stmtUpdate = $conn->prepare("UPDATE livros SET estoque = estoque - 1 WHERE id = ?");
            $stmtUpdate->execute([$livro_id]);

            // Confirma a operação no banco de dados
            $conn->commit();

            // Define e formata o fuso horário e a regra de negócios dos 7 dias corridos
            date_default_timezone_set('America/Sao_Paulo');
            $dataDevolucao = date('d/m/Y', strtotime('+7 days'));

            echo json_encode([ // Retorna o sucesso e a data de devolução 
                'sucesso' => true,
                'data_devolucao' => $dataDevolucao
            ]);
            exit;
        } else {
            $conn->rollBack(); // Reverte a transação caso o livro esteja esgotado ou não exista
            echo json_encode(['sucesso' => false, 'mensagem' => 'Desculpe, este livro acabou de esgotar no estoque!']);
            exit;
        }
//caso de erro, reverte e retorna a mensagem de falha
    } catch (Exception $e) { 
        if ($conn->inTransaction()) {
            $conn->rollBack();
        } 
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao processar transação: ' . $e->getMessage()]);
        exit;
    }
} else { //caso de outro tipo de erro
    echo json_encode(['sucesso' => false, 'mensagem' => 'Requisição inválida.']);
    exit;
}