<?php
include_once("common.php");
include_once ('generalFunctions.php');
$tripDetails = $_POST['tripDetails'];
$iUserId = $_POST['iUserId'];
if($tripDetails=='CabRequestAccepted') { 
	$stylefirst = 'work-invoice'; $stylesec = 'work-invoice-new';$stylethird = 'work-invoice-new'; 
	$secstylefirst = 'color-invo'; $secstylesec = 'color-invo-new';$secstylethird = 'color-invo-new'; 
}
else if($tripDetails=='TripStarted') { 
	$stylefirst = 'work-invoice'; $stylesec = 'work-invoice'; $stylethird = 'work-invoice-new';
	$secstylefirst = 'color-invo'; $secstylesec = 'color-invo'; $secstylethird = 'color-invo-new';
}
else if($tripDetails=='TripEnd') { 
	$stylefirst = 'work-invoice'; $stylesec = 'work-invoice'; $stylethird = 'work-invoice'; 
	$secstylefirst = 'color-invo'; $secstylesec = 'color-invo'; $secstylethird = 'color-invo'; 
} else {
	$stylefirst = 'work-invoice-new'; $stylesec = 'work-invoice-new';$stylethird = 'work-invoice-new'; 
	$secstylefirst = 'color-invo-new'; $secstylesec = 'color-invo-new';$secstylethird = 'color-invo-new'; 
}

?>
<b style="visibility: hidden;text-align:center;">10:20 AM</b><li class="work work-invoice"><strong class="color-invo">Your Trip is started</strong></li>
                                                    
<!--<b style="visibility: hidden;text-align:center;">10:20 AM</b><li class="work <?php echo $stylefirst; ?>"><strong class="<?php echo $secstylefirst; ?>" id="CabRequestAccepted">Driver is arriving</strong></li>-->
<?php //} else { ?>
<b style="visibility: hidden;text-align:center;">10:20 AM</b><li class="work <?php echo $stylesec; ?>" id="TripStartedLi"><strong class="<?php echo $secstylesec; ?>" id="TripStarted">Your trip has begun.</strong></li>

<b style="visibility: hidden;text-align:center;">10:20 AM</b><li class="work <?php echo $stylethird; ?>"><strong class="<?php echo $secstylethird; ?>" id="TripEnd">Your Trip is finished</strong></li>