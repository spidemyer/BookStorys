<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_logged'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_estoque.php");
    exit;
}
//dados do formulário 
$titulo  = trim($_POST['titulo'] ?? '');
$autor   = trim($_POST['autor'] ?? '');
$estoque = isset($_POST['estoque']) ? (int)$_POST['estoque'] : 0;
$url_capa = trim($_POST['url_capa'] ?? '');

// capa (preciso ver isso)
if (empty($url_capa)) {
    $url_capa = 'img/default-cover.jpg'; 
}

//verifica se os campos obrigatórios foram preenchidos e mostra erro caso não 
if (empty($titulo) || empty($autor) || $estoque < 0) {
    header("Location: admin_estoque.php?mensagem=Preencha todos os campos corretamente!&tipo=erro");
    exit;
}

try {
    //define o rf do funcionario e mostra o padrão 
    $funcionario_rf = $_SESSION['user_rf'] ?? '123456';

    $sql = "INSERT INTO livros (titulo, autor, estoque, url_capa, funcionario_rf) 
            VALUES (:titulo, :autor, :estoque, :url_capa, :funcionario_rf)";
            
    $stmt = $conn->prepare($sql);
    
    // Vinculando os parâmetros de forma segura
    $stmt->bindValue(':titulo', $titulo);
    $stmt->bindValue(':autor', $autor);
    $stmt->bindValue(':estoque', $estoque, PDO::PARAM_INT);
    $stmt->bindValue(':url_capa', $url_capa);
    $stmt->bindValue(':funcionario_rf', $funcionario_rf);
    
    $stmt->execute();

    // Redireciona de volta para o painel
    header("Location: admin_estoque.php?mensagem=Novo livro cadastrado com sucesso!&tipo=sucesso");
    exit;

} catch (PDOException $e) {
    // Em caso de falha no banco de dados, devolve o erro para o painel
    $erro_msg = urlencode("Erro ao cadastrar no banco de dados: " . $e->getMessage());
    header("Location: admin_estoque.php?mensagem=" . $erro_msg . "&tipo=erro");
    exit;
}