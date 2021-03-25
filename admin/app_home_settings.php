<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

if(!$userObj->hasPermission('view-app-home-settings')){
  $userObj->redirect();
}

$script = 'App Main Screen Settings';
if(DELIVERALL == 'No'){
  $sql = "SELECT * FROM  `configurations_cubejek` WHERE eInputType = 'Select' AND iSettingId NOT IN (23,30,41)  AND eStatus = 'Active'  ORDER BY iSettingId";
} else {
  $sql = "SELECT * FROM  `configurations_cubejek` WHERE eInputType = 'Select' AND eStatus = 'Active' ORDER BY iSettingId";
}
$totalData = $obj->MySQLSelect($sql);

?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> | App Main Screen Settings</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php');?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2>App Main Screen Settings</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                  <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                  <table class="table table-striped table-bordered table-hover">
                                      <thead>
                                          <tr>
                                              <th width="10%">Name</th>
                                              <th width="10%" style="text-align:center;">Image Type</th>
                                              <th width="8%" style="text-align:center;">Action</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php if(!empty($totalData)){
                                            foreach($totalData as $value){
                                              $TitleName = $value['vName'];
                                              switch($TitleName){
                                                  case "RIDE_SHOW_SELECTION":
                                                      $name = 'Taxi Ride';
                                                      $PageType = 'ride';
                                                      break;
                                                  case "DELIVERY_SHOW_SELECTION":
                                                      $name = 'Box';
                                                      $PageType = 'delivery';
                                                      break;
                                                  case "RENTAL_SHOW_SELECTION":
                                                      $name = 'Car Rental';
                                                      $PageType = 'rental';
                                                      break;
                                                  case "MOTO_RIDE_SHOW_SELECTION":
                                                      $name = 'Moto Ride';
                                                      $PageType = 'moto_ride';
                                                      break;
                                                  case "MOTO_DELIVERY_SHOW_SELECTION":
                                                      $name = 'Moto Send';
                                                      $PageType = 'moto_delivery';
                                                      break;
                                                  case "MOTO_RENTAL_SHOW_SELECTION":
                                                      $name = 'Moto Rental';
                                                      $PageType = 'moto_rental';
                                                      break;
                                                  case "MULTI_DELIVERY_SHOW_SELECTION":
                                                      $name = 'Common Deliveries';
                                                      $PageType = 'multi_delivery';
                                                      break;
                                                  case "FOOD_APP_SHOW_SELECTION":
                                                      $name = 'Food App';
                                                      $PageType = 'food_app';
                                                      break;
                                                  case "GROCERY_APP_SHOW_SELECTION":
                                                      $name = 'Grocery';
                                                      $PageType = 'grocery_app';
                                                      break;
                                                  case "DELIVER_ALL_APP_SHOW_SELECTION":
                                                      $name = 'Deliver All';
                                                      $PageType = 'deliver_all_app';
                                                      break;
                                              }
                                              if($value['vValue'] == 'None'){
                                                $imgType = 'Disable/Hide';
                                              } else {
                                                $imgType = $value['vValue'];
                                              }
                                             ?>
                                            <tr class="gradeA">
                                              <td><?= $name?></td>
                                              <td style="text-align:center;"><?= $imgType?></td>
                                              <td style="text-align:center;">
                                                <a href="app_home_settings_action.php?PageType=<?= $PageType?>" data-toggle="tooltip" title="Edit">
                                                  <img src="img/edit-icon.png" alt="Edit">
                                                </a>
                                              </td> 
                                          </tr>
                                          <?php }
                                           } ?>
                                        </tbody>
                                      </table>
                                  </form>
                                </div>
                              </div> <!--TABLE-END-->
                          </div>
                    </div>
                    <div class="admin-notes">
                         <h4>Notes:</h4>
                         <ul>
                          <? if(ENABLE_RENTAL_OPTION == 'Yes') { ?>
                             <li>This section list the Ride, delivery, rental and Moto type for the Apps </li>
                              <li>Please close the application and open it again to see the settings reflected after saving the new setting values above.</li>
                              <li>Set the Icon and Banner images for the user app from this screen</li>
                              <li> Also, you can disable any of the services by selecting "Show selection field - Disable/Hide" </li>
                              <li>Once you have added Taxi ride/ Moto ride/ Box/ Moto Send icon or banner, then you need to add the appropriate vehicle type from Manage vehicle section. <a href="vehicle_type.php">Click here</a> to go to Manage Vehicle section.</li>
                              <li>Once you have added Car Rental/ Moto Rental icon or baner, then you need to add the packages for the rent vehicle from Manage vehicle section. <a href="rental_vehicle_list.php" target="_blank">Click here</a> to go to Manage Rental Packages section</li>
                              <li>You can add the rental packages only after you have added the vehicle type of "RIDE" from the Manage vehicle section.</li>
                              <li>Disable  Taxi Ride, Car Rental, Moto Ride and Moto Rental service If you don't want to allow <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> to add vehicles for it.</li>
                          <? } else {?>
                              <li>This section list the Ride, delivery and Moto type for the Apps </li>
                              <li>Please close the application and open it again to see the settings reflected after saving the new setting values above.</li>
                              <li>Set the Icon and Banner images for the user app from this screen</li>
                              <li> Also, you can disable any of the services by selecting "Show selection field - Disable/Hide" </li>
                              <li>Once you have added Taxi ride/ Moto ride/ Box/ Moto Send icon or banner, then you need to add the appropriate vehicle type from Manage vehicle section. <a href="vehicle_type.php">Click here</a> to go to Manage Vehicle section.</li>
                              <li>Disable  Taxi Ride, Car Rental, Moto Ride If you don't want to allow <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> to add vehicles for it.</li>
                            <? } ?>
                          </ul> 
                    </div>
                </div>
                <!--END PAGE CONTENT -->
            </div>
        </div>   
        <!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
</body>
    <!-- END BODY-->
</html>