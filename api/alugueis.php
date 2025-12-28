<?php
// api/alugueis.php
require_once __DIR__ . '/../includes/verifica_sessao.php';
require '../includes/db.php';

$id_usuario = $_SESSION['usuario']['id'];

$stmt = $conn->prepare("
    SELECT a.id AS aluguel_id, l.titulo, l.autor
    FROM alugueis a
    JOIN livros l ON a.id_livro = l.id
    WHERE a.id_usuario = ? AND a.devolvido = 0
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$alugueis = array();
while ($row = $result->fetch_assoc()) {
    $alugueis[] = $row;
}

echo json_encode($alugueis);
?>
