<?php

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

final class CourierModel {
    private mysqli $db;
    public function __construct(){ $this->db = db(); }

    public function profile(int $id): array {
        return db_fetch_one("SELECT u.user_id,u.full_name,u.email,u.phone,u.account_status,
            cp.organisation_name,cp.contact_person_name,cp.office_address_line1,cp.office_address_line2,
            cp.office_city_town,cp.office_postal_code,cp.office_district_id,cp.availability_status,
            cp.verification_status,d.district_name
            FROM users u JOIN courier_partner_profiles cp ON cp.courier_partner_id=u.user_id
            LEFT JOIN districts d ON d.district_id=cp.office_district_id
            WHERE u.user_id=? AND u.role='COURIER_PARTNER' LIMIT 1", 'i', [$id]) ?? [];
    }
    public function stats(int $id): array {
        return [
            'offers'=>(int)db_scalar("SELECT COUNT(*) FROM delivery_assignment_offers WHERE courier_partner_id=? AND offer_status='PENDING'",'i',[$id],0),
            'active'=>(int)db_scalar("SELECT COUNT(*) FROM deliveries WHERE courier_partner_id=? AND delivery_status IN ('ASSIGNED','PICKED_UP','IN_TRANSIT','OUT_FOR_DELIVERY','DELIVERED')",'i',[$id],0),
            'completed'=>(int)db_scalar("SELECT COUNT(*) FROM deliveries WHERE courier_partner_id=? AND delivery_status='COMPLETED'",'i',[$id],0),
            'pending_earnings'=>(float)db_scalar("SELECT COALESCE(SUM(amount),0) FROM earnings WHERE beneficiary_user_id=? AND beneficiary_type='COURIER_PARTNER' AND earning_status IN ('HELD','PENDING_PAYOUT')",'i',[$id],0),
            'unread'=>(int)db_scalar("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0",'i',[$id],0),
        ];
    }
    public function districts(): array { return db_fetch_all("SELECT district_id,district_name FROM districts WHERE is_active=1 ORDER BY district_name"); }
    public function routes(int $id): array { return db_fetch_all("SELECT r.route_id,r.origin_district_id,r.destination_district_id,r.is_active,o.district_name origin,d.district_name destination FROM courier_coverage_routes r JOIN districts o ON o.district_id=r.origin_district_id JOIN districts d ON d.district_id=r.destination_district_id WHERE r.courier_partner_id=? ORDER BY o.district_name,d.district_name",'i',[$id]); }
    public function addRoute(int $id,int $origin,int $dest): bool {
        if ($origin<=0||$dest<=0) return false;
        if ((int)db_scalar('SELECT COUNT(*) FROM districts WHERE district_id IN (?,?) AND is_active=1','ii',[$origin,$dest],0) !== ($origin===$dest?1:2)) return false;
        return db_execute("INSERT INTO courier_coverage_routes(courier_partner_id,origin_district_id,destination_district_id,is_active) VALUES(?,?,?,1) ON DUPLICATE KEY UPDATE is_active=1",'iii',[$id,$origin,$dest]);
    }
    public function updateRoute(int $id,int $routeId,int $origin,int $destination,bool $active): bool {
        if ((int)db_scalar('SELECT COUNT(*) FROM districts WHERE district_id IN (?,?) AND is_active=1','ii',[$origin,$destination],0) !== ($origin===$destination?1:2)) return false;
        if (!db_fetch_one('SELECT route_id FROM courier_coverage_routes WHERE route_id=? AND courier_partner_id=?','ii',[$routeId,$id])) return false;
        return db_execute('UPDATE courier_coverage_routes SET origin_district_id=?,destination_district_id=?,is_active=? WHERE route_id=? AND courier_partner_id=?','iiiii',[$origin,$destination,(int)$active,$routeId,$id]);
    }
    public function deleteRoute(int $id,int $routeId): bool { return db_execute("DELETE FROM courier_coverage_routes WHERE route_id=? AND courier_partner_id=?",'ii',[$routeId,$id]); }
    public function setAvailability(int $id,string $status): bool {
        $status=$status==='AVAILABLE'?'AVAILABLE':'UNAVAILABLE';
        return db_execute("UPDATE courier_partner_profiles SET availability_status=? WHERE courier_partner_id=?",'si',[$status,$id]);
    }
    public function offers(int $id,int $limit=50): array {
        $limit=max(1,min(200,$limit));
        return db_fetch_all("SELECT dao.offer_id,dao.order_id,dao.offer_status,dao.offered_at,dao.expires_at,
            f.full_name farmer_name,b.full_name buyer_name,od.district_name destination_district,fd.district_name origin_district,o.delivery_fee
            FROM delivery_assignment_offers dao JOIN orders o ON o.order_id=dao.order_id
            JOIN users f ON f.user_id=o.farmer_id JOIN users b ON b.user_id=o.buyer_id
            LEFT JOIN farmer_profiles fp ON fp.farmer_id=o.farmer_id LEFT JOIN districts fd ON fd.district_id=fp.district_id
            LEFT JOIN districts od ON od.district_id=o.destination_district_id
            WHERE dao.courier_partner_id=? ORDER BY dao.offered_at DESC LIMIT ?",'ii',[$id,$limit]);
    }
    public function respondOffer(int $id,int $offerId,string $response): array {
        $offer=db_fetch_one("SELECT * FROM delivery_assignment_offers WHERE offer_id=? AND courier_partner_id=? AND offer_status='PENDING' AND expires_at > NOW() LIMIT 1",'ii',[$offerId,$id]);
        if(!$offer) return [false,0,'Offer is no longer available.'];
        $orderId=(int)$offer['order_id'];
        if(!in_array($response,['accept','reject'],true))return [false,$orderId,'Invalid response.'];
        if(!db_fetch_one("SELECT order_id FROM orders WHERE order_id=? AND order_status IN ('READY_FOR_DELIVERY','PENDING_ASSIGNMENT')",'i',[$orderId]))return [false,$orderId,'Order is no longer awaiting assignment.'];
        if($response==='accept'){
            $this->db->begin_transaction();
            try{
                db_execute("UPDATE delivery_assignment_offers SET offer_status='ACCEPTED',responded_at=NOW() WHERE offer_id=? AND courier_partner_id=?",'ii',[$offerId,$id]);
                db_execute("UPDATE delivery_assignment_offers SET offer_status='CANCELLED',responded_at=NOW() WHERE order_id=? AND offer_id<>? AND offer_status='PENDING'",'ii',[$orderId,$offerId]);
                db_execute("INSERT INTO deliveries(order_id,courier_partner_id,delivery_status,assigned_at) VALUES(?,?,'ASSIGNED',NOW()) ON DUPLICATE KEY UPDATE courier_partner_id=VALUES(courier_partner_id),delivery_status='ASSIGNED',assigned_at=NOW()",'ii',[$orderId,$id]);
                db_execute("UPDATE orders SET order_status='ASSIGNED' WHERE order_id=?",'i',[$orderId]);
                db_execute("INSERT INTO order_status_history(order_id,status,changed_by_user_id,note) VALUES(?,'ASSIGNED',?,'Courier Partner accepted the assignment')",'ii',[$orderId,$id]);
                db_execute("UPDATE deliveries SET accepted_at=NOW() WHERE order_id=? AND courier_partner_id=? AND accepted_at IS NULL",'ii',[$orderId,$id]);
                $deliveryFee = (float)db_scalar("SELECT delivery_fee FROM orders WHERE order_id=?", 'i', [$orderId], 0);
                $existingCourierEarning = (int)db_scalar(
                    "SELECT COUNT(*) FROM earnings WHERE order_id=? AND beneficiary_type='COURIER_PARTNER' AND earning_type='COURIER_DELIVERY'",
                    'i', [$orderId], 0
                );
                if ($existingCourierEarning === 0 && $deliveryFee > 0) {
                    db_execute(
                        "INSERT INTO earnings(order_id,beneficiary_type,beneficiary_user_id,earning_type,amount,earning_status) VALUES(?,'COURIER_PARTNER',?,'COURIER_DELIVERY',?,'HELD')",
                        'iid', [$orderId,$id,$deliveryFee]
                    );
                }
                $this->db->commit(); return [true,$orderId,'Delivery request accepted.'];
            }catch(Throwable $e){$this->db->rollback();return [false,$orderId,'Unable to accept the request.'];}
        }
        db_execute("UPDATE delivery_assignment_offers SET offer_status='REJECTED',responded_at=NOW(),rejection_reason='Rejected by Courier Partner' WHERE offer_id=? AND courier_partner_id=?",'ii',[$offerId,$id]);
        attemptAutomaticCourierAssignment($orderId);
        return [true,$orderId,'Delivery request rejected. The next eligible Courier Partner will be attempted.'];
    }
    public function activeDeliveries(int $id): array {
        return db_fetch_all("SELECT d.delivery_id,d.delivery_status,(SELECT COUNT(*) FROM delivery_attempts da WHERE da.delivery_id=d.delivery_id) AS active_attempt_count,o.order_id,o.delivery_fee,o.recipient_name,o.recipient_phone,o.delivery_address_line1,
            dest.district_name destination_district,f.full_name farmer_name,fp.pickup_address_line1,orig.district_name origin_district
            FROM deliveries d JOIN orders o ON o.order_id=d.order_id JOIN users f ON f.user_id=o.farmer_id
            LEFT JOIN farmer_profiles fp ON fp.farmer_id=o.farmer_id LEFT JOIN districts orig ON orig.district_id=fp.district_id LEFT JOIN districts dest ON dest.district_id=o.destination_district_id
            WHERE d.courier_partner_id=? AND d.delivery_status IN ('ASSIGNED','PICKED_UP','IN_TRANSIT','OUT_FOR_DELIVERY','DELIVERED') ORDER BY d.updated_at DESC",'i',[$id]);
    }
    public function delivery(int $id,int $deliveryId): ?array {
        return db_fetch_one("SELECT d.*,(SELECT COUNT(*) FROM delivery_attempts da WHERE da.delivery_id=d.delivery_id) AS active_attempt_count,o.order_id,o.delivery_fee,o.recipient_name,o.recipient_phone,o.delivery_address_line1,o.delivery_address_line2,o.delivery_city_town,o.delivery_postal_code,
            dest.district_name destination_district,f.full_name farmer_name,fp.pickup_address_line1,fp.pickup_address_line2,fp.pickup_city_town,orig.district_name origin_district
            FROM deliveries d JOIN orders o ON o.order_id=d.order_id JOIN users f ON f.user_id=o.farmer_id
            LEFT JOIN farmer_profiles fp ON fp.farmer_id=o.farmer_id LEFT JOIN districts orig ON orig.district_id=fp.district_id LEFT JOIN districts dest ON dest.district_id=o.destination_district_id
            WHERE d.delivery_id=? AND d.courier_partner_id=? LIMIT 1",'ii',[$deliveryId,$id]);
    }
    public function updateDeliveryStatus(int $id,int $deliveryId,string $next): array {
        $row=$this->delivery($id,$deliveryId); if(!$row) return [false,'Delivery not found.'];
        $allowed=['ASSIGNED'=>'PICKED_UP','PICKED_UP'=>'IN_TRANSIT','IN_TRANSIT'=>'OUT_FOR_DELIVERY','OUT_FOR_DELIVERY'=>'DELIVERED'];
        $current=(string)$row['delivery_status']; if(($allowed[$current]??'')!==$next) return [false,'Invalid delivery status transition.'];
        $orderId=(int)$row['order_id'];
        $extra=''; $extraTypes=''; $extraValues=[];
        if($next==='PICKED_UP')$extra=',picked_up_at=NOW()';
        if($next==='IN_TRANSIT')$extra=',in_transit_at=NOW()';
        if($next==='OUT_FOR_DELIVERY')$extra=',out_for_delivery_at=NOW()';
        if($next==='DELIVERED'){
            $extra=',delivered_at=NOW(),buyer_confirmation_deadline=DATE_ADD(NOW(),INTERVAL ? HOUR)';
            $extraTypes='i'; $extraValues=[buyerConfirmationHours()];
        }
        $this->db->begin_transaction();
        try{
            $statement=$this->db->prepare("UPDATE deliveries SET delivery_status=? $extra WHERE delivery_id=? AND courier_partner_id=? AND delivery_status=?");
            $values=[$next]; $types='s';
            foreach($extraValues as $value){ $values[]=$value; $types.=$extraTypes; }
            $values[]=$deliveryId; $values[]=$id; $values[]=$current; $types.='iis';
            $refs=[$types];
            foreach($values as $key=>$value){ $refs[]=&$values[$key]; }
            call_user_func_array([$statement,'bind_param'],$refs);
            $ok=$statement->execute();$changed=$statement->affected_rows;$statement->close();
            if(!$ok) throw new RuntimeException('Delivery update failed.');
            if($changed!==1)throw new RuntimeException('Delivery status has changed. Please refresh.');
            db_execute("UPDATE orders SET order_status=?, delivered_at=IF(?='DELIVERED',NOW(),delivered_at) WHERE order_id=?",'ssi',[$next,$next,$orderId]);
            db_execute("INSERT INTO order_status_history(order_id,status,changed_by_user_id,note) VALUES(?,?,?,'Updated by Courier Partner')",'isi',[$orderId,$next,$id]);
            if($next==='DELIVERED'){
                $attempt=(int)$row['active_attempt_count']+1;
                if($attempt>maxDeliveryAttempts())$attempt=maxDeliveryAttempts();
                db_execute("INSERT INTO delivery_attempts(delivery_id,attempt_number,attempt_result,notes) VALUES(?,?,'DELIVERED','Delivery marked delivered by Courier Partner') ON DUPLICATE KEY UPDATE attempt_result='DELIVERED',notes=VALUES(notes)",'ii',[$deliveryId,$attempt]);
                db_execute("INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read) SELECT buyer_id,'DELIVERY_UPDATE','Order delivered','Your order has been marked Delivered. Please confirm receipt.',order_id,0 FROM orders WHERE order_id=?",'i',[$orderId]);
            }
            $this->db->commit(); return [true,'Delivery status updated.'];
        }catch(Throwable $e){$this->db->rollback();return [false,$e->getMessage()];}
    }
    public function buyerUnavailable(int $id,int $deliveryId,string $notes=''): array {
        $row=$this->delivery($id,$deliveryId); if(!$row || (string)$row['delivery_status']!=='OUT_FOR_DELIVERY') return [false,'Buyer-unavailable can only be recorded while Out for Delivery.'];
        $attempt=(int)$row['active_attempt_count']+1; $orderId=(int)$row['order_id'];
        $limit=maxDeliveryAttempts();
        if($attempt>$limit) return [false,'Maximum delivery attempts already reached.'];
        $notes=trim($notes); if(strlen($notes)>500)$notes=substr($notes,0,500);
        db_execute("INSERT INTO delivery_attempts(delivery_id,attempt_number,attempt_result,notes) VALUES(?,?,'BUYER_UNAVAILABLE',?)",'iis',[$deliveryId,$attempt,$notes]);
        if($attempt>=$limit){
            db_execute("UPDATE deliveries SET delivery_status='UNDELIVERABLE' WHERE delivery_id=?",'i',[$deliveryId]);
            db_execute("UPDATE orders SET order_status='UNDELIVERABLE' WHERE order_id=?",'i',[$orderId]);
            db_execute("INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read) SELECT buyer_id,'DELIVERY_UPDATE','Delivery unsuccessful','Two delivery attempts were unsuccessful. Admin has been notified.',order_id,0 FROM orders WHERE order_id=?",'i',[$orderId]);
            $admins=db_fetch_all("SELECT user_id FROM users WHERE role='ADMIN' AND account_status='ACTIVE'");
            foreach($admins as $a) db_execute("INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read) VALUES(?,'DELIVERY_ISSUE','Undeliverable order','Two Buyer-unavailable attempts were recorded. Please review the order.',?,0)",'ii',[(int)$a['user_id'],$orderId]);
            return [true,'Second unsuccessful attempt recorded. Delivery is now Undeliverable and Admin was notified.'];
        }
        db_execute("UPDATE deliveries SET delivery_status='IN_TRANSIT' WHERE delivery_id=?",'i',[$deliveryId]);
        db_execute("UPDATE orders SET order_status='IN_TRANSIT' WHERE order_id=?",'i',[$orderId]);
        return [true,'First unsuccessful attempt recorded. One more delivery attempt is allowed.'];
    }
    public function history(int $id): array { return db_fetch_all("SELECT d.delivery_id,d.delivery_status,d.completed_at,o.order_id,o.delivery_fee,dest.district_name destination_district FROM deliveries d JOIN orders o ON o.order_id=d.order_id LEFT JOIN districts dest ON dest.district_id=o.destination_district_id WHERE d.courier_partner_id=? AND d.delivery_status IN ('COMPLETED','UNDELIVERABLE') ORDER BY d.updated_at DESC",'i',[$id]); }
    public function earnings(int $id): array { return db_fetch_all("SELECT e.*,o.order_id FROM earnings e JOIN orders o ON o.order_id=e.order_id WHERE e.beneficiary_user_id=? AND e.beneficiary_type='COURIER_PARTNER' ORDER BY e.created_at DESC",'i',[$id]); }
    public function earningsTotals(int $id): array {
        return [
            'held'=>(float)db_scalar("SELECT COALESCE(SUM(amount),0) FROM earnings WHERE beneficiary_user_id=? AND beneficiary_type='COURIER_PARTNER' AND earning_status='HELD'",'i',[$id],0),
            'pending_payout'=>(float)db_scalar("SELECT COALESCE(SUM(amount),0) FROM earnings WHERE beneficiary_user_id=? AND beneficiary_type='COURIER_PARTNER' AND earning_status='PENDING_PAYOUT'",'i',[$id],0),
            'paid'=>(float)db_scalar("SELECT COALESCE(SUM(amount),0) FROM earnings WHERE beneficiary_user_id=? AND beneficiary_type='COURIER_PARTNER' AND earning_status='PAID'",'i',[$id],0),
        ];
    }
    public function complaints(int $id): array { return db_fetch_all("SELECT c.*,o.order_id FROM complaints c JOIN orders o ON o.order_id=c.order_id WHERE c.complainant_user_id=? AND c.complainant_role='COURIER_PARTNER' ORDER BY c.created_at DESC",'i',[$id]); }
    public function complaintOrders(int $id): array { return db_fetch_all("SELECT DISTINCT o.order_id FROM orders o LEFT JOIN deliveries d ON d.order_id=o.order_id WHERE d.courier_partner_id=? ORDER BY o.created_at DESC",'i',[$id]); }
    public function addComplaint(int $id,int $orderId,string $category,string $description,?string $evidence): bool {
        $owns=(int)db_scalar("SELECT COUNT(*) FROM deliveries WHERE order_id=? AND courier_partner_id=?",'ii',[$orderId,$id],0)>0; if(!$owns) return false;
        return db_execute("INSERT INTO complaints(order_id,complainant_user_id,complainant_role,category,description,evidence_path,complaint_status) VALUES(?,?,'COURIER_PARTNER',?,?,?,'OPEN')",'iisss',[$orderId,$id,$category,$description,$evidence]);
    }
    public function notifications(int $id): array { return db_fetch_all("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 100",'i',[$id]); }
    public function markNotificationsRead(int $id): bool { return db_execute("UPDATE notifications SET is_read=1,read_at=COALESCE(read_at,NOW()) WHERE user_id=? AND is_read=0",'i',[$id]); }
    public function updateProfile(int $id,array $d): bool {
        $districtId=(int)($d['office_district_id']??0);
        if ($districtId<=0) return false;
        if ((int)db_scalar('SELECT COUNT(*) FROM districts WHERE district_id=? AND is_active=1','i',[$districtId],0)!==1) return false;
        $this->db->begin_transaction();
        try{
            // users.full_name is the signed-in contact identity and is NOT overwritten
            // with the organisation name. The organisation lives in
            // courier_partner_profiles.organisation_name.
            db_execute("UPDATE users SET phone=? WHERE user_id=?",'si',[$d['phone'],$id]);
            db_execute("UPDATE courier_partner_profiles SET organisation_name=?,contact_person_name=?,office_address_line1=?,office_address_line2=?,office_city_town=?,office_postal_code=?,office_district_id=? WHERE courier_partner_id=?",'ssssssii',[$d['organisation_name'],$d['contact_person_name'],$d['office_address_line1'],$d['office_address_line2'],$d['office_city_town'],$d['office_postal_code'],$districtId,$id]);
            $this->db->commit(); return true;
        }catch(Throwable $e){$this->db->rollback();return false;}
    }
}
