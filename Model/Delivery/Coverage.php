<?php
require_once __DIR__ . '/Database.php';

class Coverage {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // CREATE
    public function create($courierId, $originId, $destId, $isActive) {
        if ($this->exists($courierId, $originId, $destId)) {
            return ['success' => false, 'message' => 'This route already exists.'];
        }
        $sql = "INSERT INTO courier_coverage_routes 
                (courier_partner_id, origin_district_id, destination_district_id, is_active) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courierId, $originId, $destId, $isActive ? 1 : 0]);
        return ['success' => true, 'route_id' => $this->db->lastInsertId()];
    }

    // READ - all routes for courier
    public function getAllByCourier($courierId) {
    $sql = "SELECT 
                r.route_id,
                r.origin_district_id AS origin_id,
                od.district_name AS origin_name,
                r.destination_district_id AS destination_id,
                dd.district_name AS destination_name,
                r.is_active,
                DATE_FORMAT(r.created_at, '%b %d, %Y') AS created_date
            FROM courier_coverage_routes r
            INNER JOIN districts od ON r.origin_district_id = od.district_id
            INNER JOIN districts dd ON r.destination_district_id = dd.district_id
            WHERE r.courier_partner_id = ?
            ORDER BY r.route_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courierId]);
        return $stmt->fetchAll();
    }

    // READ - single
    public function getById($routeId, $courierId) {
        $sql = "SELECT * FROM courier_coverage_routes 
                WHERE route_id = ? AND courier_partner_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$routeId, $courierId]);
        return $stmt->fetch();
    }

    // UPDATE
    public function update($routeId, $courierId, $originId, $destId, $isActive) {
        if ($this->existsExcept($courierId, $originId, $destId, $routeId)) {
            return ['success' => false, 'message' => 'This route already exists.'];
        }
        $sql = "UPDATE courier_coverage_routes 
                SET origin_district_id = ?, destination_district_id = ?, is_active = ?
                WHERE route_id = ? AND courier_partner_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$originId, $destId, $isActive ? 1 : 0, $routeId, $courierId]);
        return ['success' => true];
    }

    // TOGGLE active/inactive
    public function toggle($routeId, $courierId) {
        $sql = "UPDATE courier_coverage_routes 
                SET is_active = NOT is_active
                WHERE route_id = ? AND courier_partner_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$routeId, $courierId]);
        $route = $this->getById($routeId, $courierId);
        return ['success' => true, 'is_active' => $route ? $route['is_active'] : 0];
    }

    // DELETE
    public function delete($routeId, $courierId) {
        $sql = "DELETE FROM courier_coverage_routes 
                WHERE route_id = ? AND courier_partner_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$routeId, $courierId]);
        return ['success' => true];
    }

    // Helpers
    private function exists($courierId, $originId, $destId) {
        $sql = "SELECT COUNT(*) FROM courier_coverage_routes 
                WHERE courier_partner_id = ? AND origin_district_id = ? AND destination_district_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courierId, $originId, $destId]);
        return $stmt->fetchColumn() > 0;
    }

    private function existsExcept($courierId, $originId, $destId, $exceptId) {
        $sql = "SELECT COUNT(*) FROM courier_coverage_routes 
                WHERE courier_partner_id = ? AND origin_district_id = ? AND destination_district_id = ? 
                AND route_id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courierId, $originId, $destId, $exceptId]);
        return $stmt->fetchColumn() > 0;
    }
}
?>