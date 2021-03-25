<?php

	include_once("common.php");

include_once ('include_generalFunctions_dl.php'); 
    check_type_wise_mr('service_listing');
	$meta = $generalobj->getStaticPage(1,$_SESSION['sess_lang']);
	$_SESSION['sess_language']=$_SESSION['sess_lang'];
	  
	if (!isset($generalobjAdmin)) {
		require_once(TPATH_CLASS . "class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}     
	
	// if(empty($_SESSION['sess_iAdminUserId'])){
	// 		$_SESSION['success'] = 3;
	// 		header("Location:admin/index.php");exit;
	// }

	include_once ('include_generalFunctions_dl.php'); 
	
	$script="Restaurant";
	$iServiceId=1;
	   $iUserId=$_SESSION['sess_iUserId_mr'];
	$_SESSION["sess_vName_mr"];
	$_SESSION["sess_user_mr"];
	$_SESSION["sess_vCurrency_mr"]; 
	  $iUserAddressId =$_SESSION["sess_iUserAddressId_mr"];
	// if (empty($_SESSION['sess_iUserId_mr']) || empty($_SESSION['sess_iUserAddressId_mr']))
	//  {    
	// 	header("location:customer_info.php"); exit;
	//  }

   $_REQUEST["vLang"] = $_SESSION['sess_lang'];
   include('assets/libraries/configuration.php');
   //print_r($serviceCategoriesTmp);
   if(isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)){
    $service_categories = $serviceCategoriesTmp;
   }else{
    $service_categories = array();
   }
  ?> 

<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>
         Restaurant Lisiting
      </title>
      <meta name="keywords" value="<?= $meta['meta_keyword']; ?>"/>
      <meta name="description" value="<?= $meta['meta_desc']; ?>"/>
      <!-- Default Top Script and css -->
      <?php
         include_once("top/top_script.php");
         ?>
      <?php include_once("store_css_include.php"); ?>

	   <style>
         .error {
         color:red;
         font-weight: normal;
         }
         .select2-container--default .select2-search--inline .select2-search__field{
         width:500px !important;
         }
      </style>
      <script>
         $(document).ready(function(){
           var LISTHEIGHT
             //var LISTHEIGHT = window.innerHeight - $('.catlist-holder').offset().top;
             $('.catlist-holder').css('height',LISTHEIGHT);
             $(document).on('click','.search-holder input',function(){
               
                 $(this).closest('.search-main').addClass('ACTIVE');
                 setTimeout(() => {
                     var w = $(window);
                     LISTHEIGHT = window.innerHeight - ($('.catlist-holder').offset().top-w.scrollTop());
                     $('.catlist-holder').css('height',LISTHEIGHT);
                     
                 }, 500);
                 $('.catlist-holder').slideDown(300);
                 $('body,html').addClass('overflow-hide');
             })
             $(document).on('click','.search-holder .close_ico',function(){
              
                 $(this).closest('.search-main').removeClass('ACTIVE');
                 setTimeout(() => {
                     LISTHEIGHT = window.innerHeight - ($('.catlist-holder').offset().top-w.scrollTop());
                     $('.catlist-holder').css('height',LISTHEIGHT);
                 }, 500);
                 $('.catlist-holder').slideUp(300);
                 $('body,html').removeClass('overflow-hide');
                 $("#loadstore").html('');
                $("#restlisting").show();
                $('#magicsearchingg').val('');
             })
         })
      </script>
      <!-- End: Default Top Script and css-->
   </head>
   <body>
      <div id="main-uber-page">
         <!-- Left Menu -->
         <?php
            include_once("top/left_menu.php");
            ?>
         <!-- End: Left Menu-->
         <!-- home page -->
         <!-- Top Menu -->
         <?php

            include_once("top/header_topbar.php");

            ?>  
         <!-- End: Top Menu-->
         <!-- contact page-->
         <div class="page-contant">
            <div class="page-contant-inner">
               <div class="listing-main" id="restlisting">
                  <ul class="rest-listing" >
                     <?php
                     //print_r($service_categories);
                        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", "1");
                        $companyname       = array();
                        if (count($service_categories) > 0) {
                            for ($i = 0; $i < count($service_categories); $i++) {
                                
                                $iServiceId = $service_categories[$i]['iServiceId'];
                                if ($service_categories[$i]['vImage'] == "") {
                                    $service_categories[$i]['vImage'] = $tconfig['tsite_url'] . '/assets/img/burger.jpg';
                                }
                                
                        ?>
                     <li>
                        <a href="<?php
                           echo $tconfig['tsite_url'];
                           ?>restaurant_listing.php?serviceid=<?php
                           echo $iServiceId;
                           ?>">
                           <div class="rest-pro" style="background-image:url(<?php
                              echo ($service_categories[$i]['vImage']);
                              ?>);"></div>
                           <strong><?php
                              echo ucfirst($service_categories[$i]['vServiceName']);
                              ?>   </strong>
                        </a>
                     </li>
                     <?php
                        }
                        }
                        ?>
                  </ul>
                  <?php
                     if (count($service_categories) == 0) {
                     ?>
                  <div  align="center" >
                     <h4><span style="color:#98441ef5;"><strong><?php
                        echo $languageLabelsArr['LBL_NO_RESTAURANT_FOUND_TXT'];
                        ?></strong></span> </h4>
                  </div>
                  <?php
                     }
                     ?>
               </div>
               <div id="loadstore"> </div>
               <div class="static-page">
                  <? // =$meta['page_desc']; 
                     ?>
               </div>
            </div>
         </div>
         <!-- home page end-->
         <!-- footer part -->
         <?php
            include_once('footer/footer_home.php');
            ?>
         <script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
         <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
         <!-- End:contact page-->
         <div style="clear:both;"></div>
      </div>
      <?php
         include_once('top/footer_script.php');
         ?>
   </body>
</html>