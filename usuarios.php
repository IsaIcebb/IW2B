<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "pacientes";

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

           
            $stmt = $conn->prepare("SELECT ID, telefone, Nome, Email, Ativo FROM pacientes WHERE  Nome LIKE ? ORDER BY Nome ASC");
            $stmt->bind_param("ss", $pesquisa);
        } else {
         
            $stmt = $conn->prepare("SELECT ID, telefone, Nome, Email, Ativo FROM pacientes ORDER BY ID DESC");
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
        
       

        $stmt = $conn->prepare("INSERT INTO pacientes (telefone, Nome, Email,  Ativo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $data['telefone'], $data['Nome'], $data['Email'], $data['Ativo']);
        $stmt->execute();

        echo json_encode(["status" => "ok", "message" => "Usuário criado com sucesso!", "insert_id" => $stmt->insert_id]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php//input"), true);
        
   

        echo json_encode(["status" => "ok", "message" => "Usuário atualizado com sucesso!"]);
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM pacientes WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["status" => "ok", "message" => "Usuário excluído com sucesso!"]);
        break;
}

$conn->close();
?>
