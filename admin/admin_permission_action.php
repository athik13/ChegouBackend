<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

if(!$userObj->hasRole(1)){
    $userObj->redirect();
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'admin_permissions';
$script = 'AdminGroups';


// set all variables with either post (when submit) either blank (when insert)
$permission_name = isset($_POST['permission_name']) ? $_POST['permission_name'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

if (isset($_POST['submit'])) {

    
    if(is_numeric($_POST['display_group_id'])){
        $display_group_id = $_POST['display_group_id'];
    }else{
        $display_group = Models\AdminPermissionDisplayGroup::firstOrCreate([ 'name' => trim($_POST['display_group_id']) ]);
        $display_group_id = $display_group->id;
    }
    if ($id != "" && SITE_TYPE == 'Demo') {
		$_SESSION['success'] = '2';
		header("location:".$backlink);
        exit;
    }

    //Add Custom validation
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['permission_name'], 'req', 'Permission Name is required');
    $error = $validobj->validate();

    if ($error) {
        $success = 3;
        $newError = $error;
    } else {
        if(empty($id)){
            $data = Models\AdminPermission::firstOrCreate(['permission_name' => $permission_name]);
        }else{
            $data = Models\AdminPermission::find($id);
        }

        $data->permission_name = $permission_name;
        $data->display_group_id = $display_group_id;
        $data->save();

        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        header("location:".$backlink);
    }
}

$PermissionGroups = Models\AdminPermissionDisplayGroup::get()->pluck('name', 'id');
if ($action == 'Edit') {
    $edit_data = Models\AdminPermission::find($id);
}
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> | Admin <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?
        include_once('global_files.php');
        ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <link rel="stylesheet" href="css/select2/select2.min.css"></link>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?
            include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
							<h2><?= $action; ?> Admin Permission</h2>
							<a class="back_link" href="admin_permissions.php">
								<input type="button" value="Back to Listing" class="add-btn">
							</a>
                        </div>
                    </div>
                    <hr />
					
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 2) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div><br/>
                            <?} ?>
                            <? if ($success == 3) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
								<?php print_r($error); ?>
                            </div><br/>
                            <?} ?>
                            <form name="_admin_form" id="_admin_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" value="admin_permissions.php"/>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Permission Name<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="permission_name"  id="permission_name" value="<?= $edit_data['permission_name']; ?>" placeholder="Permission Name">
                                    </div>
                                </div>
                                <div class="row">
                                
                                    <div class="col-lg-12">
                                        <label>Permission Display Group<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select id="permitionGroupSelect" name="display_group_id" class="form-control">
                                            <?php foreach ($PermissionGroups as $key => $value) { ?>
                                                <option <?php echo $edit_data['display_group_id'] == $key ? "selected" : ""; ?> value="<?php echo $key ?>"> <?php echo $value ?> </option>
                                            <?php } ?>
                                          
                                        </select>
                                    </div>

                                    
                                </div>
                               
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?php  if($action=='Add'){?><?= $action; ?> Admin Permission<?php } else{ ?>Update<?php } ?>">
                                        <input type="reset" value="Reset" class="btn btn-default">
                                        <a href="admin.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <?
        include_once('footer.php');
        ?>
        <script type="text/javascript" src="js/plugins/select2.min.js"></script>
        <script type="text/javascript">
            $("#permitionGroupSelect").select2({
              tags: true
            });
       
        $('#_admin_form').validate({
            rules: {
                permission_name: {
                    required: true
                }
            }
        }); 
	</script>
    </body>
    <!-- END BODY-->
</html> 
<script> 
	</script>
<!-- <script>
$(document).ready(function() {
	var referrer;
	if($("#previousLink").val() == "" ){
		referrer =  document.referrer;	
		//alert(referrer);
	}else { 
		referrer = $("#previousLink").val();
	}
	if(referrer == "") {
		referrer = "admin_permissions.php";
	}else {
		$("#backlink").val(referrer);
	}
	$(".back_link").attr('href',referrer);
});
</script> -->