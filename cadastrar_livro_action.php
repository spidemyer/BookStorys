<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário é um funcionário logado e se a requisição é POST
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login_admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {  
    header("Location: admin_estoque.php");
    exit;
}

// Validação dos dados recebidos do formulário
$titulo  = trim($_POST['titulo'] ?? ''); 
$autor   = trim($_POST['autor'] ?? '');
$estoque = isset($_POST['estoque']) ? (int)$_POST['estoque'] : 0;
$url_capa = 'img/default-cover.jpg'; 

if (empty($titulo) || empty($autor) || $estoque < 0) { 
    header("Location: admin_estoque.php?mensagem=" . urlencode("Preencha todos os campos corretamente!") . "&tipo=erro");
    exit;
}

// Limite Máximo de 50 CARACTERES 
if (mb_strlen($titulo, 'UTF-8') > 50 || mb_strlen($autor, 'UTF-8') > 50) {
    header("Location: admin_estoque.php?mensagem=" . urlencode("Erro: O título ou autor excederam o limite máximo de 50 caracteres!") . "&tipo=erro");
    exit;
}

// Processamento do Arquivo de Imagem
if (isset($_FILES['capa_arquivo']) && $_FILES['capa_arquivo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['capa_arquivo']['tmp_name'];
    $fileName    = $_FILES['capa_arquivo']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $extensionsPermitidas = ['jpg', 'jpeg', 'png']; 
    
    if (in_array($fileExtension, $extensionsPermitidas)) { // Verifica se a extensão do arquivo é permitida
        $novoNome = time() . '_' . uniqid('book_', true) . '.' . $fileExtension;
        $uploadFileDir = 'img/'; 
        $dest_path = $uploadFileDir . $novoNome;
        
        // Cria a pasta img se ela não existir
        if (!file_exists($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        if (move_uploaded_file($fileTmpPath, $dest_path)) {  // Move o arquivo para o diretório local
            $url_capa = $dest_path;
        } else {
            header("Location: admin_estoque.php?mensagem=" . urlencode("Erro ao mover a imagem para o diretório local.") . "&tipo=erro");
            exit;
        }
    } else {
        header("Location: admin_estoque.php?mensagem=" . urlencode("Extensão de imagem inválida. Use apenas JPG ou PNG.") . "&tipo=erro");
        exit;
    }
} else {
    header("Location: admin_estoque.php?mensagem=" . urlencode("O upload da imagem da capa é obrigatório.") . "&tipo=erro");
    exit;
}

try { // Insere o novo livro no banco de dados
    $funcionario_rf = $_SESSION['user_rf'] ?? '123456'; 

    $sql = "INSERT INTO livros (titulo, autor, estoque, url_capa, funcionario_rf) 
            VALUES (:titulo, :autor, :estoque, :url_capa, :funcionario_rf)";
            
    $stmt = $conn->prepare($sql);
    
    $stmt->bindValue(':titulo', $titulo);
    $stmt->bindValue(':autor', $autor);
    $stmt->bindValue(':estoque', $estoque, PDO::PARAM_INT);
    $stmt->bindValue(':url_capa', $url_capa);
    $stmt->bindValue(':funcionario_rf', $funcionario_rf);
    
    $stmt->execute();

    header("Location: admin_estoque.php?mensagem=" . urlencode("Novo livro cadastrado com sucesso!") . "&tipo=sucesso");
    exit;

} catch (PDOException $e) {
    if ($url_capa !== 'img/default-cover.jpg' && file_exists($url_capa)) {
        unlink($url_capa);
    }
    header("Location: admin_estoque.php?mensagem=" . urlencode("Erro no banco de dados: " . $e->getMessage()) . "&tipo=erro");
    exit;
}
?>