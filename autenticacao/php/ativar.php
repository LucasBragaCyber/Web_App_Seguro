<?php
require '../../includes/db.php';

$token = $_GET['token'] ?? '';

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE token = ? AND ativado = 0");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE usuarios SET ativado = 1, token = NULL WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    header("Location: /BookShell/autenticacao/html/ativacao_sucesso.html");
    exit;
} else {
    header("Location: /BookShell/autenticacao/html/ativacao_falha.html");
    exit;
}
