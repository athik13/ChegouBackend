<?php
include_once('../common.php');
$tbl_name = 'food_menu';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$iParentId = isset($_REQUEST['iParentId']) ? $_REQUEST['iParentId'] : '';
$iFoodMenuId = isset($_REQUEST['iFoodMenuId']) ? $_REQUEST['iFoodMenuId'] : '';
$iMenuItemId = isset($_REQUEST['iMenuItemId']) ? $_REQUEST['iMenuItemId'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$oldVal = isset($_REQUEST['oldVal']) ? $_REQUEST['oldVal'] : '';
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
$itemParentId = isset($_REQUEST['itemParentId']) ? $_REQUEST['itemParentId'] : '';

if($page == 'items') {
	
	if($method == 'getSubMenuCategory') {
		$sql = "SELECT iFoodMenuId,vMenu_EN FROM " . $tbl_name . " WHERE iParentId = '" . $iParentId . "' AND eStatus='Active'";
		$db_data = $obj->MySQLSelect($sql); ?>
		<label>Sub-Menu Category</label>
		<select class="form-control" name = 'iFoodMenuId' onChange="changeDisplayOrder(this.value,'<?php echo $iMenuItemId; ?>');" required>
		<option value="">--select--</option>
		<?php for($i=0;$i<count($db_data);$i++) {?>
			<option value="<?php echo $db_data[$i]['iFoodMenuId']; ?>" <?php if($oldVal == $db_data[$i]['iFoodMenuId']) echo 'selected'; ?> ><?php echo $db_data[$i]['vMenu_EN']; ?></option>
		<?php } ?>
		</select>
	<?php exit; }
	
	if($method == 'getParentItems') {
		$sql = "SELECT iMenuItemId,vItemType_EN FROM menu_items WHERE iFoodMenuId = '" . $iFoodMenuId . "' AND iParentId='0' AND eStatus='Active'";
		$db_data = $obj->MySQLSelect($sql); ?>
		<option value="0">Add New Parent</option>
		<?php for($i=0;$i<count($db_data);$i++) {?>
			<option value="<?php echo $db_data[$i]['iMenuItemId']; ?>" <?php if($itemParentId == $db_data[$i]['iMenuItemId']) echo 'selected'; ?> ><?php echo $db_data[$i]['vItemType_EN']; ?></option>
		<?php } ?>
	<?php exit; }
	
	if(isset($_REQUEST['iFoodMenuId'])) {
		$cmpss = "";
		if($iFoodMenuId != "") {
			$cmpss = " AND iFoodMenuId = '$iFoodMenuId' ";
		}

		$sql = "SELECT COUNT(iMenuItemId) AS Total FROM menu_items WHERE 1=1 $cmpss";
		$db_count = $obj->MySQLSelect($sql);
		$count = $db_count[0]['Total'];
		$newCnt = $count+1;
		$totalVal = $count+1;
		
		if($iMenuItemId != "") {
			$sql = "SELECT iDisplayOrder FROM menu_items WHERE 1=1 AND iMenuItemId='$iMenuItemId' $cmpss";
			$db_old = $obj->MySQLSelect($sql);
			if(!empty($db_old)) {
				$newCnt = $count;
				$totalVal = $db_old[0]['iDisplayOrder'];
			}
		}
		?>
		<select name="iDisplayOrder" id="iDisplayOrder" class="form-control">
		<?php for($i=1;$i<=$newCnt;$i++) {?>
		<option value="<?php echo $i?>" 
		<?php if($i == $totalVal)
		echo 'selected';?>> <?php echo $i?> </option>
		<?php } ?>
		</select>
	<?php exit; }
}else {

	if(isset($_REQUEST['iCompanyId']) && isset($_REQUEST['iParentId'])) {
		$cmpss = '';
		if($iCompanyId != '' && $iParentId != '') {
			$cmpss = " AND iCompanyId='$iCompanyId'";
		}
		$sql = "SELECT COUNT(iFoodMenuId) AS Total FROM " . $tbl_name . " WHERE 1=1 $cmpss";
		$db_count = $obj->MySQLSelect($sql);
		$count = $db_count[0]['Total'];
		$newCnt = $count+1;
		$totalVal = $count+1;
		
		if($iFoodMenuId != "") {
			$sql = "SELECT iDisplayOrder FROM " . $tbl_name . " WHERE 1=1 AND iFoodMenuId='$iFoodMenuId'";
			$db_old = $obj->MySQLSelect($sql);
			if(!empty($db_old)) {
				$newCnt = $count;
				$totalVal = $db_old[0]['iDisplayOrder'];
			}
		}
		?>
		<select name="iDisplayOrder" id="iDisplayOrder" class="form-control">
		<?php for($i=1;$i<=$newCnt;$i++) {?>
		<option value="<?php echo $i?>" 
		<?php if($i == $totalVal)
		echo 'selected';?>> <?php echo $i?> </option>
		<?php } ?>
		</select>
	<?php exit; }
} ?>