<?php
// conexao.php

$host    = 'localhost';
$port    = '5432'; // Porta padrão do Postgres
$db_name = 'bookstorys';
$usuario = 'postgres'; // Usuário padrão do Postgres
$senha   = 'postgres'; // Insira a senha que você configurou no seu Postgres

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db_name", $usuario, $senha);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco PostgreSQL do BookStorys: " . $e->getMessage());
}
?>