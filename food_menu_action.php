<?php
include_once('common.php');

//added by SP for cubex changes on 07-11-2019
if ($generalobj->checkXThemOn() == 'Yes') {
    include_once("cx-food_menu_action.php");
    exit;
}

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$generalobj->check_member_login();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$generalobj->setRole($abc,$url);
if($_SESSION["sess_eSystem"] != "DeliverAll")
{
  header('Location:profile.php');
}
  /*error_reporting(-1);
  error_reporting(E_ALL);  
  ini_set('display_errors','1');
*/
if($_REQUEST['id'] != '' && $_SESSION['sess_iCompanyId'] != ''){
  $sql = "select * from food_menu where iFoodMenuId = '".$_REQUEST['id']."' AND iCompanyId = '".$_SESSION['sess_iCompanyId']."'";
  $db_cmp_id = $obj->MySQLSelect($sql);
  
  if(!count($db_cmp_id) > 0) {
    header("Location:food_menu.php?success=0&var_msg=".$langage_lbl['LBL_NOT_YOUR_FOOD']);
  }
}

$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$action = ($id != '') ? 'Edit' : 'Add';
$sessioniCompanyId = $_SESSION['sess_iUserId'];

$tbl_name = 'food_menu';
$script = 'FoodMenu';

// For Restaurants
$sql = "SELECT * FROM `company` where eStatus='Active' AND iCompanyId = ".$sessioniCompanyId." ORDER BY `vCompany`";
$db_company = $obj->MySQLSelect($sql);

// For Languages
$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);


