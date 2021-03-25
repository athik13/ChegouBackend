<?php
include_once("common.php");
include_once ('generalFunctions.php');
$order_details = $_POST['order_details'];
$iUserId = $_POST['iUserId'];
$iUserType = $_POST['iUserType'];
$orderStatus_json = getOrderDetailsforGettingliveStatus($order_details['iOrderId'], $iUserId, $iUserType);
$orderStatus = json_decode($orderStatus_json);
if(!empty($_POST['chk_driver_assign'])) {
    $driverid = 0;
    foreach ($orderStatus->message as $key => $value) {
        if($value->iStatusCode==4 && $value->eCompleted=='Yes') {
            $driverid = $value->iDriverId;
            echo $driverid; exit;
        }
    }
    echo $driverid; exit;
} else {
foreach ($orderStatus->message as $key => $value) {
    $time = date("h:i A", strtotime($value->dDate));
    if ($value->eCompleted == 'Yes') {
        if($value->iStatusCode==8 || $value->iStatusCode==9 || $value->iStatusCode==11) { ?>
            <b style="text-align:center;font-size: 17px;"><?= $time; ?></b><li class="cancel"><strong class="color-invo" id="status<?= $value->iStatusCode; ?>"><?= $value->vStatus; ?></strong><p style="margin:0 0 0 60px"><?= $value->vStatus_Track; ?></p></li>   
        <?php $flag_cancelled = 1; } else {
        ?>
<b style="text-align:center;font-size: 17px;"><?php echo $time; ?></b><li class="work work-invoice"><strong class="color-invo" id="status<?php echo $value->iStatusCode; ?>"><?php echo $value->vStatus; ?></strong><p style="margin:0 0 0 60px"><?php echo $value->vStatus_Track; ?></p></li>
        <?php } 
    } else { ?>
        <b style="visibility: hidden;text-align:center;font-size: 17px;"><?php echo $time; ?></b><li class="work work-invoice-new"><strong class="color-invo-new" id="status<?php echo $value->iStatusCode; ?>"><?php echo $value->vStatus; ?></strong><p style="margin:0 0 0 60px"><?php echo $value->vStatus_Track; ?></p></li>
<?php } } 
} ?>

        