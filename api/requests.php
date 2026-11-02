<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/lands_helper.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $requests = get_all_requests($pdo);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'requests' => $requests
        ]
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = isset($input['action']) ? $input['action'] : '';

    if ($action === 'submit_request') {
        $land_id = isset($input['land_id']) ? intval($input['land_id']) : 0;
        $plot_id = isset($input['plot_id']) ? intval($input['plot_id']) : 0;
        $purpose = isset($input['purpose']) ? htmlspecialchars(trim($input['purpose'])) : '';
        $duration = isset($input['duration']) ? htmlspecialchars(trim($input['duration'])) : '6 months';

        if (!$land_id || !$plot_id || empty($purpose)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        // Retrieve land title
        $stmtLand = $pdo->prepare("SELECT title FROM `lands` WHERE id = ?");
        $stmtLand->execute([$land_id]);
        $land_title = $stmtLand->fetchColumn() ?: "Community Garden";

        // Retrieve plot number
        $stmtPlot = $pdo->prepare("SELECT plot_number FROM `plots` WHERE id = ?");
        $stmtPlot->execute([$plot_id]);
        $plot_num = $stmtPlot->fetchColumn() ?: "Plot";

        $gardener_id = $_SESSION['user_id'] ?? null;
        $gardener_name = $_SESSION['user_name'] ?? '';

        if (!$gardener_id) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Please log in to submit a plot request.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `requests` 
                (gardener_id, gardener_name, land_id, plot_id, land_title, plot_number, purpose, message, requested_duration, status)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $gardener_id, $gardener_name, $land_id, $plot_id,
                $land_title, $plot_num, $purpose, $purpose, $duration
            ]);
            $new_id = (int)$pdo->lastInsertId();

            $newRequest = [
                'id' => $new_id,
                'land_id' => $land_id,
                'plot_id' => $plot_id,
                'gardener' => $gardener_name,
                'land_title' => $land_title,
                'plot_num' => $plot_num,
                'purpose' => $purpose,
                'duration' => $duration,
                'status' => 'pending',
                'notes' => ''
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Plot application submitted successfully! Tracking is available under Requests.',
                'data' => $newRequest
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'make_rental_offer') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $rate = isset($input['offered_rental_rate']) ? floatval($input['offered_rental_rate']) : (isset($input['rental_rate']) ? floatval($input['rental_rate']) : 110000.00);
        $duration = isset($input['rental_duration']) ? htmlspecialchars(trim($input['rental_duration'])) : '1 Year';
        $terms = isset($input['terms_conditions']) ? htmlspecialchars(trim($input['terms_conditions'])) : (isset($input['terms']) ? htmlspecialchars(trim($input['terms'])) : 'Standard 50 sq. m. community garden lease. Permitted organic crops.');

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request ID.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE `requests` 
                SET `status` = 'offer_sent',
                    `offered_rental_rate` = ?,
                    `rental_duration` = ?,
                    `terms_conditions` = ?,
                    `response_notes` = ?
                WHERE `id` = ?
            ");
            $stmt->execute([$rate, $duration, $terms, $terms, $id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Rental offer of ₱' . number_format($rate, 2) . ' sent to gardener for approval!'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'counter_offer') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $counterRate = isset($input['counter_rate']) ? floatval($input['counter_rate']) : 0.0;
        $counterNotes = isset($input['counter_notes']) ? htmlspecialchars(trim($input['counter_notes'])) : '';

        if (!$id || $counterRate <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please propose a valid rental counter-offer amount.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE `requests` 
                SET `status` = 'counter_offered',
                    `counter_rental_rate` = ?,
                    `counter_notes` = ?,
                    `counter_at` = NOW()
                WHERE `id` = ?
            ");
            $stmt->execute([$counterRate, $counterNotes, $id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Counter-offer of ₱' . number_format($counterRate, 2) . '/yr submitted to landowner!'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'accept_counter_offer') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request ID.']);
            exit;
        }

        try {
            $stmtReq = $pdo->prepare("SELECT plot_id, gardener_name, counter_rental_rate FROM `requests` WHERE id = ?");
            $stmtReq->execute([$id]);
            $reqData = $stmtReq->fetch(PDO::FETCH_ASSOC);

            if (!$reqData) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Application not found.']);
                exit;
            }

            $finalRate = !empty($reqData['counter_rental_rate']) ? floatval($reqData['counter_rental_rate']) : 110000.00;

            $stmt = $pdo->prepare("
                UPDATE `requests` 
                SET `status` = 'approved',
                    `offered_rental_rate` = ?,
                    `response_notes` = 'Landowner accepted gardener counter-offer.'
                WHERE `id` = ?
            ");
            $stmt->execute([$finalRate, $id]);

            if (!empty($reqData['plot_id'])) {
                $pdo->prepare("
                    UPDATE `plots` 
                    SET `status` = 'occupied',
                        `farmer_name` = ?,
                        `rental_price_per_year` = ?,
                        `lease_end` = DATE_ADD(CURDATE(), INTERVAL 1 YEAR)
                    WHERE `id` = ?
                ")->execute([$reqData['gardener_name'], $finalRate, $reqData['plot_id']]);
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Counter-offer accepted! Lease agreement is now active at ₱' . number_format($finalRate, 2) . '/year.'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'gardener_decision') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $decision = isset($input['decision']) ? htmlspecialchars(trim($input['decision'])) : '';

        if (!$id || !in_array($decision, ['approved', 'declined'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid decision parameters.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE `requests` 
                SET `status` = ?
                WHERE `id` = ?
            ");
            $stmt->execute([$decision, $id]);

            // If approved by gardener, activate plot occupancy
            if ($decision === 'approved') {
                $stmtReq = $pdo->prepare("SELECT plot_id, gardener_name, offered_rental_rate FROM `requests` WHERE id = ?");
                $stmtReq->execute([$id]);
                $reqData = $stmtReq->fetch(PDO::FETCH_ASSOC);
                if ($reqData && !empty($reqData['plot_id'])) {
                    $rentRate = !empty($reqData['offered_rental_rate']) ? floatval($reqData['offered_rental_rate']) : 110000.00;
                    $pdo->prepare("
                        UPDATE `plots` 
                        SET `status` = 'occupied', 
                            `farmer_name` = ?,
                            `rental_price_per_year` = ?,
                            `lease_end` = DATE_ADD(CURDATE(), INTERVAL 1 YEAR)
                        WHERE `id` = ?
                    ")->execute([$reqData['gardener_name'], $rentRate, $reqData['plot_id']]);
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => $decision === 'approved' 
                    ? 'Rental offer accepted! Your plot lease agreement is now active.' 
                    : 'Rental offer declined.'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'update_status') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $status = isset($input['status']) ? htmlspecialchars(trim($input['status'])) : '';
        $notes = isset($input['notes']) ? htmlspecialchars(trim($input['notes'])) : '';

        if (!$id || !in_array($status, ['approved', 'rejected', 'offer_sent', 'declined'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action parameters.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE `requests` 
                SET `status` = ?, `response_notes` = ?
                WHERE `id` = ?
            ");
            $stmt->execute([$status, $notes, $id]);

            // If approved, update plot to occupied
            if ($status === 'approved') {
                $stmtReq = $pdo->prepare("SELECT plot_id, gardener_name FROM `requests` WHERE id = ?");
                $stmtReq->execute([$id]);
                $reqData = $stmtReq->fetch(PDO::FETCH_ASSOC);
                if ($reqData && !empty($reqData['plot_id'])) {
                    $pdo->prepare("UPDATE `plots` SET `status` = 'occupied', `farmer_name` = ? WHERE `id` = ?")
                        ->execute([$reqData['gardener_name'], $reqData['plot_id']]);
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Request status updated successfully!'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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
