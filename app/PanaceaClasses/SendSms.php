<?php
namespace App\PanaceaClasses;
use Twilio;
class SendSms
{
    
    public function sendMessage($mobile_number_to_send,$message){
        //
        Twilio::message($mobile_number_to_send, $message);
         return "true";
    }	
}
