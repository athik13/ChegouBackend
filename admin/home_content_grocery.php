<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

if (!$userObj->hasPermission('view-home-page-content')) {
    $userObj->redirect();
}
$script = 'homecontentgrocery';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$tbl_name = 'language_master';
if ($id != '' && $status != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE `" . $tbl_name . "` SET eStatus = '" . $status . "' WHERE iLanguageMasId = '" . $id . "'";
        $obj->sql_query($query);
    } else {
        header("Location:home_content_grocery.php?success=2");
        exit;
    }
}

//Start Sorting

$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY vCode ASC';

if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vCode ASC";
    else
        $ord = " ORDER BY vCode DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY eStatus ASC";
    else
        $ord = " ORDER BY eStatus DESC";
}
//End Sorting
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if (strpos($option, 'eStatus') !== false) {
            $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }
    } else {
        $ssql .= " AND vCode LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%'";
    }
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(iLanguageMasId) AS Total FROM `" . $tbl_name . "` WHERE 1=1 AND eStatus = 'Active' $ssql $ord";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
$sql = "SELECT vTitle,iLanguageMasId FROM language_master WHERE 1 = 1 AND eStatus = 'Active' $ssql $ord LIMIT $start, $per_page ";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = count($data_drv);

$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Grocery Home Page</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
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
                                <h2>Grocery Home Page</h2>
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
                                                    <th width="58%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Title<?php
                                                                           if ($sortby == 1) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th align="center" style="text-align:center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) {

                                                        $default = '';
                                                        if ($data_drv[$i]['eDefault'] == 'Yes') {
                                                            $default = 'disabled';
                                                        }
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td>Grocery Page - <?= $data_drv[$i]['vTitle']; ?></td> 
                                                            <td align="center" style="text-align:center;" class="action-btn001">
                                                                <a href="home_content_grocery_action.php?id=<?= $data_drv[$i]['iLanguageMasId']; ?>" data-toggle="tooltip" title="Edit">
                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="2"> No Records Found.</td>
                                                    </tr>
                                    <?php } ?>
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
                            <li>Admin can change the grocery home page content as per language</li>
                            <li>Click on Action "Edit" icon to change the content for grocery home page</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->   
<?php include_once('footer.php'); ?>
        <script>
            $("#Search").on('click', function () {
                //$('html').addClass('loading');
                var action = $("#_list_form").attr('action');
                // alert(action);
                var formValus = $("#frmsearch").serialize();
                // alert(action+formValus);
                window.location.href = action + "?" + formValus;
            });

        </script>
    </body>
    <!-- END BODY-->
</html>
