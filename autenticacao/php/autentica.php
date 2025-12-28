<?php
session_start();
include '../../includes/db.php';

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// Validação simples
if (empty($email) || empty($senha)) {
    $_SESSION['mensagem_erro'] = "Preencha todos os campos.";
    header("Location: ../html/autenticacao.html");
    exit;
}

// Consulta ao banco apenas se conta estiver ativada
$query = "SELECT id, nome, email, senha FROM usuarios WHERE email = ? AND ativado = 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($usuario = mysqli_fetch_assoc($result)) {
    if (password_verify($senha, $usuario['senha'])) {
        // Estrutura da sessão
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email']
        ];

        header("Location: ../../autenticado.html");
        exit;
    } else {
        $_SESSION['mensagem_erro'] = "Senha incorreta.";
    }
} else {
    // Verifica se o e-mail existe, mas a conta não está ativada
    $verifica_sql = "SELECT id FROM usuarios WHERE email = ?";
    $verifica_stmt = mysqli_prepare($conn, $verifica_sql);
    mysqli_stmt_bind_param($verifica_stmt, "s", $email);
    mysqli_stmt_execute($verifica_stmt);
    $verifica_result = mysqli_stmt_get_result($verifica_stmt);

    if (mysqli_fetch_assoc($verifica_result)) {
        $_SESSION['mensagem_erro'] = "Conta ainda não ativada. Verifique seu e-mail.";
    } else {
        $_SESSION['mensagem_erro'] = "Usuário não encontrado.";
    }
}

// Redirecionar em caso de falha
header("Location: ../html/autenticacao.html");
exit;
