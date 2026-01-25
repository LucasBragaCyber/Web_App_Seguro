# ☕ BookShell Livraria e Café - Projeto Web App Seguro

![Made with PHP](https://img.shields.io/badge/Made%20with-PHP-blue?logo=php)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white)
![CryptoJS](https://img.shields.io/badge/CryptoJS-%23dd3333?logo=javascript)
![OpenSSL](https://img.shields.io/badge/OpenSSL-4E9A06?logo=openssl&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-%234479A1?logo=mysql)
![Dockerized](https://img.shields.io/badge/Dockerized-Yes-2496ED?logo=docker)
![Security Focused](https://img.shields.io/badge/Security-Focused-success?logo=shield)
![License](https://img.shields.io/badge/License-MIT-green)

Uma aplicação web de cafeteria e aluguel de livros construída com foco em **Segurança Ofensiva e Defensiva** e **Codificação Segura**.

---

## 📖 Sobre o Projeto

O **BookShell** é uma plataforma web que integra a experiência de uma cafeteria com um serviço de aluguel de livros para usuários cadastrados.  
O objetivo principal deste projeto não é apenas entregar as funcionalidades de negócio, mas servir como um **laboratório prático** para implementação de **arquitetura segura**, **mitigação de vulnerabilidades (OWASP Top 10)**, testes de ferramentas de segurança ofensiva **(Pentest)** e práticas de **criptografia avançada**, como também práticas de codificação segura.

---

## 🛡️ Destaques de Segurança (Security by Design)

Diferente de aplicações comuns, o BookShell implementa **camadas de defesa em profundidade**:

### 🔐 1. Criptografia Híbrida de ponta a ponta (Simulação E2EE)

Os dados sensíveis (como credenciais de login) e outras ações do usuário **nunca trafegam em texto plano**, nem mesmo via HTTPS.

**Fluxo:**
- **Front-end:** O cliente obtém uma chave pública RSA do servidor.  
  Gera uma chave simétrica (AES-256) e um IV aleatórios.  
- **Envio:** Os dados são encriptados com AES; a chave AES é encriptada com RSA.  
- **Back-end:** O servidor usa sua chave privada para recuperar a chave AES e, em seguida, descriptografar os dados do usuário.

**Tecnologias:**  
OpenSSL (PHP) e CryptoJS + JSEncrypt (JS)

---

### 🧠 Entendendo a Criptografia Híbrida

Por questões didáticas, mas também para garantir performance e segurança máxima, o projeto utiliza uma abordagem híbrida. O diagrama abaixo detalha o ciclo de vida dos dados durante uma tentativa de login, conforme implementado em `login.js` (Cliente) e `login.php` (Servidor).

#### 🗝️ Legenda das Chaves
* 🟧 **Chave Pública (RSA):** Compartilhada livremente pelo servidor. Usada apenas para *trancar* informações.
* 🟦 **Chave Privada (RSA):** Mantida em segredo absoluto no servidor (`privada.pem`). Única capaz de *destrancar* o que a chave pública fechou.
* 🟩 **Chave Simétrica (AES-256):** Uma chave temporária e descartável, gerada pelo cliente para aquela sessão específica.

#### 1. No Cliente (Navegador)
Antes dos dados saírem do computador do usuário, o seguinte processo ocorre:

1.  **Handshake:** O cliente solicita a **Chave Pública (🟧)** ao servidor.
2.  **Geração de Segredo:** O cliente gera uma **Chave Simétrica (🟩)** aleatória e um IV (Vetor de Inicialização).
3.  **Encriptação dos Dados:** Os dados (como e-mail e senha) são criptografados usando a **Chave Simétrica (🟩)** (Algoritmo AES-256-CBC).
4.  **Proteção da Chave:** Para enviar a **Chave Simétrica (🟩)** com segurança, ela é criptografada usando a **Chave Pública (🟧)** do servidor.
5.  **Envio:** O payload é enviado contendo `{ encryptedKey, encryptedData }`.

#### 2. No Servidor (Back-end)
Ao receber o pacote criptografado, o PHP realiza o processo inverso:

1.  **Recuperação da Chave:** O servidor usa sua **Chave Privada (🟦)** para descriptografar o pacote `encryptedKey`. Isso revela a **Chave Simétrica (🟩)** original gerada pelo usuário.
2.  **Acesso aos Dados:** Com a **Chave Simétrica (🟩)** em mãos, o servidor descriptografa o `encryptedData`, revelando as credenciais (e-mail/senha) em texto plano apenas na memória volátil, pronto para verificação no banco de dados.

> **Por que isso é seguro?** <br> Mesmo que um atacante intercepte a requisição, ele terá os dados trancados pelo AES e a chave do AES trancada pelo RSA. Sem a Chave Privada do servidor, é computacionalmente inviável abrir o pacote.

---

### 🗄️ 2. Proteção de Credenciais do Banco

As credenciais de acesso ao **MySQL** não estão hardcoded em texto plano no código.

- Existe um sistema de **“cofre” (`credenciais.enc`)** onde as configurações de acesso ao DB são armazenadas criptografadas (AES-256-CBC).  
- A aplicação descriptografa essas credenciais **em tempo de execução**, apenas na memória, para realizar a conexão.

---

### 🧱 3. Defesa contra Injeção SQL e XSS

- **SQL Injection:** Uso estrito de *Prepared Statements* em todas as interações com o banco (MySQLi).  
- **XSS & CSRF:** Sanitização rigorosa de inputs e configuração de cookies de sessão com flags `HttpOnly`, `Secure` e `SameSite=Lax`.

---

### 🐳 4. Infraestrutura Isolada

Ambiente **totalmente dockerizado**, garantindo que a aplicação rode em um ambiente **controlado e reproduzível**.

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia |
|--------|-------------|
| **Back-end** | PHP 8+ (Estrutura MVC / API REST) |
| **Front-end** | HTML5, CSS3, Vanilla JavaScript, Bootstrap |
| **Banco de Dados** | MySQL 8 - PhpMyAdmin |
| **Infraestrutura** | Docker & Docker Compose |
| **Bibliotecas de Criptografia** | PHP: OpenSSL / JS: CryptoJS, JSEncrypt |

---

## 🚀 Como Executar o Projeto

### Pré-requisitos

- [Docker](https://www.docker.com/) e Docker Compose instalados.
- [Docker Desktop](https://docs.docker.com/desktop/setup/install/windows-install/) para Windows 

### Passo a Passo

**1. Clone o repositório:**
```bash
git clone https://github.com/LucasBragaCyber/BookShell-secure-app.git
cd BookShell-secure-app
```

**2. Suba os containers:**
- Na raíz do projeto:

```bash
docker-compose up -d --build
```

**3. Configuração de Segurança Inicial (Crítico):**
- Para gerar o arquivo criptografado de conexão com o banco, acesse a seguinte URL no navegador uma única vez:

```yml
http://localhost:8080/BookShell/includes/setup_db.php
```

⚠️ Nota:
1. no `.htaccess` em `includes`, apagar a linha `Require all denied` para poder executar o arquivo de configuração da conexão do banco.
2. Após ver a mensagem de sucesso, o arquivo `credenciais.enc` será criado.
Por segurança, o script `setup_db.php` deve ser removido ou bloqueado em ambiente de produção. Volte no `.htaccess` e escreva novamente `Require all denied`. Assim, o arquivo estará protegido de acesso externo, pelo navegador.

**4. Acessar a Aplicação:**

- No seu navegador, acesse: *http://localhost:8080/BookShell*.

#### Para administrar o banco de dados com interface gráfica (PhpMyAdmin):

- No seu navegador, acesse: *http://localhost:8081/* 

## 🗺️ Roadmap & Próximos Passos
O desenvolvimento é contínuo. As próximas atualizações focarão em:

- [ ] Mais implementações das mitigações do OWASP Top 10.

- [ ] Codificação segura levando em consideração diversas CWEs (Common Weakness Enumeration)

- [ ] Sistema de Logs Centralizado com GrayLog para auditoria de segurança.

- [ ] Painel administrativo para gestão de livros e usuários.

---
#### Desenvolvido por Lucas Bragagnolo 💻🔒
---

