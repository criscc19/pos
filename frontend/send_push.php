<?php
$token = 'f4sG3pLueBgtOshNigH2V:APA91bE127OKuUewTk4ThgKErJO_2v9uB9UoeUQBabiQxNClF9H3-RZBrjY-8nW9_FcIjRJ8JsLqQEb52ySf1HFU_77KGDFQjfDIQO17_s_EjVLPDQNBMof42dACePOseGiVlbZtrOtC';
$serverKey ='AAAAEDxpJwQ:APA91bHcLDY9IDXRJwkhImrJTMKCRjCFIi3hE8j3Dt7CfhUeoXcDUQAmcg0DH03kHPyfyUK6yHiXt72amtAYuZ5VLwJUlaFlsFRkS5bVviNf9fGTTmDvaozkRdz7S6eHKfTjbeWs0JJH';
/* $headers = array(
    'Content-Type:application/json',
  'Authorization:key='.$server_key
);
$url = 'https://fcm.googleapis.com/fcm/send';
$post = '{
    "to" : "f4sG35pLueBgtOshNigH2V:APA91bE127OKuUewTk4ThgKErJO_2v9uB9UoeUQBabiQxNClF9H3-RZBrjY-8nW9_FcIjRJ8JsLqQEb52ySf1HFU_77KGDFQjfDIQO17_s_EjVLPDQNBMof42dACePOseGiVlbZtrOtC",
    "notification": {
        "title": "Background Message Title",
        "body": "Background message body"
      },
    "data" :{
        "prueba": "prueba"
    }
  }'; */
  $url = "https://fcm.googleapis.com/fcm/send";
  $title = "Notification title";
  $body = "Hello I am from Your php server";
  $notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => '1');
  $arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
  $json = json_encode($arrayToSend);
  $headers = array();
  $headers[] = 'Content-Type: application/json';
  $headers[] = 'Authorization: key='. $serverKey;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
  curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
  //Send the request
  $response = curl_exec($ch);
  //Close request
  if ($response === FALSE) {
  die('FCM Send Error: ' . curl_error($ch));
  }
  curl_close($ch);
var_dump($response);exit;
?>