<?php
// api/registrar.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../includes/db.php';
require_once '../mailer.php';

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
        
        $keyData = json_decode($decryptedKeyJson, true);
        $symmetricKey = hex2bin($keyData['key']);
        $iv = hex2bin($keyData['iv']);

        // 4. DESCRIPTOGRAFAR OS DADOS DO USUÁRIO (COM AES-256-CBC)
        $encryptedData = base64_decode($dados->encryptedData);
        $decryptedDataJson = openssl_decrypt($encryptedData, 'aes-256-cbc', $symmetricKey, OPENSSL_RAW_DATA, $iv);

        if ($decryptedDataJson === false) {
            http_response_code(400);
            $response["mensagem"] = "Falha ao descriptografar os dados do usuário.";
            echo json_encode($response);
            exit;
        }
        
        $userData = json_decode($decryptedDataJson, true);

        // --- 5. VALIDAÇÃO DA FORÇA DA SENHA NO SERVIDOR ---
        $senha_plaintext = $userData['senha'] ?? '';
        
        $temMaiuscula = preg_match('/[A-Z]/', $senha_plaintext);
        $temNumero = preg_match('/[0-9]/', $senha_plaintext);
        $temMinimo8 = strlen($senha_plaintext) >= 8;

        if (!$temMaiuscula || !$temNumero || !$temMinimo8) {
            http_response_code(400); // Bad Request
            $response["mensagem"] = "A senha precisa ter no mínimo 8 caracteres, 1 letra maiúscula 1 um número.";
            echo json_encode($response);
            exit; // Interrompe o script
        }
        // --- FIM DA VALIDAÇÃO DE SENHA ---
        
        // --- A PARTIR DAQUI, A LÓGICA ORIGINAL CONTINUA COM OS DADOS DESCRIPTOGRAFADOS ---
        
        $nome = trim($userData['nome']);
        $email = trim($userData['email']);
        $telefone = trim($userData['telefone']);
        $senha = password_hash($userData['senha'], PASSWORD_DEFAULT); // Hash da senha descriptografada
        $token = md5(uniqid(rand(), true));

        // Validar dados descriptografados
        if (empty($nome) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($telefone) || empty($userData['senha'])) {
            http_response_code(400);
            $response["mensagem"] = "Dados inválidos ou incompletos após a descriptografia.";
            echo json_encode($response);
            exit;
        }

        if (!isset($conn) || $conn->connect_error) {
            http_response_code(500);
            $response["mensagem"] = "Erro de conexão com o banco de dados.";
            echo json_encode($response);
            exit;
        }

        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            http_response_code(409); // Conflict
            $response["status"] = "erro";
            $response["mensagem"] = "Este e-mail já está cadastrado.";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (email, telefone, nome, senha, token, ativado) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt_insert->bind_param("sssss", $email, $telefone, $nome, $senha, $token);

            if ($stmt_insert->execute()) {
                if (function_exists('enviarEmailAtivacao')) {
                    if (enviarEmailAtivacao($email, $nome, $token)) {
                        http_response_code(201);
                        $response["status"] = "sucesso";
                        $response["mensagem"] = "Usuário cadastrado com sucesso! Verifique seu e-mail para ativar a conta.";
                    } else {
                        http_response_code(207);
                        $response["status"] = "parcial";
                        $response["mensagem"] = "Usuário cadastrado, mas houve um erro ao enviar o e-mail de ativação.";
                    }
                } else {
                    http_response_code(201);
                    $response["status"] = "sucesso";
                    $response["mensagem"] = "Usuário cadastrado com sucesso! (Ativação pendente)";
                }
            } else {
                http_response_code(500);
                $response["mensagem"] = "Erro ao cadastrar usuário: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
        $conn->close();

    } else {
        http_response_code(400);
        $response["status"] = "erro";
        $response["mensagem"] = "Requisição inválida. Dados criptografados esperados.";
    }
} else {
    http_response_code(405);
    $response["mensagem"] = "Método não permitido.";
}

echo json_encode($response);
?>