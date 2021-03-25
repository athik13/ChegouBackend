<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
$script = 'AdminPermissions';


$sql = "SELECT * FROM admin_permissions where status = 'Active' ORDER BY display_order ASC";
$data_drv = $obj->MySQLSelect($sql);

?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> | Admin Groups</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php');?>

        <link rel="stylesheet" href="css/nestedSortable.css">
        <script src="https://code.jquery.com/ui/1.11.3/jquery-ui.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="js/plugins/jquery.mjs.nestedSortable2.js"></script>
        <style type="text/css">
            .ui-sortable-container{
                max-height: calc(100vh - 220px);
                overflow: auto;
                margin-bottom: 10px;
            }
            ol.sortable{
                margin: 0;
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
                    
                        <div class="row">
                            <div class="col-lg-12">
                                <h2>Admin Permissions Reorder</h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                            </div>
                        </div>
                            <hr>

                    <?php include('valid_msg.php'); ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ui-sortable-container">
                                <ol class="sortable">
                                    <?php 
                                    foreach ($data_drv as $key => $row) {
                                        echo "<li id='list_{$row['id']}'><div>{$row['permission_name']}</div></li>";
                                    }
                                    ?>
                                </ol>
                            </div>
                        </div> 
                    </div>

                    <button id="toArray" class="btn btn-success ladda-button" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-save"></i> Save </span></button>
                </div>
                <!--END PAGE CONTENT -->
            </div>
            <!--END MAIN WRAPPER -->
      
    <?php
    include_once('footer.php');
    ?>
    
    <script type="text/javascript">
        $(document).ready(function() {

            // initialize the nested sortable plugin
            $('.sortable').nestedSortable({
                forcePlaceholderSize: true,
                handle: 'div',
                helper: 'clone',
                items: 'li',
                opacity: .6,
                placeholder: 'placeholder',
                revert: 250,
                tabSize: 25,
                tolerance: 'pointer',
                toleranceElement: '> div',
                maxLevels: 1,

                isTree: false,
                expandOnHover: 700,
                startCollapsed: false
            });

            $('.disclose').on('click', function() {
                $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
            });

            $('#toArray').click(function(e){
                // get the current tree order
                arraied = $('ol.sortable').nestedSortable('toArray', {startDepthCount: 0});

                // log it
                //console.log(arraied);

                // send it with POST
                $.ajax({
                    url: 'action/admin_permissions.php',
                    type: 'POST',
                    data: { tree: arraied },
                })
                .done(function() {
                    //console.log("success");
                    
                  })
                .fail(function() {
                    //console.log("error");
                    
                  })
                .always(function() {
                    window.location = "";
                    //console.log("complete");
                });

            });

        });
    </script>
    </body>
</html>