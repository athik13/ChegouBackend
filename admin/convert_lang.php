<?php

include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
ini_set("display_errors", 1);
error_reporting(E_ALL);
$tableArr = array("pages_lang" => "iPageId", "email_templates_lang" => "iEmailId");
$tableArr = array("email_templates_lang" => "iEmailId");
$tableArr = array("delivery_fields"=>"iDeliveryFieldId");
$tableArr = array();
$getLang = "SELECT iLanguageMasId,vTitle,vCode,vLangCode FROM language_master WHERE vCode!='EN' AND eStatus='Active'";
$lang_data = $obj->MySQLSelect($getLang);
echo "<pre>";
$engLangCode = "_EN";
$sql = "SELECT vLangCode FROM language_master where eStatus='Active' AND eDefault = 'Yes'";
$data = $obj->MySQLSelect($sql);
$vGMapLangCode = isset($data[0]["vLangCode"]) ? $data[0]["vLangCode"] : 'en';
$htmlTags = array("div" => "8069", "/div" => "1612", "p" => "1575", "/p" => "5015", "ul" => "3212", "/ul" => "5642", "li" => "4432", "/li" => "8745", "br /" => "3624", "br" => "8954", "strong" => "9065", "/strong" => "2132", "span" => "5435", "/span" => "3214");
$emailTags = array("#NAME#" => "5643", "#Contact_Name#" => "9735", "#Contact_Phone#" => "3245", "#Contact_Email#" => "3462", "#Contact_Subject#" => "8756", "#Contact_Message#" => "9986", "#MailFooter#" => "7656", "#Company#" => "3346", "#Email#" => "7464", "#SITE_FOOTER#" => "0986", "#details#" => "4256", "projectname" => "7546", "#PASSWORD#" => "5241", "#SOCIALNOTES#" => "4324", "#Driver#" => "3412", "#Rider#" => "7764", "#BookingNo#" => "9631", "#Ddate#" => "8032", "#User_Name#" => "3124", "#<b>Withdrawal_amount</b>#" => "6423", "#LINK#" => "4412", "#SourceAddress#" => "8742", "#DestinationAddress#" => "5323", "#Reason#" => "6421", "#PROVIDER_NAME#" => "4130", "#BOOKING_NUMBER#" => "5312", "#PROVIDERNAME#" => "5313");
$langArr = array();

foreach ($tableArr as $tableName => $pkIdField) {
    $getTableData = "SELECT * FROM " . $tableName . " ORDER BY $pkIdField ASC";
    $table_data = $obj->MySQLSelect($getTableData);
    //print_r($table_data);die;
    for ($d = 0; $d < count($table_data); $d++) {
        $pkId = $table_data[$d][$pkIdField];
        $countNum = $d + 1;
        $whereCondi = $pkIdField . "=" . $pkId;
        $updateValue = "";
        foreach ($table_data[$d] as $key => $val) {
            if (strpos($key, $engLangCode) !== false) {
                $englishText = $val;
                //$englishText = "<ul><li>Rental can be used for local travels only. A package cannot be changed after booking is confirmed.</li><li>For usage beyond selected package, the additional fare will be applicable as per rates above.</li><li>Additional applicable to the fare: The toll will be added to the final bill if applicable, also please pay parking fees when required.</li></ul>";
                //$englishText = "<p>The Application is the smart way of travelling, all with your smart phone and three little taps. Tap and look for your ride, Tap and book your ride and Tap and complete your ride. Your tap brings the driver to you for the best riding experience of your life, each one better than the previous one.No hassles of direction giving as your driver know exactly where you want to go. Payment completed via your credit card. Just sit back and enjoy your ride!</p>";
                //$englishText = "About Us";
                foreach ($htmlTags as $tag => $repVal) {
                    $search = "<" . $tag . ">";
                    $englishText = str_replace($search, $repVal, $englishText);
                }
                if ($tableName == "email_templates_lang") {
                    foreach ($emailTags as $hashKey => $repHash) {
                        $englishText = str_replace($hashKey, $repHash, $englishText);
                    }
                }
                //echo $englishText . "==========";
                $explodeKey = explode("_", $key);
                if (isset($explodeKey[0])) {
                    $fieldName = $explodeKey[0];
                    for ($l = 0; $l < count($lang_data); $l++) {
                        $langField = $fieldName . "_" . $lang_data[$l]['vCode'];
                        $vGmapCode = $lang_data[$l]['vLangCode'];
                        //$vGmapCode = "ZU";
                        $englishText = strip_tags($englishText, '<br>');
                        $url = 'http://api.mymemory.translated.net/get?q=' . urlencode($englishText) . '&de=harshilmehta1982@gmail.com&langpair=' . $vGMapLangCode . '|' . $vGmapCode;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, false);
                        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        //curl_setopt($ch, CURLOPT_POSTFIELDS, $finalFields);
                        $result = curl_exec($ch); //echo "<pre>";
                        //print_r($result);exit;
                        //$result = file_get_contents($url);
                        $finalResult = json_decode($result);
                        $getText = $finalResult->responseData;
                        $responseStatus = $finalResult->responseStatus;
                        if ($responseStatus != "200") {
                            $translatedText = $englishText;
                        } else {
                            $translatedText = $getText->translatedText;
                        }
                        foreach ($htmlTags as $repTag => $searchVal) {
                            $replaceTag = "<" . $repTag . ">";
                            $translatedText = str_replace($searchVal, $replaceTag, $translatedText);
                        }
                        if ($tableName == "email_templates_lang") {
                            foreach ($emailTags as $hashKey => $repHash) {
                                $translatedText = str_replace($repHash, $hashKey, $translatedText);
                            }
                        }
                        //echo $translatedText;die;
                        //$updateValue .= $langField . "='" . $translatedText . "',";
                        $updateValue .= $langField . '="' . htmlentities($translatedText) . '",';
                        $langArr[$pkId][$langField] = htmlentities($translatedText);
                    }
                }
            }
        }
        if ($updateValue != "") {
            $updateValue = trim($updateValue, ",");
            $update_query = "UPDATE " . $tableName . " SET $updateValue WHERE $whereCondi";
            $obj->sql_query($update_query);
        }
        echo $countNum . ") Table :" . $tableName . " WHERE " . $whereCondi . " Data Updated<br><br>";
    }
}
if (count($tableArr) == 0) {
    echo "Table list not found<br>";
} else {
    echo "All Data Translated Successfully<br>";
}
print_R($langArr);
die;
