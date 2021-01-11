<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PushNotification;
use Illuminate\Http\Request;

class PushNotificationController extends Controller {

    public function test() {

        $user = User::find(1);

        $user->notify(new PushNotification());
    }

    public function send() {
        define('API_ACCESS_KEY', 'AAAA6TqT9lY:APA91bHMszGcgMrwlwum2XKvSWGuUfgn6mwbwtfY90NlZ1H16ajomhUrGaT99dD3as9nkR-Ixn-6VHvyzKiJu_JwA_9ITieS6kYawVQ0GSF9u_FM3qqRbDT-4qe_MI2vujrI4h_8iOG2');

        $expiry = 10 * 3600;
        $msg = array
            (
            "title" => "Mario",
            "body" => "great match!",
            "sound"=> "default",
            "lastname"=>"Ankit",
            "image-url"=> "https://lh3.googleusercontent.com/-3L2zxVtiRtE/XiHp6VWPM7I/AAAAAAAANxo/4jr8TUAfuPUudtzZd1d-wp_HTHTZdldawCK8BGAsYHg/s0/2020-01-17.jpg"
            
            
        );




        $fields = array
            (
            'to' => 'fh2ECfHVSemjIgNhZLPCZt:APA91bGqeiN1_wsuQkw4voNtP_2PBZ9nhJFvuWIq5pKUxUsfMWuvyHkQv9u6F33ubGeXg9svLnJc0HnbJgodSRfOSC7r4k1WBMK7-QVikXnwTt8xnPgXMLPPzYMC4YbGTWQSlLehSkd_',
            'data' => $msg,
            'time_to_live' => $expiry
        );


        echo '<pre>';
        print_r($fields);
        echo '</pre>';

        $headers = array
            (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );


        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';
//api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
//header with content_type api key
//CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Oops! FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);

        print_r($result);
    }

}
