<?php

include_once('../common.php');
include_once('../assets/libraries/class.general.php');
$generalobj = new General();

require_once("OAuth.php");
define('CID', '78k11dlg9wrgsz');
define('CSECKEY', 'yFcSkIFKGoL4Y5jR');
define('RURL', 'http://192.168.1.131/cubejekdev/linkedin-login/linkedinconfig.php');
//define('SCOPE', 'r_liteprofile r_emailaddress');
define('SCOPE', 'r_emailaddress r_liteprofile w_member_social');
class LinkedIn {

    public $base_url = "http://api.linkedin.com";
    public $secure_base_url = "https://api.linkedin.com";
    public $oauth_callback = "oob";
    public $consumer;
    public $request_token;
    public $access_token;
    public $oauth_verifier;
    public $signature_method;
    public $request_token_path;
    public $access_token_path;
    public $authorize_path;

    function __construct($consumer_key, $consumer_secret, $oauth_callback = NULL) {

        if ($oauth_callback) {
            $this->oauth_callback = $oauth_callback;
        }

        $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret, $this->oauth_callback);
        $this->signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        $this->request_token_path = $this->secure_base_url . "/uas/oauth/requestToken";
        //$this->access_token_path = $this->secure_base_url . "/uas/oauth/accessToken";oauth/v2/accessToken
        $this->access_token_path = $this->secure_base_url . "/oauth/v2/accessToken";
        //$this->authorize_path = $this->secure_base_url . "/uas/oauth/authorize";uas/oauth2/authorization
        $this->authorize_path = $this->secure_base_url . "/uas/oauth2/authorization";
        
    }

    function getRequestToken() {
        $consumer = $this->consumer;
        $request = OAuthRequest::from_consumer_and_token($consumer, NULL, "GET", $this->request_token_path);
        $request->set_parameter("oauth_callback", $this->oauth_callback);
        $request->sign_request($this->signature_method, $consumer, NULL);
        $headers = Array();
        $url = $request->to_url();
        $response = $this->httpRequest($url, $headers, "GET");
        parse_str($response, $response_params);
        //print_R($response_params); exit;
        $this->request_token = new OAuthConsumer($response_params['oauth_token'], $response_params['oauth_token_secret'], 1);
    }

    function generateAuthorizeUrl() {
        $consumer = $this->consumer;
        $request_token = $this->request_token;
        //echo $this->authorize_path; exit;
        
        return $this->authorize_path . "?oauth_token=" . $request_token->key;
    }

    function getAccessToken($oauth_verifier) {
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->request_token, "GET", $this->access_token_path);
        $request->set_parameter("oauth_verifier", $oauth_verifier);
        $request->sign_request($this->signature_method, $this->consumer, $this->request_token);
        $headers = Array();
        $url = $request->to_url();
        $response = $this->httpRequest($url, $headers, "GET");
        parse_str($response, $response_params);
        $this->access_token = new OAuthConsumer($response_params['oauth_token'], $response_params['oauth_token_secret'], 1);
    }

    function getProfile($resource = "~") {
        //$profile_url = $this->base_url . "/v1/people/" . $resource;
        $profile_url = "https://api.linkedin.com/v2/me";
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->access_token, "GET", $profile_url);
        $request->sign_request($this->signature_method, $this->consumer, $this->access_token);
        $auth_header = $request->to_header("https://api.linkedin.com"); # this is the realm
        # This PHP library doesn't generate the header correctly when a realm is not specified.
        # Make sure there is a space and not a comma after OAuth
        // $auth_header = preg_replace("/Authorization\: OAuth\,/", "Authorization: OAuth ", $auth_header);
        // # Make sure there is a space between OAuth attribute
        // $auth_header = preg_replace('/\"\,/', '", ', $auth_header);
        // $response will now hold the XML document
        $response = $this->httpRequest($profile_url, $auth_header, "GET");
        
        return $response;
    }

    function setStatus($status) {
        $profile_url = $this->base_url . "/v1/people/~";
        $status_url = $this->base_url . "/v1/people/~/current-status";
        echo "Setting status...\n";
        $xml = "<current-status>" . htmlspecialchars($status, ENT_NOQUOTES, "UTF-8") . "</current-status>";
        echo $xml . "\n";
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->access_token, "PUT", $status_url);
        $request->sign_request($this->signature_method, $this->consumer, $this->access_token);
        $auth_header = $request->to_header("https://api.linkedin.com");

        $response = $this->httpRequest($profile_url, $auth_header, "GET");
        return $response;
    }

    # Parameters should be a query string starting with "?"
    # Example search("?count=10&start=10&company=LinkedIn");

    function search($parameters) {
        $search_url = $this->base_url . "/v1/people-search:(people:(id,first-name,last-name,picture-url,site-standard-profile-request,headline),num-results)" . $parameters;
        //$search_url = $this->base_url . "/v1/people-search?keywords=facebook";

        echo "Performing search for: " . $parameters . "<br />";
        echo "Search URL: $search_url <br />";
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->access_token, "GET", $search_url);
        $request->sign_request($this->signature_method, $this->consumer, $this->access_token);
        $auth_header = $request->to_header("https://api.linkedin.com");
        $response = $this->httpRequest($search_url, $auth_header, "GET");
        return $response;
    }

    function httpRequest($url, $auth_header, $method, $body = NULL) {
        if (!$method) {
            $method = "GET";
        };

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header)); // Set the headers.

        if ($body) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header, "Content-Type: text/xml;charset=utf-8"));
        }

        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }
    
    
    
    

