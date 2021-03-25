<?
	$default_lang 	= $generalobj->get_default_lang();
	
	$def_lang_name = $generalobj->get_default_lang_name();
	
	if(!isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] == ""){
		/* $sql="select eDirectionCode from language_master where vCode='$default_lang'";
		$lang = $obj->MySQLSelect($sql);
		$_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode']; */
		$_SESSION['eDirectionCode'] = $vSystemDefaultLangDirection;
	}
	
	function get_langcode($lang) {
		global $obj, $Data_ALL_langArr;
		if(!empty($Data_ALL_langArr) && count($Data_ALL_langArr) > 0){
			foreach($Data_ALL_langArr as $language_item){
				if(strtoupper($language_item['vCode']) == strtoupper($lang)){
					$vLangCode = $language_item['vLangCode'];
				}
			}
		}
		if(!empty($vLangCode)){
			return $vLangCode;
		}
		$sql = "SELECT vLangCode FROM language_master WHERE vCode = '".$lang."'";
		$result = $obj->MySQLSelect($sql);
		return $result[0]['vLangCode'];
	}
?>
