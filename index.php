<?php

echo "Hello World!";

$test = array();
$test["test1"] = "value 1";
echo "<br />".$test["test1"];

/*
$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://login.microsoftonline.com/84196002-7e4c-4b0b-a9c4-0ef30b7c5d95/oauth2/token",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "username=admin%40iwa.ie&password=TiwawFi1960!&grant_type=password&resource=https%3A%2F%2Fdeviwa.crm4.dynamics.com%2F&client_id=2cd03a89-f0ba-4bd7-93cf-a4a6b3ec2287&client_secret=2wVuRE31R3VHkXZeC%2BGnSF37TIh6as00UyX%2F6VhN6FM%3D",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: application/x-www-form-urlencoded"
   
  ),
));

$responseJson = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  //echo $response;
 $response = json_decode($responseJson,true); 
  echo $response["access_token"];
}
*/

?>