<?php
ob_start();
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

if (!$userObj->hasPermission('manage-language')) {
    $userObj->redirect();
}

$success = $_REQUEST['success'];

$sql = "SELECT * FROM language_master  order by iDispOrder";
$db_currency = $obj->MySQLSelect($sql);
 count($db_currency); 

$vTitle = "SELECT vTitle FROM language_master  order by iDispOrder";
$db_vName = $obj->MySQLSelect($vTitle);
for ($i = 0; $i < count($db_vName); $i++) {
    $db_name[$i] = $db_vName[$i]["vTitle"];
}
$script = 'Language';
if (isset($_REQUEST['reload'])) {
    $siteUrl = $tconfig['tsite_url'] . "".SITE_ADMIN_URL."/language.php?success=1";
    ?>
    <script>window.location.replace("<?php echo $siteUrl; ?>");</script>
<?php } ?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>
            <?= $SITE_NAME; ?>
            | Language</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2>Language</h2>
                                <!-- <input type="button" id="show-add-form" value="ADD A DRIVER" class="add-btn">
                                                                                    <input type="button" id="cancel-add-form" value="CANCEL" class="cancel-btn"> -->
                            </div>
                        </div>
                        <hr />
                    </div>
                    <div style="clear:both;"></div>
                    <? if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <br/>
                        <?
                    } else if ($success == 2) {
                        ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <br/>
                        <?
                    }
                    ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form action="language_action.php" method="post" id="formId">
                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th>Language</th>
                                                    <th>LangCode</th>
                                                    <th>Default</th>
                                                    <th>DispOrder</th>
                                                    <th>Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($db_currency as $key => $value) {

                                                    $eStatus = $value['eStatus'] ;

                                                    $iDispOrder = $value['iDispOrder'] ;

                                                    $eDefault = "";
                                                    if ($value['eDefault'] == "Yes") {
                                                        //$eDefault = " checked ";
                                                        $eDefault = "Yes";
                                                        $readonlyadd = "readonly";
                                                    } else {
                                                        //$eDefault = "";
                                                        $eDefault = "No";
                                                        $readonlyadd = "";
                                                    }

                                                    echo '<tr>
										<td><input class="form-control" type="hidden" name="vTitle[]" value="' . $value['vTitle'] . '" />' . $value["vTitle"] . '</td>



                                        <td><input class="form-control" type="hidden" name="iLanguageMasId[]" value="' . $value['iLanguageMasId'] . '" />' . $value['vCode'] .  '</td>';



                                                    echo '<td align="Center">' . $eDefault . '</td>';
                                             ?>

                                        <td width="12%" >

                                        <!-- <input type="hidden" name="iDispOrder[]" value="" class="form-control"> -->

                                        <select name="iDispOrder[]" id="estatus_value" class="form-control">

                                            <?php
                                            for ($i = 1; $i < count($db_currency)+1; $i++) {
                                                // $db_currency[$i] = $db_currency[$i]["iDispOrder"];


                                         ?>   <option value ="<?= $i; ?>" <? if($i == $iDispOrder){echo "selected";}?> > <?= $i; ?> </option>
                                             
                                              <?php }  ?>
                                        </select>
                                        

                                    </td>

 <?php if ($userObj->hasPermission('update-status-manage-language')) { ?>
                                                <td width="12%" class="estatus_options" id="eStatus_options" >
                                                    <?php if ($readonlyadd != "") {
                                         ?>
                                        <input type="hidden" name="eStatus[]" id="estatus_value" value="Active" class="form-control">
                                            <?= $eStatus; ?>
                                        <?php } else { ?>
                                        <select name="eStatus[]" id="estatus_value" class="form-control">
                                            <!-- <option value="" >Select Status</option> -->
                                            <option value='Active' <?php
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
                                        <?php } ?>
                                    </td>
 <?php } else{ ?>
                                    <td width="12%" class="estatus_options" id="eStatus_options" ><?= $eStatus; ?></td>
 <?php } ?>


                                    <?php

                                                    /* echo '<td><input  class="form-control" name="eDefault" id="eDefault_'.$value['iCurrencyId'].'" type="radio" value="'.$value['iCurrencyId'].'" '.$eDefault.' /></td>'; */

                                                    echo '</tr>';
                                                }
                                                ?>
<?php if ($userObj->hasPermission('update-status-manage-language')) { ?>
                                                <tr>

                                                    <td colspan="6" align="center"><input type="submit" name="btnSubmit" class="btn btn-default" value="Edit Language"></td>
                                                </tr>
<?php } ?>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>
                            </div>
                            <!--TABLE-END-->
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
        <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
        <!--<script>
                                $(document).ready(function () {
                                        $('#dataTables-example').dataTable();
                                });
                        </script>-->
        <script type="text/javascript">
        $("form").submit(function (event) {
            event.preventDefault();
            $('#formId').get(0).submit();

            /*  var value = $( 'input[name=eDefault]:checked' ).val();

             var ratio = $('#ratio_'+ value).val();
             if(ratio == 1){
             $('#formId').get(0).submit();
             } else {
             alert("Please change euro currency ratio to 1.0000 since your making it as default. Also adjust other currency ratio as per euro.");
             return false;
             } */
        });
        </script>
    </body>
    <!-- END BODY-->
</html>