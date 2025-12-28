<?php
session_start();
session_unset();
session_destroy();
header("Location: /BookShell/autenticacao/html/autenticacao.html");
exit;
?>
