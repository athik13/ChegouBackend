<?php		
			/*	
				This api run only 2 times per a day 
			*/
    session_start();

	include_once('../../common.php');
	include_once('../../assets/libraries/class.general.php');
	$generalobj = new General();
	 
		  $app_id =$EXCHANGE_CURRENCY_RATES_APP_ID;
	$sql="SELECT vName FROM currency WHERE eDefault='Yes'";
	$db_currency=$obj->MySQLSelect($sql);
	$default_currency = $db_currency[0]['vName'];
	
				$ch = curl_init();			
				$oxr_url = "https://openexchangerates.org/api/latest.json?app_id=" . $app_id.'&base='.$default_currency;

				// Open CURL session:
				$ch = curl_init($oxr_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				// Get the data:
				$json = curl_exec($ch);
				curl_close($ch);  
				
		 
			 	$exchangeRates = json_decode($json, TRUE);
			 
				if (isset($exchangeRates['rates']))
				{	 
						foreach ($exchangeRates['rates'] as $key => $data)
						{
								 
								$locale='en-US';  
							 	$currency=$key; 
								
								$fmt = new NumberFormatter( $locale."@currency=$currency", NumberFormatter::CURRENCY );
								$vSymbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
								header("Content-Type: text/html; charset=UTF-8;");
								 
									$eDefault="No";  
									$ThresholdAmount="100";
								 	$fThresholdAmount=$ThresholdAmount*$data; 
									if($key=="ZWL")
									{
										$key="ZWD";
									}
								$sql = "select  vName FROM currency  where vName='".$key."'" ;
								$db_select_currency  = $obj->MySQLSelect($sql); 
								$counter = count($db_select_currency); 
							 
								if ($counter>0)
									{	  
										$notkey=$db_select_currency[0]['vName'];
										if($notkey!="USD")
										{
										    $Table="currency"; 
											$updateData['Ratio'] = trim($data);		
											$updateData['fThresholdAmount'] = trim($fThresholdAmount);		
											 
											$where = "vName='".$key."'";
											$res = $obj->MySQLQueryPerform($Table, $updateData, 'update', $where);	
											 
											$Ratio=trim($data);										
											$subquery ="update `master_currency`  SET `Ratio` = '" . $Ratio . "' where `vName` = '" . $key . "'";
											$obj->sql_query($subquery);	  							
										}
									}else{
										
									  	$query = "Insert into  master_currency (vName,vSymbol,eDefault,Ratio) values ('".$key."','".$vSymbol."','".$eDefault."','".$data."')" ;
										$obj->sql_query($query);
										$id =   $obj->GetInsertId();
  
												
									}
						}
				}
  
?>
