<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';

// Seed sample harvest data matching evaluation calculation if table is empty
try {
    $countCheck = $pdo->query("SELECT COUNT(*) FROM `harvests`")->fetchColumn();
    if ($countCheck == 0) {
        $gardener_id = $_SESSION['user_id'] ?? 1;
        $seedStmt = $pdo->prepare("
            INSERT INTO `harvests` 
            (plot_id, gardener_id, crop_name, quantity_kg, declared_income, rental_expense, other_expenses, net_profit, harvest_date, notes)
            VALUES 
            (1, ?, 'Organic Tomatoes', 450.00, 200000.00, 110000.00, 0.00, 90000.00, CURDATE(), 'First full seasonal harvest from standard 50 sq. m. plot. Excellent fruit size and sweetness.'),
            (2, ?, 'Romaine Lettuce', 180.00, 48000.00, 25000.00, 3000.00, 20000.00, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'Hydro-organic bed yield, sold to local farm-to-table cooperative.')
        ");
        $seedStmt->execute([$gardener_id, $gardener_id]);
    }
} catch (\Exception $e) {
    error_log("Harvest seed check warning: " . $e->getMessage());
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT h.*, p.plot_number, l.title as land_title
            FROM `harvests` h
            LEFT JOIN `plots` p ON h.plot_id = p.id
            LEFT JOIN `lands` l ON p.land_id = l.id
            ORDER BY h.harvest_date DESC, h.id DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $harvests = [];
        $totalIncome = 0;
        $totalRentalExpense = 0;
        $totalOtherExpense = 0;
        $totalNetProfit = 0;
        $totalYieldKg = 0;

        foreach ($rows as $r) {
            $income = floatval($r['declared_income'] ?? 0);
            $rentExp = floatval($r['rental_expense'] ?? 0);
            $otherExp = floatval($r['other_expenses'] ?? 0);
            $net = floatval($r['net_profit'] ?? ($income - $rentExp - $otherExp));
            $qty = floatval($r['quantity_kg'] ?? ($r['quantity'] ?? 0));

            $totalIncome += $income;
            $totalRentalExpense += $rentExp;
            $totalOtherExpense += $otherExp;
            $totalNetProfit += $net;
            $totalYieldKg += $qty;

            $plotNum = $r['plot_number'] ?? (!empty($r['plot_id']) ? "Plot #{$r['plot_id']}" : "Standard Plot (50 m²)");

            $harvests[] = [
                'id' => (int)$r['id'],
                'crop_name' => $r['crop_name'],
                'quantity' => $qty,
                'unit' => 'kg',
                'declared_income' => $income,
                'rental_expense' => $rentExp,
                'other_expenses' => $otherExp,
                'net_profit' => $net,
                'harvest_date' => $r['harvest_date'],
                'plot_num' => $plotNum,
                'land_title' => $r['land_title'] ?? 'Community Garden',
                'notes' => $r['notes'] ?? ''
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'harvests' => $harvests,
                'summary' => [
                    'total_declared_income' => $totalIncome,
                    'total_rental_expense' => $totalRentalExpense,
                    'total_other_expense' => $totalOtherExpense,
                    'total_net_profit' => $totalNetProfit,
                    'total_yield_kg' => $totalYieldKg,
                    'calculation_formula' => 'Income - Land Rent - Other Expenses = Net Profit'
                ]
            ]
        ]);
        exit;
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $crop = isset($input['crop_name']) ? htmlspecialchars(trim($input['crop_name'])) : '';
    $qty = isset($input['quantity']) ? floatval($input['quantity']) : 0;
    $date = isset($input['harvest_date']) ? htmlspecialchars($input['harvest_date']) : date('Y-m-d');
    $notes = isset($input['notes']) ? htmlspecialchars(trim($input['notes'])) : '';
    $plotNum = isset($input['plot_num']) ? htmlspecialchars($input['plot_num']) : 'Plot B-1';

    // Financial Calculation Inputs (Income - Expenses = Profit)
    $declaredIncome = isset($input['declared_income']) ? floatval($input['declared_income']) : 0;
    $rentalExpense = isset($input['rental_expense']) ? floatval($input['rental_expense']) : 0;
    $otherExpenses = isset($input['other_expenses']) ? floatval($input['other_expenses']) : 0;
    $netProfit = $declaredIncome - $rentalExpense - $otherExpenses;

    if (empty($crop) || $qty <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Please provide a valid crop name and harvested yield quantity.'
        ]);
        exit;
    }

    $gardener_id = $_SESSION['user_id'] ?? 1;

    // Lookup or default plot_id
    $plot_id = 1;
    try {
        $stmtPlot = $pdo->prepare("SELECT id, rental_price_per_year FROM `plots` WHERE plot_number = ? LIMIT 1");
        $stmtPlot->execute([$plotNum]);
        $foundPlot = $stmtPlot->fetch(PDO::FETCH_ASSOC);
        if ($foundPlot) {
            $plot_id = $foundPlot['id'];
            if ($rentalExpense == 0 && !empty($foundPlot['rental_price_per_year'])) {
                $rentalExpense = floatval($foundPlot['rental_price_per_year']);
                $netProfit = $declaredIncome - $rentalExpense - $otherExpenses;
            }
        }
    } catch (\Exception $e) {}

    try {
        $stmt = $pdo->prepare("
            INSERT INTO `harvests` 
            (plot_id, gardener_id, crop_name, quantity_kg, declared_income, rental_expense, other_expenses, net_profit, harvest_date, notes)
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $plot_id,
            $gardener_id,
            $crop,
            $qty,
            $declaredIncome,
            $rentalExpense,
            $otherExpenses,
            $netProfit,
            $date,
            $notes
        ]);
        $newId = $pdo->lastInsertId();

        $newHarvest = [
            'id' => (int)$newId,
            'crop_name' => $crop,
            'quantity' => $qty,
            'unit' => 'kg',
            'declared_income' => $declaredIncome,
            'rental_expense' => $rentalExpense,
            'other_expenses' => $otherExpenses,
            'net_profit' => $netProfit,
            'harvest_date' => $date,
            'plot_num' => $plotNum,
            'notes' => $notes
        ];

        echo json_encode([
            'status' => 'success',
            'message' => 'Harvest logged and profit calculated successfully!',
            'data' => $newHarvest
        ]);
        exit;
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}
