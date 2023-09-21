<?php
namespace App\PanaceaClasses;
use App\Notification;
class AppNotification
{
    
    public function saveNotification($user_id,$order_id=0,$subject='',$message='',$date,$type=0,$flag='order'){
        $notificationData=array();
        $notificationData['user_id']=$user_id;
        $notificationData['order_id']=$order_id;
        $notificationData['subject']=$subject;
        $notificationData['message']=$message;
        $notificationData['notification_date']=$date;
        $notificationData['type']=$type;
        $notificationData['redirect_flag']=$flag;
        return Notification::create($notificationData);
    }	
}
