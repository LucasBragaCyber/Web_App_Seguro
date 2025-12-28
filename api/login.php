<?php
// api/login.php

session_set_cookie_params([
    'lifetime' => 0, 
    'path' => '/', 
    'domain' => '', // Domínio atual
    'secure' => false, 
    'httponly' => true, 
    'samesite' => 'Lax'
]);

session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");

require_once '../includes/db.php';

$response = ["status" => "erro", "mensagem" => "Ocorreu um erro desconhecido."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents("php://input"));

    // 1. VERIFICAR SE OS DADOS CRIPTOGRAFADOS EXISTEM
    if (isset($dados->encryptedKey) && isset($dados->encryptedData)) {

        // 2. CARREGAR A CHAVE PRIVADA
        $privateKeyPath = __DIR__ . '/../includes/chaves/privada.pem';
        if (!file_exists($privateKeyPath)) {
            http_response_code(500);
            $response["mensagem"] = "Erro crítico: Chave do servidor não encontrada.";
            echo json_encode($response);
            exit;
        }
        $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));

        if ($privateKey === false) {
            http_response_code(500);
            $response["mensagem"] = "Erro ao carregar a chave do servidor.";
            echo json_encode($response);
            exit;
        }
        
        // 3. DESCRIPTOGRAFAR A CHAVE SIMÉTRICA E O IV (COM RSA)
        $encryptedKey = base64_decode($dados->encryptedKey);
        if (openssl_private_decrypt($encryptedKey, $decryptedKeyJson, $privateKey) === false) {
            http_response_code(400);
            $response["mensagem"] = "Falha ao descriptografar a chave de sessão.";
            echo json_encode($response);
            exit;
        }
        // Aqui o PHP precisa reverter o processo do stringify la do JS, para obter a chave e o IV
        // separadamente, no formato binário original, para que a função do openssl_decrypt funcione.
        $keyData = json_decode($decryptedKeyJson, true);
        $symmetricKey = hex2bin($keyData['key']);
        $iv = hex2bin($keyData['iv']);

        // 4. DESCRIPTOGRAFAR OS DADOS DO USUÁRIO (COM AES-256-CBC)
        $encryptedData = base64_decode($dados->encryptedData);
        // O padding é detectado e removido automaticamente por openssl_decrypt no modo CBC
        $decryptedDataJson = openssl_decrypt($encryptedData, 'aes-256-cbc', $symmetricKey, OPENSSL_RAW_DATA, $iv);

        if ($decryptedDataJson === false) {
            http_response_code(400);
            $response["mensagem"] = "Falha ao descriptografar os dados.";
            echo json_encode($response);
            exit;
        }
        
        $userData = json_decode($decryptedDataJson, true);
        $email = trim($userData['email']);
        $senha = $userData['senha'];


        // --- A PARTIR DAQUI, A LÓGICA ORIGINAL CONTINUA ---
        
        if (!isset($conn) || $conn->connect_error) {
            http_response_code(500);
            $response["mensagem"] = "Erro de conexão com o banco de dados: " . (isset($conn) ? $conn->connect_error : 'Variável $conn não definida');
            echo json_encode($response);
            exit;
        }

        $query = "SELECT id, nome, email, senha, ativado FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt === false) {
            http_response_code(500);
            $response["mensagem"] = "Erro ao preparar a consulta: " . mysqli_error($conn);
            echo json_encode($response);
            exit;
        }

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($usuario = mysqli_fetch_assoc($result)) {
            if ($usuario['ativado'] == 1) {
                if (password_verify($senha, $usuario['senha'])) {
                    http_response_code(200);
                    $response["status"] = "sucesso";
                    $response["mensagem"] = "Login bem-sucedido!";
                    // Regenera o ID da sessão para evitar ataques de fixação de sessão
                    session_regenerate_id(true);
                    $userData = ['id' => $usuario['id'], 'nome' => $usuario['nome'], 'email' => $usuario['email']];
                    $response["usuario"] = $userData;
                    $_SESSION['usuario'] = $userData;
                    // Armazena o timestamp da última atividade na sessão
                    $_SESSION['LAST_ACTIVITY'] = time();
                } else {
                    http_response_code(401);
                    $response["status"] = "erro";
                    $response["mensagem"] = "E-mail ou senha incorretos.";
                }
            } else {
                http_response_code(403);
                $response["status"] = "erro";
                $response["mensagem"] = "Conta ainda não ativada. Verifique seu e-mail.";
            }
        } else {
            http_response_code(401);
            $response["status"] = "erro";
            $response["mensagem"] = "E-mail ou senha incorretos.";
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

    } else {
        http_response_code(400);
        $response["status"] = "erro";
        $response["mensagem"] = "Requisição inválida. Dados criptografados esperados.";
    }
} else {
    http_response_code(405);
    $response["status"] = "erro";
    $response["mensagem"] = "Método não permitido.";
}

echo json_encode($response);
?>