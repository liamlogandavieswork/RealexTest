header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-type');

// configure client, request and HPP settings
$config = new ServicesConfig();
$config->merchantId = "MerchantId";
$config->accountId = "internet";
$config->sharedSecret = "secret";
$config->serviceUrl = "https://pay.sandbox.realexpayments.com/pay";

$config->hostedPaymentConfig = new HostedPaymentConfig();
$config->hostedPaymentConfig->version = HppVersion::VERSION_2;
$service = new HostedService($config);

// Add 3D Secure 2 Mandatory and Recommended Fields
$hostedPaymentData = new HostedPaymentData();
$hostedPaymentData->customerEmail = "james.mason@example.com";
$hostedPaymentData->customerPhoneMobile = "44|07123456789";
$hostedPaymentData->addressesMatch = false;

$billingAddress = new Address();
$billingAddress->streetAddress1 = "Flat 123";
$billingAddress->streetAddress2 = "House 456";
$billingAddress->streetAddress3 = "Unit 4";
$billingAddress->city = "Halifax";
$billingAddress->postalCode = "W5 9HR";
$billingAddress->country = "826";

$shippingAddress = new Address();
$shippingAddress->streetAddress1 = "Apartment 825";
$shippingAddress->streetAddress2 = "Complex 741";
$shippingAddress->streetAddress3 = "House 963";
$shippingAddress->city = "Chicago";
$shippingAddress->state = "IL";
$shippingAddress->postalCode = "50001";
$shippingAddress->country = "840";

try {
        $hppJson = $service->charge(19.99)
        ->withCurrency("EUR")
        ->withHostedPaymentData($hostedPaymentData)
        ->withAddress($billingAddress, AddressType::BILLING)
        ->withAddress($shippingAddress, AddressType::SHIPPING)
        ->serialize();
        
        // TODO: pass the HPP request JSON to the JavaScript, iOS or Android Library
        
} catch (ApiException $e) {
        // TODO: Add your error handling here
}