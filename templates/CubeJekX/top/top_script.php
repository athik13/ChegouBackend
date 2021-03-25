<?php 
if(file_exists($logogpath."favicon.ico")){
    $fav_icon_image  = $logogpath."favicon.ico";
}else{
    $fav_icon_image  = "favicon.ico";
}
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
<meta content="" name="author" />
<link rel="icon" href="<?= $fav_icon_image;?>" type="image/x-icon">
<base href="<?= $siteUrl;?>">
<?php
$lang = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
$sess_user = isset($_SESSION['sess_user']) ? $_SESSION['sess_user'] : "";
$sess_eSystem = isset($_SESSION['sess_eSystem']) ? $_SESSION['sess_eSystem'] : "";
$lang_arr = array('AR', 'UR', 'HW', 'PS');
$lang_ltr = "";
if (in_array($lang, $lang_arr)) {
    $lang_ltr = 'yes';
}
$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
?>
<!-- TEMPLATE CSS START -->
<link rel="stylesheet" href="assets/css/apptype/<?= $template;?>/jquery.dataTables.css" type="text/css">
<link rel="stylesheet" href="assets/css/apptype/<?= $template;?>/jquery-ui.css">
<link rel="stylesheet" href="assets/css/apptype/<?= $template;?>/font-awesome-4.css" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Poppins:100,400,500,600,700,800,900&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="assets/css/apptype/<?= $template;?>/mstepper.min.css" type="text/css">
<link rel="stylesheet" href="assets/css/apptype/<?= $template;?>/header.css" type="text/css">
<link rel="stylesheet" href="assets/css/apptype/<?= $template;?>/footer.css" type="text/css">
<link rel="stylesheet" href="assets/css/apptype/<?= $template;?>/style.css" type="text/css">
<link href="<?= $siteUrl;?>assets/css/delivery_pref_modal.css" rel="stylesheet" type="text/css" />
<?php 
if(stripos($url, 'store-listing') !== false || stripos($url, 'store-order') !== false) {
    include_once("store_css_include.php");     
} 
?>
<link rel="stylesheet" href="assets/css/apptype/<?= $template;?>/style.less" type="text/less"> <!-- Added for LESS CSS BY GP 26 SEP 2019 -->
<link rel="stylesheet" href="assets/css/apptype/<?= $template;?>/style_rtl.css" type="text/css">
<script src="<?= $templatePath; ?>assets/js/jquery.min.js"></script>
<script src="<?= $templatePath; ?>assets/js/parallax.min.js"></script>
<script src="<?= $templatePath; ?>assets/js/mstepper.min.js"></script> 
<script src="<?= $templatePath; ?>assets/js/touch-punch.js"></script> 
<link href='//fonts.googleapis.com/css?family=Raleway:400,700,300,500,900,800,600,200,100' rel='stylesheet' type='text/css'>
<script>
document.write('<style type="text/css">body{display:none}</style>');
    jQuery(function($) {
    $('body').css('display','block');
});
</script>

<script>
$(document).ready(function () {
    var hreft = $("a.book-btn").attr("href");
    <?php if($sess_user == "driver" || $sess_user == "organization") {?>
        if(hreft == "userbooking" || hreft == "companybooking" || hreft == "order-items"){
            $('a.book-btn').css('display','none');
        }
    <? } 
    if($sess_user == "company" && $sess_eSystem != "DeliverAll") {?>
        if(hreft == "order-items"){
            $('a.book-btn').css('display','none');
        }
    <? } 
    if($sess_user == "company" && $sess_eSystem == "DeliverAll") { ?>
        if(hreft == "userbooking" || hreft == "companybooking"){
            $('a.book-btn').css('display','none');
        }
    <? }?>
});

var langData = {
            "sEmptyTable":     "<?= $langage_lbl['LBL_NO_DATA_AVAIL'] ?>",
            "sInfo":           "<?= $langage_lbl['LBL_SHOWING_DATATABLE'] ?> _START_ <?= $langage_lbl['LBL_CONFIRM_TRANSFER_TO_WALLET_TXT1'] ?> _END_ <?= $langage_lbl['LBL_OF_DATATABLE'] ?> _TOTAL_ <?= $langage_lbl['LBL_ENTRIES_DATATABLE'] ?>",
            "sInfoEmpty":      "<?= $langage_lbl['LBL_SHOWING_ZERO_ENTRIES_DATATABLE'] ?>",
            "sInfoFiltered":   "(<?= $langage_lbl['LBL_FILTERED_FROM_DATATABLE'] ?> _MAX_ <?= $langage_lbl['LBL_TOTAL_DATATABLE'] ?> <?= $langage_lbl['LBL_ENTRIES_DATATABLE'] ?>)",
            "sInfoPostFix":    "",
            "sInfoThousands":  ",",
            "sLengthMenu":     "<?= $langage_lbl['LBL_SHOW_DATATABLE'] ?> _MENU_ <?= $langage_lbl['LBL_ENTRIES_DATATABLE'] ?>",
            "sLoadingRecords": "<?= $langage_lbl['LBL_LOADING_TXT'] ?>...",
            "sProcessing":     "<?= $langage_lbl['LBL_PROCESSING_DATATABLE'] ?>...",
            "sSearch":         "<?= $langage_lbl['LBL_Search'] ?>:",
            "sZeroRecords":    "<?= $langage_lbl['LBL_NO_RECORDS_FOUND1'] ?>",
            "oPaginate": {
                "sFirst":    "<?= $langage_lbl['LBL_FIRST'] ?>",
                "sLast":    "<?= $langage_lbl['LBL_LAST'] ?>",
                "sNext":    "<?= $langage_lbl['LBL_NEXT'] ?>",
                "sPrevious": "<?= $langage_lbl['LBL_PREVIOUS'] ?>"
            },
            "oAria": {
                "sSortAscending":  ": <?= $langage_lbl['LBL_ACTIVATE_SORT_ASC_DATATABLE'] ?>",
                "sSortDescending": ": <?= $langage_lbl['LBL_ACTIVATE_SORT_DESC_DATATABLE'] ?>"
            }
    };
</script>