// set all variables with either post (when submit) either blank (when insert)
$iCompanyId   = isset($_POST['iCompanyId'])?$_POST['iCompanyId']:'';
$iDisplayOrder = isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:'';
$eStatus = isset($_POST['eStatus'])?$_POST['eStatus']:'Active';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$vMenu_store =array();
//$vMenuDesc_store =array();
$count_all = count($db_master);
if($count_all > 0) {
  for($i=0;$i<$count_all;$i++) {
    $vValue = 'vMenu_'.$db_master[$i]['vCode'];
    array_push($vMenu_store ,$vValue);   
    $$vValue  = isset($_POST[$vValue])?$_POST[$vValue]:'';
  }
}

  
if (isset($_POST['submit'])) { 
/*  if(!empty($id) && SITE_TYPE =='Demo'){
    $_SESSION['success'] = 2;
    header("Location:food_menu.php?id=".$id);exit;
  }*/

if($id != "") {
    $sql = "SELECT iDisplayOrder FROM `food_menu` where iFoodMenuId = '$id'";
    $displayOld = $obj->MySQLSelect($sql);
    $oldDisplayOrder = $displayOld[0]['iDisplayOrder'];
    if($oldDisplayOrder > $iDisplayOrder) {
      $sql = "SELECT * FROM `food_menu` where iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' ORDER BY iDisplayOrder ASC";
      $db_orders = $obj->MySQLSelect($sql);
      if(!empty($db_orders)){
        $j = $iDisplayOrder+1;
        for($i=0;$i<count($db_orders);$i++){
          $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '".$db_orders[$i]['iFoodMenuId']."'";
          $obj->sql_query($query);
          $j++;
        }
      }
    }else if($oldDisplayOrder < $iDisplayOrder) {
      $sql = "SELECT * FROM `food_menu` where iCompanyId = '$iCompanyId' AND iDisplayOrder > '$oldDisplayOrder' AND iDisplayOrder <= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
      $db_orders = $obj->MySQLSelect($sql);
      if(!empty($db_orders)){
        $j = $oldDisplayOrder;
        for($i=0;$i<count($db_orders);$i++){
          $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '".$db_orders[$i]['iFoodMenuId']."'";
          $obj->sql_query($query);
          $j++;
        }
      }
    }
} else {
    $sql = "SELECT * FROM `food_menu` WHERE iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
    $db_orders = $obj->MySQLSelect($sql);
    
    if(!empty($db_orders)){
      $j = $iDisplayOrder+1;
      for($i=0;$i<count($db_orders);$i++){
       $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '".$db_orders[$i]['iFoodMenuId']."'";
       $obj->sql_query($query);
        $j++;
      }
    }
}


  for($i=0;$i<count($vMenu_store);$i++) {   
    $q = "INSERT INTO ";
    $where = '';
    
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iFoodMenuId` = '" . $id . "'";
    }

    $eStatus_query = '';
    if($action == "Add"){
      $eStatus_query = "  `eStatus` = '" . $eStatus . "',";
    }

    $vValue = 'vMenu_'.$db_master[$i]['vCode'];
    //$vValue_desc = 'vMenuDesc_'.$db_master[$i]['vCode'];
    $query = $q . " `" . $tbl_name . "` SET
        `iCompanyId` = '" . $iCompanyId . "',
        `iDisplayOrder` = '" . $iDisplayOrder . "',
        $eStatus_query
        ".$vValue." = '" .$_POST[$vMenu_store[$i]]. "'"
        . $where;
      $obj->sql_query($query);
      $id = ($id != '') ? $id : $obj->GetInsertId(); 
    }
      
    if ($action == "Add") {
        $var_msg = $langage_lbl["LBL_FOOD_CATEGORY_FRONT"].' Insert Successfully.';
    } else {
        $var_msg = $langage_lbl["LBL_FOOD_CATEGORY_FRONT"].' Updated Successfully.';
    }

    header("Location:food_menu.php?success=1&var_msg=".$var_msg);
    //End :: Upload Image Script
   // header("Location:".$backlink);exit;
}
  
// for Edit
if ($action == 'Edit') {
 $sql = "SELECT * FROM " . $tbl_name . " WHERE iFoodMenuId = '" . $id . "'";
 $db_data = $obj->MySQLSelect($sql);

 if (count($db_data) > 0) {
    for($i=0;$i<count($db_master);$i++)
    {
      foreach($db_data as $key => $value) {
        $vValue = 'vMenu_'.$db_master[$i]['vCode'];
        $$vValue = $value[$vValue];
        $iCompanyId = $value['iCompanyId'];
        $iDisplayOrder = $value['iDisplayOrder'];
        $eStatus = $value['eStatus'];
        $iFoodMenuId = $value['iFoodMenuId'];            
      }
    }
 } 

}
    
if($action == 'Add'){
  $action_lbl = $langage_lbl['LBL_ACTION_ADD'];
} elseif($action == 'Edit') {
  $action_lbl = $langage_lbl['LBL_ACTION_EDIT'];
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_FOOD_CATEGORY_FRONT']; ?> <?= $action; ?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
  </head>
  <body>
    <!-- home page -->
    <div id="main-uber-page">
      <!-- Left Menu -->
      <?php include_once("top/left_menu.php");?>
      <!-- End: Left Menu-->
      <!-- Top Menu -->
      <?php include_once("top/header_topbar.php");?>
      <!-- End: Top Menu-->
      <!-- contact page-->
      <div class="page-contant ">
        <div class="page-contant-inner page-trip-detail">

          <h2 class="header-page trip-detail food-detail1"><?= $action_lbl; ?> <?=$langage_lbl['LBL_FOOD_CATEGORY_FRONT']; ?> <?= $vName; ?>

          <a href="foodcategorylist">
            <img src="assets/img/arrow-white.png" alt=""> <?=$langage_lbl['LBL_BACK_To_Listing_WEB']; ?>
          </a></h2>
          <!-- login in page -->
          <div class="food-action-page">
            <? if ($success == 1) {?>
              <div class="alert alert-success alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <?php echo $langage_lbl['LBL_Record_Updated_successfully']; ?>
              </div>
              <?}else if($success == 2){ ?>
              <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <?php echo $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
              </div>
              <?php 
              }
            ?>
            <div style="clear:both;"></div>
          <form id="food_category_form" name="food_category_form" method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="id" id="iFoodMenuId" value="<?= $id; ?>"/>
            <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
            <input type="hidden" name="backlink" id="backlink" value="food_menu.php"/>
            <div class="form-group">
              <?php if($_SESSION['sess_user'] != 'company'){ ?>
              <div class="row">
                <div class="col-xs-12">
                  <label>Restaurant<span class="red"> *</span></label>
                </div>
                <div class="col-xs-6">
                  <select name="iCompanyId" class="form-control" id="iCompanyId" required  onchange="changeDisplayOrderCompany(this.value,'<?php  echo $id; ?>')">
                      <option value="" >Select Restaurant</option>
                       <?php foreach($db_company as $dbc) { ?>
                       <option value="<?php echo $dbc['iCompanyId']; ?>"<?if($dbc['iCompanyId'] == $iCompanyId){?>selected<? } ?>><?php echo $dbc['vCompany'] ?></option>
                       <?php } ?>
                  </select>
                </div>
              </div>
              <?php } else { ?>
                <input type="hidden" id="iCompanyId" name="iCompanyId" value="<?php echo $sessioniCompanyId; ?>" />
              <?php } ?>
              <? if($count_all > 0) {
              for($i=0;$i<$count_all;$i++) {
              $vCode = $db_master[$i]['vCode'];
              $vTitle = $db_master[$i]['vTitle'];
              $eDefault = $db_master[$i]['eDefault'];

              $vValue = 'vMenu_'.$vCode;
              $required = ($eDefault == 'Yes')?'required':''; 
              $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
              ?>
              <div class="row">
                <div class="col-xs-12">
                  <label><?php echo $langage_lbl['LBL_MENU_CATEGORY_WEB_TXT'];?> (<?=$vTitle;?>) <?php echo $required_msg;?></label>  
                </div>
                <div class="col-xs-6 custMenuCategory">
                  <input type="text" class="form-control" name="<?=$vValue;?>" id="<?=$vValue;?>" value="<?=$$vValue;?>" <?=$required;?>> 
                </div>
              </div>
            <? }
            } ?>
            <div class="row">
              <div class="col-xs-12">
              <label><?php echo $langage_lbl['LBL_DISPLAY_ORDER_FRONT'];?> <span class="red"> *</span></label>
              </div>
              <div class="col-xs-6" id="showDisplayOrder001">
                <?php if($action == 'Add') { ?>
                <select name="iDisplayOrder" id="iDisplayOrder" class="custom-select-new" >
                  <?php for($i=1;$i<=$count+1;$i++) {?>
                  <option value="<?php echo $i?>" 
                  <?php if($i == $count+1)
                  echo 'selected';?>> <?php echo $i?> </option>
                  <?php }?>
                </select>
               <?php }else { ?>
                <select name="iDisplayOrder" id="iDisplayOrder" class="custom-select-new" >
                  <?php for($i=1;$i<=$count;$i++) { ?>
                  <option value="<?php echo $i?>"
                  <?php
                  if($i == $iDisplayOrder)
                  echo 'selected';
                  ?>
                  > <?php echo $i?> </option>
                  <?php } ?>
                </select>
               <?php } ?>
              </div>
            </div>
              <div class="row">
              <div class="col-xs-12">
                <input type="submit" class="save-but" name="submit" id="submit" value="<?= $action; ?> <?php echo $langage_lbl['LBL_FOOD_ADMIN'];?>" >
              </div>
              </div>
            </div>
          </form>
          </div>
          <div style="clear:both;"></div>
        </div>
      </div>
      <!-- footer part -->
      <?php include_once('footer/footer_home.php');?>
      <!-- footer part end -->
      <!-- End:contact page-->
      <div style="clear:both;"></div>
    </div>
    <!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');
    $lang = get_langcode($_SESSION['sess_lang']);?>
    <style>
    span.help-block{
    margin:0;
    padding: 0;
    }
    </style>
    <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"]?>js/validation/jquery.validate.min.js" ></script>
    <?php if($lang != 'en') { ?>
      <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
      <? include_once('otherlang_validation.php');?>
    <?php } ?>
    <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
    <script>
       function changeDisplayOrderCompany(companyId,foodId)
      {
          $.ajax({
            type: "POST",
            url: 'ajax_display_order.php',
            data: {iFoodMenuId: foodId},
            success: function (response)
            {
              $("#showDisplayOrder001").html('');
              $("#showDisplayOrder001").html(response);
            }
          });
          
      }

      $(document).ready(function(){
        changeDisplayOrderCompany('<?php echo $iCompanyId; ?>','<?php echo $id; ?>');
      });

    </script>
  </body>
</html>