//get the auth url for linkedin
function getAuthorizationCode() {
    $params = array('response_type' => 'code',
										'client_id' => CID,
										'scope' => SCOPE,
										'state' => uniqid('', true), // unique long string
										'redirect_uri' => RURL,
              );

    // Authentication request
    $url = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);

    // Needed to identify request when it returns to us
    $_SESSION['state'] = $params['state'];

    // Redirect user to authenticate
    header("Location: $url");
    exit;
}


function linkedin_auth_get() {
	
		$code = $_GET['code'];
		//$url = 'https://www.linkedin.com/oauth/v2/oauth2_access_token';
		$url = "https://www.linkedin.com/oauth/v2/accessToken";
		$data_access_token = array(
					'grant_type' => 'authorization_code',
					'code' => $code,
					'redirect_uri' => RURL,
					'client_id' => CID,
					'client_secret' => CSECKEY
					);
		
		//$linkedin_data = send_req($url, $data_access_token, 'POST')['access_token'];
		$linkedin_data = $this->send_req($url, $data_access_token, 'POST');
		$linkedin_data = json_decode($linkedin_data,true);
		print_R($linkedin_data); exit; 
}
	

function send_req($url, $data, $method) {
    
      $options = array(
          'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => $method,
              'content' => http_build_query($data)
          )
      );
      
      $context  = stream_context_create($options);
      
      $result = file_get_contents($url, false, $context);
      
      if ($result === FALSE) {  }
      if (gettype($result) == 'string') {
        $result = json_decode($result, true);
      } else if (gettype($result) == 'object') {
        $result = array($result);
      }
      
      $access_token = $result['access_token'];
      $response = $this->fetch_linkedin_data($access_token);
      return $response;
}


function fetch_linkedin_data($access_token) {
	
		$curl = curl_init();
		curl_setopt_array($curl, array(
						//CURLOPT_URL => "https://api.linkedin.com/v1/people/~:(id,first-name,last-name,picture-url,public-profile-url,email-address)?format=json",
						//CURLOPT_URL => "https://api.linkedin.com/v2/people/~:(id,first-name,last-name,picture-url,public-profile-url,email-address)?format=json",
						CURLOPT_URL => "https://api.linkedin.com/v2/me",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 10,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "GET",
						CURLOPT_HTTPHEADER => array(
						"authorization: Bearer ".$access_token,
						"cache-control: no-cache",
						//"connection: Keep-Alive"
						),
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
		} else {
		//$response = json_decode($response, true);
		return $response;
		}
		
}

}
