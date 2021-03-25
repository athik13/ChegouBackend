<?php
ob_start();
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

if (!$userObj->hasPermission('manage-currency')) {
    $userObj->redirect();
}

$success = $_REQUEST['success'];

$sql = "SELECT * FROM currency  order by iDispOrder";
$db_currency = $obj->MySQLSelect($sql);

 count($db_currency); 

$vName = "SELECT vName FROM currency  order by iDispOrder";
$db_vName = $obj->MySQLSelect($vName);
for ($i = 0; $i < count($db_vName); $i++) {
    $db_name[$i] = $db_vName[$i]["vName"];
}
$script = 'Currency';
if (isset($_REQUEST['reload'])) {
    $siteUrl = $tconfig['tsite_url'] . "".SITE_ADMIN_URL."/currency.php?success=1";
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
            | Currency</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
		<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
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
                                <h2>Currency</h2>
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
                                    <form action="currency_action.php" method="post" id="formId">
                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th>Currency</th>
                                                    <th>Ratio</th>
                                                    <th>Threshold Amount <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Currency Wise Minimum Payment Drivers can Request from Website Driver Account to Admin.', ENT_QUOTES, 'UTF-8') ?>'></i></th>
                                                    <th>Symbol</th>
					<!--<th style="text-align:center">Rounding Off <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('The rounding is applied when the fare is generated in decimal value, where decimal range from 0 - 0.50 will consider as 0 and decimal range from 0.51 - 1 will consider as 1. E.g. 1. Fare before rounding is $8.33 and Fare after rounding is $8.00  E.g. 2. Fare before rounding is $8.50 and Fare after rounding is $8.00 E.g. 3. Fare before rounding is $8.51 and Fare after rounding is $9.00', ENT_QUOTES, 'UTF-8') ?>'></i></th>
                                                    <th>Reverse formatting&nbsp;<i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Reverse formatting', ENT_QUOTES, 'UTF-8') ?>'></i></th>
                                                    <th>Reverse Symbol&nbsp;<i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars('Reverse Symbol', ENT_QUOTES, 'UTF-8') ?>'></i></th>-->
                                                    <th>Default</th>
                                                    <th>DispOrder</th>
                                                    <th>Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?
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
													$eRoundingOffEnable = (!empty($value['eRoundingOffEnable']) && $value['eRoundingOffEnable']=='Yes')? 'Yes' : 'No';
													  $eReverseformattingEnable = (!empty($value['eReverseformattingEnable']) && $value['eReverseformattingEnable']=='Yes')? 'Yes' : 'No';
													  $eReverseSymbolEnable = (!empty($value['eReverseSymbolEnable']) && $value['eReverseSymbolEnable']=='Yes')? 'Yes' : 'No';

                                                    echo '<tr>
										<td><input class="form-control" type="hidden" name="iCurrencyId[]" value="' . $value['iCurrencyId'] . '" />' . $value["vName"] . '</td>
										<td><input class="form-control" name="Ratio[]" id="ratio_' . $value['iCurrencyId'] . '" type="text" value=' . $value['Ratio'] . ' ' . $readonlyadd . ' required/></td>
										<td><input class="form-control" name="fThresholdAmount[]" type="text" value=' . $value['fThresholdAmount'] . ' /></td>
										<td><input  class="form-control" name="vSymbol[]" type="text" value=' . $value['vSymbol'] . ' required/></td>';
										
										?>    
										<!--<td style="text-align:center">
										</tr>-->
										<!--<td style="text-align:center"><div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eRoundingOffEnable[<?=$value['iCurrencyId'];?>]" <?= ($eRoundingOffEnable == 'Yes') ? 'checked' : ''; ?>/></td>
											
											 
										<td style="text-align:center"><div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eReverseformattingEnable[<?=$value['iCurrencyId'];?>]" <?= ($eReverseformattingEnable == 'Yes') ? 'checked' : ''; ?>/></td>
											
										<td style="text-align:center"><div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox"  name="eReverseSymbolEnable[<?=$value['iCurrencyId'];?>]" <?= ($eReverseSymbolEnable == 'Yes') ? 'checked' : ''; ?>/>
											
                                        </div><?php if(ENABLE_CHANGE_CURRENCY_ROUNDING_OPTION=='Yes') { ?><h1 class="float-right"><a class="btn btn-primary edit_cur_btn" data-toggle="modal" data-target="#myModalcurrency">Edit</a></h1><?php } ?></td>-->
                                        <?php

                                        

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


 <?php if ($userObj->hasPermission('update-status-manage-currency')) { ?>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
                                        <?php if ($readonlyadd != "") {
                                         ?>
                                        <input type="hidden" name="eStatus[]" id="estatus_value" value="Active" class="form-control">
                                            <?= $eStatus; ?>
                                        <?php } else { ?>
                                        <select name="eStatus[]" id="estatus_value" class="form-control">
                                            <!-- <option value="" >Select Status</option> -->
                                            <option value='Active'  <?php 
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
<?php if ($userObj->hasPermission('update-status-manage-currency')) { ?>
                                                <tr>

                                                    <td colspan="7" align="center"><input type="submit" name="btnSubmit" class="btn btn-default" value="Edit currency"></td>
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
        
    <div class="modal fade" id="myModalcurrency" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"> Edit</h4>
            </div>
            <div class="modal-body">
                
                <div>
                    <div class="row">
                        <div class="col-lg-12">
                            <label> Range 1</label>
                        </div>
                        <div class="col-lg-3">
                            <input type="text" class="form-control" readonly name="fMiddleRangeValue"  id="fMiddleRangeValue" value="0" placeholder="Middle Range Value" > 
                        </div> 
                        <div class="range1">
                            To 
                        </div> 
                        <div class="col-lg-3">
                            <input type="text" class="form-control" name="iFirstRangeValue1"  min="0" max="99" id="iFirstRangeValue1" value="<?= $fMiddleRangeValue; ?>" placeholder="First Range Value" > 
                        </div> 
                    </div> 
                     <div class="row">
                        <div class="col-lg-12">
                            <label> Range1 Value</label>
                        </div>
                        <div class="col-lg-3">
                            <?php $fMiddleRangeValue = floatval($fMiddleRangeValue);?>
                            <select class="form-control" name='iFirstRangeValue' id="iFirstRangeValue"> 
                                <?php  //if(is_float($fMiddleRangeValue) == true){
                                    if($fMiddleRangeValue === floatval($fMiddleRangeValue)){
                                    ?>
                                    <option value=''>-- Select Value --</option>
                                    <option value='0' <? if ($iFirstRangeValue == "0") { ?>selected<?php } ?>>0</option>
                                    <option value = "0.50" <? if ($iFirstRangeValue == "0.50") { ?>selected<?php } ?>>0.5</option>
                                    <option value = "1" <? if ($iFirstRangeValue == "1") { ?>selected<?php } ?>>1</option> 
                                <?php }else{ ?>
                                    <option value=''>-- Select Value --</option>
                                    <option value='0' <? if ($iFirstRangeValue == "0") { ?>selected<?php } ?>>0</option>
                                    <option value = "50" <? if ($iFirstRangeValue == "50") { ?>selected<?php } ?>>0.5</option>
                                    <option value = "100" <? if ($iFirstRangeValue == "100") { ?>selected<?php } ?>>1</option>  
                                <?php } ?>
                            </select>
                            
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <label> Range 2</label>
                        </div> 
                        <div class="col-lg-3">
                            <input type="text" class="form-control" readonly name="fMiddleRangeValue2"  id="fMiddleRangeValue2" value="<?= $fMiddleRangeValue; ?>" placeholder="Middle Range Value2" >
                        </div> 
                        <div class="range2">
                            To 
                        </div> 
                        <div class="col-lg-3">
                            <input type="text" class="form-control" readonly name="iSecRangeValue1"  id="iSecRangeValue1" value="<?= $iSecRangeValue; ?>" placeholder="Second Range Value" >
                        </div> 
                    </div> 
                    <div class="row">
                        <div class="col-lg-12">
                            <label> Range 2 Value  </label>
                        </div> 
                        <div class="col-lg-3">
                            <select class="form-control" name='iSecRangeValue' id="iSecRangeValue"> 
                                <?php //if(is_float($fMiddleRangeValue) == true)  {
                                    if($fMiddleRangeValue === floatval($fMiddleRangeValue)){ 
                                    ?>
                                    <option value=''>-- Select Value --</option>
                                    <option value='0' <? if ($iSecRangeValue == "0") { ?>selected<?php } ?>>0</option>
                                    <option value = "0.50" <? if ($iSecRangeValue == "0.50") { ?>selected<?php } ?>>0.5</option>
                                    <option value = "1" <? if ($iSecRangeValue == "1") { ?>selected<?php } ?>>1</option>
                                <?php }else{ ?>
                                    <option value=''>-- Select Value --</option>
                                    <option value='0' <? if ($iSecRangeValue == "0") { ?>selected<?php } ?>>0</option>
                                    <option value = "50" <? if ($iSecRangeValue == "1") { ?>selected<?php } ?>>50</option>
                                    <option value = "100" <? if ($iSecRangeValue == "100") { ?>selected<?php } ?>>100</option>
                                <?php } ?>
                                
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="save_currency_rounding_data(this)">Edit</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
        
        <?
        include_once('footer.php');
        ?>
        <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
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
        
        $("#iFirstRangeValue1").on('keydown', function (e) {
              
            if($("#iFirstRangeValue1").val() != 0){

                if (e.keyCode === 190 || e.keyCode === 110) {
                    return false;
                }
            } 
        });

        $("#iFirstRangeValue1").on('keyup', function (e) {
            var iFirstRangeValue = $("#iFirstRangeValue1").val();
            if(iFirstRangeValue != ''){
                $("#fMiddleRangeValue2").val(iFirstRangeValue);
            }
 
            var inputtxt = $("#fMiddleRangeValue2");
            var decimal=  /^[-+]?[0-9]+\.[0-9]+$/; 
            //var RE = "^\d*\.?\d{0,2}$";
            //alert(inputtxt.val().match(decimal));
              
            if(inputtxt.val().match(decimal)) { 
                $("#iSecRangeValue1").val("1");
                
               /* var options1 = "<option value=''>-- Select Value --</option><option value='0.5'>0.5</option><option value='1'>1</option>";
                $("#iFirstRangeValue").html(options1);*/
            } else { 
                if(inputtxt.val() != '0'){
                    $("#iSecRangeValue1").val("100"); 
                } 
            }    

            if($("#iFirstRangeValue1").val().match(decimal)){
                console.log('iFirstRangeValue1- 0.5');
                var options1 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='0.5'>0.5</option><option value='1'>1</option>";
                /*var options2 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='0.5'>0.5</option><option value='1'>1</option>";
                $("#iFirstRangeValue").html(options2);*/
                $("#iFirstRangeValue").html(options1);

                var options2 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='0.5'>0.5</option><option value='1'>1</option>"; 
                $("#iSecRangeValue").html(options2);
            }else{
                console.log('iFirstRangeValue1- 50');
                var options1 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='50'>50</option><option value='100'>100</option>";
                $("#iFirstRangeValue").html(options1);

                var options2 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='50'>50</option><option value='100'>100</option>";
                $("#iSecRangeValue").html(options2);
            }

            /*if($("#iSecRangeValue1").val().match(decimal)){
                console.log('iSecRangeValue1- 0.5');
                var options2 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='0.5'>0.5</option><option value='1'>1</option>"; 
                $("#iSecRangeValue").html(options2);

                var options1 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='0.5'>0.5</option><option value='1'>1</option>"; 
                $("#iFirstRangeValue").html(options1);
            }else{
                console.log('iSecRangeValue1- 50');
                var options2 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='50'>50</option><option value='100'>100</option>";
                $("#iSecRangeValue").html(options2);

                var options1 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='50'>50</option><option value='100'>100</option>";
                $("#iFirstRangeValue").html(options1);
            }*/
  
        });
		
		function showhideroundingoffRange() {
			if ($('input[name=eRoundingOffEnable]').is(':checked')) {
				//alert('Checked');
				$("#showroundingoffRange").show();
			} else {
				//alert('Not checked');
				$("#showroundingoffRange").hide();
			}
		}

		showhideroundingoffRange();
		
		function showhiderReverseformatting() {
			if ($('input[name=eReverseformattingEnable]').is(':checked')) {
				//alert('Checked');
				$("#showroundingoffRange").show();
			} else {
				//alert('Not checked');
				$("#showroundingoffRange").hide();
			}
		}

		showhiderReverseformatting();
        
        /*$(".edit_cur_btn").click(function() {
            alert('aaaa');
        });*/
        
        function save_currency_rounding_data(e) {
            console.log(e);
            //ajax_edit_rounding_off.php
            var fMiddleRangeValue = $("#fMiddleRangeValue").val();
            var fMiddleRangeValue = $("#iFirstRangeValue1").val();
            var iFirstRangeValue = $("#iFirstRangeValue").val();
            var iSecRangeValue = $("#iSecRangeValue").val();
            
            $.ajax({
                type: "POST",
                url: "ajax_edit_rounding_off.php",
                dataType: "html",
                data: {'iCurrencyId': iCurrencyId, 'fMiddleRangeValue': fMiddleRangeValue, 'iFirstRangeValue': iFirstRangeValue, 'iSecRangeValue': iSecRangeValue},
                success: function (dataHtml2) {
                    

                }
            });
        }
        </script>
    </body>
    <!-- END BODY-->
</html>