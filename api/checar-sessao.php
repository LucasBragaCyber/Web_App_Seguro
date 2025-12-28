<?php
// api/checar-sessao.php
// A sessão é iniciada neste arquivo
require_once __DIR__ . '/../includes/verifica_sessao.php';

http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Sessão ativa.']);
exit();

?>