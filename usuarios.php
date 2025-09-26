<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "arquivo";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "falha na conexão: " . $conn->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

switch ($method) {
    case 'GET':
        if (!empty($_GET['pesquisa'])) {
            $pesquisa = "%" . $_GET['pesquisa'] . "%";

           
            $stmt = $conn->prepare("SELECT ID, Login, Nome, Email, Ativo FROM usuarios WHERE Login LIKE ? OR Nome LIKE ? ORDER BY Nome ASC");
            $stmt->bind_param("ss", $pesquisa, $pesquisa);
        } else {
         
            $stmt = $conn->prepare("SELECT ID, Login, Nome, Email, Ativo FROM usuarios ORDER BY ID DESC");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();

        $retorno = [];
        while ($linha = $result->fetch_assoc()) {
            $retorno[] = $linha;
        }
        
        echo json_encode($retorno);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        $senha_hash = password_hash($data['Senha'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuarios (Login, Nome, Email, Senha, Ativo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $data['Login'], $data['Nome'], $data['Email'], $senha_hash, $data['Ativo']);
        $stmt->execute();

        echo json_encode(["status" => "ok", "message" => "Usuário criado com sucesso!", "insert_id" => $stmt->insert_id]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        
   
        if (!empty($data['Senha'])) {
            $senha_hash = password_hash($data['Senha'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET Login=?, Nome=?, Email=?, Senha=?, Ativo=? WHERE ID=?");
            $stmt->bind_param("ssssii", $data['Login'], $data['Nome'], $data['Email'], $senha_hash, $data['Ativo'], $data['ID']);
        } else {
     
            $stmt = $conn->prepare("UPDATE usuarios SET Login=?, Nome=?, Email=?, Ativo=? WHERE ID=?");
            $stmt->bind_param("sssii", $data['Login'], $data['Nome'], $data['Email'], $data['Ativo'], $data['ID']);
        }
        $stmt->execute();

        echo json_encode(["status" => "ok", "message" => "Usuário atualizado com sucesso!"]);
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["status" => "ok", "message" => "Usuário excluído com sucesso!"]);
        break;
}

$conn->close();
?>
