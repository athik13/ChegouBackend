<?php
include_once("common.php");
include_once ('generalFunctions.php');

$order_details = $_POST['order_details'];
$iUserId = $_POST['iUserId'];
$iUserType = $_POST['iUserType'];
$template = $_POST['template'];
$iOrderId = $_POST['iOrderId'];
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
if($iOrderId==$order_details['iOrderId']) {    
$img_array = array("time-left.svg","tick.svg","taxi-driver.svg","deliver_scooter.svg","tick.svg");
                    
foreach ($orderStatus->message as $key => $value) {
    $time = date("h:i A", strtotime($value->dDate));
    if ($value->eCompleted == 'Yes') {
        if($value->iStatusCode==8 || $value->iStatusCode==9 || $value->iStatusCode==11) { ?>
            <li class="passed" date-time="<?= $time; ?>">
                <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/close.svg" alt=""></i>
                <strong><?= $value->vStatus; ?></strong>
                <p><?= $value->vStatus_Track; ?></p>
            </li>   
        <?php $flag_cancelled = 1; } else {
        ?>
        <li class="passed" date-time="<?= $time; ?>">
            <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/<?= $img_array[$key]; ?>" alt=""></i>
            <strong><?= $value->vStatus; ?></strong><?php if ($value->iStatusCode == 5) { ?><a class="open-popup" data-id="call-info-model" onClick="displayDriverDetails('<?= $value->phoneCode; ?>', '<?= $generalobj->clearPhone($value->vPhone); ?>', '<?= $generalobj->clearName($value->driverName); ?>');" tell=""><img src="<?= $siteUrl; ?>assets/img/call-img.svg" width="15px" height="15px"  alt="" style="margin:0 0 0 10px"></a><?php } ?></strong>
            <p><?= $value->vStatus_Track; ?></p>
        </li>
        <?php } 
    } else { ?>
        <li>
            <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/<?= $img_array[$key]; ?>" alt=""></i>
            <strong><?= $value->vStatus; ?></strong>
            <p><?= $value->vStatus_Track; ?></p>
        </li>
<?php } } } else {
    echo "No"; exit;
}
} ?>

        