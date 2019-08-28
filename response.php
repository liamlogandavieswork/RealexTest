<?php
require_once ('vendor/autoload.php');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-type: application/json');

use com\realexpayments\hpp\sdk\domain\HppResponse;
use com\realexpayments\hpp\sdk\RealexHpp;
use com\realexpayments\hpp\sdk\RealexValidationException;
use com\realexpayments\hpp\sdk\RealexException;

$config = include('config.php');

// grab the response sent by the client-side library
$responseJson = $_POST['hppResponse'];

$decodedResp = json_decode($responseJson, true);
// Values coming from the FORM
$amount = base64_decode($decodedResp["AMOUNT"]);
// Supplementary Data set on request.php
$contactId = base64_decode($decodedResp["ContactId"]);
$currencyId = base64_decode($decodedResp["CurrencyId"]);

$AzureApplicationID = base64_decode($decodedResp["AzureApplicationID"]);
$AzureApplicationSecret = base64_decode($decodedResp["AzureApplicationSecret"]);
$AzureTenantId = base64_decode($decodedResp["AzureTenantId"]);
$CRMServerURL = base64_decode($decodedResp["CRMServerURL"]);
$PortalSuccessURL = base64_decode($decodedResp["PortalSuccessURL"]);
$RealexMerchantID = base64_decode($decodedResp["RealexMerchantID"]);
$RealexPaymentURL = base64_decode($decodedResp["RealexPaymentURL"]);
$RealexSecret = base64_decode($decodedResp["RealexSecret"]);
$WebApiURL = base64_decode($decodedResp["WebApiURL"]);
//$InvoiceId = base64_decode($decodedResp["InvoiceId"]);
//$InvoiceName = base64_decode($decodedResp["InvoiceName"]);

$configParams = [
    'tenantId' => $AzureTenantId,
    'resource' => $CRMServerURL,
    'clientId' =>$AzureApplicationID,
    'clientSecret' => $AzureApplicationSecret,
    'webApiURL' => $WebApiURL
    ];

$realexHpp = new RealexHpp($RealexSecret);

try {
	// CREATE PAYMENT RECORD IN CRM
	$paymentDataArray = array(
		'new_contact@odata.bind' => "/contacts($contactId)",
		'new_name' => 'Payment',
		'new_paymentdate' => date("Y-m-d"),
		//'new_Invoice@odata.bind' => "/invoices($InvoiceId)",
		'new_paymentamount' => $amount/100,
		'new_currency' => $currencyId,
		'new_transactionstatusmessage' => 'Pending...');

	$newPaymentData = json_encode($paymentDataArray);

	$creationResponse = useWebApi("POST", $newPaymentData, "new_payments", $config, $configParams);

	$paymentId = getCreatedPaymentGuid($creationResponse);

    // create the response object
    $hppResponse = $realexHpp->responseFromJson($responseJson);
    $result = $hppResponse->getResult(); // 00
    $message = $hppResponse->getMessage(); // [ test system ] Authorised
    $authCode = $hppResponse->getAuthCode(); // 12345
	$pasref = $hppResponse->getPasRef();
	$orderID = $hppResponse->getOrderId();

	//UPDATE RECORD IN CRM WITH REALEX RESPONSE
	if ($result == '00') {
		$updatePaymentArray = array('new_transactionstatusmessage' => 'Payment Succeeded.',
			'new_transactioncompleted' => true,
			'new_transactionid' => $orderID);

		$updatePaymentData = json_encode($updatePaymentArray);

		useWebApi("PATCH", $updatePaymentData, "new_payments(".$paymentId.")", $config, $configParams);

		$serverError = "false";
		$newURL = "{$PortalSuccessURL}?id=".$paymentId;
		//$newURL = "{$PortalSuccessURL}?message=".$message."&serverError=".$serverError;
		header('Location: '.$newURL);
		exit();
	}
	else {
		$updatePaymentArray = array('new_transactionstatusmessage' => $message);

		$updatePaymentData = json_encode($updatePaymentArray);

		useWebApi("PATCH", $updatePaymentData, "new_payments(".$paymentId.")", $config, $configParams);

		$serverError = "true";
		$newURL = "{$PortalSuccessURL}?id=".$paymentId;
		//$newURL = "{$PortalSuccessURL}?message=".$message."&serverError=".$serverError;
		header('Location: '.$newURL);
		exit();
	}

	return $hppResponse;
} catch (RealexValidationException $e) {
    return $e->getMessage();
} catch (RealexException $e) {
    return $e->getMessage();
}

function getAppAccessToken($config, $configParams)
{
	error_log($configParams[tenantId"]);
	error_log($config["username"]);
	error_log($config["password"]);
	error_log($config["grant_type"]);
	error_log($configParams["resource"]);
	error_log($configParams["clientId"]);
	error_log($configParams["clientSecret"]);
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
	  echo "cURL Error #:" . $err;
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

function getCreatedPaymentGuid($jsonResponse) {
	list($headers, $response) = explode("\r\n\r\n", $jsonResponse, 2);

	$headers = explode("\n", $headers);
	foreach($headers as $header) {
		if (stripos($header, 'OData-EntityId:') !== false) {
			$OData = $header;
		}
	}

	$guid = substr($OData, strrpos($OData, '(') + 1, 36);
	return $guid;
}
?>