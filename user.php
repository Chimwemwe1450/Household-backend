<?php

header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Origin");
header("Content-Type: application/json");

ob_start();

require 'db.php'; //

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            $stmt = $pdo->query("SELECT email, password FROM registration");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $users]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['name']) && isset($data['email']) && isset($data['password'])) {
                $name = $data['name'];
                $email = $data['email'];
                $password = $data['password'];

                $stmt = $pdo->prepare("INSERT INTO registration (name, email, password) VALUES (?, ?, ?)");
                $stmt->bindParam(1, $name);
                $stmt->bindParam(2, $email);
                $stmt->bindParam(3, $password);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create user']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'], $data['name'], $data['email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            exit;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE registration SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['email'], $data['id']]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No user found to update']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $data);

        if (!isset($data['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing user ID']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM registration WHERE id = ?");
            $stmt->execute([$data['id']]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No user found to delete']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        break;
}
?>
