<?php
require_once __DIR__ . '/../../config/app.php';
final class Dashboard
{
    public function getBuyerData(): array {
        requireBuyerAuth();
        $id = currentBuyerId();
        $cartCount = (int)db_scalar(
            'SELECT COALESCE(SUM(ci.quantity),0) FROM cart_items ci
             JOIN carts c ON c.cart_id=ci.cart_id WHERE c.buyer_id=?',
            'i',
            [$id],
            0
        );
        return [
            'buyerName' => (string)db_scalar('SELECT full_name FROM users WHERE user_id=?','i',[$id],'Buyer'),
            'notificationCount' => (int)db_scalar('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0','i',[$id],0),
            'cartCount' => $cartCount,
            'activeOrders' => (int)db_scalar("SELECT COUNT(*) FROM orders WHERE buyer_id=? AND order_status NOT IN ('COMPLETED','CANCELLED','REJECTED','UNDELIVERABLE')",'i',[$id],0),
            'completedOrders' => (int)db_scalar("SELECT COUNT(*) FROM orders WHERE buyer_id=? AND order_status='COMPLETED'",'i',[$id],0),
            'awaitingConfirmation' => (int)db_scalar("SELECT COUNT(*) FROM orders WHERE buyer_id=? AND order_status='DELIVERED'",'i',[$id],0),
            'recentOrders' => db_fetch_all('SELECT o.order_id,o.order_status,o.grand_total,o.created_at,d.district_name destination_district,u.full_name farmer_name FROM orders o JOIN users u ON u.user_id=o.farmer_id LEFT JOIN districts d ON d.district_id=o.destination_district_id WHERE o.buyer_id=? ORDER BY o.created_at DESC,o.order_id DESC LIMIT 5','i',[$id]),
            'recentProducts' => db_fetch_all(
                "SELECT p.product_id,p.product_name,p.unit_price,p.unit_label,
                        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id=p.product_id
                          ORDER BY pi.is_primary DESC, pi.image_id ASC LIMIT 1) AS image_path
                 FROM products p
                 JOIN users fu ON fu.user_id=p.farmer_id
                 JOIN farmer_profiles fp ON fp.farmer_id=fu.user_id
                 JOIN product_categories pc ON pc.category_id=p.category_id
                 WHERE p.listing_status='ACTIVE' AND fu.account_status='ACTIVE'
                   AND fp.verification_status='APPROVED' AND pc.is_active=1
                 ORDER BY p.created_at DESC, p.product_id DESC LIMIT 4",
                '',
                []
            ),
        ];
    }
}
