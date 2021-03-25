<?php
class DataHelper
{

    private $SECRET_KEY = "";//16 char secret key
	
	function __construct() {
		global $SECRET_KEY;
		
		$seperation_keyword = "!!@@!!@@@@@!!@@!!";
		
		if(!empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == "192.168.1.151" && !empty($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], 'cubejekdev') !== false) == true && (empty($_SERVER['CONTENT_TYPE']) || (isset($_SERVER['CONTENT_TYPE']) && (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false) == false))){
			$queryData = file_get_contents('php://input');
			if(!empty($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == "GET"){
				$queryData = $_SERVER['QUERY_STRING'];
			}
			
			$REQUEST_PACKAGE_NAME_ARR = explode($seperation_keyword,$queryData);
			
			if(count($REQUEST_PACKAGE_NAME_ARR) > 1){
				$REQUEST_PACKAGE_NAME_ENC = $REQUEST_PACKAGE_NAME_ARR[count($REQUEST_PACKAGE_NAME_ARR) - 2];
				$SERVER_URL = base64_decode($REQUEST_PACKAGE_NAME_ARR[count($REQUEST_PACKAGE_NAME_ARR) - 1]);
			
				// $SERVER_URL = $this -> currentPagePath();
				$REQUEST_PACKAGE_NAME = base64_decode($REQUEST_PACKAGE_NAME_ENC);
			}
			
			if(!empty($REQUEST_PACKAGE_NAME)){
				$SECRET_KEY = base64_encode(md5($REQUEST_PACKAGE_NAME.$SERVER_URL.md5(base64_encode(implode("",unpack("C*", base64_encode(implode("",unpack("C*", $REQUEST_PACKAGE_NAME)))))))));
				
				$SECRET_KEY = substr($SECRET_KEY,strlen($SECRET_KEY) - 16,strlen($SECRET_KEY) - 1);
				$request_enc_data =  str_replace($seperation_keyword.$REQUEST_PACKAGE_NAME_ENC,"",$queryData);
				$request_data = urldecode($this -> decrypt($request_enc_data));
				$_POST["APP_CONFIG_PARAMS_PACKAGE"] = $request_data;
			}
		}
    }
	
	function currentPagePath() {
		 $pageURL = 'http';
		 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"];

		 $pageURL = str_replace(basename($_SERVER['SCRIPT_NAME']),"",$pageURL);

		 return $pageURL;
	}
	
	function currentPageURL() {
		 $pageURL = 'http';
		 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

		 return $pageURL;
	}
    /**
     * @param string $str
     * @param bool $isBinary whether to encrypt as binary or not. Default is: false
     * @return string Encrypted data
     */
    function encrypt($str1, $isBinary = false)
    {
		global $SECRET_KEY;
        $size = 32;//mcrypt_get_block_size('aes', 'cbc'); 
        $str = $this->pkcs5_pad($str1); 
        $iv = $SECRET_KEY;
		
        $str = $isBinary ? $str : utf8_decode($str);
        $td = mcrypt_module_open('rijndael-128', ' ', 'cbc', $iv);
        mcrypt_generic_init($td, $SECRET_KEY, $iv);
        $encrypted = mcrypt_generic($td, $str);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $isBinary ? $encrypted : base64_encode($encrypted);
    }
    /**
     * @param string $code
     * @param bool $isBinary whether to decrypt as binary or not. Default is: false
     * @return string Decrypted data
     */
    function decrypt($code, $isBinary = false)
    {
		global $SECRET_KEY;
	
        $code = $isBinary ? $code : base64_decode($code);
        $iv = $SECRET_KEY;
        $td = mcrypt_module_open('rijndael-128', ' ', 'cbc', $iv);
        mcrypt_generic_init($td, $SECRET_KEY, $iv);
        $decrypted = mdecrypt_generic($td, $code);
		
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $isBinary ? trim($decrypted) : utf8_encode(trim($this->pkcs5_unpad($decrypted)));
    }
	
    function pkcs5_pad ($text) 
    { 
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $size - (strlen($text) % $size);
        return $text . str_repeat(chr($pad), $pad);
    } 
    
    function pkcs5_unpad($text) 
    { 
        $pad = ord($text{strlen($text)-1}); 
        if ($pad > strlen($text)) return false; 
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false; 
        return substr($text, 0, -1 * $pad); 
    }
	
	function setResponse($dataArr){
		global $SECRET_KEY;
	
	// echo json_encode($dataArr, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);exit;
		if(!empty($SECRET_KEY)){
			echo $this->encrypt(json_encode($dataArr));exit;
		}else{
			echo json_encode($dataArr, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);exit;
		}
		// , JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE
	}
}
?>