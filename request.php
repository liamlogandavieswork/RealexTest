<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-type');

require_once ('vendor/autoload.php');

use com\realexpayments\hpp\sdk\domain\HppRequest;
use com\realexpayments\hpp\sdk\RealexHpp;
use com\realexpayments\hpp\sdk\RealexValidationException;
use com\realexpayments\hpp\sdk\RealexException;

date_default_timezone_set('Europe/Dublin');

class PaymentRequest {
	public $amount;
	public $MerchantID;
	public $OrderID;
	public $ProductID;
	public $Comment;
	public $TimeStamp;
}
$request = file_get_contents('php://input');
$decoded_request = json_decode($request, true);

$amount = $decoded_request["Amount"];
$currencyId = $decoded_request["CurrencyId"];
$contactId = $decoded_request["ContactId"];

$AzureApplicationID = $decoded_request["AzureApplicationID"];
$AzureApplicationSecret = $decoded_request["AzureApplicationSecret"];
$AzureTenantId = $decoded_request["AzureTenantId"];
$CRMServerURL = $decoded_request["CRMServerURL"];
$PortalSuccessURL = $decoded_request["PortalSuccessURL"];
$RealexMerchantID = $decoded_request["RealexMerchantID"];
$RealexPaymentURL = $decoded_request["RealexPaymentURL"];
$RealexSecret = $decoded_request["RealexSecret"];
$WebApiURL = $decoded_request["WebApiURL"];
$InvoiceId = $decoded_request["InvoiceId"];
$InvoiceName = $decoded_request["InvoiceName"];
$MembershipNumber =$decoded_request["MembershipNumber"]; // CUST_NUM
$EmailAddress = $decoded_request["EmailAddress"]; // COMMENT1
$CustomerName = $decoded_request["CustomerName"]; // COMMENT2
$InvoiceNumber = $decoded_request["InvoiceNumber"]; // VAR_REF
$ProductCodes = $decoded_request["ProductCodes"]; // PROD_ID

$supData = array();
$supData["CurrencyId"] = $currencyId;
$supData["ContactId"] = $contactId;

$supData["AzureApplicationID"] = $AzureApplicationID;
$supData["AzureApplicationSecret"] = $AzureApplicationSecret;
$supData["AzureTenantId"] = $AzureTenantId;
$supData["CRMServerURL"] = $CRMServerURL;
$supData["PortalSuccessURL"] = $PortalSuccessURL;
$supData["RealexMerchantID"] = $RealexMerchantID;
$supData["RealexPaymentURL"] = $RealexPaymentURL;
$supData["RealexSecret"] = $RealexSecret;
$supData["WebApiURL"] = $WebApiURL;
$supData["InvoiceId"] = $InvoiceId;
$supData["InvoiceName"] = $InvoiceName;

$merchantId = $RealexMerchantID;
//$payment_request->MerchantID = $decoded_request["merchant_ID"];

$hppRequest = (new HppRequest())
    ->addMerchantId($merchantId)
    ->addAccount("internet")
    ->addAmount($amount * 100) // realex takes the amount with no decimals
    ->addCurrency($currencyId)
    ->addAutoSettleFlag(TRUE)
    ->addSupplementaryData($supData)
    ->addCustomerNumber($MembershipNumber)
    ->addCommentOne($EmailAddress)
    ->addCommentTwo($CustomerName)
    ->addVariableReference($InvoiceNumber)
    ->addProductId($ProductCodes)
	->addTimeStamp(date('YmdHis'));

$realexHpp = new RealexHpp($RealexSecret);

try {
    $requestJson = $realexHpp->requestToJson($hppRequest);
    echo $requestJson;
    //echo "alert($requestJson);";
    // code here for your application to pass the JSON string to the client-side library
    return $requestJson;
}
catch (RealexValidationException $e) {
    echo $e->getMessage();
    //echo "alert($e->getMessage());";
    return $e->getMessage();
}
catch (RealexException $e) {
    echo $e->getMessage();
    //echo "alert($e->getMessage());";
    return $e->getMessage();
}

?>