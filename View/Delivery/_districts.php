<?php
require_once __DIR__ . '/../../Model/Delivery/Database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->query("SELECT district_id AS id, district_name AS name, province_name AS province 
                    FROM districts 
                    WHERE is_active = 1 
                    ORDER BY district_name");

$sriLankanDistricts = [];
while ($row = $stmt->fetch()) {
    $sriLankanDistricts[$row['id']] = [
        'name' => $row['name'],
        'province' => $row['province']
    ];
}
?>