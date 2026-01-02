<?php
// /BookShell/autenticacao/php/recuperarSenha.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';

require '../../includes/db.php'; 

header('Content-Type: application/json');

// Lê JSON (padrão REST) ou POST normal
$input = file_get_contents("php://input");
$data = json_decode($input, true);
$email = $data['email'] ?? $_POST['email'] ?? null;

if (!$email) {
    echo json_encode(["sucesso" => false, "mensagem" => "E-mail não informado."]);
    exit;
}

// 1. Opcional: Verificar se o e-mail existe na tabela de usuários antes de gerar token
// Isso evita spam de tokens para e-mails inexistentes.
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    // E-mail inexistente
    echo json_encode(["sucesso" => false, "mensagem" => "E-mail não encontrado."]);
    exit;
}
$stmt->close();

// 2. Gerar Token e Expiração (5 minutos)
$token = bin2hex(random_bytes(32)); // Gera 64 caracteres
$expiracao = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// 3. Salvar no Banco (MySQLi)
$stmt = $conn->prepare("INSERT INTO recuperar_senha (email, token, expiracao) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $token, $expiracao);

if (!$stmt->execute()) {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao gerar token no banco."]);
    exit;
}
$stmt->close();

// 4. Enviar E-mail
$mail = new PHPMailer(true);
$link = "http://localhost/BookShell/autenticacao/html/verificartrocadesenha.html?token=" . $token;

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'shellbooks3@gmail.com'; 
    $mail->Password   = 'jdisctutnobvqata';     // Senha do App do Gmail
    $mail->SMTPSecure = 'ssl';                  // Ou tls
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('shellbooks3@gmail.com', 'BookShell');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Recuperação de Senha - BookShell';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h3>Recuperação de Senha</h3>
            <p>Clique no link abaixo para criar uma nova senha (válido por 5 minutos):</p>
            <p><a href='$link' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Redefinir Senha</a></p>
        </div>
    ";

    $mail->send();
    echo json_encode(["sucesso" => true, "mensagem" => "Link enviado com sucesso!"]);

} catch (Exception $e) {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro no envio: {$mail->ErrorInfo}"]);
}
?>