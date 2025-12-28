<?php
// api/favoritos.php
require_once __DIR__ . '/../includes/verifica_sessao.php';
require '../includes/db.php';

$id_usuario = $_SESSION['usuario']['id'];

$query = "SELECT l.* FROM favoritos f
          JOIN livros l ON f.id_livro = l.id
          WHERE f.id_usuario = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$favoritos = array();
while ($livro = mysqli_fetch_assoc($result)) {
    $favoritos[] = $livro;
}

echo json_encode($favoritos);

mysqli_stmt_close($stmt);
?>
