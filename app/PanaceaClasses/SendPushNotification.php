<?php
namespace App\PanaceaClasses;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
class SendPushNotification
{    
   public function iOSPushNotificaton($arrayToSend) {
            //$fcmApiKey = 'AIzaSyCsarbpB9079XBMzmbCN4vH2BCUQXvnbX4';//App API Key(This is google cloud messaging api key not web api key)
            $fcmApiKey = 'AIzaSyDsFfJu9UrWjaaPFzh-f2EzSXraPjzXpoM';//App API Key(This is google cloud messaging api key not web api key)
            //$fcmApiKey = 'AIzaSyCMAID7qzkk6uWuQbgpV_LbFVbNi575-4w';//App API Key(This is google cloud messaging api key not web api key)
            $url = 'https://fcm.googleapis.com/fcm/send';//Google URL
            //Fcm Device ids array
            
            $headers = array(
                'Authorization: key=' . $fcmApiKey,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, $url );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayToSend);
            // Execute post
            $result = curl_exec($ch);
          
            // if ($result === FALSE) {
            //     die('Curl failed: ' . curl_error($ch));
            // }
            // Close connection
            curl_close($ch);
            return "1";
       }
   public function androidPushNotificaton($arrayToSend) {
            $arrayToSendTo=json_decode($arrayToSend);    
      
            $device_id=isset($arrayToSendTo->to)?$arrayToSendTo->to:'';

            if($device_id!='')
            {
              $response = PushNotification::app('appNameAndroidCustomer')
             ->to($device_id)
             ->send(($arrayToSend));           
            }
            
   }
   public function iOSPushNotificatonStar($arrayToSend) {
          //  $fcmApiKey = 'AIzaSyABge94AS3mxDcP8wACZd1FSOPzzIjxvwQ';//App API Key(This is google cloud messaging api key not web api key)
            $fcmApiKey = 'AIzaSyArSi8o9UZT0e-9N2YiiEJ-U3obVNHRkFg';//App API Key(This is google cloud messaging api key not web api key)
            $url = 'https://fcm.googleapis.com/fcm/send';//Google URL
            //Fcm Device ids array
            $headers = array(
                'Authorization: key=' . $fcmApiKey,
                'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, $url );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayToSend);
            // Execute post
            $result = curl_exec($ch);
            // if ($result === FALSE) {
            //     die('Curl failed: ' . curl_error($ch));
            // }
            // Close connection
            curl_close($ch);
            return "1";
       }	
   public function androidPushNotificatonStar($arrayToSend) {
       $arrayToSendTo=json_decode($arrayToSend);
       //dd($arrayToSendTo);
       $device_id=isset($arrayToSendTo->to)?$arrayToSendTo->to:'';
       //dd($device_id);
       if($device_id!='')
       {
         $response = PushNotification::app('appNameAndroid')
        ->to($device_id)
        ->send(($arrayToSend));
         //dd($response);
       }
     
//          // $fcmApiKey = 'AIzaSyABge94AS3mxDcP8wACZd1FSOPzzIjxvwQ';//App API Key(This is google cloud messaging api key not web api key)
//            $fcmApiKey = 'AIzaSyBVkUj0t16rCbDbJqvz8to1HMhsKNikmAI';//App API Key(This is google cloud messaging api key not web api key)
//            $url = 'https://fcm.googleapis.com/fcm/send';//Google URL
//            //Fcm Device ids array
//            
//            $headers = array(
//                'Authorization: key=' . $fcmApiKey,
//                'Content-Type: application/json'
//            );
//
//            $ch = curl_init();
//            curl_setopt( $ch,CURLOPT_URL, $url );
//            curl_setopt( $ch,CURLOPT_POST, true );
//            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
//            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
//            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayToSend);
//            // Execute post
//            $result = curl_exec($ch);
//          
//            // if ($result === FALSE) {
//            //     die('Curl failed: ' . curl_error($ch));
//            // }
//            // Close connection
//            curl_close($ch);
//            return "1";
       }	     
}
