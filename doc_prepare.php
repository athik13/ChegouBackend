<?
	include_once("common.php");
	//error_reporting(E_ALL);
	global $generalobj,$obj;
	
	function is_multidimensional(array $array) {
		if(count($array) > 0 && is_array($array[0])){
			return true;
		}
		// return count($array) !== count($array, COUNT_RECURSIVE);
		return false;
	}
	
	$sample_description_types = "This is an sample description of web service type";
	$sample_purpose_types = "This is an sample purpose of web service type";
	$sample_call_to_action_types = "This is an sample call to action of web service type";

	$sql_doc_data =$obj->MySQLSelect( "SELECT * FROM request_data");
	 	 
	$myfile = fopen("webimages/index.html.md", "w");
	fwrite($myfile, "---\n");
	fwrite($myfile, "title: API Reference\n");
	fwrite($myfile, "\n");
	fwrite($myfile, "language_tabs:\n");
	fwrite($myfile, "  - php\n");
	fwrite($myfile, "\n");
	fwrite($myfile, "toc_footers:\n");
	fwrite($myfile, "  - Documentation Powered by  <a href='https://www.v3cube.com'>V3Cube</a>\n\n\n");
	fwrite($myfile, "search: true\n");
	fwrite($myfile, "---\n\n");
	fwrite($myfile, "# Introduction\n\n");
	fwrite($myfile, "Welcome to v3cube's product api documentation. You will find all help related to our product's api.\n\n");
	fwrite($myfile, "We have used mysql db and php as server side scripting language. You can view code examples in the dark area to the right, and you can switch the programming language of the examples with the tabs in the top right.\n\n");

	fwrite($myfile, "# Webservice\n\n");

	$samples_count = 25;
	
	foreach($sql_doc_data as $sql_doc_data_item){
		if($samples_count < 1){
			break;
		}
		$samples_count = $samples_count - 1;
		
		$tRequestParam = $sql_doc_data_item['tRequestParam'];
		$tResponse = $sql_doc_data_item['tResponse'];
		$tErrorResponse = $sql_doc_data_item['tErrorResponse'];
		$titleOfType =  empty($sql_doc_data_item['tTitle']) ? $sql_doc_data_item['tType'] : $sql_doc_data_item['tTitle'];
		$descriptionOfType = empty($sql_doc_data_item['tDescription']) ? $sample_description_types." '".$titleOfType."'" : $sql_doc_data_item['tDescription'];
		$tPurpose = empty($sql_doc_data_item['tPurpose']) ? $sample_purpose_types." '".$titleOfType."'" : $sql_doc_data_item['tPurpose'];
		$tCallToAction = empty($sql_doc_data_item['tCallToAction']) ? $sample_call_to_action_types." '".$titleOfType."'" : $sql_doc_data_item['tCallToAction'];
		
		fwrite($myfile, "## ".$titleOfType."\n\n");
		// fwrite($myfile, "**Summary**".$descriptionOfType."\n\n");
		fwrite($myfile, "**Description:** ".$descriptionOfType."\n\n");
		fwrite($myfile, "**Purpose:** ".$tPurpose."\n\n");
		fwrite($myfile, "**Call To Action:** ".$tCallToAction."\n");
		// fwrite($myfile, "### HTTP Request\n\n");
		// fwrite($myfile,"***POST***\n\n");
		fwrite($myfile,">**Type:** ".$titleOfType."\n\n");
		if(!empty($tRequestParam)){	
			$tRequestParam_json = json_decode($tRequestParam);
			// fwrite($myfile,"**Parameters**\n\n");
			// fwrite($myfile,"| Name | Type | Description | Required |\n");
			// fwrite($myfile,"| Name | Type |\n");
			// fwrite($myfile,"| ---- | ---- | ----------- | -------- |\n");
			// fwrite($myfile,"| ---- | ---- |\n");
			fwrite($myfile,"```php\n");
			fwrite($myfile,"curl -X POST \"/webservice_shark.php\"\n");
			fwrite($myfile,"-H \"Content-Type: application/x-www-form-urlencoded\"\n\n");
			fwrite($myfile,"```\n\n");
			fwrite($myfile,">Example of Request Parameters\n\n");
			fwrite($myfile,"```json\n");
			
			$json_string = json_encode($tRequestParam_json, JSON_PRETTY_PRINT);
			fwrite($myfile,$json_string."\n\n");
			
			fwrite($myfile,"```\n\n");
			
			foreach($tRequestParam_json as $key => $val) {
				/* echo "KEY IS: $key<br/>";
				echo "Value IS: $val<br/>"; */
				// gettype($val);
				
				// fwrite($myfile,"| ".$key." | ".gettype($val)." |  | Yes |\n");
				// fwrite($myfile,"| ".$key." | ".gettype($val)." |\n");
				// | body | body | List of user object | Yes |  |
			}
		}
		
		if(!empty($tResponse)){
			$tResponse_json = json_decode($tResponse);
			
			fwrite($myfile,">Example of Response\n\n");
			fwrite($myfile,"```json\n");
			
			if(!empty($tResponse_json->ServiceCategories)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);

				if(count($tResponse_json_arr['ServiceCategories']) > 5){
					$tResponse_json_arr['ServiceCategories'] = array_slice($tResponse_json_arr['ServiceCategories'], 0, -(count($tResponse_json_arr['ServiceCategories'])-2));
					$tResponse_json_arr['ServiceCategories'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->UpdatedLanguageLabels)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);
				
				if(count($tResponse_json_arr['UpdatedLanguageLabels']) > 5){
					$tResponse_json_arr['UpdatedLanguageLabels'] = array_slice($tResponse_json_arr['UpdatedLanguageLabels'], 0, -(count($tResponse_json_arr['UpdatedLanguageLabels'])-5));
					$tResponse_json_arr['UpdatedLanguageLabels']["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->LanguageLabels)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);

				if(count($tResponse_json_arr['LanguageLabels']) > 5){
					$tResponse_json_arr['LanguageLabels'] = array_slice($tResponse_json_arr['LanguageLabels'], 0, -(count($tResponse_json_arr['LanguageLabels'])-5));
					$tResponse_json_arr['LanguageLabels']["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->LIST_CURRENCY)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);
				if(count($tResponse_json_arr['LIST_CURRENCY']) > 5){
					$tResponse_json_arr['LIST_CURRENCY'] = array_slice($tResponse_json_arr['LIST_CURRENCY'], 0, -(count($tResponse_json_arr['LIST_CURRENCY'])-5));
					$tResponse_json_arr['LIST_CURRENCY'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->LIST_LANGUAGES)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);
				if(count($tResponse_json_arr['LIST_LANGUAGES']) > 5){
					$tResponse_json_arr['LIST_LANGUAGES'] = array_slice($tResponse_json_arr['LIST_LANGUAGES'], 0, -(count($tResponse_json_arr['LIST_LANGUAGES'])-5));
					$tResponse_json_arr['LIST_LANGUAGES'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->message)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);
				$finalcountOfData = 5;
				if($titleOfType == "getdriverridehistory"){
					$finalcountOfData = 1;
				}
				if(is_array($tResponse_json_arr['message']) && count($tResponse_json_arr['message']) > $finalcountOfData && is_multidimensional($tResponse_json_arr['message'])){
					$tResponse_json_arr['message'] = array_slice($tResponse_json_arr['message'], 0, -(count($tResponse_json_arr['message'])-$finalcountOfData));
					$tResponse_json_arr['message'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->StateList)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);

				if(is_array($tResponse_json_arr['StateList']) && count($tResponse_json_arr['StateList']) > 5 && is_multidimensional($tResponse_json_arr['StateList'])){
					$tResponse_json_arr['StateList'] = array_slice($tResponse_json_arr['StateList'], 0, -(count($tResponse_json_arr['StateList'])-5));
					$tResponse_json_arr['StateList'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->CityList)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);

				if(is_array($tResponse_json_arr['CityList']) && count($tResponse_json_arr['CityList']) > 5 && is_multidimensional($tResponse_json_arr['CityList'])){
					$tResponse_json_arr['CityList'] = array_slice($tResponse_json_arr['CityList'], 0, -(count($tResponse_json_arr['CityList'])-5));
					$tResponse_json_arr['CityList'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->message->year)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);

				if(is_array($tResponse_json_arr['message']['year']) && count($tResponse_json_arr['message']['year']) > 5){
					$tResponse_json_arr['message']['year'] = array_slice($tResponse_json_arr['message']['year'], 0, -(count($tResponse_json_arr['message']['year'])-5));
					$tResponse_json_arr['message']['year']["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->message->vehicletypelist)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);

				if(is_array($tResponse_json_arr['message']['vehicletypelist']) && count($tResponse_json_arr['message']['vehicletypelist']) > 5){
					$tResponse_json_arr['message']['vehicletypelist'] = array_slice($tResponse_json_arr['message']['vehicletypelist'], 0, -(count($tResponse_json_arr['message']['vehicletypelist'])-5));
					$tResponse_json_arr['message']['vehicletypelist'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->message->carlist)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);

				if(is_array($tResponse_json_arr['message']['carlist']) && count($tResponse_json_arr['message']['carlist']) > 5){
					$tResponse_json_arr['message']['carlist'] = array_slice($tResponse_json_arr['message']['carlist'], 0, -(count($tResponse_json_arr['message']['carlist'])-5));
					$tResponse_json_arr['message']['carlist'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->message->CurrencyList)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);

				if(is_array($tResponse_json_arr['message']['CurrencyList']) && count($tResponse_json_arr['message']['CurrencyList']) > 5){
					$tResponse_json_arr['message']['CurrencyList'] = array_slice($tResponse_json_arr['message']['CurrencyList'], 0, -(count($tResponse_json_arr['message']['CurrencyList'])-5));
					$tResponse_json_arr['message']['CurrencyList'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			if(!empty($tResponse_json->CurrencyList)){
				$tResponse_json_arr = json_decode($tResponse, TRUE);

				if(is_array($tResponse_json_arr['CurrencyList']) && count($tResponse_json_arr['CurrencyList']) > 5){
					$tResponse_json_arr['CurrencyList'] = array_slice($tResponse_json_arr['CurrencyList'], 0, -(count($tResponse_json_arr['CurrencyList'])-5));
					$tResponse_json_arr['CurrencyList'][]["....."] = ".....";
					
					$tResponse = json_encode($tResponse_json_arr);
					$tResponse_json = json_decode($tResponse);
				}
			}
			
			
			/* if($titleOfType == "generalConfigData"){
			echo "<pre>";
				print_r($tResponse_json);exit;
			} */
			
			$json_string = json_encode($tResponse_json, JSON_PRETTY_PRINT);
			fwrite($myfile,$json_string."\n\n");
			
			fwrite($myfile,"```\n\n");
		}
	}
	
	fclose($myfile);

	echo "<pre>";print_r($sql_doc_data);exit;
	 
?>