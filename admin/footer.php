<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//Added By HJ On 02-07-2019 For Check Project Language Conversion Process Done Or Not Start
$dbAllTablesArr = $generalobj->getAllTableArray(); // For Get Current Db's All Table Arr
$checkTable = $generalobj->checkTableExists("setup_info", $dbAllTablesArr);
$setupMessage = "";
if ($checkTable == 1) {
    //echo "<pre>";
    $data_info = $obj->MysqlSelect("select * from setup_info where 1=1");
    $eLanguageLabelConversion = $eOtherTableValueConversion = $eCurrencyFieldsSetup = $eLanguageFieldsSetup = "No";
    if (isset($data_info[0]['eLanguageLabelConversion']) && $data_info[0]['eLanguageLabelConversion'] != "") {
        $eLanguageLabelConversion = $data_info[0]['eLanguageLabelConversion'];
    }
    if (isset($data_info[0]['eOtherTableValueConversion']) && $data_info[0]['eOtherTableValueConversion'] != "") {
        $eOtherTableValueConversion = $data_info[0]['eOtherTableValueConversion'];
    }
    if (isset($data_info[0]['eCurrencyFieldsSetup']) && $data_info[0]['eCurrencyFieldsSetup'] != "") {
        $eCurrencyFieldsSetup = $data_info[0]['eCurrencyFieldsSetup'];
    }
    if (isset($data_info[0]['eLanguageFieldsSetup']) && $data_info[0]['eLanguageFieldsSetup'] != "") {
        $eLanguageFieldsSetup = $data_info[0]['eLanguageFieldsSetup'];
    }
    if ($eLanguageLabelConversion != "Yes" || $eOtherTableValueConversion != "Yes" || $eCurrencyFieldsSetup != "Yes" || $eLanguageFieldsSetup != "Yes") {
        if ($eCurrencyFieldsSetup != "Yes") {
            $setupMessage .= "Currency ratio wise field setup";
        }
        if ($eLanguageFieldsSetup != "Yes") {
            if ($setupMessage != "") {
                $setupMessage .= ", ";
            }
            $setupMessage .= "Language wise field setup";
        }
        if ($eLanguageLabelConversion != "Yes") {
            if ($setupMessage != "") {
                $setupMessage .= ", ";
            }
            $setupMessage .= "Language label table's";
        }
        if ($eOtherTableValueConversion != "Yes") {
            if ($setupMessage != "") {
                $setupMessage .= " And";
            }
            $setupMessage .= " Other all table's";
        }
        $setupMessage .= " Conversion Pending.";
    }
    //print_r($setupMessage);die;
}
//Added By HJ On 02-07-2019 For Check Project Language Conversion Process Done Or Not End
?>
<script>
    var _system_script = '<?php echo $script; ?>';
    //Added BY HJ On 05-06-2019 For Auto Hide Message Section Start
    $(document).ready(function () {
        if ($('.alert').html() != '') {
            setTimeout(function () {
                $('.alert').fadeOut();
            }, 4000);
        }
    });
    function hideSetupMessage() {
        $("#footer-new-cube").hide(2000);
    }
    //Added BY HJ On 05-06-2019 For Auto Hide Message Section End
</script>
<script type="text/javascript" src="js/validation/jquery.validate.min.js" ></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js" ></script>
<script type="text/javascript" src="js/form-validation.js" ></script>
<div style="clear:both;"></div>
<?php if ($setupMessage != "") { ?>
    <div id="footer-new-cube">
        <div class="cancle-cube-cl"><img onclick="hideSetupMessage();" src="images/cancel.svg" width="20px" height="20px" /></div>
        <div class="text-cube-cl"><?= $setupMessage; ?></div>
    </div>
<?php } ?>
<div id="footer">
    <?= $COPYRIGHT_TEXT_ADMIN; ?>
</div>
