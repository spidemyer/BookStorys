<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin_logged']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_estoque.php");
    exit;
}

$id      = (int)($_POST['id'] ?? 0);
$titulo  = trim($_POST['titulo'] ?? '');
$autor   = trim($_POST['autor'] ?? '');
$estoque = isset($_POST['estoque']) ? (int)$_POST['estoque'] : 0;

if ($id <= 0 || empty($titulo) || empty($autor) || $estoque < 0) { // Validação básica dos dados recebidos do formulário
    header("Location: admin_estoque.php?mensagem=" . urlencode("Dados inválidos para edição.") . "&tipo=erro");
    exit;
}

try {
    // Busca a imagem atual cadastrada
    $stmt_busca = $conn->prepare("SELECT url_capa FROM livros WHERE id = ?");
    $stmt_busca->execute([$id]);
    $livro_atual = $stmt_busca->fetch();
    $url_capa = $livro_atual['url_capa'] ?? 'img/default-cover.jpg';

    // Verifica se uma nova imagem foi enviada no formulário
    if (isset($_FILES['capa_arquivo']) && $_FILES['capa_arquivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['capa_arquivo']['tmp_name'];
        $fileName    = $_FILES['capa_arquivo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $extensionsPermitidas = ['jpg', 'jpeg', 'png'];
        
        if (in_array($fileExtension, $extensionsPermitidas)) {
            $novoNome = time() . '_update_' . uniqid() . '.' . $fileExtension;
            $dest_path = 'img/' . $novoNome;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Apaga a imagem antiga física se ela não for o padrão do sistema
                if (!empty($url_capa) && file_exists($url_capa) && $url_capa !== 'img/default-cover.jpg') {
                    unlink($url_capa);
                }
                $url_capa = $dest_path; // Atualiza para o novo caminho
            }
        }
    }

    // Executa a atualização completa
    $sql = "UPDATE livros SET titulo = :titulo, autor = :autor, estoque = :estoque, url_capa = :url_capa WHERE id = :id";
    $stmt = $conn->prepare($sql);
    
    $stmt->bindValue(':titulo', $titulo);
    $stmt->bindValue(':autor', $autor);
    $stmt->bindValue(':estoque', $estoque, PDO::PARAM_INT);
    $stmt->bindValue(':url_capa', $url_capa); 
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    
    $stmt->execute();

    header("Location: admin_estoque.php?mensagem=" . urlencode("Livro e capa atualizados com sucesso!") . "&tipo=sucesso");
    exit;

} catch (PDOException $e) {
    header("Location: admin_estoque.php?mensagem=" . urlencode("Erro ao atualizar banco: " . $e->getMessage()) . "&tipo=erro");
    exit;
}