<?php
function getFavSelectQuery($iCompanyId = '', $iUserId) {
    if (isset($iCompanyId) && !empty($iCompanyId)) {
        return ", (SELECT IFNULL((SELECT sf.eFavStore FROM store_favorites as sf where sf.iUserId=" . $iUserId . " AND sf.iCompanyId = " . $iCompanyId . " AND sf.iServiceId = company.iServiceId limit 0,1),'No')) AS eFavStore";
    } else {
        return ", (SELECT IFNULL((SELECT sf.eFavStore FROM store_favorites as sf where sf.iUserId=" . $iUserId . " AND sf.iCompanyId = company.iCompanyId AND sf.iServiceId = company.iServiceId limit 0,1),'No')) AS eFavStore";
    }
}
function getFavFilterCondition($query) {
    global $_REQUEST;
    $eFavStore = isset($_REQUEST['eFavStore']) ? clean($_REQUEST['eFavStore']) : 'No'; // No=> 'Not 
    if (isset($eFavStore) && !empty($eFavStore) && $_REQUEST['eFavStore'] == 'Yes') {
        return $query = "SELECT * fROM($query) AS company where eFavStore='" . $eFavStore . "'";
    } else {
        return '';
    }
}
function addUpdateFavStore() {
    global $_REQUEST, $obj;
    $eFavStore = isset($_REQUEST['eFavStore']) ? clean($_REQUEST['eFavStore']) : ''; // No=> 'Not Favorite','Yes'=> 'Favorite'
    if (isset($eFavStore) && !empty($eFavStore)) {
        $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
        $iCompanyId = isset($_REQUEST['iCompanyId']) ? clean($_REQUEST['iCompanyId']) : '';
        $iServiceId = isset($_REQUEST['iServiceId']) ? clean($_REQUEST['iServiceId']) : '';
        $sql = "SELECT iStoreFavorite FROM `store_favorites` WHERE iUserId = '" . $iUserId . "' AND iCompanyId = '" . $iCompanyId . "' AND iServiceId = " . $iServiceId . "";
        $iStoreFavoriteData = $obj->MySQLSelect($sql);
        $tableName1 = 'store_favorites';
        if (count($iStoreFavoriteData) > 0) {
            $iStoreFavorite = $iStoreFavoriteData[0]['iStoreFavorite'];
        }
        $Data_update_store_favorites['iUserId'] = $iUserId;
        $Data_update_store_favorites['iCompanyId'] = $iCompanyId;
        $Data_update_store_favorites['eFavStore'] = $eFavStore;
        $Data_update_store_favorites['iServiceId'] = trim($iServiceId);
        if (count($iStoreFavoriteData) > 0) {
            $where1 = "iStoreFavorite='" . $iStoreFavorite . "'";
            $iStoreFavoriteDataId = $obj->MySQLQueryPerform($tableName1, $Data_update_store_favorites, 'update', $where1);
        } else {
            $iStoreFavoriteDataId = $obj->MySQLQueryPerform($tableName1, $Data_update_store_favorites, 'insert');
        }
    }
    if (!empty($iStoreFavoriteDataId)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "";
    }
    return $returnArr;
}
?>