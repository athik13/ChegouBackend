<?php
include_once('include_config.php');
include_once(TPATH_CLASS.'configuration.php');
$arr = getGeneralVarAll_Array();
echo "<pre>";print_r($arr);exit;
function getGeneralVarAll_Array(){
      global $obj,$generalobj;
      //$listField = $obj->MySQLGetFieldsQuery("setting");
      $ENABLE_PUBNUB=$generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
      $wri_usql = "SELECT iSettingId,vName,TRIM(vValue) as vValue FROM configurations where 1";
      $wri_ures = $obj->MySQLSelect($wri_usql);
      
      for($i = 0;$i<count($wri_ures);$i++){
          $vName = $wri_ures[$i]["vName"];
          $vValue = $wri_ures[$i]["vValue"];
          $$vName = $vValue;
          if($ENABLE_PUBNUB == "No"){
             if($vName == "ENABLE_PUBNUB"){
               $wri_ures[$i]["vValue"] = "Yes";
             }
             if($vName == "PUBNUB_DISABLED"){
               $wri_ures[$i]["vValue"] = "Yes";
             }
             if($vName == "PUBNUB_PUBLISH_KEY"){
               $wri_ures[$i]["vValue"] = "pub-c-49394564-gr96-95g7-8530-96f5f2dv9w53";
             }
             if($vName == "PUBNUB_SUBSCRIBE_KEY"){
               $wri_ures[$i]["vValue"] = "sub-c-9r3u6k8c-h9kl-66s9-b85h-d8e695euy20k";
             }
             if($vName == "PUBNUB_SECRET_KEY"){
               $wri_ures[$i]["vValue"] = "sec-c-KoPMtUgEL2QPdViKFr88UiKlOlReQWSyRGE6IJFROvgbLbKY";
             }
          }
      }

      return $wri_ures; 
}
?>