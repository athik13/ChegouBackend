<?php

include_once('../common.php');

$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

define("VEHICLE_CATEGORY", $sql_vehicle_category_table_name);
define("VEHICLE_TYPE", "vehicle_type");
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
ini_set("display_errors", 1);
error_reporting(E_ALL);
$getCategoryData = $obj->MySQLSelect("SELECT vCategory_EN,iVehicleCategoryId,iParentId,vLogo,vLogo1,ePriceType,eBeforeUpload,eAfterUpload,eShowType,eMaterialCommision,vBannerImage,eCatType,eSubCatType,eFor,eDeliveryType,iServiceId,tBannerButtonText,eDetailPageView,fCommision,fWaitingFees,iWaitingFeeTimeLimit,fCancellationFare,iCancellationTimeLimit FROM ".$sql_vehicle_category_table_name." WHERE iParentId='0' AND eStatus != 'Deleted' AND eCatType='ServiceProvider'");
$getSubCategoryData = $obj->MySQLSelect("SELECT vCategory_EN,iVehicleCategoryId,iParentId,vLogo FROM ".$sql_vehicle_category_table_name." WHERE iParentId > 0 AND eStatus != 'Deleted' AND eCatType='ServiceProvider'");
$getVehicleTypeData = $obj->MySQLSelect("SELECT iVehicleCategoryId,iVehicleTypeId FROM vehicle_type WHERE iVehicleCategoryId > 0 AND eStatus != 'Deleted'");
$categorySubCatArr = $cateTypeDataArr = array();
$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`");
$count_all = count($db_master);
//echo "<pre>";print_r($getVehicleTypeData);die;
for ($t = 0; $t < count($getVehicleTypeData); $t++) {
    $iVehicleCategoryId = $getVehicleTypeData[$t]['iVehicleCategoryId'];
    $cateTypeDataArr[$iVehicleCategoryId][] = $getVehicleTypeData[$t];
}
//echo "<pre>";print_r($cateTypeDataArr);die;
for ($i = 0; $i < count($getSubCategoryData); $i++) {
    $iParentId = $getSubCategoryData[$i]['iParentId'];
    $categorySubCatArr[$iParentId][] = $getSubCategoryData[$i];
}
//echo "<pre>";print_r($getCategoryData);die;
$srNo = 1;
for ($c = 0; $c < count($getCategoryData); $c++) {
    $iVehicleCategoryId = $getCategoryData[$c]['iVehicleCategoryId'];
    $vLogo = $getCategoryData[$c]['vLogo'];
    $vLogo1 = $getCategoryData[$c]['vLogo1'];
    $categoryName = $getCategoryData[$c]['vCategory_EN'];
    $ePriceType = $getCategoryData[$c]['ePriceType'];
    $eBeforeUpload = $getCategoryData[$c]['eBeforeUpload'];
    $eAfterUpload = $getCategoryData[$c]['eAfterUpload'];
    $eShowType = $getCategoryData[$c]['eShowType'];
    $eMaterialCommision = $getCategoryData[$c]['eMaterialCommision'];
    $vBannerImage = $getCategoryData[$c]['vBannerImage'];
    $eCatType = $getCategoryData[$c]['eCatType'];
    $eSubCatType = $getCategoryData[$c]['eSubCatType'];
    $eFor = $getCategoryData[$c]['eFor'];
    $eDeliveryType = $getCategoryData[$c]['eDeliveryType'];
    $iServiceId = $getCategoryData[$c]['iServiceId'];
    $tBannerButtonText = $getCategoryData[$c]['tBannerButtonText'];
    $eDetailPageView = $getCategoryData[$c]['eDetailPageView'];
    $fCommision = $getCategoryData[$c]['fCommision'];
    $fWaitingFees = $getCategoryData[$c]['fWaitingFees'];
    $iWaitingFeeTimeLimit = $getCategoryData[$c]['iWaitingFeeTimeLimit'];
    $fCancellationFare = $getCategoryData[$c]['fCancellationFare'];
    $iCancellationTimeLimit = $getCategoryData[$c]['iCancellationTimeLimit'];
    $catArr = array();
    if (isset($categorySubCatArr[$iVehicleCategoryId])) {
        $catArr = $categorySubCatArr[$iVehicleCategoryId];
    }
    //echo "<pre>";print_r($catArr);die;
    $categoryCount = 2;
    if (count($catArr) > 0) {
        $srNo++;
        for ($d = 0; $d < count($catArr); $d++) {
            $subCatArr = $catTypeArr = array();
            $subCatId = $catArr[$d]['iVehicleCategoryId'];
            if (isset($categorySubCatArr[$subCatId])) {
                $subCatArr = $categorySubCatArr[$subCatId];
            }
            if (isset($cateTypeDataArr[$subCatId])) {
                $catTypeArr = $cateTypeDataArr[$subCatId];
            }
            if (count($subCatArr) > 0) {
                // Nested Category Check here
            } else {
                //echo "<pre>";print_r($catTypeArr);die;
                if (count($catTypeArr) > 0) {
                    //echo $subCatId."<br>";
                    //Category's Vehicle Type Found Then Don't Create Vehicle Type
                } else {
                    //echo "<pre>";print_r($catTypeArr);die;
                    $subCategoryCount = 1;
                    for ($s = 0; $s <= count($subCategoryCount); $s++) {
                        $typeArr = array();
                        $displayOrder = ($s + 1);
                        $vehicleTypeName = $categoryName . " Service-" . $displayOrder;
                        if ($count_all > 0) {
                            for ($i = 0; $i < $count_all; $i++) {
                                $typeArr["vVehicleType_" . $db_master[$i]['vCode']] = $vehicleTypeName;
                                $typeArr["vRentalAlias_" . $db_master[$i]['vCode']] = $vehicleTypeName;
                            }
                        }
                        addUpdateVehicleType(0, $vehicleTypeName, $subCatId, $typeArr, $displayOrder);
                    }
                }
            }
        }
    } else {
        $img_path = $tconfig["tsite_upload_images_vehicle_category_path"] . "/" . $iVehicleCategoryId . "/";
        for ($s = 0; $s <= count($categoryCount); $s++) {
            $displayOrder = ($s + 1);
            //echo $displayOrder."<br>";
            $subCategoryName = $categoryName . " Sub Category-" . $displayOrder;
            $vehicleTypeName = $categoryName . " Service-" . $displayOrder;
            $descArr = $typeArr = array();
            if ($count_all > 0) {
                for ($i = 0; $i < $count_all; $i++) {
                    $descArr["vCategory_" . $db_master[$i]['vCode']] = $subCategoryName;
                    $descArr["tCategoryDesc_" . $db_master[$i]['vCode']] = $subCategoryName;
                    $typeArr["vVehicleType_" . $db_master[$i]['vCode']] = $vehicleTypeName;
                    $typeArr["vRentalAlias_" . $db_master[$i]['vCode']] = $vehicleTypeName;
                }
            }
            $descArr['iParentId'] = $iVehicleCategoryId;
            $descArr['vLogo'] = $vLogo;
            $descArr['vLogo1'] = $vLogo1;
            $descArr['vHomepageLogo'] = "";
            $descArr['ePriceType'] = $ePriceType;
            $descArr['eBeforeUpload'] = $eBeforeUpload;
            $descArr['eAfterUpload'] = $eAfterUpload;
            $descArr['iDisplayOrder'] = $displayOrder;
            $descArr['eStatus'] = "Active";
            $descArr['eShowType'] = $eShowType;
            $descArr['eMaterialCommision'] = $eMaterialCommision;
            $descArr['vBannerImage'] = $vBannerImage;
            $descArr['eCatType'] = $eCatType;
            $descArr['eSubCatType'] = $eSubCatType;
            $descArr['eFor'] = $eFor;
            $descArr['eDeliveryType'] = $eDeliveryType;
            $descArr['iServiceId'] = $iServiceId;
            $descArr['tBannerButtonText'] = $tBannerButtonText;
            $descArr['eDetailPageView'] = $eDetailPageView;
            $descArr['fCommision'] = $fCommision;
            $descArr['fWaitingFees'] = $fWaitingFees;
            $descArr['iWaitingFeeTimeLimit'] = $iWaitingFeeTimeLimit;
            $descArr['fCancellationFare'] = $fCancellationFare;
            $descArr['iCancellationTimeLimit'] = $iCancellationTimeLimit;
            $cateId = $obj->MySQLQueryPerform($sql_vehicle_category_table_name, $descArr, 'insert');
            $newPath = $tconfig["tsite_upload_images_vehicle_category_path"] . "/" . $cateId . "/";
            //echo "<pre>";print_r($typeArr);die;
            copy_directory($img_path, $newPath); // For Copy Image Folder
            $vehicleTypeId = addUpdateVehicleType(0, $vehicleTypeName, $cateId, $typeArr, $displayOrder);
            //echo $vehicleTypeId."<br>";
            //echo $iVehicleCategoryId . "<br>";die;
        }
        //echo "Done";die;
    }
    
}

function addUpdateVehicleType($typeId, $vehicleTypeName, $catId, $typeArr, $displayOrder) {
    global $obj;
    $typeArr['iVehicleCategoryId'] = $catId;
    $typeArr['iLocationid'] = "-1";
    $typeArr['iCountryId'] = $typeArr['iStateId'] = $typeArr['iCityId'] = 0;
    $typeArr['vVehicleType'] = $vehicleTypeName;
    $typeArr['vAddress'] = "";
    $typeArr['eFareType'] = "Fixed";
    $typeArr['fFixedFare'] = 10;
    $typeArr['fPricePerKM'] = $typeArr['fPricePerMin'] = $typeArr['fPricePerHour'] = $typeArr['fMinHour'] = $typeArr['fTimeSlot'] = $typeArr['fTimeSlotPrice'] = $typeArr['iBaseFare'] = $typeArr['fCommision'] = $typeArr['iMinFare'] = $typeArr['fPickUpPrice'] = $typeArr['fNightPrice'] = 0;

    $typeArr['ePickStatus'] = "Active";
    $typeArr['eNightStatus'] = "Active";
    $typeArr['tPickStartTime'] = $typeArr['tPickEndTime'] = $typeArr['tNightStartTime'] = $typeArr['tNightEndTime'] = "00:00:00";
    $typeArr['iPersonSize'] = 1;
    $typeArr['vLogo'] = $typeArr['vLogo1'] = "";
    $typeArr['eType'] = "UberX";
    $typeArr['eIconType'] = "Car";
    $typeArr['tMonPickStartTime'] = "00:00:00";
    $typeArr['tMonPickEndTime'] = "00:00:00";
    $typeArr['fMonPickUpPrice'] = 0;
    $typeArr['tTuePickStartTime'] = "00:00:00";
    $typeArr['tTuePickEndTime'] = "00:00:00";
    $typeArr['fTuePickUpPrice'] = 0;
    $typeArr['tWedPickStartTime'] = "00:00:00";
    $typeArr['tWedPickEndTime'] = "00:00:00";
    $typeArr['fWedPickUpPrice'] = 0;
    $typeArr['tThuPickStartTime'] = "00:00:00";
    $typeArr['tThuPickEndTime'] = "00:00:00";
    $typeArr['fThuPickUpPrice'] = 0;
    $typeArr['tFriPickStartTime'] = "00:00:00";
    $typeArr['tFriPickEndTime'] = "00:00:00";
    $typeArr['fFriPickUpPrice'] = 0;
    $typeArr['tSatPickStartTime'] = "00:00:00";
    $typeArr['tSatPickEndTime'] = "00:00:00";
    $typeArr['fSatPickUpPrice'] = 0;
    $typeArr['tSunPickStartTime'] = "00:00:00";
    $typeArr['tSunPickEndTime'] = "00:00:00";
    $typeArr['fSunPickUpPrice'] = 0;
    $typeArr['tNightSurgeData'] = "";
    $typeArr['eAllowQty'] = "Yes";
    $typeArr['iMaxQty'] = "1";
    $typeArr['fVisitFee'] = $typeArr['fCancellationFare'] = $typeArr['iCancellationTimeLimit'] = $typeArr['fWaitingFees'] = $typeArr['iWaitingFeeTimeLimit'] = 0;
    $typeArr['eStatus'] = "Active";
    $typeArr['iDisplayOrder'] = $displayOrder;
    $typeArr['fRadius'] = $typeArr['fDeliveryCharge'] = $typeArr['fDeliveryChargeCancelOrder'] = $typeArr['fPoolPercentage'] = $typeArr['fTripHoldFees'] = $typeArr['fBufferAmount'] = 0;
    $typeArr['ePoolStatus'] = "No";
    $typeArr['tTypeDesc'] = $typeArr['eDeliveryType'] = "";
    //echo "<pre>";print_r($typeArr);die;
    if ($typeId > 0) {
        $whereTypeId = "iVehicleTypeId='" . $typeId . "'";
        $typeId = $obj->MySQLQueryPerform("vehicle_type", $typeArr, 'update', $whereTypeId);
    } else {
        $typeId = $obj->MySQLQueryPerform("vehicle_type", $typeArr, 'insert');
    }
    return $typeId;
}

function copy_directory($src, $dst) {
    //echo "Source Directory === ".$src."====Destination Directory === ".$dst;die;
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ( $file = readdir($dir))) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function recurse_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ( $file = readdir($dir))) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

echo $srNo;
die;
echo "Vehicle Category Data Migrated Successfully";
die;
?>