<?php
// api/catalogo.php
require '../includes/db.php';
require_once __DIR__ . '/../includes/verifica_sessao.php';

$stmt = $conn->prepare("SELECT id, titulo, autor, capa FROM livros WHERE disponibilidade = 1");
$stmt->execute();
$result = $stmt->get_result();
$livros = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($livros);
?>
