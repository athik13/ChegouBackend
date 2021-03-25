<!-- HEADER SECTION -->
<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {

    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$dashboardLink = (ONLYDELIVERALL == "Yes") ? 'store-dashboard.php' : 'dashboard.php';
/* Use For Demo [User can not delete the specify company id record] */
$DelCompanyIdArray = array('36', '37');
$DEMO_NOT_DEL_COMPANY_ID = (SITE_TYPE == 'Demo') ? $DelCompanyIdArray : '';
/* Use For Demo */
//////$generalobjAdmin->check_member_login();
/* ------- ride status ---------- */

$etypeSql = "";
if(!isRideModuleAvailable()) {
    $etypeSql .= " AND eType != 'Ride'";
}
if(!isDeliveryModuleAvailable()) {
    $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
}
if(!isUberXModuleAvailable()) { 
    $etypeSql .= " AND eType != 'UberX'";     
}

$newSql = "SELECT vValue FROM configurations WHERE vName='SET_MENU_ENABLE'";
$enable = $obj->MySQLSelect($newSql);
$sql = "SELECT t.iTripId,rd.vImage,t.iDriverId,rd.vName,rd.vLastName,t.tEndDate,t.tSaddress,t.tDaddress,t.iActive FROM trips t JOIN register_driver rd ON t.iDriverId=rd.iDriverId WHERE t.iActive='Finished' AND t.eSystem = 'General' $etypeSql ORDER BY tEndDate DESC LIMIT 0,4";
$db_finished = $obj->MySQLSelect($sql);
/* ------------------ */
/* Order Status */
if (ONLYDELIVERALL == 'Yes') {
    $limit = 'LIMIT 0,4';
} else {
    $limit = 'LIMIT 0,2';
}
$sql = "SELECT c.vCompany,o.iOrderId,o.vOrderNo,c.vCaddress,c.vImage,os.vStatus,c.iCompanyId,o.tOrderRequestDate,vServiceAddress FROM orders o JOIN user_address as ua ON o.iUserAddressId=ua.iUserAddressId LEFT JOIN company c on o.iCompanyId=c.iCompanyId LEFT JOIN order_status as os on o.iStatusCode=os.iStatusCode  LEFT JOIN register_user ru on o.iUserId=ru.iUserId ORDER BY iOrderId DESC $limit";
$db_finished_orders = $obj->MySQLSelect($sql);
/* Order Status */
$sql = "SELECT dm.doc_name_" . $default_lang . ",dl.doc_usertype,rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS `Driver`,CONCAT(rdn.vName,' ',rdn.vLastName) AS `DriverName`,dv.iDriverVehicleId, c.vCompany,dl.edate,c.iCompanyId,rd.iDriverId FROM `document_list` AS dl LEFT JOIN document_master AS dm ON dm.doc_masterid=dl.doc_masterid LEFT JOIN company AS c ON ( c.iCompanyId = dl.doc_userid AND (dl.doc_usertype='company' || dl.doc_usertype='store')) LEFT JOIN register_driver AS rd ON (rd.iDriverId=dl.doc_userid AND dl.doc_usertype='driver') LEFT JOIN driver_vehicle AS dv ON (dv.iDriverVehicleId=dl.doc_userid AND dl.doc_usertype='car') LEFT JOIN register_driver AS rdn ON rdn.iDriverId=dv.iDriverId ORDER BY dl.edate DESC LIMIT 0,10";
$db_notification = $obj->MySQLSelect($sql);
if (isset($_REQUEST['allnotification'])) {
    $sql = "SELECT dm.doc_name_" . $default_lang . ",dl.doc_usertype,rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS `Driver`,CONCAT(rdn.vName,' ',rdn.vLastName) AS `DriverName`,dv.iDriverVehicleId, c.vCompany,dl.edate FROM `document_list` AS dl
		LEFT JOIN document_master AS dm ON dm.doc_masterid=dl.doc_masterid
		LEFT JOIN company AS c ON (c.iCompanyId=dl.doc_userid AND (dl.doc_usertype='company' || dl.doc_usertype='store'))
		LEFT JOIN register_driver AS rd ON (rd.iDriverId=dl.doc_userid AND dl.doc_usertype='driver')
		LEFT JOIN driver_vehicle AS dv ON (dv.iDriverVehicleId=dl.doc_userid AND dl.doc_usertype='car')
		LEFT JOIN register_driver AS rdn ON rdn.iDriverId=dv.iDriverId ORDER BY dl.edate DESC";
    $db_notification = $obj->MySQLSelect($sql);
}
if($generalobj->checkCubeXThemOn() == 'Yes') {
    $logo = "logo.png";
} else {
    $logo = "admin-logo.png";
}

$logosmall = "logo-small.png";
$adminUrl = $tconfig["tsite_url_main_admin"];
if (file_exists($tconfig["tpanel_path"] . $logogpath . $logo)) {
    $logo = $tconfig["tsite_url"] . $logogpath . $logo;
} else {
    $logo = $adminUrl . 'images/' . $logo;
}
if (file_exists($tconfig["tpanel_path"] . $logogpath . $logosmall)) {
    $logosmall = $tconfig["tsite_url"] . $logogpath . $logosmall;
} else {
    $logosmall = $adminUrl . 'images/' . $logosmall;
}
?>
<script>
    var _system_admin_url = '<?php echo $tconfig["tsite_url_main_admin"]; ?>';
</script>
<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="../assets/plugins/modernizr-2.6.2-respond-1.1.0.min.js"></script>
<!-- <script src="js/New/perfect-scrollbar.js"></script> -->
<!-- END GLOBAL SCRIPTS -->
<!-- END HEADER SECTION -->
<link type="text/css" href="css/admin_new/admin_style.css" rel="stylesheet" />
<!--<link type="text/css" href="css/adminLTE/AdminLTE.min.css" rel="stylesheet" />-->
<input type="hidden" name="baseurl" id="baseurl" value="">
<div class="wrapper1">
    <div class="new-mobile001">
        <nav class="navbar navbar-inverse navbar-fixed-top" style="padding:7px 0;">
            <a data-original-title="Show/Hide Menu" data-placement="bottom" data-tooltip="tooltip" class="accordion-toggle btn btn-primary btn-sm visible-xs" data-toggle="collapse" href="#sidebar" id="menu-toggle"><i class="icon-align-justify"></i></a>
        </nav>
    </div>
    <header class="main_header">
        <div class="header clearfix">
            <a href="<?= $dashboardLink; ?>" title="" class="logo"> <span class="logo-mini"> <img src="<?php echo $logosmall; ?>" alt="" /> </span> <span class="logo-lg minus"> <img src="<?php echo $logo; ?>" alt="" /> </span> </a>
            <nav class="navbar-static-top"> 
                <a class="sidebar-toggle" href="javascript:void(0);" data-toggle="tooltip" data-placement="right" title="show / hide sidebar"></a>
                <span style="margin: 26px 0 0 20px;float: left;"><?php echo $_SESSION['sess_vAdminFirstName'] . "&nbsp;&nbsp;" . $_SESSION['sess_vAdminLastName']; ?></span>
            </nav>
            <div>
                <a href="logout.php" title="Logout" class="header-top-button"><img src="images/logout-icon1.png" alt="" />Logout</a>
                <!-- <div id="google_translate_element" class="header-top-translate-button"></div> -->
            </div>
        </div>
    </header>
    <div class="main-sidebar">
<?php include('left_menu.php'); ?>
    </div>
    <div class="loader-default"></div>
    <script>
    function setMenuEnable(id)
    {
        $.ajax({
            method: "post",
            url: _system_admin_url + "setMenuEnable.php",
            data: "data=" + id,
            cache: false,
            dataType: 'html',
            success: function (response) {
            }
        });
    }
    $(document).ready(function () {
        $.sidebarMenu($('.sidebar-menu'));
<?php if ($enable[0]['vValue'] == 0) { ?>
            $("body").addClass("sidebar-minize");
            $("body").addClass("sidebar_hide");
            $("body").addClass("sidebar-collapse");
<?php } else { ?>
            $("body").removeClass("sidebar_hide");
            $("body").removeClass("sidebar-minize");
            $("body").removeClass("sidebar-collapse");
<?php } ?>
    });
    $.sidebarMenu = function (menu) {
        var animationSpeed = 300;
        $(menu).on('click', 'li a', function (e) {
            var $this = $(this);
            var checkElement = $this.next();
            if (checkElement.is('.treeview-menu') && checkElement.is(':visible')) {
                checkElement.slideUp(animationSpeed, function () {
                    checkElement.removeClass('menu-open');
                });
                checkElement.parent("li").removeClass("active");
            }
            //If the menu is not visible
            else if ((checkElement.is('.treeview-menu')) && (!checkElement.is(':visible'))) {
                //Get the parent menu
                var parent = $this.parents('ul').first();
                //Close all open menus within the parent
                var ul = parent.find('ul:visible').slideUp(animationSpeed);
                //Remove the menu-open class from the parent
                ul.removeClass('menu-open');
                //Get the parent li
                var parent_li = $this.parent("li");
                //Open the target menu and add the menu-open class
                checkElement.slideDown(animationSpeed, function () {
                    //Add the class active to the parent li
                    checkElement.addClass('menu-open');
                    parent.find('li.active').removeClass('active');
                    parent_li.addClass('active');
                });
            }
            //if this isn't a link, prevent the page from being redirected
            if (checkElement.is('.treeview-menu')) {
                e.preventDefault();
            }
        });
    }
    </script>
    <!-- /footer -->
</div>
<!-- END HEADER SECTION -->
<script type="text/javascript">
    $(document).ready(function () {
        if ($('#messagedisplay')) {
            $('#messagedisplay').animate({opacity: 1.0}, 2000)
            $('#messagedisplay').fadeOut('slow');
        }
        //for side bar menu
        $(".content-wrapper").css({'min-height': ($(".wrapper .main-sidebar").height() + 'px')});
        $('.sidebar-toggle').click(function () {
            $("body").toggleClass("sidebar_hide");
            if ($("body").hasClass("sidebar_hide")) {
                $("body").addClass("sidebar-minize");
                $("body").addClass("sidebar-collapse");
                setMenuEnable(0);
            } else {
                $("body").removeClass("sidebar-minize");
                $("body").removeClass("sidebar-collapse");
                setMenuEnable(1);
            }
        });
        $("#content").addClass('content_right');
        if ($(window).width() < 800) {
            $('.sidebar-toggle').click(function () {
                $("body").toggleClass("sidebar_hide");
                if ($("body").hasClass("sidebar_hide")) {
                    $("body").addClass("sidebar-open");
                    $("body").removeClass("sidebar-collapse");
                    setMenuEnable(0);
                } else {
                    $("body").removeClass("sidebar-open");
                    $("body").removeClass("sidebar-collapse");
                    setMenuEnable(1);
                }
            });
        }
        if ($(window).width() < 900) {
            $("body").removeClass("sidebar-collapse");
            $('.sidebar-toggle').click(function () {
                $('body').toggleClass('sidebar-open');
                if (sessionStorage.sidebarin == 0) {
                    $("body").addClass("sidebar-minize");
                    $("body").removeClass("sidebar-collapse");
                } else {
                    $("body").removeClass("sidebar-minize");
                    $("body").removeClass("sidebar-collapse");
                }
            });
        }
    });
</script>
<script type="text/javascript">
//===== Hide/show Menubar =====//
    $('.fullview').click(function () {
        $("body").toggleClass("clean");
        $('#sidebar').toggleClass("show-sidebar mobile-sidebar");
        $('#content').toggleClass("full-content");
    });
    $(window).resize(function () {
        if ($(window).width() < 900) {
            if (sessionStorage.sidebarin == 0) {
                $("body").addClass("sidebar-minize");
                $("body").removeClass("sidebar-collapse");
            } else {
                $("body").removeClass("sidebar-minize");
                $("body").removeClass("sidebar-collapse");
            }
        }
    });
</script>
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
    $(window).load(function () {
        $(".loader-default").fadeOut("slow");
    });
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script type="text/javascript">
    function googleTranslateElementInit() {
        //new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
    }
</script>

