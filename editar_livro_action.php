<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_logged']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_estoque.php");
    exit;
}

// Recebe as variáveis do formulário de edição
$id      = (int)($_POST['id'] ?? 0);
$titulo  = trim($_POST['titulo'] ?? '');
$autor   = trim($_POST['autor'] ?? '');
$estoque = isset($_POST['estoque']) ? (int)$_POST['estoque'] : 0;

if ($id <= 0 || empty($titulo) || empty($autor) || $estoque < 0) { //
    header("Location: admin_estoque.php?mensagem=Dados inválidos para edição.&tipo=erro");
    exit;
}

try {
    $url_capa = trim($_POST['url_capa'] ?? '');

    // Se o usuário apagar o campo e deixar vazio, define uma imagem padrão para não quebrar a vitrine
    if (empty($url_capa)) {
        $url_capa = 'img/default-cover.jpg';
    }
    $sql = "UPDATE livros SET titulo = :titulo, autor = :autor, estoque = :estoque, url_capa = :url_capa WHERE id = :id";
    $stmt = $conn->prepare($sql);
    
    // Vincula todos os parâmetros de forma segura 
    $stmt->bindValue(':titulo', $titulo);
    $stmt->bindValue(':autor', $autor);
    $stmt->bindValue(':estoque', $estoque, PDO::PARAM_INT);
    $stmt->bindValue(':url_capa', $url_capa); 
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    
    $stmt->execute();

    // Redireciona com mensagem de sucesso completa
    header("Location: admin_estoque.php?mensagem=Livro e capa atualizados com sucesso!&tipo=sucesso");
    exit;

} catch (PDOException $e) {
    $erro_msg = urlencode("Erro ao atualizar banco: " . $e->getMessage());
    header("Location: admin_estoque.php?mensagem=" . $erro_msg . "&tipo=erro");
    exit;
}