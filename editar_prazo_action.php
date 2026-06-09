<?php
session_start();
require_once 'conexao.php';

// Validação de segurança para o funcionário/admin
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login_admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aluguel_id = (int)$_POST['aluguel_id'];
    $acao = $_POST['acao'] ?? 'salvar'; // 'salvar' para data manual ou 'renovar' para +7 dias

    try {
        if ($acao === 'renovar') {
            //renova o emprestimo e da mais 7 dias para a devolução 
            $stmt_busca = $conn->prepare("SELECT data_devolucao_prevista FROM alugueis WHERE id = ?");
            $stmt_busca->execute([$aluguel_id]);
            $aluguel = $stmt_busca->fetch();

            if ($aluguel) {
                // Adiciona mais 7 dias consecutivos com base na data previamente gravada
                $nova_data = date('Y-m-d', strtotime($aluguel['data_devolucao_prevista'] . ' + 7 days'));
                
                $stmt_update = $conn->prepare("UPDATE alugueis SET data_devolucao_prevista = ? WHERE id = ?");
                $stmt_update->execute([$nova_data, $aluguel_id]);
                
                header("Location: admin_estoque.php?mensagem=Aluguel renovado por mais 7 dias com sucesso!&tipo=sucesso");
                exit;
            } else {
                header("Location: admin_estoque.php?mensagem=Empréstimo não localizado.&tipo=erro");
                exit;
            }

        } else {
            //para o funcionario conseguir editar a data de devolução manualmente
            $nova_data = $_POST['nova_data'];

            if (!empty($nova_data)) {
                $stmt_update = $conn->prepare("UPDATE alugueis SET data_devolucao_prevista = ? WHERE id = ?");
                $stmt_update->execute([$nova_data, $aluguel_id]);

                header("Location: admin_estoque.php?mensagem=Prazo limite de devolução alterado com sucesso!&tipo=sucesso");
                exit;
            } else {
                header("Location: admin_estoque.php?mensagem=Por favor, selecione uma data válida.&tipo=erro");
                exit;
            }
        }

    } catch (PDOException $e) {
        header("Location: admin_estoque.php?mensagem=Erro ao atualizar prazos: " . urlencode($e->getMessage()) . "&tipo=erro");
        exit;
    }
} else {
    header("Location: admin_estoque.php");
    exit;
}