<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set defined constant for API request
define('API_REQUEST', true);

// Include database connection
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, name, email, role, phone, status, DATE(created_at) as created FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll();
        echo json_encode([
            'status' => 'success',
            'data' => [
                'users' => $users
            ]
        ]);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to retrieve users: ' . $e->getMessage()
        ]);
    }
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = isset($input['action']) ? $input['action'] : '';

    if ($action === 'create_user') {
        $name = isset($input['name']) ? htmlspecialchars(trim($input['name'])) : '';
        $email = isset($input['email']) ? htmlspecialchars(trim($input['email'])) : '';
        $role = isset($input['role']) ? htmlspecialchars(trim($input['role'])) : 'gardener';
        $phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : '';

        if (empty($name) || empty($email)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Full name and email address are required.'
            ]);
            exit;
        }

        // Hash default password
        $defaultPassword = password_hash('password123', PASSWORD_BCRYPT);

        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Email address is already registered.'
                ]);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, status) VALUES (:name, :email, :password, :role, :phone, 'active')");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $defaultPassword,
                'role' => $role,
                'phone' => $phone
            ]);
            $newId = $pdo->lastInsertId();

            echo json_encode([
                'status' => 'success',
                'message' => 'User account created successfully!',
                'data' => [
                    'id' => $newId,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'phone' => $phone,
                    'status' => 'active',
                    'created' => date('Y-m-d')
                ]
            ]);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create user: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    if ($action === 'update_user') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $name = isset($input['name']) ? htmlspecialchars(trim($input['name'])) : '';
        $email = isset($input['email']) ? htmlspecialchars(trim($input['email'])) : '';
        $role = isset($input['role']) ? htmlspecialchars(trim($input['role'])) : '';
        $phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : '';

        if (!$id || empty($name) || empty($email) || empty($role)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'All fields are required.'
            ]);
            exit;
        }

        try {
            // Check if email already exists for another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $stmt->execute(['email' => $email, 'id' => $id]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Email address is already in use by another account.'
                ]);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, role = :role, phone = :phone WHERE id = :id");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'phone' => $phone,
                'id' => $id
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => 'User account updated successfully!'
            ]);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update user: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    if ($action === 'toggle_status') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'User ID is required.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT status FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();

            if ($user) {
                $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
                $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
                $stmt->execute(['status' => $newStatus, 'id' => $id]);

                echo json_encode([
                    'status' => 'success',
                    'message' => 'User status toggled successfully!'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'User not found.'
                ]);
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to toggle status: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    if ($action === 'delete_user') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'User ID is required.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User account deleted successfully!'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'User not found.'
                ]);
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid API action.'
    ]);
    exit;
}
