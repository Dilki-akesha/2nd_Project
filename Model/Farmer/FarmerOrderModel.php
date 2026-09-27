<?php
require_once __DIR__.'/../../config/app.php';
final class FarmerOrderModel {
 public function advance(int $id,int $farmer,bool $reject=false): bool {
  $db=db();$db->begin_transaction();try{
   $o=db_fetch_one('SELECT * FROM orders WHERE order_id=? AND farmer_id=? FOR UPDATE','ii',[$id,$farmer]);
   $flow=['PAID'=>'ACCEPTED','ACCEPTED'=>'PREPARING','PREPARING'=>'READY_FOR_DELIVERY'];
   if(!$o || !isset($flow[$o['order_status']]) || ($reject&&$o['order_status']!=='PAID')){$db->rollback();return false;}
   $next=$reject?'REJECTED':$flow[$o['order_status']];
   if(!db_execute("UPDATE orders SET order_status=?,accepted_at=IF(?='ACCEPTED',NOW(),accepted_at),ready_for_delivery_at=IF(?='READY_FOR_DELIVERY',NOW(),ready_for_delivery_at) WHERE order_id=? AND farmer_id=?",'sssii',[$next,$next,$next,$id,$farmer]))throw new RuntimeException();
   if($reject){foreach(db_fetch_all('SELECT product_id,quantity FROM order_items WHERE order_id=?','i',[$id]) as $item){if(!db_execute("UPDATE products SET available_quantity=available_quantity+?,listing_status=IF(listing_status='SOLD_OUT','ACTIVE',listing_status) WHERE product_id=?",'di',[(float)$item['quantity'],(int)$item['product_id']]))throw new RuntimeException();}}
   if(!db_execute("INSERT INTO order_status_history(order_id,status,changed_by_user_id,note) VALUES(?,?,?,'Updated by Farmer')",'isi',[$id,$next,$farmer]))throw new RuntimeException();
   if(!db_execute("INSERT INTO notifications(user_id,notification_type,title,message,related_order_id,is_read) VALUES(?,'ORDER_UPDATE','Farmer updated your order',?,?,0)",'isi',[(int)$o['buyer_id'],'Order is now '.str_replace('_',' ',$next).'.',$id]))throw new RuntimeException();
   if($next==='ACCEPTED' && db_scalar("SELECT COUNT(*) FROM payments WHERE order_id=? AND payment_status='SUCCESS'",'i',[$id],0)>0 && !db_scalar("SELECT COUNT(*) FROM earnings WHERE order_id=? AND beneficiary_type='FARMER'",'i',[$id],0)) {
    if(!db_execute("INSERT INTO earnings(order_id,beneficiary_type,beneficiary_user_id,earning_type,amount,earning_status) VALUES(?,'FARMER',?,'FARMER_NET',?,'PENDING_PAYOUT')",'iid',[$id,$farmer,(float)$o['product_subtotal']-(float)$o['farmer_marketplace_fee']]))throw new RuntimeException();
   }
   $db->commit();if($next==='READY_FOR_DELIVERY')attemptAutomaticCourierAssignment($id);return true;
  }catch(Throwable $e){$db->rollback();return false;}
 }
}
