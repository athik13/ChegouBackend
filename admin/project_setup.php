<?php
include_once('../common.php');
include_once('../include/config.php');
$tsiteUrl = $tconfig["tsite_url"];
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : 'admin';
$data = $obj->MySQLSelect('SELECT * FROM setup_info');
$filePanel = "Admin"; // Used In setup_validation.php File For Include Js File By HJ On 22-04-2019
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if ($host_system == 'cubetaxishark' || $host_system == 'cubetaxi5plus') {
    $logo = "logo-taxi.png";
} else if ($host_system == 'cubedelivery') {
    $logo = "logo_delivery.png";
} else {
    if ($generalobj->checkCubeJekXThemOn() == 'Yes' || $generalobj->checkRideCXThemOn() == 'Yes') {
        $logo = "logo-admin.png";
    } else if ($generalobj->checkCubeXThemOn() == 'Yes') {
        $logo = "admin-logo.png";
    } else {
        $logo = "logo.png";
    }
}
$activeTab = "1";
$activeTabId = "super001";
if ($userType == "billing") {
    $activeTab = "3";
    $activeTabId = "billing001";
}
if (file_exists($tconfig["tpanel_path"] . $logogpath . $logo)) {
    $admin_logo = $tsiteUrl . $logogpath . $logo;
} else {
    $admin_logo = $tsiteUrl . '' . SITE_ADMIN_URL . '/' . 'images/' . $logo;
}
$fav_icon_image = 'favicon.ico';
if (file_exists($tconfig["tpanel_path"] . $logogpath . $fav_icon_image)) {
    $fav_icon_image = $tsiteUrl . $logogpath . $fav_icon_image;
} else {
    $fav_icon_image = $tsiteUrl . $fav_icon_image;
}
$adminUrl = $tconfig["tsite_url_main_admin"];
$lAddOnConfiguration_obj = json_decode($data[0]['lAddOnConfiguration'], true);
$tProjectPortData_obj = json_decode($data[0]['tProjectPortData'], true);
 // echo "<pre>";print_r($lAddOnConfiguration_obj['GOOGLE_PLAN']);die;
$portsTobeOpened = array();

if(!empty($lAddOnConfiguration_obj['GOOGLE_PLAN'])){
	$portsTobeOpened[]=$tProjectPortData_obj['tMapsApiPort'];
	$portsTobeOpened[]=$tProjectPortData_obj['tAdminMongoPort'];
}
$portsTobeOpened[]=$tProjectPortData_obj['tSocketClusterPort'];
$portsTobeOpened[]="2195";
$portsTobeOpened[]="443";

// $redirecturl = explode('/', $_SESSION['login_redirect_url']);
$dashboardLink = $_SESSION['dashboardLink'];

/*if (!empty($redirecturl[count($redirecturl) - 1])) {
    if ($redirecturl[count($redirecturl) - 1] == 'dashboard.php' && (ONLYDELIVERALL == "Yes")) {
        $dashboardLink = $adminUrl . 'store-dashboard.php';
    } else if ($redirecturl[count($redirecturl) - 1] == 'store-dashboard.php' && (ONLYDELIVERALL == "No")) {
        $dashboardLink = $adminUrl . 'dashboard.php';
    } else {
        $dashboardLink = $adminUrl . 'dashboard.php';
    }
} else {
    $dashboardLink = (ONLYDELIVERALL == "Yes") ? $adminUrl . 'store-dashboard.php' : $adminUrl . 'dashboard.php';
}*/
?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Login Page</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link rel="icon" href="<?= $fav_icon_image; ?>" type="image/x-icon">
        <link rel="stylesheet" href="<?= $adminUrl; ?>css/bootstrap.css" />
        <link rel="stylesheet" href="<?= $adminUrl; ?>css/login.css" />
        <link rel="stylesheet" href="<?= $adminUrl; ?>css/style.css" />
        <link rel="stylesheet" href="<?= $tsiteUrl; ?>assets/css/animate/animate.min.css" />
        <link rel="stylesheet" href="<?= $tsiteUrl; ?>assets/plugins/magic/magic.css" />
        <link rel="stylesheet" href="<?= $adminUrl; ?>css/font-awesome.css" />
        <link rel="stylesheet" href="<?= $tsiteUrl; ?>assets/plugins/font-awesome-4.6.3/css/font-awesome.min.css" />
        <link href="https://fonts.googleapis.com/css?family=Exo+2:100,300,400,500,600,700&display=swap" rel="stylesheet">
    </head>
    <!-- END HEAD -->
    <!-- BEGIN BODY -->
    <body class="nobg loginPage">
        <!-- PAGE CONTENT -->
        <div class="container animated fadeInDown">
            <div class="text-center admin-logo"> <img src="<?= $admin_logo; ?>" id="Admin" alt=" Admin" /> </div>
            <div class="tab-content">
                <div id="login" class="tab-pane active">				
                    <p style="display:none; padding:5px 0;" class="btn-block btn btn-rect btn-success" id="success" ></p>
                    <div class="admin-home-tab">
                        <h1>Things to do on client's Live Server</h1>
						<p> System will use below ports on client's live server. Make sure that below mentioned ports are opened on your live server.</p>
						<ul>
						<?php
						foreach($portsTobeOpened as $portsTobeOpened_item){
						?>
							<li> <?=$portsTobeOpened_item?></li>
						<?php
						}
						?>
						</ul>
						<p> Above mentioned ports must be opened for both Inbound & Outbound connection. And these must be publicly accessible. </p>
						<hr>
						<p> When you host this website on client's live server, Perform below step. </p>
						<p> Run command in your server's WHM terminal. <br/> Command: bash /PATH_TO_PUBLIC_HTML/webimages/script_files/install_sys_components.sh</h3><BR/><BR/><BR/><BR/> </p>
						
						<p> Note: Above command will install required components of the system on client's live server. </p>
                        <div style="clear:both;"></div>
                        <input type="button" onclick="gotoAdminPanel();" class="btn text-muted text-center btn-default" value="NEXT >>"/> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- PAGE LEVEL SCRIPTS -->
    <script src="<?= $tsiteUrl; ?>assets/plugins/jquery-2.0.3.min.js"></script>
    <script src="<?= $tsiteUrl; ?>assets/plugins/bootstrap/js/bootstrap.js"></script>
    <script src="<?= $tsiteUrl; ?>assets/js/login.js"></script>
    <script>
                            function gotoAdminPanel()
                            {
                                testLink = "<?= $dashboardLink; ?>";
                                window.location = testLink;
                            }
    </script>
    <!--END PAGE LEVEL SCRIPTS -->
</body>
<!-- END BODY -->
<!-- Powered by V3Cube.com -->
</html>	
