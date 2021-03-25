<?php

/**
 * Description of InterswitchAuth
 *
 * @author Abiola.Adebanjo
 */

namespace Interswitch;


include_once __DIR__.'/lib/Utils.php';
include_once __DIR__.'/lib/Constants.php';
include_once __DIR__.'/lib/HttpClient.php';

class Interswitch {

  private $clientId;
  private $clientSecret;
  private $environment;
  private $accessToken;
  private $signature;
  private $signatureMethod;
  private $nonce;
  private $timestamp;
  const ENV_PRODUCTION = "PRODUCTION";
  const ENV_SANDBOX = "SANDBOX";
  const ENV_DEV = "DEVELOPMENT";

public function __construct($clientId, $clientSecret, $environment = null) {
  
  $this->clientId = $clientId;
  $this->clientSecret = $clientSecret;
  if ($environment !== null) {
    $this->environment = $environment;
  }
}



function getPassportUrl($env) {
  if($env === null) return Constants::SANDBOX_BASE_URL.Constants::PASSPORT_RESOURCE_URL;

  if($env === self::ENV_PRODUCTION){
    return Constants::PRODUCTION_BASE_URL . Constants::PASSPORT_RESOURCE_URL;
  }
  else if($env === self::ENV_DEV){
    return "https://qa.interswitchng.com/". Constants::PASSPORT_RESOURCE_URL;
  }
  else {
    return Constants::SANDBOX_BASE_URL.Constants::PASSPORT_RESOURCE_URL;
  }

}

function getUri($env, $uri) {
  if($env === null) return Constants::SANDBOX_BASE_URL . $uri;

  if($env === self::ENV_PRODUCTION){
    return Constants::PRODUCTION_BASE_URL . $uri;
  }
  else if($env === self::ENV_DEV){
    return Constants::DEV_BASE_URL . $uri;
  }
  else {
    return Constants::SANDBOX_BASE_URL . $uri;
  }
}

function send($uri, $httpMethod, $data = null, $headers = null, $signedParameters = null) 
{

  $this->nonce = Utils::generateNonce();
  $this->timestamp = Utils::generateTimestamp();
  $this->signatureMethod = Constants::SIGNATURE_METHOD_VALUE;

  $passportUrl = $this->getPassportUrl($this->environment);
  $uri = $this->getUri($this->environment, $uri);

  $this->signature = Utils::generateSignature($this->clientId, $this->clientSecret, $uri, $httpMethod, $this->timestamp, $this->nonce, $signedParameters);

  $passportResponse = Utils::generateAccessToken($this->clientId, $this->clientSecret, $passportUrl);
  
  if($passportResponse[Constants::HTTP_CODE] === 200) {
    $this->accessToken = json_decode($passportResponse[Constants::RESPONSE_BODY], true)['access_token'];
  } else {
    return $passportResponse;
  }

  $authorization = 'Bearer ' . $this->accessToken;
  
  $constantHeaders = [
    'Authorization: ' . $authorization,
    'SignatureMethod: ' . $this->signatureMethod,
    'Signature: ' . $this->signature,
    'Timestamp: ' . $this->timestamp,
    'Nonce: ' . $this->nonce
  ];

  $contentType = [
    'Content-Type: '. Constants::CONTENT_TYPE
  ];

  if($httpMethod != 'GET')
  {
    $constantHeaders = array_merge($contentType, $constantHeaders);
  }

  if($headers !== null && is_array($headers)) {
    $requestHeaders = array_merge($headers, $constantHeaders);
    $response = HttpClient::send($requestHeaders, $httpMethod, $uri, $data);
  } else {
    $response = HttpClient::send($constantHeaders, $httpMethod, $uri, $data);
  }

  return $response;
}




function sendWithAccessToken($uri, $httpMethod, $accessToken, $data = null, $headers = null, $signedParameters = null) {

  $this->nonce = Utils::generateNonce();
  $this->timestamp = Utils::generateTimestamp();
  $this->signatureMethod = Constants::SIGNATURE_METHOD_VALUE;

  if ($this->environment === NULL) {
    $uri = Constants::SANDBOX_BASE_URL . $uri;
  } else {
    if(strcmp($this->environment, self::ENV_PRODUCTION))
    {
      $uri = Constants::PRODUCTION_BASE_URL . $uri;
    }
    else
    {
      $uri = Constants::SANDBOX_BASE_URL . $uri;
    }
  }
  
  $this->signature = Utils::generateSignature($this->clientId, $this->clientSecret, $uri, $httpMethod, $this->timestamp, $this->nonce, $signedParameters);

  $authorization = 'Bearer ' . $accessToken;

  $constantHeaders = [
    'Authorization: ' . $authorization,
    'SignatureMethod: ' . $this->signatureMethod,
    'Signature: ' . $this->signature,
    'Timestamp: ' . $this->timestamp,
    'Nonce: ' . $this->nonce
  ];

  $contentType = [
    'Content-Type: '. Constants::CONTENT_TYPE
  ];

  if($httpMethod != 'GET')
  {
    $constantHeaders = array_merge($contentType, $constantHeaders);
  }

  //echo "<br>Headers 2: ";
  //print_r($headers);
  if($headers !== null && is_array($headers)) {
   //echo "<br> Headers is not null: " . $headers;
   $requestHeaders = array_merge($headers, $constantHeaders);
   //echo "<br> New merged Headers: " ;
   //print_r($requestHeaders);
   $response = HttpClient::send($requestHeaders, $httpMethod, $uri, $data);
  }
  else {
   //echo "<br>Headers is null";  
   $response = HttpClient::send($constantHeaders, $httpMethod, $uri, $data);
  }

  return $response;
}




function getAuthData($pan, $expDate, $cvv, $pin, $publicModulus = null, $publicExponent = null) {

  $authData = Utils::getAuthData($pan, $expDate, $cvv, $pin, $publicModulus, $publicExponent);

  return $authData;
}



function getSecureData($pan, $expDate, $cvv, $pin, $amt, $msisdn, $ttid) 
{
  //echo "<br>Pin: " . $pin;
  //echo "<br>CVV: " . $cvv;
  //echo "<br>Exp Date: " . $expDate;
 
  $options = array(
    'expiry' => $expDate,
    'pan' => $pan,
    'ttId' => $ttid,
    'amount' => $amt,
    'mobile' => $msisdn   
  );

  $pinData = array(
    'pin' => $pin,
    'cvv' => $cvv,
    'expiry' => $expDate
  );

 $secure = Utils::generateSecureData($options, $pinData);

 //echo "<br>Secure Data: " . $secure['secureData'];
 //echo "<br>Pin Block: " . $secure['pinBlock'];
 //echo "<br>Mac: " . $secure['mac'];
 
 return $secure;
}




function getAccessToken() {
    return $this->accessToken;
}

    function getSignature() {
        return $this->signature;
    }

    function getSignatureMethod() {
        return $this->signatureMethod;
    }

    function getNonce() {
        return $this->nonce;
    }

    function getTimestamp() {
        return $this->timestamp;
    }

}

