<?php
require_once __DIR__ . '/Database.php';

class Notification {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByUser($userId) {
        $sql = "SELECT notification_id, notification_type, title, message, 
                       related_order_id, is_read, 
                       DATE_FORMAT(created_at, '%b %d, %Y %h:%i %p') AS created_at
                FROM notifications 
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 50";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function markRead($notificationId, $userId) {
        $sql = "UPDATE notifications 
               SET is_read = 1, read_at = NOW() 
               WHERE notification_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notificationId, $userId]);

        $affected = $stmt->rowCount();

        // DEBUG response
        return [
            'success' => $affected > 0,
            'affected_rows' => $affected,
            'debug_notification_id' => $notificationId,
            'debug_user_id' => $userId,
            'message' => $affected > 0 
                ? 'Marked as read.' 
                : 'No rows updated. Check notification_id + user_id match.'
       ];
   }

    public function markAllRead($userId) {
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return ['success' => true];
    }

    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) FROM notifications 
                WHERE user_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
?>