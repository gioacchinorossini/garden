<?php
require_once __DIR__ . '/db.php';

/**
 * Get all properties from MySQL database
 */
function get_all_properties($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email
            FROM `properties` p
            LEFT JOIN `users` u ON p.owner_id = u.id
            ORDER BY p.`id` DESC
        ");
        $db_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($db_properties as $p) {
            $amenities = !empty($p['amenities']) ? array_map('trim', explode(',', $p['amenities'])) : ['Electricity', 'Water', 'Lighting'];

            $formatted[] = [
                'id' => (int)$p['id'],
                'owner_id' => (int)($p['owner_id'] ?? 0),
                'landowner' => $p['owner_name'] ?? 'Juan Property Owner',
                'owner_name' => $p['owner_name'] ?? 'Juan Property Owner',
                'owner_phone' => $p['owner_phone'] ?? '',
                'owner_email' => $p['owner_email'] ?? '',
                'title' => $p['title'] ?? 'Street Food Property',
                'space_type' => $p['space_type'] ?? 'food_park',
                'property_type' => $p['space_type'] ?? 'food_park',
                'permit_no' => $p['permit_no'] ?? '',
                'permit_document_path' => $p['permit_document_path'] ?? '',
                'is_verified' => (int)($p['is_verified'] ?? 0),
                'address' => $p['address'] ?? '',
                'latitude' => (float)($p['latitude'] ?? 14.5492),
                'longitude' => (float)($p['longitude'] ?? 120.9856),
                'area' => (float)($p['total_area'] ?? 0),
                'total_area' => (float)($p['total_area'] ?? 0),
                'status' => $p['status'] ?? 'pending',
                'reason' => $p['rejection_reason'] ?? '',
                'rejection_reason' => $p['rejection_reason'] ?? '',
                'description' => $p['description'] ?? '',
                'amenities' => $amenities,
                'crops' => $amenities, // fallback for legacy crop badges
                'allowed_seeds' => $amenities,
                'total_plots' => 0,
                'occupied_plots' => 0,
                'available_plots' => 0,
                'created_at' => $p['created_at'] ?? ''
            ];
        }
        return $formatted;
    } catch (\Exception $e) {
        error_log("Error fetching properties: " . $e->getMessage());
        return [];
    }
}

// Backward-compatibility alias
function get_all_lands($pdo) {
    return get_all_properties($pdo);
}

/**
 * Get all stalls from MySQL database
 */
function get_all_stalls($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT s.*, p.title as property_title, p.address as property_address
            FROM `stalls` s
            LEFT JOIN `properties` p ON s.property_id = p.id
            ORDER BY s.`id` ASC
        ");
        $db_stalls = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($db_stalls as $s) {
            $formatted[] = [
                'id' => (int)$s['id'],
                'land_id' => (int)$s['property_id'],
                'property_id' => (int)$s['property_id'],
                'plot_number' => $s['stall_number'] ?? 'Stall 1',
                'stall_number' => $s['stall_number'] ?? 'Stall 1',
                'area' => (float)($s['area'] ?? 0),
                'rental_rate' => (float)($s['rental_rate'] ?? 0),
                'rate_period' => $s['rate_period'] ?? 'monthly',
                'status' => $s['status'] ?? 'available',
                'property_title' => $s['property_title'] ?? '',
                'created_at' => $s['created_at'] ?? ''
            ];
        }
        return $formatted;
    } catch (\Exception $e) {
        error_log("Error fetching stalls: " . $e->getMessage());
        return [];
    }
}

// Backward-compatibility alias
function get_all_plots($pdo) {
    return get_all_stalls($pdo);
}

/**
 * Get all vendor stall applications from MySQL database
 */
function get_all_applications($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT a.*, 
                   u.name as vendor_name, u.email as vendor_email, u.phone as vendor_phone,
                   p.title as property_title, p.address as property_address, p.latitude, p.longitude,
                   s.stall_number, s.rental_rate, s.rate_period
            FROM `stall_applications` a
            LEFT JOIN `users` u ON a.vendor_id = u.id
            LEFT JOIN `properties` p ON a.property_id = p.id
            LEFT JOIN `stalls` s ON a.stall_id = s.id
            ORDER BY a.id DESC
        ");
        $db_apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($db_apps as $r) {
            $formatted[] = [
                'id' => (int)$r['id'],
                'land_id' => (int)($r['property_id'] ?? 0),
                'property_id' => (int)($r['property_id'] ?? 0),
                'plot_id' => (int)($r['stall_id'] ?? 0),
                'stall_id' => (int)($r['stall_id'] ?? 0),
                'gardener' => $r['vendor_name'] ?? 'Maria Street Vendor',
                'vendor_name' => $r['vendor_name'] ?? 'Maria Street Vendor',
                'land_title' => $r['property_title'] ?? 'Food Space',
                'property_title' => $r['property_title'] ?? 'Food Space',
                'plot_num' => $r['stall_number'] ?? 'Stall',
                'stall_number' => $r['stall_number'] ?? 'Stall',
                'rental_rate' => $r['rental_rate'] ?? 0,
                'rate_period' => $r['rate_period'] ?? 'monthly',
                'purpose' => $r['food_concept'] ?? 'Street Food Stall',
                'food_concept' => $r['food_concept'] ?? 'Street Food Stall',
                'duration' => $r['requested_duration'] ?? '3 months',
                'requested_duration' => $r['requested_duration'] ?? '3 months',
                'status' => $r['status'] ?? 'pending',
                'email' => $r['vendor_email'] ?? 'vendor@streetbites.com',
                'phone' => $r['vendor_phone'] ?? '09333456789',
                'notes' => $r['response_notes'] ?? '',
                'response_notes' => $r['response_notes'] ?? '',
                'requested_at' => $r['requested_at'] ?? '',
                'latitude' => (float)($r['latitude'] ?? 0),
                'longitude' => (float)($r['longitude'] ?? 0),
                'land_address' => $r['property_address'] ?? ''
            ];
        }
        return $formatted;
    } catch (\Exception $e) {
        error_log("Error fetching applications: " . $e->getMessage());
        return [];
    }
}

// Backward-compatibility alias
function get_all_requests($pdo) {
    return get_all_applications($pdo);
}
