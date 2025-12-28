<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Caminhos para os arquivos da lib (ajuste se necessário)
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

function enviarEmailAtivacao($email, $nome, $token)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP (Gmail ou outro)
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shellbooks3@gmail.com';  // e-mail aqui
        $mail->Password   = 'jdisctutnobvqata';       // Senha de app do Gmail
        $mail->SMTPSecure = 'ssl';                    // Ou 'tls'
        $mail->Port       = 465;

        // E-mail de envio
        $mail->setFrom('shellbooks3@gmail.com', 'ShellBooks');
        $mail->addAddress($email, $nome);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmacao de Cadastro';

        // link localhost       http://localhost/BookShell/autenticacao/php/ativar.php?token=
        // link do laragon      http://projetoweb.test/BookShell/autenticacao/php/ativar.php

        //$link = "http://projetoweb.test/BookShell/autenticacao/php/ativar.php?token=" . $token;  // para o Laragon

        $link = "http://localhost/BookShell/autenticacao/php/ativar.php?token=" . $token;          // para o localhost (MAMP)
        $mail->Body = "
            <h3>Olá, $nome!</h3>
            <p>Confirme seu cadastro clicando no botão abaixo:</p>
            <a href='$link' style='padding:10px 20px; background:#28a745; color:white; text-decoration:none;'>Ativar Conta</a>
            <p>Ou copie e cole no navegador: $link</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Erro no envio: " . $mail->ErrorInfo;
        return false;
    }
}
