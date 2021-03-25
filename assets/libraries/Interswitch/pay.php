<?php
	require_once 'source/Interswitch.php';


	use Interswitch\Interswitch as Interswitch;


	$respBody = payWithToken();
	
	echo "<br/>";
	echo "Response::";
	echo "<br/>";
	echo "<pre>";
	print_r($respBody);
	
	function payWithTokenNoOTP(){
	$signatureCipher = "POST&https://sandbox.interswitchng.com/api/v3/purchases&1536330881&091f90dbbf9748f0b2854a038dbdac44&IKIA9614B82064D632E9B6418DF358A6A4AEA84D7218&XCTiBtLy1G9chAnyg0z3BcaFK4cVpwDg/GTw2EmjTZ8=&100&Ya9eUTuNGrt8A0msJ79DMwUQRTSiSNgWERgclvXdzrM/zq+2BhrjtVZW0qN+M6FhwK55afP7k88ayrUYHJzky9RfdzKT7Hqn82l4BRUbldowQR4HG/I5Tz06ZjBCDuTOe5zao/Ruf3pghlXIkUEZjPHn1aoJC/7EWM63DTche32/9fxFq1lwYQJWRwov86VvLk5OYfzP9pKnBDzNlqV3BxNK5MDtDB+cg0IduWXObDecyHX0TNZWKXI6dtON74xB7MwkhdPqKfAuqX/tqwFyUpcp8bYcaoPMkGv8hCQiRrfX5A9naPUJKZeLOaZTgCairpYnYHsHODpEgPkEfCAMyg==";

 
		$headers = array(
		'Authorization: Bearer eyJhbGciOiJSUzI1NiJ9.eyJhdWQiOlsiY2FyZGxlc3Mtc2VydmljZSIsImluY29nbml0byIsImlzdy1jb2xsZWN0aW9ucyIsImlzdy1jb3JlIiwiaXN3LWxlbmRpbmctc2VydmljZSIsImlzdy1wYXltZW50Z2F0ZXdheSIsInBhc3Nwb3J0IiwicGF5bWVudC1zZXJ2aWNlIiwicHJvamVjdC14LW1lcmNoYW50IiwidmF1bHQiXSwibWVyY2hhbnRfY29kZSI6Ik1YMTg3IiwicHJvZHVjdGlvbl9wYXltZW50X2NvZGUiOiIwNDI1OTQxMzAyNDYiLCJyZXF1ZXN0b3JfaWQiOiIwMDExNzYxNDk5MiIsInNjb3BlIjpbInByb2ZpbGUiXSwiZXhwIjoxNTMyNzU5NjM5LCJqdGkiOiI4MWY4ZDBjYS02ZDViLTQ1ZGUtYjc0MS01YTc1N2EwMGQ2N2IiLCJwYXlhYmxlX2lkIjoiMjMyNCIsImNsaWVudF9pZCI6IklLSUE5NjE0QjgyMDY0RDYzMkU5QjY0MThERjM1OEE2QTRBRUE4NEQ3MjE4IiwicGF5bWVudF9jb2RlIjoiMDUxNDE5ODE1NDY4NSJ9.SBx0bkK4kHW8FfPAdahfIjauyHN1yLYFolqIrgUWZVj03rifMNuatXI45UXWM-bRxdWzl_4NtH-KLPeExYiKRt5G-Wht-j0iNm6EYRaXkzVNyY6U_x2TyRy_K3MDE2610u3aTOgMDS6aAXN4ajtOgTUCJpR40t5UQPNVRY50aM-SloUuBmbtV4QI90iNRNpIVbSVvZubraGI18KVavqGRufIB4LXRZrlzEzasHvo4fVsA6oF7OB6Cjgue1cRuT8-M08WZL5Y79M1ssmmxuJ8DUYfRwZTMOt6HIPweM9h4VFbJeX-Nvbm-v7pAUgbrmVf_7eP6y6YV-PTPuAVsExwlQ',
		'Content-Type: application/json',
		'Nonce: 00e899e7d286a6d442cc30010f8ba1c7',
		'SignatureMethod: SHA1',
		'Timestamp: 1532673801',
		'AuthKeyVersion: 1',
		'Signature: 00e899e7d286a6d442cc30010f8ba1c7'
		);
		
		$customerId = "kandarp.esw@gmail.com";
		$currency = "NGN";
		$amount = "10000";
		$transactionRef = mt_rand(0, 65535);
		$authData = "Ya9eUTuNGrt8A0msJ79DMwUQRTSiSNgWERgclvXdzrM/zq+2BhrjtVZW0qN+M6FhwK55afP7k88ayrUYHJzky9RfdzKT7Hqn82l4BRUbldowQR4HG/I5Tz06ZjBCDuTOe5zao/Ruf3pghlXIkUEZjPHn1aoJC/7EWM63DTche32/9fxFq1lwYQJWRwov86VvLk5OYfzP9pKnBDzNlqV3BxNK5MDtDB+cg0IduWXObDecyHX0TNZWKXI6dtON74xB7MwkhdPqKfAuqX/tqwFyUpcp8bYcaoPMkGv8hCQiRrfX5A9naPUJKZeLOaZTgCairpYnYHsHODpEgPkEfCAMyg==";
		
		$data = array(
		 "customerId" => $customerId,
		 "amount" => $amount,
		 "transactionRef" => $transactionRef,
		 "currency" => $currency,
		 "authData" => $authData
		);
		
		$request = json_encode($data);
		//Setup headers:
		// echo "<pre>";print_r($headers);exit;
		//Setup curl, add headers and post parameters.
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://sandbox.interswitchng.com/api/v3/purchases' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true  );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, $request );
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    
		//Send the request
		$response = curl_exec($ch); //echo "<pre>";print_r($response);exit;
		if ($response === FALSE) {
			// die('Curl failed: ' . curl_error($ch));
			if($ENABLE_PUBNUB == "No"){ 
				$returnArr['Action'] = "0";
				$returnArr['message'] = "LBL_SERVER_COMM_ERROR";
				$returnArr['ERROR'] = curl_error($ch);
				echo json_encode($returnArr);
				exit;
			}   
		}
		// $responseArr = json_decode($response);
		// $success = $responseArr->success; 
		//Close request
		curl_close($ch);
		return $response;
	}
		
	function payWithToken(){
		$CLIENT_ID = "IKIA9614B82064D632E9B6418DF358A6A4AEA84D7218";
		$CLIENT_SECRET = "XCTiBtLy1G9chAnyg0z3BcaFK4cVpwDg/GTw2EmjTZ8=";
		$interswitch = new Interswitch($CLIENT_ID, $CLIENT_SECRET);
		
		$customerId = "kandarp.esw@gmail.com";
		$currency = "NGN";
		$amount = "10000";
		$transactionRef = mt_rand(0, 65535);
		$authData = "Ya9eUTuNGrt8A0msJ79DMwUQRTSiSNgWERgclvXdzrM/zq+2BhrjtVZW0qN+M6FhwK55afP7k88ayrUYHJzky9RfdzKT7Hqn82l4BRUbldowQR4HG/I5Tz06ZjBCDuTOe5zao/Ruf3pghlXIkUEZjPHn1aoJC/7EWM63DTche32/9fxFq1lwYQJWRwov86VvLk5OYfzP9pKnBDzNlqV3BxNK5MDtDB+cg0IduWXObDecyHX0TNZWKXI6dtON74xB7MwkhdPqKfAuqX/tqwFyUpcp8bYcaoPMkGv8hCQiRrfX5A9naPUJKZeLOaZTgCairpYnYHsHODpEgPkEfCAMyg==";
		
		$data = array(
		 "customerId" => $customerId,
		 "amount" => $amount,
		 "transactionRef" => $transactionRef,
		 "currency" => $currency,
		 "authData" => $authData
		);
		$request = json_encode($data);
			
		// add records to the log
		$response = $interswitch->send("api/v3/purchases", "POST", $request);
		$httpRespCode = $response["HTTP_CODE"];
		$respBody = $response["RESPONSE_BODY"];
		
		return $respBody;
	}
		
	function payWithCard(){
		// Initialize Interswitch object
		$CLIENT_ID = "IKIA9614B82064D632E9B6418DF358A6A4AEA84D7218";
		$CLIENT_SECRET = "XCTiBtLy1G9chAnyg0z3BcaFK4cVpwDg/GTw2EmjTZ8=";
		$interswitch = new Interswitch($CLIENT_ID, $CLIENT_SECRET);

		// Create sensitive data
		$pan = "6280511000000095";
		$expiryDate = "5004";
		$cvv = "111";
		$pin = "1111";
		$authData = $interswitch->getAuthData($pan, $expiryDate, $cvv, $pin);

		// Build request data
		$transactionRef = "ISW|API|JAM|" . mt_rand(0, 65535);
		$customerId = "api-jam@interswitchgroup.com";
		$currency = "NGN";
		$amount = "50000"; // Minor denomination

		$data = array(
		 "customerId" => $customerId,
		 "amount" => $amount,
		 "transactionRef" => $transactionRef,
		 "currency" => $currency,
		 "authData" => $authData
		);
		$request = json_encode($data);
			
		// add records to the log
		$response = $interswitch->send("api/v2/purchases", "POST", $request);
		$httpRespCode = $response["HTTP_CODE"];
		$respBody = $response["RESPONSE_BODY"];
		
		return $respBody;
	}
?>