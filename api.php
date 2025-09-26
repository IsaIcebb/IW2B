<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "arquivo";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Falha na conexão: " . $conn->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['pesquisa'])) {
            $pesquisa = "%" . $_GET['pesquisa'] . "%";
            $stmt = $conn->prepare("
                SELECT * FROM pets 
                WHERE nome_dono LIKE ? OR nome_pet LIKE ?
                ORDER BY id DESC
            ");
            $stmt->bind_param("ss", $pesquisa, $pesquisa);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT * FROM pets ORDER BY id DESC");
        }

        $retorno = [];
        while ($linha = $result->fetch_assoc()) {
            $retorno[] = $linha;
        }

        echo json_encode($retorno);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("
            INSERT INTO pets (nome_dono, email, telefone, rg, nome_pet, idade_pet, raca_pet) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssssis",
            $data['nome_dono'],
            $data['email'],
            $data['telefone'],
            $data['rg'],
            $data['nome_pet'],
            $data['idade_pet'],
            $data['raca_pet']
        );
        $stmt->execute();

        echo json_encode(["status" => "ok", "insert_id" => $stmt->insert_id]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("
            UPDATE pets 
            SET nome_dono=?, email=?, telefone=?, rg=?, nome_pet=?, idade_pet=?, raca_pet=? 
            WHERE id=?
        ");
        $stmt->bind_param(
            "sssssisi",
            $data['nome_dono'],
            $data['email'],
            $data['telefone'],
            $data['rg'],
            $data['nome_pet'],
            $data['idade_pet'],
            $data['raca_pet'],
            $data['id']
        );
        $stmt->execute();

        echo json_encode(["status" => "ok"]);
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("DELETE FROM pets WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            echo json_encode(["status" => "ok"]);
        } else {
            echo json_encode(["error" => "ID não informado"]);
        }
        break;
}

$conn->close();
?>
