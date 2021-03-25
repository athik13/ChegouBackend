<?php
    include_once('../common.php');
    
    if (!isset($generalobjAdmin)) {
        require_once(TPATH_CLASS . "class.general_admin.php");
        $generalobjAdmin = new General_admin();
    }
    
    if(!$userObj->hasPermission('view-store-categories')){
      	$userObj->redirect();
    }
    
    $script = 'ManageStoreCategories';
    $tbl_name = 'store_categories';

    $all_iServiceIds_arr = $service_categories_ids_arr;
    $all_iServiceIds = implode(',', $all_iServiceIds_arr);
    $s_iServiceId = isset($_REQUEST['iServiceId']) ? $_REQUEST['iServiceId'] : $all_iServiceIds_arr[0];

    if ($default_lang == "") {
        $default_language = 'EN';
    }else{
        $default_language = $default_lang;
    }

    //Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

    $ord = ' ORDER BY iCategoryId DESC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY tCategoryName ASC";
      else
      $ord = " ORDER BY tCategoryName DESC";
    }
    
    if ($sortby == 2) {
	    if ($order == 0) {
	        $ord = " ORDER BY iDisplayOrder ASC";
	    } else {
	        $ord = " ORDER BY iDisplayOrder DESC";
	    }
	}

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }
    
    // Start Search Parameters
	$option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
	$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
	$eStatus = isset($_REQUEST['eStatus'])?$_REQUEST['eStatus']:"";
	$ssql = '';
	if($keyword != ''){ 
	    $keyword_new = $keyword;
	    $chracters = array("(", "+", ")");
	    $removespacekeyword =  preg_replace('/\s+/', '', $keyword);
	    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
	    if(is_numeric($keyword_new)){
	      $keyword_new = $keyword_new;
	    } else {
	      $keyword_new = $keyword;
	    }  
	    if($option != '') {
			
			if ($eStatus != '') {
	            $ssql .= " WHERE " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND iServiceId = ".$s_iServiceId;
	        } else {
	            $ssql .= " WHERE " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND iServiceId = ".$s_iServiceId;
	        }
			
			
	    }else {
			if ($eStatus != '') {
	            $ssql .= " WHERE (tCategoryName LIKE '%".$keyword_new."%') AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND iServiceId = ".$s_iServiceId;
	        } else {
	            $ssql .= " WHERE (tCategoryName LIKE '%".$keyword_new."%' ) AND iServiceId = ".$s_iServiceId;
	        }
	    }
	}
	else if ($eStatus != '' && $keyword == '') {
	    $ssql .= " WHERE eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND iServiceId = ".$s_iServiceId;
	}

	if($ssql == "")
	{
		$ssql .= " WHERE iServiceId = ".$s_iServiceId;
	}
    $sql = "SELECT * FROM ".$tbl_name." $ssql $ord";
    $data_drv = $obj->MySQLSelect($sql);

    $sc_sql = "SELECT iServiceId, vServiceName_".$default_language." FROM service_categories WHERE iServiceId IN (".$all_iServiceIds.") AND eStatus = 'Active'";
    $sc_sqlData = $obj->MySQLSelect($sc_sql);
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> | Store Categories </title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php');?>
        <style type="text/css">
        	.store-category-table tbody td {
        		vertical-align: middle !important;
        	}
        </style>
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
                                <h2>Store Categories</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
					    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
					        <tbody>
					            <tr>
					                <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
					                <td width="15%" class=" padding-right10">
					                    <select name="option" id="option" class="form-control">
					                        <option  value="tCategoryName">Category Name</option>
					                    </select>
					                </td>
					                <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
					                <td width="12%" class="estatus_options" id="eStatus_options" >
					                    <select name="eStatus" id="eStatus" class="form-control">
					                        <option value="" >Select Status</option>
					                        <option value="Active" <?php
					                            if ($eStatus == 'Active') {
					                                echo "selected";
					                            }
					                            ?> >Active</option>
					                        <option value="Inactive" <?php
					                            if ($eStatus == 'Inactive') {
					                                echo "selected";
					                            }
					                            ?> >Inactive</option>
					                    </select>
					                </td>
					                <td width="15%" class="estatus_options" id="eStatus_options" >
					                    <select name="iServiceId" id="iServiceId" class="form-control">
					                        <?php foreach ($sc_sqlData as $service) { ?>
					                        <option value="<?= $service['iServiceId'] ?>" <?php
					                            if ($service['iServiceId'] == $s_iServiceId) {
					                                echo "selected";
					                            }
					                            ?> ><?= $service['vServiceName_'.$default_language] ?>
					                        </option>
					                    	<?php } ?>
					                    </select>
					                </td>
					                <td width="20%">
					                    <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
					                    <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='store_category.php'"/>
					                </td>
					            </tr>
					        </tbody>
					    </table>
					</form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                	<form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
	                                    <table class="table table-striped table-bordered table-hover store-category-table">
	                                        <thead>
	                                            <tr>
	                                            	<th width="3%">Category Image</th>
	                                                <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Category Name <?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
	                                                <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)">Display Order <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
	                                                <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)">Status <?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
	                                                <th width="8%" align="center" style="text-align:center;">Action</th>
	                                            </tr>
	                                        </thead>
	                                        <tbody>
	                                            <?php 
	                                                if(!empty($data_drv)) {
	                                                for ($i = 0; $i < count($data_drv); $i++) { 
	                                                    ?>
	                                            <tr class="gradeA">
	                                            	<td style="text-align: center;"><img src="<?= $tconfig["tsite_upload_images_store_categories"].'/'.$data_drv[$i]['tCategoryImage'] ?>" style="width: 50px"></td>
	                                                <td><?php
	                                                    $categoryName = '';
	                                                    $vTitleArr = json_decode($data_drv[$i]['tCategoryName'], true);
	                                                    if(isset($vTitleArr['tCategoryName_'.$default_language]) && $vTitleArr['tCategoryName_'.$default_language] != ''){
	                                                        $categoryName = $vTitleArr['tCategoryName_'.$default_language];
	                                                    }
	                                                    echo $categoryName;
	                                                      ?>
	                                                </td>
	                                                <td align="center" style="text-align:center;">
	                                                    <?php echo $data_drv[$i]['iDisplayOrder'] ?>
	                                                </td>
	                                                <td align="center" style="text-align:center;">
	                                                    <?php if($data_drv[$i]['eStatus'] == 'Active') {
	                                                        $dis_img = "img/active-icon.png";
	                                                        }else {
	                                                        $dis_img = "img/inactive-icon.png";
	                                                        }?>
	                                                    <img src="<?= $dis_img; ?>" alt="<?=$data_drv[$i]['eStatus'];?>" data-toggle="tooltip" title="<?=$data_drv[$i]['eStatus'];?>">
	                                                </td>
	                                                <td align="center" style="text-align:center;" class="action-btn001">
	                                                    <div class="share-button openHoverAction-class" style="display: block;">
	                                                        <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
	                                                        <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iCategoryId']; ?>">
	                                                            <ul>
	                                                                <li class="entypo-twitter" data-network="twitter"><a href="store_category_action.php?id=<?= $data_drv[$i]['iCategoryId']; ?>" data-toggle="tooltip" title="Edit">
	                                                                    <img src="img/edit-icon.png" alt="Edit">
	                                                                    </a>
	                                                                </li>
	                                                                <?php  if($userObj->hasPermission('update-status-store-categories') && $data_drv[$i]['eType'] != 'list_all'){ ?>
	                                                                <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iCategoryId']; ?>','Inactive')"  data-toggle="tooltip" title="Activate">
	                                                                    <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
	                                                                    </a>
	                                                                </li>
	                                                                <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iCategoryId']; ?>','Active')" data-toggle="tooltip" title="Deactivate">
	                                                                    <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >    
	                                                                    </a>
	                                                                </li>
	                                                                <?php  } ?>
	                                                            </ul>
	                                                        </div>
	                                                    </div>
	                                                </td>
	                                            </tr>
	                                            <?php } }else { ?>
	                                            <tr class="gradeA">
	                                                <td colspan="3"> No Records Found.</td>
	                                            </tr>
	                                            <?php } ?>
	                                        </tbody>
	                                    </table>
	                                </form>
	                                <?php include('pagination_n.php'); ?>
                                </div>
                            </div>
                            <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>Manage Store Categories will show list of all the categories to be associated with the stores.</li>
                            <li>Admin can only “Activate”, “Inactivate” and “Edit” the Store Category.</li>
                            <li>Admin cannot delete any of the predefined Store Categories.</li>
                            <li>Admin can associate more than one Store Category while adding orupdating the Store.</li>
                            <li>Store would be listed to the respective Store Category depending on the Store category selection.</li>
                            <li>Random 12 Stores would be shown under the respective Store Categories on the Mobile Application as well as under the Food Ordering from the Front.</li>
                            <li>Number of Random Stores to be shown in the Mobile Application and Food Ordering from the Front is not configurable by admin.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <form name="pageForm" id="pageForm" action="action/store_category.php" method="post" >
		<input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
		<input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
		<input type="hidden" name="iCategoryId" id="iMainId01" value="" >
		<input type="hidden" name="status" id="status01" value="" >
		</form>
        <!--END MAIN WRAPPER -->
        <?php
            include_once('footer.php');
            ?>
        <script>
        	$("#Search").on('click', function(){
                var action = $("#_list_form").attr('action');
                var formValus = $("#frmsearch").serialize();
                window.location.href = action+"?"+formValus;
            });

            $('.entypo-export').click(function(e){
               e.stopPropagation();
               var $this = $(this).parent().find('div');
               $(".openHoverAction-class div").not($this).removeClass('active');
               $this.toggleClass('active');
            });
            
            $(document).on("click", function(e) {
              if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
                $(".show-moreOptions").removeClass("active");
              }
            });
            
        </script>
    </body>
    <!-- END BODY-->
</html>