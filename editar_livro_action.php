<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin_logged']) || $_SERVER['REQUEST_METHOD'] !== 'POST') { //confirma se o adm está logado, se não estiver redireciona para a página de login
    header("Location: login_admin.php");
    exit;
}

$id      = (int)($_POST['id'] ?? 0);
$titulo  = trim($_POST['titulo'] ?? '');
$autor   = trim($_POST['autor'] ?? '');
$estoque = isset($_POST['estoque']) ? (int)$_POST['estoque'] : 0;

if ($id <= 0 || empty($titulo) || empty($autor) || $estoque < 0) { 
    header("Location: admin_estoque.php?mensagem=" . urlencode("Dados inválidos para edição.") . "&tipo=erro");
    exit;
}


// Limite Máximo de 50 CARACTERES 
if (mb_strlen($titulo, 'UTF-8') > 50 || mb_strlen($autor, 'UTF-8') > 50) {
    header("Location: admin_estoque.php?mensagem=" . urlencode("Erro: O título ou autor excederam o limite máximo de 50 caracteres!") . "&tipo=erro");
    exit;
}

try {
    // Busca a imagem atual cadastrada
    $stmt_busca = $conn->prepare("SELECT url_capa FROM livros WHERE id = ?");
    $stmt_busca->execute([$id]);
    $livro_atual = $stmt_busca->fetch(PDO::FETCH_ASSOC);
    $url_capa = $livro_atual['url_capa'] ?? 'default-cover.jpg';

    // Verifica se uma nova imagem foi enviada no formulário
    if (isset($_FILES['capa_arquivo']) && $_FILES['capa_arquivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['capa_arquivo']['tmp_name'];
        $fileName    = $_FILES['capa_arquivo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $extensionsPermitidas = ['jpg', 'jpeg', 'png'];
        
        if (in_array($fileExtension, $extensionsPermitidas)) { // Verifica se a extensão do arquivo é permitida
            $novoNome = time() . '_update_' . uniqid() . '.' . $fileExtension;
            $dest_path = './' . $novoNome;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Remove a imagem antiga da raiz se ela existir e não for o arquivo padrão
                if (!empty($url_capa) && file_exists($url_capa) && $url_capa !== 'default-cover.jpg') {
                    unlink($url_capa);
                }
                $url_capa = $novoNome; // Atualiza para o novo nome de arquivo que será salvo no banco
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
?>