<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//PHPMailer/src/PHPMailer.php
//PHPMailer/src/SMTP.php
require '../BookShell/PHPMailer/src/PHPMailer.php';
require '../BookShell/PHPMailer/src/SMTP.php';
require '../BookShell/PHPMailer/src/Exception.php';

session_start();

$email = $_POST['email'] ?? null;

if (!$email) {
    die("E-mail não informado.");
}

// Gera código aleatório
$codigo = rand(100000, 999999);

// Salva código e e-mail na sessão
$_SESSION['codigo_recuperacao'] = $codigo;
$_SESSION['email_recuperacao'] = $email;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'shellbooks3@gmail.com'; // Seu e-mail
    $mail->Password   = 'jdisctutnobvqata';       // Senha de app do Gmail
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->setFrom('shellbooks3@gmail.com', 'BookShell');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Recuperação de senha - BookShell';
    $mail->Body = "
        <h3>Olá!</h3>
        <p>Você solicitou a recuperação de senha no site <strong>BookShell</strong>.</p>
        <p>Seu código de verificação é: <strong>$codigo</strong></p>
        <p>Digite esse código na página de verificação para redefinir sua senha.</p>
    ";

    $mail->send();
    echo "<script>
        alert('Código enviado com sucesso para o e-mail informado!');
        window.location.href = 'http://localhost/BookShell/autenticacao/html/verificartrocadesenha.html';
    </script>";
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
}

// LARAGON: window.location.href = 'http://projetoweb.test/BookShell/autenticacao/html/verificartrocadesenha.html';