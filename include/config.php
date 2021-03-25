<?php

$tab = "false";
/* if(!empty($_REQUEST['CUS_APP_TYPE'])){
  $APP_TYPE = $_REQUEST['CUS_APP_TYPE'];
  $_SESSION['CUS_APP_TYPE'] = $APP_TYPE;
  }else if(!empty($_SESSION['CUS_APP_TYPE'])){
  $APP_TYPE = $_SESSION['CUS_APP_TYPE'];
  }

  if(!empty($_REQUEST['CUS_CUBE_X_THEME'])){
  $CUS_CUBE_X_THEME = $_REQUEST['CUS_CUBE_X_THEME'];
  $_SESSION['CUS_CUBE_X_THEME'] = $CUS_CUBE_X_THEME;
  }else if(!empty($_SESSION['CUS_CUBE_X_THEME'])){
  $CUS_CUBE_X_THEME = $_SESSION['CUS_CUBE_X_THEME'];
  }

  if(!empty($_REQUEST['FOOD_ONLY'])){
  $FOOD_ONLY = $_REQUEST['FOOD_ONLY'];
  $_SESSION['FOOD_ONLY'] = $FOOD_ONLY;
  }else if(!empty($_SESSION['FOOD_ONLY'])){
  $FOOD_ONLY = $_SESSION['FOOD_ONLY'];
  }


  if(!empty($_REQUEST['ONLYDELIVERALL'])){
  define('ONLYDELIVERALL', $_REQUEST['ONLYDELIVERALL']);
  $_SESSION['ONLYDELIVERALL'] = ONLYDELIVERALL;
  }else if(!empty($_SESSION['ONLYDELIVERALL'])){
  define('ONLYDELIVERALL', $_SESSION['ONLYDELIVERALL']);
  } */


//$APP_TYPE = "Ride-Delivery";
if ($APP_TYPE == 'Ride') {
    $tab = "true";
    $template = 'Ride';
} else if ($APP_TYPE == 'Delivery') {
    $tab = "true";
    $template = 'Delivery';
} else if ($APP_TYPE == 'Ride-Delivery') {
    $tab = "true";
    $template = 'Ride-Delivery';
} else if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $tab = "true";
    $template = 'Ride-Delivery-UberX';
} else if ($APP_TYPE == 'UberX') {
    $Ssql = "SELECT iMasterVehicleCategoryId FROM  `vehicle_category` WHERE eStatus='Active' and iParentId=0";
    $ServiceData = $obj->MySQLSelect($Ssql);
    if (!empty($ServiceData)) {
        if (count($ServiceData) < 1) {
            $vService = 'UberX';
        } else {
            $vService = $ServiceData[0]['iMasterVehicleCategoryId'];
        }
    } else {
        $vService = 'UberX';
    }

    $template = 'UberX/' . $vService;
    if (file_exists($tconfig["tpanel_path"] . "templates/" . $template . "/")) {
        $template = 'UberX/' . $vService;
    } else {
        $template = 'UberX/UberX';
    }
} else if ($APP_TYPE == 'Delivery') {
    $template = 'uber';
}
//added by SP on 08-08-2019 for checking cubex is on or not
global $generalobj;  //added this bc in webservice it is not included bydefault, in front side it works without include
if ($generalobj->checkCubexThemOn() == 'Yes') {
    $template = 'Cubex';
} else if ($generalobj->checkCubeJekXThemOn() == 'Yes') { //added by SP on 05-12-2019 for checking CubeJek is on or not
    $template = 'CubeJekX';
} else if ($generalobj->checkRideCXThemOn() == 'Yes') { //added by SP on 05-03-2020 for checking RideCX is on or not
    $template = 'RideCX';
} else if ($generalobj->checkDeliveryXThemOn() == 'Yes') {
    $template = 'DeliveryX';
} else if ($generalobj->checkRideDeliveryXThemOn() == 'Yes') {
    $template = 'Ride-Delivery-X';
} else if ($generalobj->checkServiceXThemOn() == 'Yes') {
    $template = 'ServiceX';
}
if (ONLYDELIVERALL == 'Yes') {
    $tab = "true";
    //$template = 'deliverall';
    $template = '';
    $eDeliverallTheme = $generalobj->checkDeliverallXThemOn();
    $eDeliverallThemev2 = $generalobj->checkDeliverallXv2ThemOn();
    $serviceCategories_data = json_decode(serviceCategories);
    if (!empty($serviceCategories_data)) {
        if (count($serviceCategories_data) > 1) {
            $template = "Deliverall/deliverall";
            if (strtoupper($eDeliverallTheme) == 'YES') {
                $template = "DeliverallX/deliverall";
            }
            if (strtoupper($eDeliverallThemev2) == 'YES') {
                $template = "DeliverallXv2/deliverall";
            }
        } else {
            $vService = $serviceCategories_data[0]->vService;
            $template = "Deliverall/" . $vService;
            if (strtoupper($eDeliverallTheme) == 'YES') {
                $template = "DeliverallX/" . $vService;
            }
            if (strtoupper($eDeliverallThemev2) == 'YES') {
                $template = "DeliverallXv2/" . $vService;
            }

            if (!file_exists($tconfig["tpanel_path"] . "templates/" . $template . "/")) {
                $template = "Deliverall/deliverall";
                if (strtoupper($eDeliverallTheme) == 'YES') {
                    $template = "DeliverallX/deliverall";
                }
                if (strtoupper($eDeliverallThemev2) == 'YES') {
                    $template = "DeliverallXv2/deliverall";
                }
            }

            /* if ($serviceCategories_data[0]->iServiceId != 1) {
              $template = "Deliverall";
              if (count($serviceCategories_data) > 1) {

              $vService = 'deliverall';
              } else {
              $vService = $serviceCategories_data[0]->vService;
              $logogpathcheck = $tconfig["tpanel_path"].'assets/img/apptype/' . $template . '/' . $vService . '/';
              if (empty($vService) || !is_dir($logogpathcheck)) {
              $vService = 'deliverall';
              }
              }

              if (file_exists($tconfig["tpanel_path"]."templates/" . $template . "/")) {
              $template = "Deliverall/" . $vService;
              } else {
              $template = "Deliverall/deliverall";
              }
              } else {
              $template = "Deliverall/food";
              $tab = "false";
              } */
        }
    }
}
//$template = 'uber';
//$template = "cubejek";
$templatePath = "templates/" . $template . "/";
$logogpath = 'assets/img/apptype/' . $template . '/';
?>