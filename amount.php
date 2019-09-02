<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-type');

require_once ('vendor/autoload.php');
$config = include('config.php');

// grab the response sent by the client-side library
$request = file_get_contents('php://input');
$decoded_request = json_decode($request, true);

$AzureApplicationID = $decoded_request["AzureApplicationID"];
$AzureApplicationSecret = $decoded_request["AzureApplicationSecret"];
$AzureTenantId = $decoded_request["AzureTenantId"];
$CRMServerURL = $decoded_request["CRMServerURL"];
$WebApiURL = $decoded_request["WebApiURL"];
$PurchaseID = $decoded_request["PurchaseID"];

$configParams = [
    'tenantId' => $AzureTenantId,
    'resource' => $CRMServerURL,
    'clientId' =>$AzureApplicationID,
    'clientSecret' => $AzureApplicationSecret,
    'webApiURL' => $WebApiURL
    ];
    
$paymentDataArray = array(
    'PurchaseId' => $PurchaseID);

$Data = json_encode($paymentDataArray);
$actionResponse = useWebApi("POST", $Data, "msevtmgt_GetPurchaseDetailsAction", $config, $configParams);
error_log($actionResponse);
$response = json_encode($actionResponse);
return $response;

function getAppAccessToken($config, $configParams)
{
	$tenantID = $configParams["tenantId"];
	$username = urlencode($config["username"]);
	$password = $config["password"];
	$grant_type = urlencode($config["grant_type"]);
	$resource = urlencode($configParams["resource"]);
	$clientid = $configParams["clientId"];
	$clientsecret = urlencode($configParams["clientSecret"]);
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://login.microsoftonline.com/".$tenantID."/oauth2/token",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "username=".$username."&password=".$password."&grant_type=".$grant_type."&resource=".$resource."&client_id=".$clientid."&client_secret=".$clientsecret,
	  CURLOPT_HTTPHEADER => array(
		"cache-control: no-cache",
		"content-type: application/x-www-form-urlencoded"
	  ),
	));

	$responseJson = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		error_log($err);
	} else {
	 return $response = json_decode($responseJson,true);
	}
}

function useWebApi($method, $data, $odata, $config, $configParams) {
//GET APP ACCESS TOKEN
	$token = getAppAccessToken($config, $configParams);
	$url = $configParams["webApiURL"];
	echo "console.log('PHP.RealexIntegrationAzure.Response.WebApiUrl: ' + $url);";
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url . $odata,
	  CURLOPT_HEADER => 1,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => $method,
	  CURLOPT_POSTFIELDS => $data,
	  CURLOPT_HTTPHEADER => array(
		"Authorization: Bearer ".$token["access_token"],
		"cache-control: no-cache",
		"content-type: application/json"
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		return "cURL Error #:" . $err;
	} else {
		return $response;
	}
}
?>