import json

# Dados da conexão
credenciais = {
    "host": "localhost",
    "user": "braga",
    "pass": "senha123",
    "dbname": "cadastro_bookshell"
}

# Caminhos dos arquivos
original_image = "original.jpg"    # Imagem sem dados esteganografados
output_image = "hidden.jpg"        # Imagem que receberá os dados
marker = "--CRED--"

# Serializa os dados
credenciais_json = json.dumps(credenciais)

# Lê o conteúdo da imagem original (rb -> Read Binary)
with open(original_image, "rb") as f:
    image_data = f.read()

# Anexa as credenciais ao final do arquivo com um marcador
with open(output_image, "wb") as f:
    f.write(image_data)
    f.write(b"\n" + marker.encode() + credenciais_json.encode())

print(f"Credenciais embutidas com sucesso em '{output_image}'")
