<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';

$vendor_id = $_SESSION['user_id'] ?? 3;
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        // Fetch menu items for this vendor
        $stmtMenu = $pdo->prepare("SELECT * FROM `menu_items` WHERE vendor_id = ? ORDER BY id DESC");
        $stmtMenu->execute([$vendor_id]);
        $menu_items = $stmtMenu->fetchAll(PDO::FETCH_ASSOC);

        // Fetch sales logs for this vendor
        $stmtSales = $pdo->prepare("SELECT * FROM `vendor_sales_logs` WHERE vendor_id = ? ORDER BY log_date DESC, id DESC");
        $stmtSales->execute([$vendor_id]);
        $sales_logs = $stmtSales->fetchAll(PDO::FETCH_ASSOC);

        // Also return legacy format so existing UI components continue to function
        $legacy_harvests = [];
        foreach ($sales_logs as $s) {
            $legacy_harvests[] = [
                'id' => (int)$s['id'],
                'crop_name' => $s['item_name'],
                'quantity' => (float)$s['quantity_sold'],
                'unit' => 'servings',
                'harvest_date' => $s['log_date'],
                'plot_num' => 'Stall Sale',
                'notes' => 'Revenue: ₱' . number_format($s['total_revenue'], 2) . (!empty($s['notes']) ? ' — ' . $s['notes'] : '')
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'menu_items' => $menu_items,
                'sales_logs' => $sales_logs,
                'harvests' => $legacy_harvests
            ]
        ]);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = $input['action'] ?? 'add_menu_item';

    // 1. Add Street Food Menu Item
    if ($action === 'add_menu_item') {
        $name = htmlspecialchars(trim($input['item_name'] ?? ''));
        $category = htmlspecialchars(trim($input['category'] ?? 'Street Food'));
        $price = floatval($input['price'] ?? 0);
        $desc = htmlspecialchars(trim($input['description'] ?? ''));

        if (empty($name) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please provide a valid item name and price.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `menu_items` (vendor_id, item_name, category, price, description, is_available)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$vendor_id, $name, $category, $price, $desc]);
            $new_id = (int)$pdo->lastInsertId();

            echo json_encode([
                'status' => 'success',
                'message' => 'Street food item added to menu!',
                'data' => [
                    'id' => $new_id,
                    'item_name' => $name,
                    'category' => $category,
                    'price' => $price,
                    'description' => $desc,
                    'is_available' => 1
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 2. Record Vendor Daily Sale
    if ($action === 'record_sale' || !empty($input['crop_name'])) {
        $item_name = htmlspecialchars(trim($input['item_name'] ?? ($input['crop_name'] ?? '')));
        $qty = intval($input['quantity_sold'] ?? ($input['quantity'] ?? 0));
        $date = htmlspecialchars(trim($input['log_date'] ?? ($input['harvest_date'] ?? date('Y-m-d'))));
        $notes = htmlspecialchars(trim($input['notes'] ?? ''));
        $price = floatval($input['price'] ?? 0);
        $total_revenue = floatval($input['total_revenue'] ?? ($qty * $price));

        if (empty($item_name) || $qty <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Item name and quantity are required.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `vendor_sales_logs` (vendor_id, item_name, quantity_sold, total_revenue, log_date, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$vendor_id, $item_name, $qty, $total_revenue, $date, $notes]);
            $new_id = (int)$pdo->lastInsertId();

            echo json_encode([
                'status' => 'success',
                'message' => 'Sale logged successfully!',
                'data' => [
                    'id' => $new_id,
                    'item_name' => $item_name,
                    'quantity_sold' => $qty,
                    'total_revenue' => $total_revenue,
                    'log_date' => $date
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 3. Toggle Menu Item Availability
    if ($action === 'toggle_availability') {
        $item_id = intval($input['item_id'] ?? 0);
        $is_available = intval($input['is_available'] ?? 1);

        try {
            $stmt = $pdo->prepare("UPDATE `menu_items` SET is_available = ? WHERE id = ? AND vendor_id = ?");
            $stmt->execute([$is_available, $item_id, $vendor_id]);

            echo json_encode(['status' => 'success', 'message' => 'Availability updated!']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    exit;
}
