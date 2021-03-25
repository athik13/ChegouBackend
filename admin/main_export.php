<?php

include_once('../common.php');

if (!isset($generalobjAdmin)) {

    require_once(TPATH_CLASS . "class.general_admin.php");

    $generalobjAdmin = new General_admin();

}

$baseURL = $tconfig["tsite_url"];

$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

//ini_set("display_errors", 1);

//error_reporting(E_ALL);

$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';

$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;

$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";

$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : "";

$select_cat = isset($_REQUEST['selectcategory']) ? $_REQUEST['selectcategory'] : "";

$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : "";

$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : "";

$type = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : '';

$ssql = "";

require('fpdf/fpdf.php');

require('TCPDF-master/tcpdf.php'); // Added By Hasmukh

$date = new DateTime();

$timestamp_filename = $date->getTimestamp();

$default_lang = $generalobj->get_default_lang();

function change_key($array, $old_key, $new_key) {

    if (!array_key_exists($old_key, $array))

        return $array;

    $keys = array_keys($array);

    $keys[array_search($old_key, $keys)] = $new_key;

    return array_combine($keys, $array);

}

function cleanData(&$str) {

    $str = preg_replace("/\t/", "\\t", $str);

    $str = preg_replace("/\r?\n/", "\\n", $str);

    if (strstr($str, '"'))

        $str = '"' . str_replace('"', '""', $str) . '"';

}

if($section == "map_api"){

	$checkedvalues = $_REQUEST['checkedvalues'];

	$DbName = TSITE_DB;

	$TableName = "auth_master_accounts_places";

	$TableName_Accounts = "auth_accounts_places";

	$TableName_usage_report = "auth_report_accounts_places";

	$siteUrl = $tconfig['tsite_url'];

	$data_drv['servicedata'] = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName);

	$data_drv['auth_accounts_places'] = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName_Accounts);

	$data_drv['usage_report'] = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName_usage_report);

	// $time = time();

	$date = date('d_m_Y_h_i_s');

	$file = 'map_api_export_'.$date.'.json';

	file_put_contents($file, json_encode($data_drv));

	header("Content-type: application/json");

	header('Content-Disposition: attachment; filename="'.basename($file).'"'); 

	header('Content-Length: ' . filesize($file));

	// echo json_encode($data_drv) ."\t";

	echo json_encode($data_drv, JSON_PRETTY_PRINT) ."\t";

}

if ($section == 'blocked_driver') {

    $cancel_for_hours = $CANCEL_DECLINE_TRIPS_IN_HOURS;

    $c_date = date("Y-m-d H:i:s");

    $s_date = date("Y-m-d H:i:s", strtotime('-' . $cancel_for_hours . ' hours'));

    $ord = ' ORDER BY  `Total Cancelled Trips (Till now)` DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY rd.vName ASC";

        else

            $ord = " ORDER BY rd.vName DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY rd.vEmail ASC";

        else

            $ord = " ORDER BY rd.vEmail DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` ASC";

        else

            $ord = " ORDER BY `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY `Total Declined Trips (In " . $cancel_for_hours . " hours)` ASC";

        else

            $ord = " ORDER BY `Total Declined Trips (In " . $cancel_for_hours . " hours)` DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY `Total Cancelled Trips (Till now)` ASC";

        else

            $ord = " ORDER BY `Total Cancelled Trips (Till now)` DESC";

    }

    if ($sortby == 6) {

        if ($order == 0)

            $ord = " ORDER BY `Total Declined Trips (Till now)` ASC";

        else

            $ord = " ORDER BY `Total Declined Trips (Till now)` DESC";

    }

    if ($sortby == 7) {

        if ($order == 0)

            $ord = " ORDER BY `eIsBlocked` ASC";

        else

            $ord = " ORDER BY `eIsBlocked` DESC";

    }

    if ($sortby == 8) {

        if ($order == 0)

            $ord = " ORDER BY tBlockeddate ASC";

        else

            $ord = " ORDER BY tBlockeddate DESC";

    }

    if ($keyword != '') {

        $keyword_new = $keyword;

        $chracters = array("(", "+", ")");

        $removespacekeyword = preg_replace('/\s+/', '', $keyword);

        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

        if (is_numeric($keyword_new)) {

            $keyword_new = $keyword_new;

        } else {

            $keyword_new = $keyword;

        }

        if ($option != '') {

            $option_new = $option;

            if ($option == 'DriverName') {

                $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";

            }

            if ($eIsBlocked != '') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }

        } else {

            if (ONLYDELIVERALL == 'Yes') {

                if ($eIsBlocked != '') {

                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";

                } else {

                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";

                }

            } else {

                if ($eIsBlocked != '') {

                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";

                } else {

                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";

                }

            }

        }

    } else if ($eIsBlocked != '' && $keyword == '') {

        $ssql .= " AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";

    }

    // End Search Parameters

    $ssql1 = "AND (rd.vEmail != '' OR rd.vPhone != '')";

    $sql = "SELECT  CONCAT(rd.vName,' ',rd.vLastName) AS Name, rd.vEmail as Email , COALESCE( m.cnt , 0 ) AS `Total Cancelled Trips (In " . $cancel_for_hours . " hours)`,  COALESCE( d.cnt, 0 ) AS `Total Declined Trips (In " . $cancel_for_hours . " hours)`,  COALESCE( mAll.cntAll, 0 ) AS   `Total Cancelled Trips (Till now)`, COALESCE( dAll.cntAll, 0 ) AS  `Total Declined Trips (Till now)` ,rd.eIsBlocked as `Block driver`,rd.tBlockeddate as `Block date` FROM  register_driver rd LEFT JOIN (SELECT  iDriverId,COUNT( tr.iTripId ) AS cnt,iActive,tEndDate FROM trips tr where tEndDate BETWEEN  '" . $s_date . "' AND  '" . $c_date . "'  AND  iActive =  'Canceled' AND eCancelledBy	='Driver' GROUP BY tr.iDriverId ) m ON rd.iDriverId = m.iDriverId LEFT JOIN (SELECT  iDriverId,COUNT( trAll.iTripId ) AS cntAll,iActive FROM trips trAll where  iActive =  'Canceled' AND eCancelledBy	='Driver' GROUP BY trAll.iDriverId ) mAll ON rd.iDriverId = mAll.iDriverId LEFT JOIN (SELECT  iDriverId,COUNT( dr.iDriverRequestId ) AS cnt,dAddedDate,eStatus FROM driver_request dr where  dr.dAddedDate BETWEEN  '" . $s_date . "'  AND  '" . $c_date . "'	AND dr.eStatus =  'Decline' GROUP BY  dr.iDriverId ) d ON rd.iDriverId = d.iDriverId LEFT JOIN (SELECT  iDriverId,COUNT( drAll.iDriverRequestId ) AS cntAll,dAddedDate,eStatus FROM driver_request drAll where  drAll.eStatus =  'Decline' GROUP BY  drAll.iDriverId ) dAll ON rd.iDriverId = dAll.iDriverId  where (mAll.cntAll >'0' $ssql $ssql1) OR  (dAll.cntAll >'0' $ssql $ssql1)  $ord";

    //ini_set("display_errors", 1);

    //error_reporting(E_ALL);

    $result = $obj->MySQLSelect($sql) or die('Query Failed!');

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {

            $result[0] = change_key($result[0], 'Total Drivers', 'Total Providers');

        }

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Name') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                if ($key == 'Total Cancelled Trips (In ' . $cancel_for_hours . ' hours)') {

                    $val = ($val);

                }

                if ($key == 'Total Declined Trips (In ' . $cancel_for_hours . ' hours)') {

                    $val = ($val);

                }

                if ($key == 'Total Cancelled Trips (Till now)') {

                    $val = ($val);

                }

                if ($key == 'Total Declined Trips (Till now)') {

                    $val = ($val);

                }

                if ($key == 'Block driver') {

                    $val = ($val);

                }

                if ($key == 'Block date') {

                    $val = $generalobjAdmin->DateTime($val, 'No');

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else if ($type == 'PDF') {

        //Added By HJ On 18-01-2019 For Solved Client Bug - 6720 Start

        $heading = array('Provider Name', 'Email', 'A', 'B', 'C', 'D', 'Block driver', 'Block Date');

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");

        //echo "<pre>";

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = "blocked_driver_" . $configPdf['pdfName'];

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Decline / Canceled Trip / Jobs Alert For Drivers");

        $pdf->Ln();

        $aTxt = 'A-Total Cancelled Trips (In ' . $cancel_for_hours . ' hours)';

        $bTxt = 'B-Total Declined Trips (In ' . $cancel_for_hours . ' hours)';

        $cTxt = 'C-Total Cancelled Trips (Till now)';

        $dTxt = 'D-Total Declined Trips (Till now)';

        $pdf->SetFont($language, 'b', 9);

        $pdf->Cell(100, 5, $aTxt);

        $pdf->Ln();

        $pdf->Cell(100, 5, $bTxt);

        $pdf->Ln();

        $pdf->Cell(100, 5, $cTxt);

        $pdf->Ln();

        $pdf->Cell(100, 5, $dTxt);

        $pdf->Ln();

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Provider Name' || $column_heading == 'Email') {

                $pdf->Cell(60, 10, $column_heading, 1);

            } else if ($column_heading == 'Block Date') {

                $pdf->Cell(40, 10, $column_heading, 1);

            } else if ($column_heading == 'Block driver') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else {

                $pdf->Cell(20, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Name') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Email') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Name' || $column == 'Email') {

                    $pdf->Cell(60, 10, $values, 1);

                } else if ($column == 'Block date') {

                    $pdf->Cell(40, 10, $values, 1);

                } else if ($column == 'Block driver') {

                    $pdf->Cell(25, 10, $values, 1);

                } else {

                    $pdf->Cell(20, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

        //Added By HJ On 18-01-2019 For Solved Client Bug - 6720 End

    }

}

if ($section == 'blocked_rider') {

    $cancel_for_hours = $CANCEL_DECLINE_TRIPS_IN_HOURS;

    $c_date = date("Y-m-d H:i:s");

    $s_date = date("Y-m-d H:i:s", strtotime('-' . $cancel_for_hours . ' hours'));

    $ord = ' ORDER BY `Total Cancelled Trips (Till now)` DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vName ASC";

        else

            $ord = " ORDER BY vName DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY rd.vEmail ASC";

        else

            $ord = " ORDER BY rd.vEmail DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` ASC";

        else

            $ord = " ORDER BY `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY `Total Cancelled Trips (Till now)` ASC";

        else

            $ord = " ORDER BY `Total Cancelled Trips (Till now)` DESC";

    }if ($sortby == 7) {

        if ($order == 0)

            $ord = " ORDER BY eIsBlocked ASC";

        else

            $ord = " ORDER BY eIsBlocked DESC";

    }if ($sortby == 8) {

        if ($order == 0)

            $ord = " ORDER BY tBlockeddate ASC";

        else

            $ord = " ORDER BY tBlockeddate DESC";

    }

    //End Sorting

    if ($keyword != '') {

        $keyword_new = $keyword;

        $chracters = array("(", "+", ")");

        $removespacekeyword = preg_replace('/\s+/', '', $keyword);

        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

        if (is_numeric($keyword_new)) {

            $keyword_new = $keyword_new;

        } else {

            $keyword_new = $keyword;

        }

        if ($option != '') {

            $option_new = $option;

            if ($option == 'RiderName') {

                $option_new = "CONCAT(vName,' ',vLastName)";

            }

            if ($eIsBlocked != '') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }

        } else {

            if ($eIsBlocked != '') {

                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";

            } else {

                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";

            }

        }

    } else if ($eIsBlocked != '' && $keyword == '') {

        $ssql .= " AND rd.eIsBlocked = '" . $generalobjAdmin->clean($eIsBlocked) . "'";

    }

    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

    $cmp_ssql = "";

    if ($eStatus != '') {

        $estatusquery = "";

    } else {

        $estatusquery = " AND eStatus != 'Deleted'";

    }

    $ssql1 = "AND (vEmail != '' OR vPhone != '')";

    $sql = "SELECT  CONCAT(rd.vName,' ',rd.vLastName) AS Name,rd.vEmail as Email,  COALESCE( m.cnt , 0 ) AS `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` ,  COALESCE( mAll.cnt , 0 ) AS `Total Cancelled Trips (Till now)`,rd.eIsBlocked as `Block Rider`,rd.tBlockeddate as `Block Date` FROM  register_user rd LEFT JOIN (SELECT  iUserId,COUNT( tr.iTripId ) AS cnt,iActive,tEndDate   FROM trips tr where tEndDate BETWEEN  '" . $s_date . "' AND  '" . $c_date . "'  AND   tr.iActive =  'Canceled' AND tr.eCancelledBy ='Passenger' GROUP BY tr.iUserId ) m ON rd.iUserId = m.iUserId LEFT JOIN (SELECT  iUserId,COUNT( trAll.iTripId ) AS cnt,iActive FROM trips trAll where trAll.iActive =  'Canceled' AND trAll.eCancelledBy ='Passenger' GROUP BY trAll.iUserId ) mAll ON rd.iUserId = mAll.iUserId where (mAll.cnt >'0') $ssql $ssql1 $ord";

    $result = $obj->MySQLSelect($sql) or die('Query Failed!');

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {

            $result[0] = change_key($result[0], 'Total Drivers', 'Total Providers');

        }

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Name') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                if ($key == 'Total Cancelled Trips (In ' . $cancel_for_hours . ' hours)') {

                    $val = ($val);

                }

                if ($key == 'Total Cancelled Trips (Till now)') {

                    $val = ($val);

                }

                if ($key == 'Block Rider') {

                    $val = ($val);

                }

                if ($key == 'Block Date') {

                    $val = $generalobjAdmin->DateTime($val, 'No');

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else if ($type == 'PDF') {

        //Added By HJ On 18-01-2019 For Solved Client Bug - 6720 Start

        $heading = array('User Name', 'Email', 'A', 'B', 'Block Driver', 'Block Date');

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        //echo "<pre>";

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = "blocked_rider_" . $configPdf['pdfName'];

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Cancelled Trip/Jobs Alert For ".$langage_lbl_admin['LBL_RIDERS_ADMIN']);

        $pdf->Ln();

        $aTxt = 'A-Total Cancelled Trips (In ' . $cancel_for_hours . ' hours)';

        $bTxt = 'B-Total Cancelled Trips (Till now)';

        $pdf->SetFont($language, 'b', 9);

        $pdf->Cell(100, 5, $aTxt);

        $pdf->Ln();

        $pdf->Cell(100, 5, $bTxt);

        $pdf->Ln();

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'User Name') {

                $pdf->Cell(50, 10, $column_heading, 1);

            } else if ($column_heading == 'Email') {

                $pdf->Cell(55, 10, $column_heading, 1);

            } else if ($column_heading == 'Block Date') {

                $pdf->Cell(40, 10, $column_heading, 1);

            } else if ($column_heading == 'Block Driver') {

                $pdf->Cell(23, 10, $column_heading, 1);

            } else {

                $pdf->Cell(15, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Name') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Email') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Name') {

                    $pdf->Cell(50, 10, $values, 1);

                } else if ($column == 'Email') {

                    $pdf->Cell(55, 10, $values, 1);

                } else if ($column == 'Block Date') {

                    $pdf->Cell(40, 10, $values, 1);

                } else if ($column == 'Block Rider') {

                    $pdf->Cell(23, 10, $values, 1);

                } else {

                    $pdf->Cell(15, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

        //Added By HJ On 18-01-2019 For Solved Client Bug - 6720 End

    }

}

if ($section == 'admin') {

    $query = Models\Administrator::with(['roles', 'locations']);

    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;

    $order = isset($_REQUEST['order']) && $_REQUEST['order'] == 1 ? 'ASC' : 'DESC';

    switch ($sortby) {

        case 1:

            $query->orderBy('vFirstName', $order);

            break;

        case 2:

            $query->orderBy('vEmail', $order);

            break;

        case 3:

            break;

        case 4:

            $query->orderBy('eStatus', $order);

            break;

        default:

            break;

    }

    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";

    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";

    if (!empty($keyword)) {

        if (!empty($option)) {

            if ($option == 'eStatus') {

                $query->where('eStatus', $keyword);

            }

        } else {

            $query->where(function($q) use ($keyword) {

                $q->where(DB::raw('concat(`vFirstName`," ",`vLastName`)'), "LIKE", "%{$keyword}%");

                $q->orWhere('vEmail', "LIKE", "%{$keyword}%");

                $q->orwhere('vContactNo', "LIKE", "%{$keyword}%");

                $q->orwhere('eStatus', "LIKE", "%{$keyword}%");

            });

        }

    }

    if (!$userObj->hasRole(1)) {

        $query->where('iGroupId', $userObj->role_id);

    }

    if ($option != 'eStatus') {

        $query->where('eStatus', '!=', "Deleted");

    }

    $start = 0;

    $data_drv = $query->get();

    //echo "<pre>";

    $result = array();

    foreach ($data_drv as $key => $row) {

        $data = array();

        $data['Name'] = $generalobjAdmin->clearName($row['vFirstName'] . ' ' . $row['vLastName']);

        $data['Email'] = $generalobjAdmin->clearEmail($row['vEmail']);

        $data['Admin Roles'] = $row->roles->vGroup;

        $data['Status'] = $row['eStatus'];

        $result[] = $data;

    }

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Name') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array('Name', 'Email', 'Admin Roles', 'Status');

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Admin ".$langage_lbl_admin['LBL_RIDERS_ADMIN']);

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Id') {

                $pdf->Cell(10, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Name') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Email') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Id') {

                    $pdf->Cell(10, 10, $values, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(25, 10, $values, 1);

                } else {

                    $pdf->Cell(45, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

if ($section == 'company') {

    $ord = ' ORDER BY c.iCompanyId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY c.vCompany ASC";

        else

            $ord = " ORDER BY c.vCompany DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY c.vEmail ASC";

        else

            $ord = " ORDER BY c.vEmail DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY c.eStatus ASC";

        else

            $ord = " ORDER BY c.eStatus DESC";

    }

    //End Sorting

    if ($keyword != '') {

        $keyword_new = $keyword;

        $chracters = array("(", "+", ")");

        $removespacekeyword = preg_replace('/\s+/', '', $keyword);

        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

        if (is_numeric($keyword_new)) {

            $keyword_new = $keyword_new;

        } else {

            $keyword_new = $keyword;

        }

        if ($option != '') {

            $option_new = $option;

            if ($option == 'MobileNumber') {

                $option_new = "CONCAT(c.vCode,'',c.vPhone)";

            }

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'))";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    $cmp_ssql = "";

    if ($eStatus != '') {

        $eStatus_sql = "";

    } else {

        $eStatus_sql = " AND c.eStatus != 'Deleted'";

    }

    $eSystem = " AND  c.eSystem ='General'";

    $sql = "SELECT c.vCompany AS Name, c.vEmail AS Email,(SELECT count(rd.iDriverId) FROM register_driver AS rd WHERE rd.iCompanyId=c.iCompanyId) AS `Total Drivers`, CONCAT(c.vCode,' ',c.vPhone) AS Mobile,c.eStatus AS Status FROM company AS c WHERE 1 = 1 $eSystem $eStatus_sql $ssql $cmp_ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query Failed!');

        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {

            $result[0] = change_key($result[0], 'Total Drivers', 'Total Providers');

        }

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                if ($key == 'Mobile') {

                    $val = $generalobjAdmin->clearPhone($val);

                }

                if ($key == 'Name') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {

            $heading = array('Name', 'Email', 'Total Providers', 'Mobile', 'Status');

        } else {

            $heading = array('Name', 'Email', 'Total Drivers', 'Mobile', 'Status');

        }

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Companies");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Total Drivers') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Total Providers') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Mobile') {

                $pdf->Cell(30, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else {

                $pdf->Cell(55, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Email') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Mobile') {

                    $values = $generalobjAdmin->clearPhone($key);

                }

                if ($column == 'Name') {

                    $values = $generalobjAdmin->clearCmpName($key);

                }

                if ($column == 'Total Drivers') {

                    $pdf->Cell(25, 10, $values, 1);

                } else if ($column == 'Total Providers') {

                    $pdf->Cell(25, 10, $values, 1);

                } else if ($column == 'Mobile') {

                    $pdf->Cell(30, 10, $values, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(25, 10, $values, 1);

                } else {

                    $pdf->Cell(55, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

if ($section == 'store') {

    $ord = ' ORDER BY c.iCompanyId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY c.vCompany ASC";

        else

            $ord = " ORDER BY c.vCompany DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY c.vEmail ASC";

        else

            $ord = " ORDER BY c.vEmail DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY c.eStatus ASC";

        else

            $ord = " ORDER BY c.eStatus DESC";

    }

    //End Sorting

    if ($keyword != '') {

        $keyword_new = $keyword;

        $chracters = array("(", "+", ")");

        $removespacekeyword = preg_replace('/\s+/', '', $keyword);

        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

        if (is_numeric($keyword_new)) {

            $keyword_new = $keyword_new;

        } else {

            $keyword_new = $keyword;

        }

        if ($option != '') {

            $option_new = $option;

            if ($option == 'MobileNumber') {

                $option_new = "CONCAT(c.vCode,'',c.vPhone)";

            }

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            }if ($select_cat != "") {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

            }if ($select_cat != "" && $eStatus != '') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

            } else {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }

        } else {

            if ($eStatus == '' && $select_cat != "" && $keyword_new != "") {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "'";

            } else if ($eStatus != '' && $select_cat != "") {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "'";

            } else if ($eStatus != '') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else if ($select_cat != "") {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "'";

            } else {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'))";

            }

        }

    } else if ($eStatus != '' && $select_cat != "" && $keyword == '') {

        $ssql .= " AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "'";

    } else if ($eStatus != '' && $keyword == '' && $select_cat == "") {

        $ssql .= " AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    } else if ($eStatus == '' && $keyword == '' && $select_cat != "") {

        $ssql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "'";

    }

    $cmp_ssql = "";

    if ($eStatus != '') {

        $eStatus_sql = "";

    } else {

        $eStatus_sql = " AND c.eStatus != 'Deleted'";

    }

    $eSystem = " AND  c.eSystem ='DeliverAll'";

    $ssql .= " AND sc.iServiceId IN(" . $enablesevicescategory . ")";

if(!checkSystemStoreSelection()) {

    $sql = "SELECT c.vCompany AS Name, c.vEmail AS Email,(SELECT count(iFoodMenuId) FROM food_menu WHERE iCompanyId = c.iCompanyId AND eStatus != 'Deleted') as `Item Categories`, CONCAT(c.vCode,' ',c.vPhone) AS Mobile, c.tRegistrationDate `Registration Date`,c.eStatus AS Status FROM company AS c left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE 1 = 1 and sc.eStatus='Active' $eSystem $eStatus_sql $ssql $cmp_ssql $ord";

} else {

     $sql = "SELECT c.vCompany AS Name, c.vEmail AS Email,(SELECT count(iFoodMenuId) FROM food_menu WHERE iCompanyId = c.iCompanyId AND eStatus != 'Deleted') as `Item Categories`, CONCAT(c.vCode,' ',c.vPhone) AS Mobile, c.tRegistrationDate `Registration Date`,c.eStatus AS Status FROM company AS c left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE 1 = 1 and sc.eStatus='Active' $eSystem $eStatus_sql $ssql $cmp_ssql GROUP BY sc.iServiceId  $ord";

}

   // echo $sql;die;

    //added by SP on 28-06-2019

    //$catdata = serviceCategories;

    //$service_cat_data = json_decode($catdata, true);

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query Failed!');

        //echo "<pre>";print_r($result);die;

        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {

            $result[0] = change_key($result[0], 'Total Drivers', 'Total Providers');

        }

        //$result[0] = change_key($result[0], 'iServiceId', 'Service Categories');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        //$result[0] = change_key($result[0], 'Service Categories', 'iServiceId');

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                if ($key == 'Mobile') {

                    $val = $generalobjAdmin->clearPhone($val);

                }

                if ($key == 'Name') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                if ($key == 'Registration Date') {

                    $val = $generalobjAdmin->DateTime($val);

                }

                //added by SP on 28-06-2019

                /*if ($key == 'iServiceId') {

                    if (count($service_cat_data) > 1) {

                        foreach ($service_cat_data as $servicedata) {

                            if ($servicedata['iServiceId'] == $val) {

                                $val = (isset($servicedata['vServiceName']) ? $servicedata['vServiceName'] : '');

                            }

                        }

                    }

                }*/

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array('Name', 'Email', 'Item Categories', 'Mobile', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Store");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Item Categories') {

                $pdf->Cell(30, 10, $column_heading, 1);

            } else if ($column_heading == 'Mobile') {

                $pdf->Cell(30, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else {

                $pdf->Cell(55, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Email') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Mobile') {

                    $values = $generalobjAdmin->clearPhone($key);

                }

                if ($column == 'Name') {

                    $values = $generalobjAdmin->clearCmpName($key);

                }

                if ($column == 'Item Categories') {

                    $pdf->Cell(30, 10, $values, 1);

                } else if ($column == 'Mobile') {

                    $pdf->Cell(30, 10, $values, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(25, 10, $values, 1);

                } else {

                    $pdf->Cell(55, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

if ($section == 'organization') {

    $ord = ' ORDER BY c.iOrganizationId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY c.vCompany ASC";

        else

            $ord = " ORDER BY c.vCompany DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY c.vEmail ASC";

        else

            $ord = " ORDER BY c.vEmail DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY c.eStatus ASC";

        else

            $ord = " ORDER BY c.eStatus DESC";

    }

    //End Sorting

    if ($keyword != '') {

        $keyword_new = $keyword;

        $chracters = array("(", "+", ")");

        $removespacekeyword = preg_replace('/\s+/', '', $keyword);

        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

        if (is_numeric($keyword_new)) {

            $keyword_new = $keyword_new;

        } else {

            $keyword_new = $keyword;

        }

        if ($option != '') {

            $option_new = $option;

            if ($option == 'MobileNumber') {

                $option_new = "CONCAT(c.vCode,'',c.vPhone)";

            }

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'))";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    $cmp_ssql = "";

    if ($eStatus != '') {

        $eStatus_sql = "";

    } else {

        $eStatus_sql = " AND c.eStatus != 'Deleted'";

    }

    $sql = "SELECT c.vCompany AS Name, c.vEmail AS Email, CONCAT(c.vCode,' ',c.vPhone) AS Mobile,c.eStatus AS Status,c.iUserProfileMasterId AS Type,c.ePaymentBy AS Payment FROM organization AS c WHERE 1 = 1 $eStatus_sql $ssql $cmp_ssql $ord";

    $orgTypeArr = array();

    $orgType_sql = "SELECT vProfileName,iUserProfileMasterId FROM user_profile_master ORDER BY iUserProfileMasterId ASC";

    $orgProfileData = $obj->MySQLSelect($orgType_sql);

    $default_lang = $_SESSION['sess_lang'];

    for ($p = 0; $p < count($orgProfileData); $p++) {

        $profileName = (array) json_decode($orgProfileData[$p]['vProfileName']);

        $orgTypeArr[$orgProfileData[$p]['iUserProfileMasterId']] = $profileName['vProfileName_' . $default_lang];

    }

    //echo "<pre>";

    //print_r($orgTypeArr);die;

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . "_organization.xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query Failed!');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Type') {

                    $orgType = "";

                    if (isset($orgTypeArr[$val])) {

                        $orgType = $orgTypeArr[$val];

                    }

                    $val = $orgType;

                }

                if ($key == 'Payment') {

                    $payByName = $val;

                    if ($val == "" || $val == "Passenger") {

                        $payByName = $langage_lbl_admin['LBL_RIDER'];

                    }

                    $val = "Pay By " . $payByName;

                }

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                if ($key == 'Mobile') {

                    $val = $generalobjAdmin->clearPhone($val);

                }

                if ($key == 'Name') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array('Name', 'Email', 'Mobile', 'Status', 'Type', 'Payment');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Organizations");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Mobile' || $column_heading == 'Type') {

                $pdf->Cell(55, 10, $column_heading, 1);

            } else if ($column_heading == 'Payment') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(30, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Type') {

                    $orgType = "";

                    if (isset($orgTypeArr[$key])) {

                        $orgType = $orgTypeArr[$key];

                    }

                    $values = $orgType;

                }

                if ($column == 'Payment') {

                    $payByName = $key;

                    if ($payByName == "") {

                        $payByName = $langage_lbl_admin['LBL_RIDER'];

                    }

                    $values = "Pay By " . $payByName;

                }

                if ($column == 'Email') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Mobile') {

                    $values = $generalobjAdmin->clearPhone($key);

                }

                if ($column == 'Name') {

                    $values = $generalobjAdmin->clearCmpName($key);

                }

                if ($column == 'Mobile' || $column == 'Type') {

                    $pdf->Cell(55, 10, $values, 1);

                } else if ($column == 'Payment') {

                    $pdf->Cell(45, 10, $values, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(30, 10, $values, 1);

                } else {

                    $pdf->Cell(45, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

if ($section == 'rider') {

    $ord = ' ORDER BY iUserId DESC';

    if ($sortby == 1) {

        if ($order == 0) 

            $ord = " ORDER BY vName ASC";

        else

            $ord = " ORDER BY vName DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vEmail ASC";

        else

            $ord = " ORDER BY vEmail DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY tRegistrationDate ASC";

        else

            $ord = " ORDER BY tRegistrationDate DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    $rdr_ssql = "";

    if (SITE_TYPE == 'Demo') {

        $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";

    }

    if ($keyword != '') {

        $keyword_new = $keyword;

        $chracters = array("(", "+", ")");

        $removespacekeyword = preg_replace('/\s+/', '', $keyword);

        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

        if (is_numeric($keyword_new)) {

            $keyword_new = $keyword_new;

        } else {

            $keyword_new = $keyword;

        }

        if ($option != '') {

            $option_new = $option;

            if ($option == 'RiderName') {

                $option_new = "CONCAT(vName,' ',vLastName)";

            }

            if ($option == 'MobileNumber') {

                $option_new = "CONCAT(vPhoneCode,'',vPhone)";

            }

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (CONCAT(vPhoneCode,'',vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (CONCAT(vPhoneCode,'',vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'))";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    $ssql1 = "AND (vEmail != '' OR vPhone != '') AND eHail='No'";

    $sql = "SELECT CONCAT(vName,' ',vLastName) as `User Name`,vEmail as Email,tRegistrationDate as `Signup Date`,CONCAT(vPhoneCode,' ',vPhone) AS Mobile,iUserId AS `Wallet Balance`,eStatus as Status FROM register_user WHERE 1=1 $eStatus_sql $ssql $ssql1 $rdr_ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        //echo "<pre>";

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            $user_available_balance = $generalobj->get_user_available_balance($value['Wallet Balance'], "Rider");

            $value['Wallet Balance'] = $generalobj->trip_currency($user_available_balance);

            foreach ($value as $key => $val) {

//                if ($key == "Signup Date") {

//                    $val = $generalobjAdmin->DateTime($val);

//                }

                if ($key == 'User Name') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                if ($key == 'Signup Date') {
                    $val = $generalobjAdmin->DateTime($val);
                }

                if ($key == 'Mobile') {

                    $val = $generalobjAdmin->clearPhone($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array('User Name', 'Email', 'Signup Date', 'Mobile', 'Wallet Balance', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $user_available_balance = $generalobj->get_user_available_balance($row['Wallet Balance'], "Rider");

            $row['Wallet Balance'] = $generalobj->trip_currency($user_available_balance);

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Riders");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Email') {

                $pdf->Cell(55, 10, $column_heading, 1);

            } else if ($column_heading == 'Mobile') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Signup Date') {

                $pdf->Cell(55, 10, $column_heading, 1);

            } else if ($column_heading == 'Wallet Balance') {

                $pdf->Cell(40, 10, $column_heading, 1);

            } else {

                $pdf->Cell(50, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'User Name') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Email') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Mobile') {

                    $values = $generalobjAdmin->clearPhone($key);

                }

                if ($column == "Signup Date") {

                    $values = $generalobjAdmin->DateTime($key);

                }

                if ($column == 'Email') {

                    $pdf->Cell(55, 10, $values, 1);

                } else if ($column == 'Mobile') {

                    $pdf->Cell(45, 10, $values, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(25, 10, $values, 1);

                } else if ($column == 'Signup Date') {

                    $pdf->Cell(55, 10, $values, 1);

                } else if ($column == 'Wallet Balance') {

                    $pdf->Cell(40, 10, $values, 1);

                } else {

                    $pdf->Cell(50, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//make 

if ($section == 'make') {

    $ord = ' ORDER BY vMake ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vMake ASC";

        else

            $ord = " ORDER BY vMake DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (vMake LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . ($keyword) . "%')";

        }

    }

    if ($option == "eStatus") {

        $eStatussql = " AND eStatus = '" . ($keyword) . "'";

    } else {

        $eStatussql = " AND eStatus != 'Deleted'";

    }

    $sql = "SELECT vMake as Make, eStatus as Status FROM make where 1=1 $eStatussql $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Make', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Make");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(70, 10, $column_heading, 1);

            } else {

                $pdf->Cell(80, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Status') {

                    $pdf->Cell(70, 10, $key, 1);

                } else {

                    $pdf->Cell(80, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//make

////////// Package Start //////////////

if ($section == 'package_type') {

    $ord = ' ORDER BY vName ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vName ASC";

        else

            $ord = " ORDER BY vName DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (vName LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . ($keyword) . "%')";

        }

    }

    if ($option == "eStatus") {

        $eStatussql = " AND eStatus = '" . ($keyword) . "'";

    } else {

        $eStatussql = " AND eStatus != 'Deleted'";

    }

    $sql = "SELECT vName as Name, eStatus as Status FROM package_type where 1=1 $eStatussql $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Name', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Package Type");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(70, 10, $column_heading, 1);

            } else {

                $pdf->Cell(80, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Status') {

                    $pdf->Cell(70, 10, $key, 1);

                } else {

                    $pdf->Cell(80, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

////////// Package End ////////////// 

//model

if ($section == 'model') {

    $ord = ' ORDER BY mo.vTitle ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY mo.vTitle ASC";

        else

            $ord = " ORDER BY mo.vTitle DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY mk.vMake ASC";

        else

            $ord = " ORDER BY mk.vMake DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY mo.eStatus ASC";

        else

            $ord = " ORDER BY mo.eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (mo.vTitle LIKE '%" . $keyword . "%' OR mo.eStatus LIKE '%" . $keyword . "%' OR mk.vMake LIKE '%" . $keyword . "%')";

        }

    }

    if ($option == "eStatus") {

        $eStatussql = " AND mo.eStatus = '" . ucfirst($keyword) . "'";

    } else {

        $eStatussql = " AND mo.eStatus != 'Deleted'";

    }

    $sql = "SELECT mo.vTitle AS Title, mk.vMake AS Make, mo.eStatus AS Status FROM model  AS mo LEFT JOIN make AS mk ON mk.iMakeId = mo.iMakeId WHERE 1=1 $eStatussql $ssql $ord";

    //die;

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Title', 'Make', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Model");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Id') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(60, 10, $column_heading, 1);

            } else {

                $pdf->Cell(70, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Id') {

                    $pdf->Cell(45, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(60, 10, $key, 1);

                } else {

                    $pdf->Cell(70, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//model

//country

if ($section == 'country') {

    $ord = ' ORDER BY vCountry ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vCountry ASC";

        else

            $ord = " ORDER BY vCountry DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vPhoneCode ASC";

        else

            $ord = " ORDER BY vPhoneCode DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY eUnit ASC";

        else

            $ord = " ORDER BY eUnit DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    //End Sorting

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (vCountry LIKE '%" . stripslashes($keyword) . "%' OR vPhoneCode LIKE '%" . stripslashes($keyword) . "%' OR vCountryCodeISO_3 LIKE '%" . stripslashes($keyword) . "%') AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND (vCountry LIKE '%" . stripslashes($keyword) . "%' OR vPhoneCode LIKE '%" . stripslashes($keyword) . "%' OR vCountryCodeISO_3 LIKE '%" . stripslashes($keyword) . "%')";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    if ($eStatus != '') {

        $eStatus_sql = "";

    } else {

        $eStatus_sql = " AND eStatus != 'Deleted'";

    }

    $sql = "SELECT vCountry as Country,vPhoneCode as PhoneCode, eUnit as Unit, eStatus as Status FROM country where 1 = 1 $eStatus_sql $ssql";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Country', 'PhoneCode', 'Unit', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Country");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(44, 10, $column_heading, 1);

            } else {

                $pdf->Cell(44, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Status') {

                    $pdf->Cell(44, 10, $key, 1);

                } else {

                    $pdf->Cell(44, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//State

if ($section == 'state') {

    $ord = ' ORDER BY s.vState ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY c.vCountry ASC";

        else

            $ord = " ORDER BY c.vCountry DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY s.vState ASC";

        else

            $ord = " ORDER BY s.vState DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY s.vStateCode ASC";

        else

            $ord = " ORDER BY s.vStateCode DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY s.eStatus ASC";

        else

            $ord = " ORDER BY s.eStatus DESC";

    }

    //End Sorting

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 's.eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR s.vState LIKE '%" . $keyword . "%' OR s.vStateCode LIKE '%" . $keyword . "%' OR s.eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $sql = "SELECT s.vState AS State,s.vStateCode AS `State Code`,c.vCountry AS Country,s.eStatus as Status FROM state AS s INNER JOIN country AS c ON c.iCountryId = s.iCountryId WHERE s.eStatus !=  'Deleted' $ssql $ord";

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('State', 'State Code', 'Country', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "State");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(40, 10, $column_heading, 1);

            } else {

                $pdf->Cell(40, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Status') {

                    $pdf->Cell(40, 10, $key, 1);

                } else {

                    $pdf->Cell(40, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//State

if ($section == 'city') {

    $ord = ' ORDER BY vCity ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY st.vState ASC";

        else

            $ord = " ORDER BY st.vState DESC";

    }

    if ($sortby == 2) {

        if($order == 0)

            $ord = " ORDER BY ct.vCity ASC";

        else

            $ord = " ORDER BY ct.vCity DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY c.vCountry ASC";

        else

            $ord = " ORDER BY c.vCountry DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY ct.eStatus ASC";

        else

            $ord = " ORDER BY ct.eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (ct.vCity LIKE '%" . $keyword . "%' OR st.vState LIKE '%" . $keyword . "%' OR c.vCountry LIKE '%" . $keyword . "%' OR ct.eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $sql = "SELECT ct.vCity AS City,st.vState AS State,c.vCountry AS Country, ct.eStatus AS Status FROM city AS ct INNER JOIN country AS c ON c.iCountryId =ct.iCountryId INNER JOIN state AS st ON st.iStateId=ct.iStateId WHERE  ct.eStatus != 'Deleted' $ssql $ord";

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('City', 'State', 'Country', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "City");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else {

                $pdf->Cell(35, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Status') {

                    $pdf->Cell(35, 10, $key, 1);

                } else {

                    $pdf->Cell(35, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//city

//faq

if ($section == 'faq') {

    $ord = ' ORDER BY f.vTitle_' . $default_lang . ' ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY f.vTitle_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY f.vTitle_" . $default_lang . " DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY fc.vTitle ASC";

        else

            $ord = " ORDER BY fc.vTitle DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY f.iDisplayOrder ASC";

        else

            $ord = " ORDER BY f.iDisplayOrder DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY f.eStatus ASC";

        else

            $ord = " ORDER BY f.eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (f.vTitle_" . $default_lang . " LIKE '%" . $keyword . "%' OR fc.vTitle LIKE '%" . $keyword . "%' OR f.iDisplayOrder LIKE '%" . $keyword . "%' OR f.eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $tbl_name = 'faqs';

    $sql = "SELECT f.vTitle_" . $default_lang . " as `Title`, fc.vTitle as `Category` ,f.iDisplayOrder as `DisplayOrder` ,f.eStatus  as `Status` FROM " . $tbl_name . " f, faq_categories fc WHERE f.iFaqcategoryId = fc.iUniqueId AND fc.vCode = '" . $default_lang . "' $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Title', 'Category', 'Order', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "FAQ");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Title') {

                $pdf->Cell(80, 10, $column_heading, 1);

            } else if ($column_heading == 'Category') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else if ($column_heading == 'Order') {

                $pdf->Cell(28, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(28, 10, $column_heading, 1);

            } else {

                $pdf->Cell(28, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Title') {

                    $pdf->Cell(80, 10, $key, 1);

                } else if ($column == 'Category') {

                    $pdf->Cell(45, 10, $key, 1);

                } else if ($column == 'Order') {

                    $pdf->Cell(28, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(28, 10, $key, 1);

                } else {

                    $pdf->Cell(28, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//faq

// help Detail

if ($section == 'help_detail') {

    $ord = ' ORDER BY f.vTitle_' . $default_lang . ' ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY f.vTitle_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY f.vTitle_" . $default_lang . " DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY fc.vTitle ASC";

        else

            $ord = " ORDER BY fc.vTitle DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY f.iDisplayOrder ASC";

        else

            $ord = " ORDER BY f.iDisplayOrder DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY f.eStatus ASC";

        else

            $ord = " ORDER BY f.eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (f.vTitle_" . $default_lang . " LIKE '%" . $keyword . "%' OR fc.vTitle LIKE '%" . $keyword . "%' OR f.iDisplayOrder LIKE '%" . $keyword . "%' OR f.eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $tbl_name = 'help_detail';

    $sql = "SELECT f.vTitle_" . $default_lang . " as `Title`, fc.vTitle as `Category` ,f.iDisplayOrder as `DisplayOrder` ,f.eStatus  as `Status` FROM " . $tbl_name . " f, help_detail_categories fc WHERE f.iHelpDetailCategoryId = fc.iUniqueId AND fc.vCode = '" . $default_lang . "' $ssql $ord";

    //die;

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Title', 'Category', 'Order', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        //print_r($result);die;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Help Detail");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Title') {

                $pdf->Cell(80, 10, $column_heading, 1);

            } else if ($column_heading == 'Category') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else if ($column_heading == 'Order') {

                $pdf->Cell(28, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(28, 10, $column_heading, 1);

            } else {

                $pdf->Cell(28, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Title') {

                    $pdf->Cell(80, 10, $key, 1);

                } else if ($column == 'Category') {

                    $pdf->Cell(45, 10, $key, 1);

                } else if ($column == 'Order') {

                    $pdf->Cell(28, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(28, 10, $key, 1);

                } else {

                    $pdf->Cell(28, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//help detail end

//faq category

if ($section == 'faq_category') {

    $ord = ' ORDER BY vTitle ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vImage ASC";

        else

            $ord = " ORDER BY vImage DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vTitle ASC";

        else

            $ord = " ORDER BY vTitle DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY iDisplayOrder ASC";

        else

            $ord = " ORDER BY iDisplayOrder DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (vTitle LIKE '%" . $keyword . "%' OR iDisplayOrder LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $sql = "SELECT vTitle as `Title`, iDisplayOrder as `Order`, eStatus as `Status` FROM faq_categories where vCode = '" . $default_lang . "' $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Title', 'Order', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "FAQ Category");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(44, 10, $column_heading, 1);

            } else {

                $pdf->Cell(44, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Status') {

                    $pdf->Cell(44, 10, $key, 1);

                } else {

                    $pdf->Cell(44, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//faq category

//Help Detail category

if ($section == 'help_detail_category') {

    $ord = ' ORDER BY vTitle ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vImage ASC";

        else

            $ord = " ORDER BY vImage DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vTitle ASC";

        else

            $ord = " ORDER BY vTitle DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY iDisplayOrder ASC";

        else

            $ord = " ORDER BY iDisplayOrder DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (vTitle LIKE '%" . $keyword . "%' OR iDisplayOrder LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $sql = "SELECT vTitle as `Title`, iDisplayOrder as `Order`, eStatus as `Status` FROM help_detail_categories where vCode = '" . $default_lang . "' $ssql $ord";

    // die;

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Title', 'Order', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Help Detail Category");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(44, 10, $column_heading, 1);

            } else {

                $pdf->Cell(60, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Status') {

                    $pdf->Cell(44, 10, $key, 1);

                } else {

                    $pdf->Cell(60, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//Help Detail category

//pages

if ($section == 'page') {

    $ord = ' ORDER BY vPageName ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vPageName ASC";

        else

            $ord = " ORDER BY vPageName DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vPageTitle_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY vPageTitle_" . $default_lang . " DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (vPageName LIKE '%" . $keyword . "%' OR vPageTitle_" . $default_lang . " LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $sql = "SELECT vPageName as `Name`, vPageTitle_" . $default_lang . " as `PageTitle` FROM pages where ipageId NOT IN('5','20','21','20') AND eStatus != 'Deleted' $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Name', 'PageTitle');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Pages");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Name') {

                $pdf->Cell(57, 10, $column_heading, 1);

            } else if ($column_heading == 'PageTitle') {

                $pdf->Cell(100, 10, $column_heading, 1);

            } else {

                $pdf->Cell(20, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Name') {

                    $pdf->Cell(57, 10, $key, 1);

                } else if ($column == 'PageTitle') {

                    $pdf->Cell(100, 10, $key, 1);

                } else {

                    $pdf->Cell(20, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//pages

//languages

if ($section == 'languages') {

    $checktext = isset($_REQUEST['checktext']) ? stripslashes($_REQUEST['checktext']) : "";

    $selectedlanguage = isset($_REQUEST['selectedlanguage']) ? stripslashes($_REQUEST['selectedlanguage']) : '';

    if (!empty($selectedlanguage)) {

        $tbl_name = 'language_label_' . $selectedlanguage;

    } else {

        $tbl_name = 'language_label';

    }

    $ord = ' ORDER BY vValue ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vLabel ASC";

        else

            $ord = " ORDER BY vLabel DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vValue ASC";

        else

            $ord = " ORDER BY vValue DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . addslashes($option) . " LIKE '" . addslashes($keyword) . "'";

            } else {

                if ($checktext == 'Yes' && $option == 'vValue') {

                    $ssql .= " AND " . addslashes($option) . " LIKE '" . addslashes($keyword) . "'";

                } else {

                    $ssql .= " AND " . addslashes($option) . " LIKE '%" . addslashes($keyword) . "%'";

                }

            }

        } else {

            $ssql .= " AND (vLabel  LIKE '%" . addslashes($keyword) . "%' OR vValue  LIKE '%" . addslashes($keyword) . "%') ";

        }

    }

    $sql = "SELECT vLabel as `Code`,vValue as `Value in English Language`  FROM " . $tbl_name . " WHERE vCode = '" . $default_lang . "' $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Code', 'Value in English Language');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Languages");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Code') {

                $pdf->Cell(88, 10, $column_heading, 1);

            } else {

                $pdf->Cell(185, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Code') {

                    $pdf->Cell(88, 10, $key, 1);

                } else {

                    $pdf->Cell(185, 10, $key, 1);

                    

                    /*$html = 'sadasdasd<br>dsdfsdfsdf<br> dfsdsdf dfdsfsdf fsad <br>sdfsdfdsf';

                    $pdf->writeHTML($html, true, 0, false, false);

                    /*$parts = str_split($key, 120);

                    $final = implode("<br>", $parts);

                    $strText = str_replace("\n", "<br>", $final);

                    $pdf->MultiCell(185, 10, $strText, 1, 'J', 0, 1, '', '', true, null, true);*/

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//language label other

if ($section == 'language_label_other') {

    $checktext = isset($_REQUEST['checktext']) ? stripslashes($_REQUEST['checktext']) : "";

    $ord = ' ORDER BY vValue ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vLabel ASC";

        else

            $ord = " ORDER BY vLabel DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vValue ASC";

        else

            $ord = " ORDER BY vValue DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                if ($checktext == 'Yes' && $option == 'vValue') {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

                } else {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

                }

            }

        } else {

            $ssql .= " AND (vLabel LIKE '%" . $keyword . "%' OR vValue LIKE '%" . $keyword . "%')";

        }

    }

    $tbl_name = 'language_label_other';

    $sql = "SELECT vLabel as `Code`,vValue as `Value in English Language`  FROM " . $tbl_name . " WHERE vCode = '" . $default_lang . "' $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Code', 'Value in English Language');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Admin Language Label");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(88, 10, $column_heading, 1);

            } else {

                $pdf->Cell(88, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Status') {

                    $pdf->Cell(88, 10, $key, 1);

                } else {

                    $pdf->Cell(88, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//language label other

//vehicle_type

if ($section == 'vehicle_type') {

    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : "";

    $eType = isset($_REQUEST['eType']) ? ($_REQUEST['eType']) : "";

    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

    $iLocationid = isset($_REQUEST['location']) ? stripslashes($_REQUEST['location']) : "";

    $ord = ' ORDER BY vt.vVehicleType_' . $default_lang . ' ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vt.fPricePerKM ASC";

        else

            $ord = " ORDER BY vt.fPricePerKM DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY vt.fPricePerMin ASC";

        else

            $ord = " ORDER BY vt.fPricePerMin DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY vt.iPersonSize ASC";

        else

            $ord = " ORDER BY vt.iPersonSize DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY vt.eStatus ASC";

        else

            $ord = " ORDER BY vt.eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                if ($iVehicleCategoryId != '') {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'  AND vt.eStatus = '" . $eStatus . "'";

                } else {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.eStatus = '" . $eStatus . "'";

                }

            } else {

                if ($iVehicleCategoryId != '') {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";

                } else {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

                }

            }

        } else {

            if ($eStatus != '') {

                if ($iVehicleCategoryId != '') {

                    $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $eStatus . "'";

                } else {

                    $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%') AND vt.eStatus = '" . $eStatus . "'";

                }

            } else {

                if ($iVehicleCategoryId != '') {

                    $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";

                } else {

                    $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%')";

                }

            }

        }

    } else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus != '') {

        $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    } else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus == '') {

        $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";

    } else if ($eType != '' && $keyword == '' && $eStatus != '') {

        $ssql .= " AND vt.eType = '" . $eType . "' AND vt.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    } else if ($eType != '' && $keyword == '' && $eStatus == '') {

        $ssql .= " AND vt.eType = '" . $eType . "'";

    } else if ($iLocationid != '' && $keyword == '' && $eStatus != '') {

        $ssql .= " AND vt.iLocationid = '" . $iLocationid . "' AND vt.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    } else if ($iLocationid != '' && $keyword == '' && $eStatus == '') {

        $ssql .= " AND vt.iLocationid = '" . $iLocationid . "'";

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND vt.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    if ($eStatus != '') {

        $eStatussql = "";

    } else {

        $eStatussql = " AND vt.eStatus != 'Deleted'";

    }

    if ($APP_TYPE == 'Delivery') {

        $Vehicle_type_name = 'Deliver';

    } else if ($APP_TYPE == 'Ride-Delivery-UberX') {

        $Vehicle_type_name = 'Ride-Delivery';

    } else {

        $Vehicle_type_name = $APP_TYPE;

    }

    if ($Vehicle_type_name == "Ride-Delivery") {

        if (empty($eType)) {

            $ssql .= "AND (vt.eType ='Ride' or vt.eType ='Deliver')";

        }

        $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vt.fPricePerKM as PricePer" . $DEFAULT_DISTANCE_UNIT . ",vt.fPricePerMin as PricePerMin,vt.iBaseFare as BaseFare,vt.fCommision as Commision,vt.iPersonSize as PersonSize,vt.eType as `Service Type`, vt.eStatus as Status, lm.vLocationName as location, vt.iLocationid as locationId from  vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid where 1=1  $eStatussql $ssql $ord";

    } else {

        if ($APP_TYPE == 'UberX') {

            $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vc.vCategory_" . $default_lang . " as Subcategory, vt.eStatus as Status, lm.vLocationName as location,vt.iLocationid as locationId from vehicle_type as vt  left join ".$sql_vehicle_category_table_name." as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId left join country as c ON c.iCountryId = vt.iCountryId left join state as st ON st.iStateId = vt.iStateId left join city as ct ON ct.iCityId = vt.iCityId left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "' $eStatussql $ssql $ord";

        } else if ($APP_TYPE == 'Ride-Delivery-UberX') {

            $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vt.fPricePerKM as PricePer" . $DEFAULT_DISTANCE_UNIT . ",vt.fPricePerMin as PricePerMin,vt.iBaseFare as BaseFare,vt.fCommision as Commision,vt.iPersonSize as PersonSize, vt.eStatus as Status ,lm.vLocationName as location,vt.iLocationid as locationId from vehicle_type as vt left join country as c ON c.iCountryId = vt.iCountryId left join state as st ON st.iStateId = vt.iStateId left join city as ct ON ct.iCityId = vt.iCityId left join location_master as lm ON lm.iLocationId = vt.iLocationid  where 1=1 $eStatussql $ssql $ord";

        } else {

            $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vt.fPricePerKM as PricePer" . $DEFAULT_DISTANCE_UNIT . ",vt.fPricePerMin as PricePerMin,vt.iBaseFare as BaseFare,vt.fCommision as Commision,vt.iPersonSize as PersonSize, vt.eStatus as Status, lm.vLocationName as location,vt.iLocationid as locationId  from  vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "'  $eStatussql $ssql $ord";

        }

    }

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        $data = array_keys($result[0]);

        $arr = array_diff($data, array("locationId"));

        echo implode("\t", $arr) . "\r\n";

        $i = 0;

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'locationId') {

                    $val = "";

                }

                if ($key == 'location' && $value['locationId'] == '-1') {

                    $val = "All Location";

                }

                echo $val . "\t";

            }

            echo "\r\n";

            $i++;

        }

    } else {

        if ($APP_TYPE == 'UberX') {

            $heading = array('Type', 'Subcategory', 'Location Name');

        } else {

            if ($Vehicle_type_name == "Ride-Delivery") {

                $heading = array('Type', 'PricePer' . $DEFAULT_DISTANCE_UNIT, 'PricePerMin', 'BaseFare', 'Commision', 'PersonSize', $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'], 'Status', 'Location Name');

            } else {

                $heading = array('Type', 'PricePer' . $DEFAULT_DISTANCE_UNIT, 'PricePerMin', 'BaseFare', 'Commision', 'PersonSize', 'Status', 'Location Name');

            }

        }

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Type' && $APP_TYPE == 'UberX') {

                $pdf->Cell(80, 10, $column_heading, 1);

            } else if ($column_heading == 'Type' && $APP_TYPE != 'UberX') {

                $pdf->Cell(30, 10, $column_heading, 1);

            } else if ($column_heading == $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']) {

                $pdf->Cell(22, 10, $column_heading, 1);

            } else if ($column_heading == 'PricePerKM') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else if ($column_heading == 'BaseFare') {

                $pdf->Cell(18, 10, $column_heading, 1);

            } else if ($column_heading == 'Commision') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else if ($column_heading == 'PersonSize') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(15, 10, $column_heading, 1);

            } else if ($column_heading == 'Location Name') {

                $pdf->Cell(26, 10, $column_heading, 1);

            } else if ($column_heading == 'Subcategory') {

                $pdf->Cell(50, 10, $column_heading, 1);

            } else {

                $pdf->Cell(26, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Type' && $APP_TYPE == 'UberX') {

                    $pdf->Cell(80, 10, $key, 1);

                } else if ($column == 'Type' && $APP_TYPE != 'UberX') {

                    $pdf->Cell(30, 10, $key, 1);

                } else if ($column == 'Service Type') {

                    $pdf->Cell(22, 10, $key, 1);

                } else if ($column == 'PricePerKM') {

                    $pdf->Cell(20, 10, $key, 1);

                } else if ($column == 'BaseFare') {

                    $pdf->Cell(18, 10, $key, 1);

                } else if ($column == 'Commision') {

                    $pdf->Cell(20, 10, $key, 1);

                } else if ($column == 'PersonSize') {

                    $pdf->Cell(20, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(15, 10, $key, 1);

                } else if ($column == 'location' && $row['locationId'] == "-1") {

                    $pdf->Cell(26, 10, 'All Location', 1);

                } else if ($column == 'locationId') {

                    $pdf->Cell(2, 10, '', 0);

                } else if ($column == 'Subcategory') {

                    $pdf->Cell(50, 10, $key, 1);

                } else {

                    $pdf->Cell(26, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//service_type

if ($section == 'service_type') {

    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : "";

    $eType = isset($_REQUEST['eType']) ? ($_REQUEST['eType']) : "";

    $eStatus = isset($_REQUEST['eStatus']) ? ($_REQUEST['eStatus']) : "";

    $ord = ' ORDER BY vt.iVehicleCategoryId ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY vt.eStatus ASC";

        else

            $ord = " ORDER BY vt.eStatus DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY vt.iDisplayOrder ASC";

        else

            $ord = " ORDER BY vt.iDisplayOrder DESC";

    }

    

if ($parent_ufx_catid > 0) {

    $getSubCat = $obj->MySQLSelect("SELECT GROUP_CONCAT(DISTINCT CONCAT('''',iVehicleCategoryId, '''')) SUB_CAT FROM ".$sql_vehicle_category_table_name." WHERE iParentId='" . $parent_ufx_catid . "'");

    if (count($getSubCat) > 0) {

        $ssql .= " AND vt.iVehicleCategoryId IN (" . $getSubCat[0]['SUB_CAT'] . ")";

    }

}

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $eStatus . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $eStatus . "'";

            } else {

                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";

            }

        }

    } else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus != '') {

        $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus='" . $eStatus . "'";

    } else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus == '') {

        $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";

    } else if ($iVehicleCategoryId == '' && $keyword == '' && $eStatus != '') {

        $ssql .= " AND vt.eStatus='" . $eStatus . "'";

    }

    // $Vehicle_type_name = ($APP_TYPE == 'Delivery')? 'Deliver':$APP_TYPE ;

    if ($APP_TYPE == 'Delivery') {

        $Vehicle_type_name = 'Deliver';

    } else if ($APP_TYPE == 'Ride-Delivery-UberX') {

        $Vehicle_type_name = 'UberX';

    } else {

        $Vehicle_type_name = $APP_TYPE;

    }

    if ($eStatus != '') {

        $eStatussql = "";

    } else {

        $eStatussql = " AND vt.eStatus != 'Deleted'";

    }

    $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vc.vCategory_" . $default_lang . " as Subcategory,vt.iDisplayOrder as `Display Order`,lm.vLocationName as location,vt.iLocationid as locationId from vehicle_type as vt  left join ".$sql_vehicle_category_table_name." as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId left join country as c ON c.iCountryId = vt.iCountryId left join state as st ON st.iStateId = vt.iStateId left join city as ct ON ct.iCityId = vt.iCityId left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "' $ssql $eStatussql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        $data = array_keys($result[0]);

        $arr = array_diff($data, array("locationId"));

        echo implode("\t", $arr) . "\r\n";

        $i = 0;

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'locationId') {

                    $val = "";

                }

                if ($key == 'location' && $value['locationId'] == '-1') {

                    $val = "All Location";

                }

                echo $val . "\t";

            }

            echo "\r\n";

            $i++;

        }

    } else {

        if ($Vehicle_type_name == 'UberX') {

            $heading = array('Type', 'Subcategory', 'Display Order', 'Location Name');

        }

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Service Type");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Type' && $Vehicle_type_name == 'UberX') {

                $pdf->Cell(80, 10, $column_heading, 1);

            } else if ($column_heading == 'Location Name') {

                $pdf->Cell(26, 10, $column_heading, 1);

            } else if ($column_heading == 'Subcategory') {

                $pdf->Cell(50, 10, $column_heading, 1);

            } else if ($column_heading == 'Display Order') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else {

                $pdf->Cell(26, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Type' && $Vehicle_type_name == 'UberX') {

                    $pdf->Cell(80, 10, $key, 1);

                } else if ($column == 'location' && $row['locationId'] == "-1") {

                    $pdf->Cell(26, 10, 'All Location', 1);

                } else if ($column == 'locationId') {

                    $pdf->Cell(2, 10, '', 0);

                } else if ($column == 'Subcategory') {

                    $pdf->Cell(50, 10, $key, 1);

                } else if ($column == 'Display Order') {

                    $pdf->Cell(25, 10, $key, 1);

                } else {

                    $pdf->Cell(26, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//service_type

//coupon

if ($section == 'coupon') {

    $ord = ' ORDER BY iCouponId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vCouponCode ASC";

        else

            $ord = " ORDER BY vCouponCode DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY dActiveDate ASC";

        else

            $ord = " ORDER BY dActiveDate DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY dExpiryDate ASC";

        else

            $ord = " ORDER BY dExpiryDate DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY eValidityType ASC";

        else

            $ord = " ORDER BY eValidityType DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($sortby == 6) {

        if ($order == 0)

            $ord = " ORDER BY iUsageLimit ASC";

        else

            $ord = " ORDER BY iUsageLimit DESC";

    }

    if ($sortby == 7) {

        if ($order == 0)

            $ord = " ORDER BY iUsed ASC";

        else

            $ord = " ORDER BY iUsed DESC";

    }

    if ($sortby == 9) {

        if ($order == 0)

            $ord = " ORDER BY vPromocodeType ASC";

        else

            $ord = " ORDER BY vPromocodeType DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (vCouponCode LIKE '%" . $keyword . "%'  OR eValidityType LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";

        }

    }

    

    //added by SP for date changes and estatus on 28-06-2019

    if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    } else if($eStatus != '') {

        $ssql .= " AND eStatus = '".$eStatus."'";

    } else {

        $ssql .= " AND eStatus != 'Deleted'";

    }

    

    $field = '';

    if (($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') && ONLYDELIVERALL == "No") {

        $field = ',eSystemType';

    } 

    

    $sql = "SELECT vCouponCode as `Gift Certificate`,fDiscount as `Discount`,eValidityType as `ValidityType`,vPromocodeType as `PromoCode Type`,

            CASE WHEN (DATE_FORMAT(dActiveDate,'%d/%m/%Y')='00/00/0000') THEN '-'

            ELSE DATE_FORMAT(dActiveDate,'%d/%m/%Y')

            END AS `Active Date`,

            CASE WHEN (DATE_FORMAT(dExpiryDate,'%d/%m/%Y')='00/00/0000') THEN '-'

            ELSE DATE_FORMAT(dExpiryDate,'%d/%m/%Y')

            END AS `ExpiryDate`,

            iUsageLimit as `Usage Limit`,iUsed as `Used`,iUsed as `UsedInScheduleBooking`,eStatus as `Status`$field FROM coupon WHERE 1 $ssql $ord";

    

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

 

        $getCouponDataArray = $obj->MySQLSelect($sql) or die('Query failed!');

        $couponArray = array();

        if(count($getCouponDataArray)> 0 && !empty($getCouponDataArray)) {

            for($i=0;$i<count($getCouponDataArray);$i++) {   

                    array_push($couponArray,$getCouponDataArray[$i]['Gift Certificate']);

            }

            $couponString = "'".implode("','", $couponArray)."'";

            $couponData = $generalobj->getUnUsedPromocode($couponString);

        }

        while ($row = mysqli_fetch_assoc($result)) {

             if (array_key_exists($row['Gift Certificate'], $couponData)) {

                 $row['UsedInScheduleBooking'] =  $couponData[$row['Gift Certificate']];

                                                                                     

             } else {

                $row['UsedInScheduleBooking'] = 0;                                                    

            }

            if (!$flag) {

                if(ONLYDELIVERALL == "Yes") {

                    unset($row['UsedInScheduleBooking']);

                }

            

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            if(ONLYDELIVERALL == "Yes") {

                    unset($row['UsedInScheduleBooking']);

                }

                

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Gift Certificate', 'Discount', 'ValidityType', 'PromoCode Type', 'Active Date', 'ExpiryDate', 'Usage Limit', 'Used', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Coupon");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Gift Certificate') {

                $pdf->Cell(42, 10, $column_heading, 1);

            } else if ($column_heading == 'Discount') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else if ($column_heading == 'Validity Type') {

                $pdf->Cell(26, 10, $column_heading, 1);

            } else if ($column_heading == 'PromoCode Type') {

                $pdf->Cell(26, 10, $column_heading, 1);

            } 

            else if ($column_heading == 'Active Date') {

                $pdf->Cell(28, 10, $column_heading, 1);

            } else if ($column_heading == 'ExpiryDate') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Usage Limit') {

                $pdf->Cell(24, 10, $column_heading, 1);

            } else if ($column_heading == 'Used') {

                $pdf->Cell(12, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(17, 10, $column_heading, 1);

            } else {

                $pdf->Cell(25, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            //echo "<pre>";

            $symbol = '$';

            if ($row['eType'] == 'percentage') {

                $symbol = '%';

            }

            unset($row['eType']);

            //if($result[])

            foreach ($row as $column => $key) {

                if ($column == 'Gift Certificate') {

                    $pdf->Cell(42, 10, $key, 1);

                } else if ($column == 'Discount') {

                    $key = $key . ' ' . $symbol;

                    $pdf->Cell(20, 10, $key, 1);

                } else if ($column == 'ValidityType') {

                    if ($key == 'Defined') {

                        $key = 'Custom';

                        $pdf->Cell(25, 10, $key, 1);

                    } else {

                        $pdf->Cell(25, 10, $key, 1);

                    }

                } else if ($column == 'PromoCode Type') {

                    $pdf->Cell(17, 10, $key, 1);

                } else if ($column == 'Active Date') {

                    $pdf->Cell(28, 10, $key, 1);

                } else if ($column == 'ExpiryDate') {

                    $pdf->Cell(25, 10, $key, 1);

                } else if ($column == 'Usage Limit') {

                    $pdf->Cell(24, 10, $key, 1);

                } else if ($column == 'Used') {

                    $pdf->Cell(12, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(17, 10, $key, 1);

                } else {

                    $pdf->Cell(25, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//coupon

//driver 

if ($section == 'driver') {

    $ord = ' ORDER BY rd.iDriverId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY rd.vName ASC";

        else

            $ord = " ORDER BY rd.vName DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY c.vCompany ASC";

        else

            $ord = " ORDER BY c.vCompany DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY rd.vEmail ASC";

        else

            $ord = " ORDER BY rd.vEmail DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY rd.tRegistrationDate ASC";

        else

            $ord = " ORDER BY rd.tRegistrationDate DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY rd.eStatus ASC";

        else

            $ord = " ORDER BY rd.eStatus DESC";

    }

    if ($keyword != '') {

        $keyword_new = $keyword;

        $chracters = array("(", "+", ")");

        $removespacekeyword = preg_replace('/\s+/', '', $keyword);

        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

        if (is_numeric($keyword_new)) {

            $keyword_new = $keyword_new;

        } else {

            $keyword_new = $keyword;

        }

        if ($option != '') {

            $option_new = $option;

            if ($option == 'MobileNumber') {

                $option_new = "CONCAT(rd.vCode,'',rd.vPhone)";

            }

            if ($option == 'DriverName') {

                $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";

            }

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND rd.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }

        } else {

            if (ONLYDELIVERALL == 'Yes') {

                if ($eStatus != '') {

                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND rd.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

                } else {

                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'))";

                }

            } else {

                if ($eStatus != '') {

                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND rd.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

                } else {

                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'))";

                }

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND rd.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    $dri_ssql = "";

    if (SITE_TYPE == 'Demo') {

        $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";

    }

    if ($eStatus != '') {

        $eStatus_sql = "";

    } else {

        $eStatus_sql = " AND rd.eStatus != 'Deleted'";

    }

    $IsFeaturedEnable = "No";

    if (ONLYDELIVERALL == 'No' && ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $ufxEnable == "Yes") {

         $IsFeaturedEnable = "Yes";

    }

    $ssql1 = "AND (rd.vEmail != '' OR rd.vPhone != '')";

    if (ONLYDELIVERALL == 'Yes') {

        $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS `Driver Name`,rd.vEmail as `Email`, rd.tRegistrationDate as `Signup Date`,CONCAT(rd.vCode,' ',rd.vPhone) as `Mobile`,rd.iDriverId AS `Wallet Balance`,rd.eStatus as `Status`,rd.eIsFeatured AS IsFeatured FROM register_driver rd  WHERE 1 = 1  $eStatus_sql $ssql $ssql1 $dri_ssql $ord";

    } else {

        $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS `Driver Name`,c.vCompany as `Company Name`,rd.vEmail as `Email`, rd.tRegistrationDate as `Signup Date`,CONCAT(rd.vCode,' ',rd.vPhone) as `Mobile`,rd.iDriverId AS `Wallet Balance`,rd.eStatus as `Status`,rd.eIsFeatured AS IsFeatured FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE 1 = 1  $eStatus_sql $ssql $ssql1 $dri_ssql $ord";

    }

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {

            $result[0] = change_key($result[0], 'Driver Name', 'Provider Name');

        }
		
		if (isStoreDriverAvailable()>0) {
			$result[0] = change_key($result[0], 'Company Name', 'Company/Store Name');
		}
        //echo "<pre>";

        if($IsFeaturedEnable == "No")

        {

            unset($result[0]['IsFeatured']);

        }

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            $user_available_balance = $generalobj->get_user_available_balance($value['Wallet Balance'], "Driver");

            $value['Wallet Balance'] = $generalobj->trip_currency($user_available_balance);

            foreach ($value as $key => $val) {

                

                if($key == "IsFeatured")

                {

                    unset($val);

                }

                if ($key == 'Driver Name') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                if ($key == 'Provider Name') {

                    //echo $val."<br>";

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                if ($key == 'Mobile') {

                    $val = $generalobjAdmin->clearPhone($val);

                }

                if ($key == 'Company Name') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        if (ONLYDELIVERALL == 'Yes') {

            $heading = array($langage_lbl_admin['LBL_DRIVER_NAME_EXPORT'], 'Email', 'Signup Date', 'Mobile', 'Wallet Balance', 'Status', 'IsFeatured');

        } else {

            $heading = array($langage_lbl_admin['LBL_DRIVER_NAME_EXPORT'], 'Company Name', 'Email', 'Signup Date', 'Mobile', 'Wallet Balance', 'Status', 'IsFeatured');

        }

        //echo "<pre>";

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $user_available_balance = $generalobj->get_user_available_balance($row['Wallet Balance'], "Driver");

            $row['Wallet Balance'] = $generalobj->trip_currency($user_available_balance);

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']);

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            //echo $column_heading;

            if ($column_heading == $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT']) {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else if ($column_heading == 'Company Name' || $column_heading == 'Wallet Balance') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else if ($column_heading == 'Email') {

                $pdf->Cell(50, 10, $column_heading, 1);

            } else if ($column_heading == 'Signup Date') {

                $pdf->Cell(37, 10, $column_heading, 1);

            } else if ($column_heading == 'Mobile') {

                $pdf->Cell(30, 10, $column_heading, 1);

            } else if ($column_heading == 'Status' || $column_heading == 'IsFeatured') {

                $pdf->Cell(22, 10, $column_heading, 1);

            } else {

                $pdf->Cell(20, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                //echo $column."<br>";

                if ($column == 'Driver Name') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Email') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Mobile') {

                    $values = $generalobjAdmin->clearPhone($key);

                }

                if ($column == 'Company Name') {

                    $values = $generalobjAdmin->clearCmpName($key);

                }

                if ($column == 'Driver Name') {

                    $pdf->Cell(35, 10, $values, 1, 0, "1");

                } else if ($column == 'Company Name' || $column == 'Wallet Balance') {

                    $pdf->Cell(35, 10, $values, 1);

                } else if ($column == 'Email') {

                    $pdf->Cell(50, 10, $values, 1);

                } else if ($column == 'Signup Date') {

                    $pdf->Cell(37, 10, $values, 1);

                } else if ($column == 'Mobile') {

                    $pdf->Cell(30, 10, $values, 1);

                } else if ($column == 'Status' || $column == 'IsFeatured') {

                    $pdf->Cell(22, 10, $values, 1);

                } else {

                    $pdf->Cell(20, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//driver

//vehicles 

if ($section == 'vehicles') {

    $eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";

    $ord = ' ORDER BY dv.iDriverVehicleId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY m.vMake ASC";

        else

            $ord = " ORDER BY m.vMake DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY c.vCompany ASC";

        else

            $ord = " ORDER BY c.vCompany DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY rd.vName ASC";

        else

            $ord = " ORDER BY rd.vName DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY dv.eType ASC";

        else

            $ord = " ORDER BY dv.eType DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY dv.eStatus ASC";

        else

            $ord = " ORDER BY dv.eStatus DESC";

    }

    //End Sorting

    $dri_ssql = "";

    if (SITE_TYPE == 'Demo') {

        $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";

    }

    // Start Search Parameters

    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";

    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";

    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : "";

    $ssql = '';

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND dv.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            if (ONLYDELIVERALL != 'Yes') {

                if ($eStatus != '') {

                    $ssql .= " AND (m.vMake LIKE '%" . $keyword . "%' OR c.vCompany LIKE '%" . $keyword . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . $keyword . "%')  AND dv.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

                } else {

                    $ssql .= " AND (m.vMake LIKE '%" . $keyword . "%' OR c.vCompany LIKE '%" . $keyword . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . $keyword . "%')";

                }

            } else {

                if ($eStatus != '') {

                    $ssql .= " AND (m.vMake LIKE '%" . $keyword . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . $keyword . "%')  AND dv.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

                } else {

                    $ssql .= " AND (m.vMake LIKE '%" . $keyword . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . $keyword . "%')";

                }

            }

        }

    } else if ($eStatus != '' && $keyword == '' && $eType == '') {

        $ssql .= " AND dv.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    } else if ($eType != '' && $keyword == '' && $eStatus == '') {

        $ssql .= " AND dv.eType = '" . $generalobjAdmin->clean($eType) . "'";

    } else if ($eType != '' && $keyword == '' && $eStatus != '') {

        $ssql .= " AND dv.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND dv.eType = '" . $generalobjAdmin->clean($eType) . "'";

    }

    // End Search Parameters

    if ($iDriverId != "") {

        $query1 = "SELECT COUNT(iDriverVehicleId) as total FROM driver_vehicle where iDriverId ='" . $iDriverId . "'";

        $totalData = $obj->MySQLSelect($query1);

        $total_vehicle = $totalData[0]['total'];

        if ($total_vehicle > 1) {

            $ssql .= " AND dv.iDriverId='" . $iDriverId . "'";

        }

    }

    //Pagination Start

    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

    if ($eStatus != '') {

        $eStatus_sql = "";

    } else {

        $eStatus_sql = " AND dv.eStatus != 'Deleted' AND dv.eType != 'UberX'";

    }

    if (ONLYDELIVERALL != 'Yes') {

        if ($APP_TYPE == 'UberX') {

            $sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total  FROM driver_vehicle AS dv, register_driver rd, make m, model md, company c WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId" . $eStatus_sql . $ssql . $dri_ssql;

        } else {

            $sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total FROM driver_vehicle AS dv, register_driver rd, make m, model md, company c WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId" . $eStatus_sql . $ssql . $dri_ssql;

        }

    } else {

        $sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total FROM driver_vehicle AS dv, register_driver rd, make m, model md WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId" . $eStatus_sql . $ssql . $dri_ssql;

    }

    $totalData = $obj->MySQLSelect($sql);

    $total_results = $totalData[0]['Total'];

    $total_pages = ceil($total_results / $per_page); //total pages we going to have

    $show_page = 1;

    //-------------if page is setcheck------------------//

    $start = 0;

    $end = $per_page;

    if (isset($_GET['page'])) {

        $show_page = $_GET['page'];             //it will telles the current page

        if ($show_page > 0 && $show_page <= $total_pages) {

            $start = ($show_page - 1) * $per_page;

            $end = $start + $per_page;

        }

    }

    // display pagination

    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;

    $tpages = $total_pages;

    if ($page <= 0)

        $page = 1;

    //Pagination End

    if (ONLYDELIVERALL != 'Yes') {

        if ($APP_TYPE == 'UberX') {

            $sql = "SELECT dv.iDriverVehicleId,dv.eStatus,CONCAT(rd.vName,' ',rd.vLastName) AS driverName,dv.vLicencePlate, c.vCompany FROM driver_vehicle dv, register_driver rd,company c

        WHERE 1 = 1   AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId $eStatus_sql $ssql $dri_ssql";

        } else {

            if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {

                $sql = "SELECT  CONCAT(m.vMake,' ', md.vTitle) AS Taxis, c.vCompany AS Company, CONCAT(rd.vName,' ',rd.vLastName) AS Driver,dv.eStatus as Status FROM driver_vehicle dv, register_driver rd, make m, model md, company c WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId $eStatus_sql $ssql $dri_ssql $ord ";

            } else {

                $sql = "SELECT  CONCAT(m.vMake,' ', md.vTitle) AS Taxis, c.vCompany AS Company, CONCAT(rd.vName,' ',rd.vLastName) AS Driver ,dv.eStatus as Status FROM driver_vehicle dv, register_driver rd, make m, model md, company c WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId $eStatus_sql $ssql $dri_ssql $ord ";

            }

        }

    } else {

        $sql = "SELECT  CONCAT(m.vMake,' ', md.vTitle) AS Taxis, CONCAT(rd.vName,' ',rd.vLastName) AS Driver ,dv.eStatus as Status FROM driver_vehicle dv, register_driver rd, make m, model md WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId $eStatus_sql $ssql $dri_ssql $ord ";

    }

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {

            $result[0] = change_key($result[0], 'Driver', 'Provider');

        }

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Taxis') {

                    $val;

                }

                if ($key == 'Company') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                if ($key == 'Driver') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'Status') {

                    $val;

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        if (ONLYDELIVERALL == 'Yes') {

            $heading = array('Taxis', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], 'Status');

        } else if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {

            $heading = array('Taxis', 'Company', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], 'Status');

        } else {

            $heading = array('Taxis', 'Company', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], 'Status');

        }

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Taxis");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Taxis') {

                $pdf->Cell(65, 10, $column_heading, 1);

            } else if ($column_heading == 'Company') {

                $pdf->Cell(40, 10, $column_heading, 1);

            } else if ($column_heading == $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) {

                $pdf->Cell(40, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Taxis') {

                    $pdf->Cell(65, 10, $key, 1);

                } else if ($column == 'Company') {

                    $pdf->Cell(40, 10, $generalobjAdmin->clearCmpName($key), 1);

                } else if ($column == 'Driver') {

                    $pdf->Cell(40, 10, $generalobjAdmin->clearName($key), 1); //}

                } /* else if ($column == 'Sevice Type') {

                  $pdf->Cell(25, 10, $key, 1);

                  } */ else if ($column == 'Status') {

                    $pdf->Cell(25, 10, $key, 1);

                } else {

                    $pdf->Cell(45, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//vehicles

//email_template

if ($section == 'email_template') {

    $ord = ' ORDER BY vSubject_' . $default_lang . ' ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vSubject_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY vSubject_" . $default_lang . " DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vPurpose ASC";

        else

            $ord = " ORDER BY vPurpose DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (vSubject_" . $default_lang . " LIKE '%" . $keyword . "%' OR vPurpose LIKE '%" . $keyword . "%')";

        }

    }

    $default_lang = $generalobj->get_default_lang();

    $tbl_name = 'email_templates';

    $sql = "SELECT vSubject_" . $default_lang . " as `Email Subject`, vPurpose as `Purpose` FROM " . $tbl_name . " WHERE eStatus = 'Active' $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Email Subject', 'Purpose');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Email Templates");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Email Subject') {

                $pdf->Cell(98, 10, $column_heading, 1);

            } else if ($column_heading == 'Purpose') {

                $pdf->Cell(98, 10, $column_heading, 1);

            } else {

                $pdf->Cell(8, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Email Subject') {

                    $pdf->Cell(98, 10, $key, 1);

                } else if ($column == 'Purpose') {

                    $pdf->Cell(98, 10, $key, 1);

                } else {

                    $pdf->Cell(8, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//email_template

//Restricted Area

if ($section == 'restrict_area') {

    $ord = ' ORDER BY lm.vLocationName ASC';

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY lm.vLocationName ASC";

        else

            $ord = " ORDER BY lm.vLocationName DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY ra.eRestrictType ASC";

        else

            $ord = " ORDER BY ra.eRestrictType DESC";

    }

    if ($sortby == 6) {

        if ($order == 0)

            $ord = " ORDER BY ra.eStatus ASC";

        else

            $ord = " ORDER BY ra.eStatus DESC";

    }

    if ($sortby == 7) {

        if ($order == 0)

            $ord = " ORDER BY ra.eType ASC";

        else

            $ord = " ORDER BY ra.eType DESC";

    }

    //End Sorting

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'ra.eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($generalobjAdmin->clean($keyword)) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($generalobjAdmin->clean($keyword)) . "%'";

            }

        } else {

            $ssql .= " AND (lm.vLocationName LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR ra.eStatus LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR ra.eRestrictType LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR ra.eType LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

        }

    }

    $sql = "SELECT lm.vLocationName as Address, ra.eRestrictType AS Area, ra.eType AS Type, ra.eStatus AS Status FROM restricted_negative_area AS ra LEFT JOIN location_master AS lm ON lm.iLocationId=ra.iLocationId WHERE 1=1 $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Address', 'Area', 'Type', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Address");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Area') {

                $pdf->Cell(40, 10, $column_heading, 1);

            } else if ($column_heading == 'Address') {

                $pdf->Cell(80, 10, $column_heading, 1);

            } else {

                $pdf->Cell(40, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Area') {

                    $pdf->Cell(40, 10, $key, 1);

                } else if ($column == 'Address') {

                    $pdf->Cell(80, 10, $key, 1);

                } else {

                    $pdf->Cell(40, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//visit location 

if ($section == 'visitlocation') {

    $ord = ' ORDER BY iVisitId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY tDestLocationName ASC";

        else

            $ord = " ORDER BY tDestLocationName DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY tDestAddress ASC";

        else

            $ord = " ORDER BY tDestAddress DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (tDestLocationName LIKE '%" . $keyword . "%' OR tDestAddress LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $sql = "SELECT vSourceAddresss as SourceAddress, tDestAddress as DestAddress,eStatus as Status FROM visit_address where eStatus != 'Deleted' $ssql $ord";

    //die;

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

        $heading = array('SourceAddress', 'DestAddress', 'Status');

    } else {

        $heading = array('SourceAddress', 'DestAddress', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Visit Location");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'SourceAddress') {

                $pdf->Cell(75, 10, $column_heading, 1);

            } else if ($column_heading == 'DestAddress') {

                $pdf->Cell(75, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'SourceAddress') {

                    $pdf->Cell(75, 10, $generalobjAdmin->clearCmpName($key), 1);

                } else if ($column == 'DestAddress') {

                    $pdf->Cell(75, 10, $generalobjAdmin->clearName($key), 1); //}

                } else if ($column == 'Status') {

                    $pdf->Cell(25, 10, $key, 1);

                } else {

                    $pdf->Cell(45, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//hotel rider

if ($section == 'hotel_rider') {

    $ord = ' ORDER BY vName ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vName ASC";

        else

            $ord = " ORDER BY vName DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vEmail ASC";

        else

            $ord = " ORDER BY vEmail DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY tRegistrationDate ASC";

        else

            $ord = " ORDER BY tRegistrationDate DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    $rdr_ssql = "";

    if (SITE_TYPE == 'Demo') {

        $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (concat(vFirstName,' ',vLastName) LIKE '%" . $keyword . "%' OR vEmail LIKE '%" . $keyword . "%' OR vPhone LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $sql = "SELECT  CONCAT(vName,' ',vLastName) as Name,vEmail as Email,CONCAT(vPhoneCode,' ',vPhone) AS Mobile,eStatus as Status FROM hotel WHERE eStatus != 'Deleted' $ssql $rdr_ssql $ord";

    //die;

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Name') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                if ($key == 'Mobile') {

                    $val = $generalobjAdmin->clearPhone($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array('Name', 'Email', 'Mobile', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Hotel Riders");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Email') {

                $pdf->Cell(55, 10, $column_heading, 1);

            } else if ($column_heading == 'Mobile') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Name') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Email') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Mobile') {

                    $values = $generalobjAdmin->clearPhone($key);

                }

                if ($column == 'Email') {

                    $pdf->Cell(55, 10, $values, 1);

                } else if ($column == 'Mobile') {

                    $pdf->Cell(45, 10, $values, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(25, 10, $values, 1);

                } else {

                    $pdf->Cell(45, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

if ($section == 'sub_service_category') {

    global $tconfig;

    $sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : '';

    $ord = ' ORDER BY iDisplayOrder ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vCategory_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY vCategory_" . $default_lang . " DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY Servicetypes ASC";

        else

            $ord = " ORDER BY Servicetypes DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY iDisplayOrder ASC";

        else

            $ord = " ORDER BY iDisplayOrder DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'  AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (vCategory_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%') AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND (vCategory_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    if ($parent_ufx_catid != "0") {

        $sql = "SELECT vCategory_" . $default_lang . " as SubCategory, (SELECT vCategory_" . $default_lang . " FROM ".$sql_vehicle_category_table_name." WHERE iVehicleCategoryId='" . $sub_cid . "') as Category, (select count(iVehicleTypeId) from vehicle_type where vehicle_type.iVehicleCategoryId = ".$sql_vehicle_category_table_name.".iVehicleCategoryId) as `Service Types`, iDisplayOrder as `Display Order`, eStatus as Status FROM ".$sql_vehicle_category_table_name." WHERE (iParentId='" . $sub_cid . "' || iVehicleCategoryId='".$parent_ufx_catid."') AND  1 = 1 $ssql $ord";

    } else {

        $sql = "SELECT vCategory_" . $default_lang . " as SubCategory, (SELECT vCategory_" . $default_lang . " FROM ".$sql_vehicle_category_table_name." WHERE iVehicleCategoryId='" . $sub_cid . "') as Category,(select count(iVehicleTypeId) from vehicle_type where vehicle_type.iVehicleCategoryId = ".$sql_vehicle_category_table_name.".iVehicleCategoryId) as `Service Types`, iDisplayOrder as `Display Order`,eStatus as Status FROM ".$sql_vehicle_category_table_name." WHERE (iParentId='" . $sub_cid . "' || iVehicleCategoryId='".$parent_ufx_catid."') $ssql $ord";

    }

    // filename for download

    if ($type == 'XLS') {

        $filename = $section . "_" . date('Ymd') . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        // echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'SubCategory') {

                    $val = $generalobjAdmin->clearName($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array('SubCategory', 'Category', 'Service Types', 'Display Order', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Sub Category");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Service Types') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Display Order') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                $id = "";

                if ($column == 'iVehicleCategoryId') {

                    $id2 = $key;

                }

                if ($column == 'SubCategory') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Display Order') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Status') {

                    $pdf->Cell(25, 10, $values, 1);

                } else if ($column == 'Service Types') {

                    $pdf->Cell(25, 10, $values, 1);

                } else if ($column == 'Display Order') {

                    $pdf->Cell(20, 10, $values, 1);

                } else {

                    $pdf->Cell(45, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

if ($section == 'service_category') {

    global $tconfig;

    $sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : '';

    $ord = ' ORDER BY iDisplayOrder ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vc.vCategory_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY vc.vCategory_" . $default_lang . " DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vc.eStatus ASC";

        else

            $ord = " ORDER BY vc.eStatus DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY SubCategories ASC";

        else

            $ord = " ORDER BY SubCategories DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY iDisplayOrder ASC";

        else

            $ord = " ORDER BY iDisplayOrder DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vc.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND vc.(vCategory_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%') AND vc.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND vc.(vCategory_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND vc.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    $sql = "SELECT vc.vCategory_" . $default_lang . " as Category ,(select count(iVehicleCategoryId) from ".$sql_vehicle_category_table_name." where iParentId=vc.iVehicleCategoryId) as SubCategories,vc.iDisplayOrder as `Display Order`,vc.eStatus as Status FROM ".$sql_vehicle_category_table_name." as vc WHERE  vc.iParentId='0' $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Category') {

                    $val = $generalobjAdmin->clearName($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array('Category', 'SubCategories', 'Display Order', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Category");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Category') {

                $pdf->Cell(55, 10, $column_heading, 1);

            } else if ($column_heading == 'Total') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else if ($column_heading == 'Display Order') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Category') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Total') {

                    $values = $key;

                }

                if ($column == 'Category') {

                    $pdf->Cell(55, 10, $values, 1);

                } else if ($column == 'Total') {

                    $pdf->Cell(45, 10, $values, 1);

                } else if ($column == 'Display Order') {

                    $pdf->Cell(45, 10, $values, 1);

                } else {

                    $pdf->Cell(45, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//mask_number

if ($section == 'mask_number') {

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND (mask_number LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";

        }

    }

    $sql = "SELECT masknum_id as `Id`, mask_number as `Masking Number`,adding_date as `Added Date`, eStatus as `Status` FROM masking_numbers where 1 = 1 $ssql";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        // echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Id', 'Masking Number', 'Added Date', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $$pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Masking Numbers");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Id') {

                $pdf->Cell(18, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(55, 10, $column_heading, 1);

            } else {

                $pdf->Cell(55, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Id') {

                    $pdf->Cell(18, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(55, 10, $key, 1);

                } else {

                    $pdf->Cell(55, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//mask_number

//document master

//driver 

if ($section == 'Document_Master') {

    $eType_value = isset($_REQUEST['eType_value']) ? stripslashes($_REQUEST['eType_value']) : "";

	$doc_userTypeValue = isset($_REQUEST['doc_userTypeValue']) ? stripslashes($_REQUEST['doc_userTypeValue']) : "";

    $ord = ' ORDER BY dm.doc_name ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY c.vCountry ASC";

        else

            $ord = " ORDER BY c.vCountry DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY dm.doc_usertype ASC";

        else

            $ord = " ORDER BY dm.doc_usertype DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY dm.doc_name ASC";

        else

            $ord = " ORDER BY dm.doc_name DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY dm.status ASC";

        else

            $ord = " ORDER BY dm.status DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY dm.eType ASC";

        else

            $ord = " ORDER BY dm.eType DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if ($eType_value != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND dm.eType = '" . $generalobjAdmin->clean($eType_value) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

			if ($doc_userTypeValue != '') {

				$ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND dm.doc_usertype = '" . $generalobjAdmin->clean($doc_userTypeValue) . "'";

			} else {

				$ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

			}

        } else {

            if ($eType_value != '') {

                $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_usertype LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%') AND dm.eType = '" . $generalobjAdmin->clean($eType_value) . "'";

            } else {

                $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_usertype LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%')";

            }

			if ($doc_userTypeValue != '') {

            $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%') AND dm.eType = '" . $generalobjAdmin->clean($eType_value) . "' AND dm.doc_usertype = '" . $generalobjAdmin->clean($doc_userTypeValue) . "'";

        } else {

            $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_usertype LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%')";

        }

        }

    } else if ($eType_value != '' && $keyword == '') {

        $ssql .= " AND dm.eType = '" . $generalobjAdmin->clean($eType_value) . "'";

    }else if ($doc_userTypeValue != '' && $keyword == '') {

		$ssql .= " AND dm.doc_usertype = '" . $generalobjAdmin->clean($doc_userTypeValue) . "'";

	}

    if ($eType_value != '') {

        $ssql .= " AND dm.doc_usertype != 'company'";

    }

    if ($option == "dm.status") {

        $eStatussql = " AND dm.status = '$keyword'";

    } else {

        $eStatussql = " AND dm.status != 'Deleted'";

    }

    $dri_ssql = "";

    if (SITE_TYPE == 'Demo') {

        $dri_ssql = " And dm.doc_instime > '" . WEEK_DATE . "'";

    }

    /*if ($APP_TYPE == 'Ride-Delivery') {

        $eTypeQuery = " AND (dm.eType='Ride' OR dm.eType='Delivery')";

    } else if ($APP_TYPE == 'Ride-Delivery-UberX') {

        $eTypeQuery = " AND (dm.eType='Ride' OR dm.eType='Delivery' OR dm.eType='UberX')";

    } else {

        $eTypeQuery = " AND dm.eType='" . $APP_TYPE . "'";

    }*/

    if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') {

        $sql = "SELECT if(c.vCountry IS NULL,'All',c.vCountry) as Country,dm.doc_name as `Document Name`,dm.doc_usertype as `Document For`, dm.status as Status FROM `document_master` AS dm

 LEFT JOIN `country` AS c ON c.vCountryCode=dm.country

 WHERE 1=1 $eStatussql $eTypeQuery $ssql $dri_ssql $ord";

    } else {

        $sql = "SELECT if(c.vCountry IS NULL,'All',c.vCountry) as Country,dm.doc_name as `Document Name`,dm.doc_usertype as `Document For`, dm.status as Status FROM `document_master` AS dm

  LEFT JOIN `country` AS c ON c.vCountryCode=dm.country

  WHERE 1=1 $eStatussql $eTypeQuery $ssql $dri_ssql $ord";

    }

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        // echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($val == 'UberX') {

                    $val = 'Other Services';

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') {

            $heading = array('Country', 'Document Name', 'Document For', 'Status');

        } else {

            $heading = array('Country', 'Document Name', 'Document For', 'Status');

        }

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Documents");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Country') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else if ($column_heading == 'Document For') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else if ($column_heading == 'Document Name') {

                $pdf->Cell(50, 10, $column_heading, 1);

            } else if ($column_heading == 'Service Type') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else {

                $pdf->Cell(20, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Country') {

                    $pdf->Cell(35, 10, $values, 1);

                } else if ($column == 'Document For') {

                    $pdf->Cell(35, 10, $values, 1);

                } else if ($column == 'Document Name') {

                    $pdf->Cell(50, 10, $values, 1);

                } else if ($column == 'Service Type') {

                    if ($values == 'UberX') {

                        $values = 'Other Services';

                    }

                    $pdf->Cell(35, 10, $values, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(35, 10, $values, 1);

                } else {

                    $pdf->Cell(20, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//document master

// review page 

if ($section == 'review') {

    $reviewtype = isset($_REQUEST['reviewtype']) ? $_REQUEST['reviewtype'] : 'Driver';

    $adm_ssql = "";

    if (SITE_TYPE == 'Demo') {

        if($reviewtype == "Driver"){

            $adm_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";

        }else{

            $adm_ssql = " And ru.tRegistrationDate > '" . WEEK_DATE . "'";

        }

        

    }

    $type = (isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] != '') ? $_REQUEST['reviewtype'] : 'Driver';

    $reviewtype = $type;

//Start Sorting

$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;

$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$ord = ' ORDER BY iRatingId DESC';

if ($sortby == 1) {

    if ($order == 0)

        $ord = " ORDER BY t.vRideNo ASC";

    else

        $ord = " ORDER BY t.vRideNo DESC";

}

if ($sortby == 2) {

    if ($reviewtype == 'Driver') {

        if ($order == 0)

            $ord = " ORDER BY rd.vName ASC";

        else

            $ord = " ORDER BY rd.vName DESC";

    }

    else {

        if ($order == 0)

            $ord = " ORDER BY ru.vName ASC";

        else

            $ord = " ORDER BY ru.vName DESC";

    }

}

if ($sortby == 6) {

    if ($reviewtype == 'Driver') {

        if ($order == 0)

            $ord = " ORDER BY ru.vName ASC";

        else

            $ord = " ORDER BY ru.vName DESC";

    }

    else {

        if ($order == 0)

            $ord = " ORDER BY rd.vName ASC";

        else

            $ord = " ORDER BY rd.vName DESC";

    }

}

if ($sortby == 3) {

    if ($order == 0)

        $ord = " ORDER BY r.vRating1 ASC";

    else

        $ord = " ORDER BY r.vRating1 DESC";

}

if ($sortby == 4) {

    if ($order == 0)

        $ord = " ORDER BY r.tDate ASC";

    else

        $ord = " ORDER BY r.tDate DESC";

}

if ($sortby == 5) {

    if ($order == 0)

        $ord = " ORDER BY r.vMessage ASC";

    else

        $ord = " ORDER BY r.vMessage DESC";

}

//End Sorting

$adm_ssql = "";

if (SITE_TYPE == 'Demo') {

    $adm_ssql = " And ru.tRegistrationDate > '" . WEEK_DATE . "'";

}

// Start Search Parameters

$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";

$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";

$ssql = '';

if ($keyword != '') {

    if ($option != '') {

        if (strpos($option, 'r.eStatus') !== false) {

            $ssql .= " AND " . stripslashes($option) . " LIKE '" . $generalobjAdmin->clean($keyword) . "'";

        } else {

            $option_new = $option;

            if ($option == 'drivername') {

                $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";

            }

            if ($option == 'ridername') {

                $option_new = "CONCAT(ru.vName,' ',ru.vLastName)";

            }

            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%'";

        }

    } else {

        $ssql .= " AND (t.vRideNo LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR r.vRating1 LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

    }

}

// End Search Parameters

//Pagination Start

$chkusertype = "";

if ($type == "Driver") {

    $chkusertype = "Passenger";

} else {

    $chkusertype = "Driver";

}

$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$sql = "SELECT count(r.iRatingId) as Total FROM ratings_user_driver as r LEFT JOIN trips as t ON r.iTripId=t.iTripId LEFT JOIN register_driver as rd ON rd.iDriverId=t.iDriverId 	LEFT JOIN register_user as ru ON ru.iUserId=t.iUserId WHERE eUserType='" . $chkusertype . "' And ru.eStatus!='Deleted' AND t.eSystem = 'General' $ssql $adm_ssql";

$totalData = $obj->MySQLSelect($sql);

$total_results = $totalData[0]['Total'];

$total_pages = ceil($total_results / $per_page); //total pages we going to have

$show_page = 1;

//-------------if page is setcheck------------------//

if (isset($_GET['page'])) {

    $show_page = $_GET['page'];             //it will telles the current page

    if ($show_page > 0 && $show_page <= $total_pages) {

        $start = ($show_page - 1) * $per_page;

        $end = $start + $per_page;

    } else {

        // error - show first set of results

        $start = 0;

        $end = $per_page;

    }

} else {

    // if page isn't set, show first set of results

    $start = 0;

    $end = $per_page;

}

// display pagination

$page = isset($_GET['page']) ? intval($_GET['page']) : 0;

$tpages = $total_pages;

if ($page <= 0)

    $page = 1;

//Pagination End

$chkusertype = "";

if ($type == "Driver") {

    $chkusertype = "Passenger";

} else {

    $chkusertype = "Driver";

}

$sql = "SELECT t.vRideNo as RiderNumber,CONCAT(rd.vName,' ',rd.vLastName) as DriverName,CONCAT(ru.vName,' ',ru.vLastName) as RiderName,r.vRating1 as Rate,r.tDate as Date,r.vMessage as Comment FROM ratings_user_driver as r LEFT JOIN trips as t ON r.iTripId=t.iTripId LEFT JOIN register_driver as rd ON rd.iDriverId=t.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=t.iUserId 

WHERE 1=1 AND r.eUserType='" . $chkusertype . "' And ru.eStatus!='Deleted' AND t.eSystem = 'General' $ssql $adm_ssql $ord";

$type = 'XLS';

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        // echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'RiderNumber') {

                    $val = $val;

                }

                if ($key == 'DriverName') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'RiderName') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($reviewtype == "Driver") {

                    if ($key == 'DriverName') {

                        $val = $val;

                    }

                } else {

                    if ($key == 'RiderName') {

                        $val = $val;

                    }

                }

                if ($key == 'AverageRate') {

                    $val = $val;

                }

                if ($reviewtype == "Driver") {

                    if ($key == 'RiderName') {

                        $val = $val;

                    }

                } else {

                    if ($key == 'DriverName') {

                        $val = $val;

                    }

                }

                if ($key == 'Rate') {

                    $val = $val;

                }

                if ($key == 'Date') {

                    $val = $generalobjAdmin->DateTime($val);

                }

                if ($key == 'Comment') {

                    $val = $val;

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        if ($reviewtype == "Driver") {

            $heading = array('RiderNumber', 'DriverName', 'AverageRate', 'RiderName', 'Rate', 'Date', 'Comment');

        } else {

            $heading = array('RiderNumber', 'RiderName', 'AverageRate', 'DriverName', 'Rate', 'Date', 'Comment');

        }

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Review");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'RiderNumber') {

                $pdf->Cell(22, 10, $column_heading, 1);

            } else if ($column_heading == 'DriverName') {

                $pdf->Cell(40, 10, $column_heading, 1);

            } else if ($column_heading == 'AverageRate') {

                $pdf->Cell(21, 10, $column_heading, 1);

            } else if ($column_heading == 'RiderName') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Rate') {

                $pdf->Cell(10, 10, $column_heading, 1);

            } else if ($column_heading == 'Date') {

                $pdf->Cell(42, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'RiderNumber') {

                    $values = $generalobjAdmin->clearPhone($key);

                }

                if ($column == 'DriverName') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'RiderName') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Date') {

                    $values = $generalobjAdmin->DateTime($key);

                }

                $generalobjAdmin->DateTime($val);

                if ($column == 'RiderNumber') {

                    $pdf->Cell(22, 10, $values, 1);

                } else if ($column == 'DriverName') {

                    $pdf->Cell(40, 10, $values, 1);

                } else if ($column == 'AverageRate') {

                    $pdf->Cell(21, 10, $values, 1);

                } else if ($column == 'RiderName') {

                    $pdf->Cell(25, 10, $values, 1);

                } else if ($column == 'Rate') {

                    $pdf->Cell(10, 10, $values, 1);

                } else if ($column == 'Date') {

                    $pdf->Cell(42, 10, $values, 1);

                } else {

                    $pdf->Cell(45, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//sms_template

if ($section == 'sms_template') {

    $ord = " ORDER BY vEmail_Code ASC";

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vEmail_Code ASC";

        else

            $ord = " ORDER BY vEmail_Code DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY vSubject_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY vSubject_" . $default_lang . " DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND vEmail_Code LIKE '%" . $keyword . "%' OR vSubject_" . $default_lang . " LIKE '%" . $keyword . "%'";

        }

    }

    $default_lang = $generalobj->get_default_lang();

    $tbl_name = 'send_message_templates';

    $sql = "SELECT vSubject_" . $default_lang . " as `SMS Title`,vEmail_Code as `SMS Code` FROM " . $tbl_name . " WHERE eStatus = 'Active' $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        // echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('SMS Title', 'SMS Code');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "SMS Templates");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'SMS Title') {

                $pdf->Cell(82, 10, $column_heading, 1);

            } else if ($column_heading == 'SMS Code') {

                $pdf->Cell(82, 10, $column_heading, 1);

            } else {

                $pdf->Cell(82, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'SMS Title') {

                    $pdf->Cell(82, 10, $key, 1);

                } else if ($column == 'SMS Code') {

                    $pdf->Cell(82, 10, $key, 1);

                } else {

                    $pdf->Cell(82, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

// locationwise fare

if ($section == 'airportsurcharge_fare') {

    $ord = ' ORDER BY ls.iLocatioId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY ls.iLocationIds ASC";

        else

            $ord = " ORDER BY ls.iLocationIds DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY ls.fpickupsurchargefare ASC";

        else

            $ord = " ORDER BY ls.fpickupsurchargefare DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY ls.fdropoffsurchargefare ASC";

        else

            $ord = " ORDER BY ls.fdropoffsurchargefare DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY ls.eStatus ASC";

        else

            $ord = " ORDER BY ls.eStatus DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY vt.vVehicleType ASC";

        else

            $ord = " ORDER BY vt.vVehicleType DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND lm2.vLocationName LIKE '%" . $keyword . "%' OR ls.eStatus LIKE '%" . $keyword . "%' OR vt.vVehicleType LIKE '%" . $keyword . "%'";

        }

    }

    if ($option == "eStatus") {

        $eStatussql = " AND ls.eStatus = '" . ucfirst($keyword) . "'";

    } else {

        $eStatussql = " AND ls.eStatus != 'Deleted'";

    }

    $sql = "SELECT lm2.vLocationName as `Location Name`, ls.fpickupsurchargefare as `Pickup Surcharge Fare`,ls.fpickupsurchargefare as `Dropoff Surcharge Fare`,vt.vVehicleType  as `Vehicle Type`,ls.eStatus as `Status` FROM `airportsurcharge_fare` ls left join location_master lm2 on ls.iLocationIds = lm2.iLocationId left join vehicle_type as vt on vt.iVehicleTypeId=ls.iVehicleTypeId WHERE 1 = 1 $eStatussql $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Location Name', 'Pickup Surcharge Fare', 'Dropoff Surcharge Fare', 'Vehicle Type', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Airport surcharge Fare");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Location Name') {

                $pdf->Cell(65, 10, $column_heading, 1);

            } else if ($column_heading == 'Pickup Surcharge Fare') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else if ($column_heading == 'Dropoff Surcharge Fare') {

                $pdf->Cell(45, 10, $column_heading, 1);

            } else if ($column_heading == 'Vehicle Type') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else {

                $pdf->Cell(30, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Location Name') {

                    $pdf->Cell(65, 10, $key, 1);

                } else if ($column == 'Pickup Surcharge Fare') {

                    $pdf->Cell(45, 10, $key, 1);

                } else if ($column == 'Dropoff Surcharge Fare') {

                    $pdf->Cell(45, 10, $key, 1);

                } else if ($column == 'Vehicle Type') {

                    $pdf->Cell(25, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(20, 10, $key, 1);

                } else {

                    $pdf->Cell(30, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

// locationwise fare

if ($section == 'locationwise_fare') {

    $ord = ' ORDER BY ls.iLocatioId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY lm1.vLocationName ASC";

        else

            $ord = " ORDER BY lm1.vLocationName DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY lm2.vLocationName ASC";

        else

            $ord = " ORDER BY lm2.vLocationName DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY ls.fFlatfare ASC";

        else

            $ord = " ORDER BY ls.fFlatfare DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY ls.eStatus ASC";

        else

            $ord = " ORDER BY ls.eStatus DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY vt.vVehicleType ASC";

        else

            $ord = " ORDER BY vt.vVehicleType DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            $ssql .= " AND lm1.vLocationName LIKE '%" . $keyword . "%' OR lm2.vLocationName LIKE '%" . $keyword . "%' OR ls.fFlatfare LIKE '%" . $keyword . "%' OR ls.eStatus LIKE '%" . $keyword . "%' OR vt.vVehicleType LIKE '%" . $keyword . "%'";

        }

    }

    if ($option == "eStatus") {

        $eStatussql = " AND ls.eStatus = '" . ucfirst($keyword) . "'";

    } else {

        $eStatussql = " AND ls.eStatus != 'Deleted'";

    }

    $sql = "SELECT lm2.vLocationName as `Source LocationName`,lm1.vLocationName as `Destination LocationName`,ls.fFlatfare as `Flat Fare`,vt.vVehicleType as `Vehicle Type`,ls.eStatus as `Status` FROM `location_wise_fare` ls left join location_master lm1 on ls.iToLocationId = lm1.iLocationId left join location_master lm2 on ls.iFromLocationId = lm2.iLocationId left join vehicle_type as vt on vt.iVehicleTypeId=ls.iVehicleTypeId  WHERE 1 = 1 $eStatussql $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Source LocationName', 'Destination LocationName', 'Flat Fare', 'Vehicle Type', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Locationwise Fare");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Source LocationName') {

                $pdf->Cell(65, 10, $column_heading, 1);

            } else if ($column_heading == 'Destination LocationName') {

                $pdf->Cell(65, 10, $column_heading, 1);

            } else if ($column_heading == 'Flat Fare') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else {

                $pdf->Cell(30, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Source LocationName') {

                    $pdf->Cell(65, 10, $key, 1);

                } else if ($column == 'Destination LocationName') {

                    $pdf->Cell(65, 10, $key, 1);

                } else if ($column == 'Flat Fare') {

                    $pdf->Cell(20, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(20, 10, $key, 1);

                } else {

                    $pdf->Cell(30, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

// locationwise fare

//FoodMenu 

if ($section == 'FoodMenu') {

    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

    $ord = ' ORDER BY f.iFoodMenuId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY f.vMenu_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY f.vMenu_" . $default_lang . " DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY c.vCompany ASC";

        else

            $ord = " ORDER BY c.vCompany DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY f.iDisplayOrder ASC";

        else

            $ord = " ORDER BY f.iDisplayOrder DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY MenuItems ASC";

        else

            $ord = " ORDER BY MenuItems DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY f.eStatus ASC";

        else

            $ord = " ORDER BY f.eStatus DESC";

    }

    $ssql = '';

    if ($keyword != '') {

        if ($option != '') {

            $option_new = $option;

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' AND f.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR f.vMenu_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%') AND f.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR f.vMenu_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND f.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    if ($eStatus != '') {

        $eStatus_sql = "";

    } else {

        $eStatus_sql = " AND f.eStatus != 'Deleted'";

    }

    $sql = "SELECT f.vMenu_" . $default_lang . " as Title,c.vCompany as Store,f.iDisplayOrder as `Display Order`,(select count(iMenuItemId) from menu_items where iFoodMenuId = f.iFoodMenuId) as `Items`, f.eStatus as Status  FROM  `food_menu` as f LEFT JOIN company c ON f.iCompanyId = c.iCompanyId  WHERE 1=1 $eStatus_sql $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        // echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Title') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'Store') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'Display Order') {

                    $val = $generalobjAdmin->clearPhone($val);

                }

                if ($key == 'Status') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array('Title', 'Store', 'Display Order', 'Items', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Item Categories");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Title') {

                $pdf->Cell(50, 10, $column_heading, 1);

            } else if ($column_heading == 'Store') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else if ($column_heading == 'Display Order') {

                $pdf->Cell(30, 10, $column_heading, 1);

            } else if ($column_heading == 'Items') {

                $pdf->Cell(30, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else {

                $pdf->Cell(20, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Title') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Store') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Display Order') {

                    $values = $generalobjAdmin->clearPhone($key);

                }

                if ($column == 'Status') {

                    $values = $generalobjAdmin->clearCmpName($key);

                }

                if ($column == 'Title') {

                    $pdf->Cell(50, 10, $values, 1, 0, "1");

                } else if ($column == 'Store') {

                    $pdf->Cell(35, 10, $values, 1);

                } else if ($column == 'Display Order') {

                    $pdf->Cell(30, 10, $values, 1);

                } else if ($column == 'Items') {

                    $pdf->Cell(30, 10, $values, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(35, 10, $values, 1);

                } else {

                    $pdf->Cell(20, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//FoodMenu

//MenuItems

if ($section == 'MenuItems') {

    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

    $ord = ' ORDER BY mi.iMenuItemId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY mi.vItemType_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY mi.vItemType_" . $default_lang . " DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY c.vCompany ASC";

        else

            $ord = " ORDER BY c.vCompany DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY f.vMenu_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY f.vMenu_" . $default_lang . " DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY mi.iDisplayOrder ASC";

        else

            $ord = " ORDER BY mi.iDisplayOrder DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY mi.eStatus ASC";

        else

            $ord = " ORDER BY mi.eStatus DESC";

    }

    $ssql = '';

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' AND mi.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (f.vMenu_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR mi.vItemType_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%') AND mi.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND (f.vMenu_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR mi.vItemType_" . $default_lang . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND mi.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    if ($eStatus != '') {

        $eStatus_sql = "";

    } else {

        $eStatus_sql = " AND mi.eStatus != 'Deleted'";

    }

    $sql = "SELECT mi.vItemType_" . $default_lang . " as Item, f.vMenu_" . $default_lang . " as Category, c.vCompany as Store, mi.iDisplayOrder as `Display Order`,mi.eStatus as Status  FROM  `menu_items` as mi INNER JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId INNER JOIN company as c on c.iCompanyId=f.iCompanyId WHERE 1=1 $eStatus_sql $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        // echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Item') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'Category') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'Store') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($key == 'Display Order') {

                    $val = $generalobjAdmin->clearPhone($val);

                }

                if ($key == 'Status') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array('Item', 'Category', 'Store', 'Display Order', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Items");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Item') {

                $pdf->Cell(50, 10, $column_heading, 1);

            } else if ($column_heading == 'Category') {

                $pdf->Cell(50, 10, $column_heading, 1);

            } else if ($column_heading == 'Store') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else if ($column_heading == 'Display Order') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else {

                $pdf->Cell(20, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Item') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Category') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Store') {

                    $values = $generalobjAdmin->clearEmail($key);

                }

                if ($column == 'Display Order') {

                    $values = $generalobjAdmin->clearPhone($key);

                }

                if ($column == 'Status') {

                    $values = $generalobjAdmin->clearCmpName($key);

                }

                if ($column == 'Item') {

                    $pdf->Cell(50, 10, $values, 1);

                } else if ($column == 'Category') {

                    $pdf->Cell(50, 10, $values, 1);

                } else if ($column == 'Store') {

                    $pdf->Cell(35, 10, $values, 1);

                } else if ($column == 'Display Order') {

                    $pdf->Cell(25, 10, $values, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(20, 10, $values, 1);

                } else {

                    $pdf->Cell(20, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//MenuItems

//cuisine 

if ($section == 'cuisine') {

    $ord = ' ORDER BY c.cuisineName_' . $default_lang . ' ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY c.cuisineName_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY c.cuisineName_" . $default_lang . " DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY c.eStatus ASC";

        else

            $ord = " ORDER BY c.eStatus DESC";

    }

    $ssql = '';

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (c.cuisineName_" . $default_lang . " LIKE '%" . $keyword . "%' OR sc.vServiceName_" . $default_lang . " LIKE '%" . $keyword . "%') AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND (c.cuisineName_" . $default_lang . " LIKE '%" . $keyword . "%' OR sc.vServiceName_" . $default_lang . " LIKE '%" . $keyword . "%') ";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    if ($eStatus != '') {

        $eStatussql = "";

    } else {

        $eStatussql = " AND c.eStatus != 'Deleted'";

    }

    if (ONLYDELIVERALL == 'Yes') {

        $sql = "SELECT c.cuisineName_" . $default_lang . " as `Item Type`, c.eStatus as Status FROM cuisine as c LEFT JOIN service_categories as sc on sc.iServiceId=c.iServiceId where 1=1 $eStatussql $ssql $ord";

    } else {

        $sql = "SELECT c.cuisineName_" . $default_lang . " as `Item Type`,sc.vServiceName_" . $default_lang . " as `DeliveryAll Service Category`, c.eStatus as Status FROM cuisine as c LEFT JOIN service_categories as sc on sc.iServiceId=c.iServiceId where 1=1 $eStatussql $ssql $ord";

    }

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        if (ONLYDELIVERALL == 'Yes') {

            $heading = array('Item Type', 'Status');

        } else {

            $heading = array('Item Type', 'DeliveryAll Service Category', 'Status');

        }

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Service Categories");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Status') {

                $pdf->Cell(50, 10, $column_heading, 1);

            } else {

                $pdf->Cell(70, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Status') {

                    $pdf->Cell(50, 10, $key, 1);

                } else {

                    $pdf->Cell(70, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//Cuisine

//vehicle_type

if ($section == 'store_vehicle_type') {

    $eSystem = " AND eType = 'DeliverAll' ";

    $ord = ' ORDER BY vt.vVehicleType_' . $default_lang . ' ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";

        else

            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vt.fDeliveryCharge ASC";

        else

            $ord = " ORDER BY vt.fDeliveryCharge DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY vt.fRadius ASC";

        else

            $ord = " ORDER BY vt.fRadius DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.eStatus = '" . $eStatus . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fDeliveryCharge LIKE '%" . $keyword . "%' OR vt.fDeliveryChargeCancelOrder LIKE '%" . $keyword . "%' OR vt.fRadius LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%') AND vt.eStatus = '" . $eStatus . "'";

            } else {

                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fDeliveryCharge LIKE '%" . $keyword . "%' OR vt.fDeliveryChargeCancelOrder LIKE '%" . $keyword . "%' OR vt.fRadius LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%')";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND vt.eStatus = '" . $eStatus . "'";

    }

    if (count($userObj->locations) > 0) {

        $locations = implode(', ', $userObj->locations);

        $ssql .= " AND vt.iLocationid IN(-1, {$locations})";

    }

    if ($eStatus != '') {

        $eStatussql = "";

    } else {

        $eStatussql = " AND vt.eStatus != 'Deleted'";

    }

    $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vt.fDeliveryCharge as `Delivery Fees Completed Orders`,vt.fDeliveryChargeCancelOrder as `Delivery Fees Cancelled Orders`,vt.fRadius as Radius,vt.eStatus as Status, lm.vLocationName as location,vt.iLocationid as locationId  from  vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid where 1 = 1 $eSystem $eStatussql $ssql $ord";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        $data = array_keys($result[0]);

        $arr = array_diff($data, array("locationId"));

        echo implode("\t", $arr) . "\r\n";

        $i = 0;

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'locationId') {

                    $val = "";

                }

                if ($key == 'location' && $value['locationId'] == '-1') {

                    $val = "All Location";

                }

                echo $val . "\t";

            }

            echo "\r\n";

            $i++;

        }

    } else {

        if ($APP_TYPE == 'UberX') {

            $heading = array('Type', 'Subcategory', 'Location Name');

        } else {

            $heading = array('Type', 'Delivery Fees Completed Orders', 'Delivery Fees Cancelled Orders', 'Radius', 'Status', 'Location Name');

        }

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Vehicle Type");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Type' && $APP_TYPE == 'UberX') {

                $pdf->Cell(80, 10, $column_heading, 1);

            } else if ($column_heading == 'Type' && $APP_TYPE != 'UberX') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else if ($column_heading == 'Delivery Fees Completed Orders') {

                $pdf->Cell(54, 10, $column_heading, 1);

            } else if ($column_heading == 'Delivery Fees Cancelled Orders') {

                $pdf->Cell(54, 10, $column_heading, 1);

            } else if ($column_heading == 'Radius') {

                $pdf->Cell(15, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else if ($column_heading == 'Location Name') {

                $pdf->Cell(35, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Type' && $APP_TYPE == 'UberX') {

                    $pdf->Cell(80, 10, $key, 1);

                } else if ($column == 'Type' && $APP_TYPE != 'UberX') {

                    $pdf->Cell(20, 10, $key, 1);

                } else if ($column == 'Delivery Fees Completed Orders') {

                    $pdf->Cell(54, 10, $key, 1);

                } else if ($column == 'Delivery Fees Cancelled Orders') {

                    $pdf->Cell(54, 10, $key, 1);

                } else if ($column == 'Radius') {

                    $pdf->Cell(15, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(20, 10, $key, 1);

                } else if ($column == 'location' && $row['locationId'] == "-1") {

                    $pdf->Cell(35, 10, 'All Location', 1);

                } else if ($column == 'location') {

                    $pdf->Cell(35, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//vehicle_type

// review page 

if ($section == 'store_review') {

    $reviewtype = isset($_REQUEST['reviewtype']) ? $_REQUEST['reviewtype'] : 'Driver';

    $adm_ssql = "";

    if (SITE_TYPE == 'Demo') {

        $adm_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";

    }

    $ord = ' ORDER BY iRatingId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY o.vOrderNo ASC";

        else

            $ord = " ORDER BY o.vOrderNo DESC";

    }

    if ($sortby == 2) {

        if ($reviewtype == 'Driver') {

            if ($order == 0)

                $ord = " ORDER BY rd.vName ASC";

            else

                $ord = " ORDER BY rd.vName DESC";

        } else if ($reviewtype == 'Company') {

            if ($order == 0)

                $ord = " ORDER BY c.vCompany ASC";

            else

                $ord = " ORDER BY c.vCompany DESC";

        } else {

            if ($order == 0)

                $ord = " ORDER BY ru.vName ASC";

            else

                $ord = " ORDER BY ru.vName DESC";

        }

    }

    if ($sortby == 6) {

        if ($reviewtype == 'Driver') {

            if ($order == 0)

                $ord = " ORDER BY ru.vName ASC";

            else

                $ord = " ORDER BY ru.vName DESC";

        } else if ($reviewtype == 'Company') {

            if ($order == 0)

                $ord = " ORDER BY ru.vName ASC";

            else

                $ord = " ORDER BY ru.vName DESC";

        } else {

            if ($order == 0)

                $ord = " ORDER BY rd.vName ASC";

            else

                $ord = " ORDER BY rd.vName DESC";

        }

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY r.vRating1 ASC";

        else

            $ord = " ORDER BY r.vRating1 DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY r.tDate ASC";

        else

            $ord = " ORDER BY r.tDate DESC";

    }

    if ($sortby == 5) {

        if ($order == 0)

            $ord = " ORDER BY r.vMessage ASC";

        else

            $ord = " ORDER BY r.vMessage DESC";

    }

    //End Sorting

    $ssql = '';

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'r.eStatus') !== false) {

                $ssql .= " AND " . stripslashes($option) . " LIKE '" . $generalobjAdmin->clean($keyword) . "'";

            } else {

                $option_new = $option;

                if ($option == 'drivername') {

                    $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";

                }

                if ($option == 'ridername') {

                    $option_new = "CONCAT(ru.vName,' ',ru.vLastName)";

                }

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%'";

            }

        } else {

            if ($reviewtype == 'Driver') {

                $ssql .= " AND (o.vOrderNo LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR r.vRating1 LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

            } else if ($reviewtype == 'Company') {

                $ssql .= " AND (o.vOrderNo LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR  c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR r.vRating1 LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

            } else {

                $ssql .= " AND (o.vOrderNo LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR r.vRating1 LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

            }

        }

    }

// End Search Parameters

    $chkusertype = "";

    if ($reviewtype == "Driver") {

        $chkusertype = "Driver";

    } else if ($reviewtype == "Company") {

        $chkusertype = "Company";

    } else {

        $chkusertype = "Passenger";

    }

    if ($reviewtype == "Driver") {

        $sql = "SELECT o.vOrderNo as `Order Number`, CONCAT(ru.vName,' ',ru.vLastName) as `From User Name`,CONCAT(rd.vName,' ',rd.vLastName) as `To Driver Name` ,rd.vAvgRating as AverageRate,r.vRating1 as Rate,r.tDate as `Date`,r.vMessage as Comment FROM ratings_user_driver as r LEFT JOIN orders as o ON r.iOrderId=o.iOrderId LEFT JOIN company as c ON c.iCompanyId=o.iCompanyId LEFT JOIN register_driver as rd ON rd.iDriverId=o.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=o.iUserId WHERE 1=1 AND r.eToUserType='" . $chkusertype . "' And ru.eStatus!='Deleted' $ssql $adm_ssql $ord";

    } else if ($reviewtype == "Company") {

        $sql = "SELECT o.vOrderNo as `Order Number`,CONCAT(ru.vName,' ',ru.vLastName) as `From User Name`,c.vCompany as `To Restaurant Name`,r.vRating1 as Rate,r.tDate as `Date`,c.vAvgRating as AverageRate,r.vMessage as Comment FROM ratings_user_driver as r LEFT JOIN orders as o ON r.iOrderId=o.iOrderId LEFT JOIN company as c ON c.iCompanyId=o.iCompanyId LEFT JOIN register_driver as rd ON rd.iDriverId=o.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=o.iUserId WHERE 1=1 AND r.eToUserType='" . $chkusertype . "' AND ru.eStatus!='Deleted' $ssql $adm_ssql $ord";

    } else {

        $sql = "SELECT o.vOrderNo as `Order Number`,CONCAT(rd.vName,' ',rd.vLastName) as `From Delivery Driver Name`,CONCAT(ru.vName,' ',ru.vLastName) as `To User Name`,ru.vAvgRating as AverageRate,vRating1 as Rate,r.tDate as `Date`,r.vMessage as Comment FROM ratings_user_driver as r LEFT JOIN orders as o ON r.iOrderId=o.iOrderId LEFT JOIN company as c ON c.iCompanyId=o.iCompanyId LEFT JOIN register_driver as rd ON rd.iDriverId=o.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=o.iUserId WHERE 1=1 AND r.eToUserType='" . $chkusertype . "' And ru.eStatus!='Deleted'  $ssql $adm_ssql $ord";

    }

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        // echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'RiderNumber') {

                    $val = $generalobjAdmin->clearName($val);

                }

                if ($reviewtype == "Driver") {

                    if ($key == 'DriverName') {

                        $val = $generalobjAdmin->clearName($val);

                    }

                } else {

                    if ($key == 'RiderName') {

                        $val = $generalobjAdmin->clearName($val);

                    }

                }

                if ($key == 'AverageRate') {

                    $val = $val;

                }

                if ($reviewtype == "Driver") {

                    if ($key == 'RiderName') {

                        $val = $generalobjAdmin->clearName($val);

                    }

                } else {

                    if ($key == 'DriverName') {

                        $val = $generalobjAdmin->clearName($val);

                    }

                }

                if ($key == 'Rate') {

                    $val = $val;

                }

                if ($key == 'Date') {

                    $val = $generalobjAdmin->DateTime($val);

                }

                if ($key == 'Comment') {

                    $val = $val;

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        if ($reviewtype == "Driver") {

            $heading = array('RiderNumber', 'DriverName', 'AverageRate', 'RiderName', 'Rate', 'Date', 'Comment');

        } else {

            $heading = array('RiderNumber', 'RiderName', 'AverageRate', 'DriverName', 'Rate', 'Date', 'Comment');

        }

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Review");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'RiderNumber') {

                $pdf->Cell(22, 10, $column_heading, 1);

            } else if ($column_heading == 'DriverName') {

                $pdf->Cell(40, 10, $column_heading, 1);

            } else if ($column_heading == 'AverageRate') {

                $pdf->Cell(21, 10, $column_heading, 1);

            } else if ($column_heading == 'RiderName') {

                $pdf->Cell(25, 10, $column_heading, 1);

            } else if ($column_heading == 'Rate') {

                $pdf->Cell(10, 10, $column_heading, 1);

            } else if ($column_heading == 'Date') {

                $pdf->Cell(42, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'DriverName') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Date') {

                    $values = $generalobjAdmin->DateTime($key);

                }

                $generalobjAdmin->DateTime($val);

                if ($column == 'RiderNumber') {

                    $pdf->Cell(22, 10, $values, 1);

                } else if ($column == 'DriverName') {

                    $pdf->Cell(40, 10, $values, 1);

                } else if ($column == 'AverageRate') {

                    $pdf->Cell(21, 10, $values, 1);

                } else if ($column == 'RiderName') {

                    $pdf->Cell(25, 10, $values, 1);

                } else if ($column == 'Rate') {

                    $pdf->Cell(10, 10, $values, 1);

                } else if ($column == 'Date') {

                    $pdf->Cell(42, 10, $values, 1);

                } else {

                    $pdf->Cell(45, 10, $values, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//Cancel Reason 

if ($section == 'cancel_reason') {

    $eType = isset($_REQUEST['eType']) ? stripslashes($_REQUEST['eType']) : "";

    $ord = ' ORDER BY vTitle ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vTitle ASC";

        else

            $ord = " ORDER BY vTitle DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if (strpos($option, 'eStatus') !== false) {

                if ($eType != '') {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "' AND eType = '" . $eType . "' ";

                } else {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

                }

            } else {

                if ($eType != '') {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND eType = '" . $eType . "' ";

                } else {

                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

                }

            }

        } else {

            if ($eType != '') {

                $ssql .= " AND ( vTitle_" . $default_lang . " LIKE '%" . $keyword . "%') AND  eType='" . $eType . "'";

            } else {

                $ssql .= " AND ( vTitle_" . $default_lang . " LIKE '%" . $keyword . "%')";

            }

        }

    } else if ($eType != '' && $keyword == '') {

        $ssql .= " AND eType = '" . $generalobjAdmin->clean($eType) . "'";

    }

    if ($option == "eStatus") {

        $eStatussql = " AND eStatus = '" . ($keyword) . "'";

    } else {

        $eStatussql = " AND eStatus != 'Deleted'";

    }

    $sql = "SELECT vTitle_EN as Title, eType as `Service Type` ,eStatus as Status FROM cancel_reason where 1=1 $eStatussql $ssql";

    // filename for download

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        // echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        while ($row = mysqli_fetch_assoc($result)) {

            if (!$flag) {

                // display field/column names as first row

                echo implode("\t", array_keys($row)) . "\r\n";

                $flag = true;

            }

            array_walk($row, __NAMESPACE__ . '\cleanData');

            echo implode("\t", array_values($row)) . "\r\n";

        }

    } else {

        $heading = array('Title', 'Service Type', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Cancel Reason");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Title') {

                $pdf->Cell(100, 10, $column_heading, 1);

            } else if ($column_heading == 'Status') {

                $pdf->Cell(30, 10, $column_heading, 1);

            } else {

                $pdf->Cell(30, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == 'Title') {

                    $pdf->Cell(100, 10, $key, 1);

                } else if ($column == 'Status') {

                    $pdf->Cell(30, 10, $key, 1);

                } else {

                    $pdf->Cell(30, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

//Cancel Reason

//Added By Hasmukh On 1-11-2018 For Set Common PDF Configuration Start

function manage_tcpdf($pageOrientation, $unit, $imagePath, $imageName, $pageType = "P", $pageSize = "A4") {

    $pdf = new TCPDF($pageOrientation, $unit, "Letter", true, 'UTF-8', false);

    $image_file = $imagePath . $imageName;

    //print_r($image_file);die;

    $pdf->AddPage($pageType, $pageSize);

    $pdf->Image($image_file, 90, 6, 30);

    $lg = Array();

    $lg['a_meta_charset'] = 'UTF-8';

    $lg['a_meta_language'] = 'ar';

    // set some language-dependent strings (optional)

    $pdf->setLanguageArray($lg);

    $language = "dejavusans";

    //$language = "Arial";

    $pdfName = time() . ".pdf";

    $result = array("pdf" => $pdf, "language" => $language, "pdfName" => $pdfName);

    return $result;

}

//Added By Hasmukh On 1-11-2018 For Set Common PDF Configuration End

// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 1 Start

if ($section == 'movement_report_before') {

    $ssql = "";

    $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : "";

    if ($searchDriver != "") {

        $ssql .= " AND t.iDriverId ='" . $searchDriver . "'";

    }

    if ($startDate != '') {

        $ssql .= " AND Date(tDate) >='" . $startDate . " 00:00:00'";

    }

    if ($endDate != '') {

        $ssql .= " AND Date(tDate) <='" . $endDate . " 23:59:59'";

    }

    $sql = "SELECT tl.*,rd.vName, rd.vLastName, t.vRideNo,t.iDriverId,t.fDistance,t.tStartDate AS dStartTime,t.tEndDate AS dEndTime,concat(rd.vName,' ',rd.vLastName) as Driver FROM trips_locations tl, register_driver as rd, trips as t WHERE  t.iDriverId = rd.iDriverId AND tl.iTripId = t.iTripId AND t.iActive = 'Active' $ssql ORDER BY iTripId DESC, iTripLocationId";

    $db_movement = $obj->MySQLSelect($sql);

    if ($type == 'XLS') {

        $filename = $section . "_" . date('Ymd') . ".xls";

        $flag = false;

        $header .= "Driver" . "\t";

        $header .= "Trip No." . "\t";

        $header .= "Distance (Mile)" . "\t";

        $header .= "Type" . "\t";

        $header .= "Date" . "\t";

        $header .= "Total Time" . "\t";

        $header .= "Location" . "\t";

        for ($i = 0; $i < count($db_movement); $i++) {

            $tPlatitudes = explode(",", $db_movement[$i]['tPlatitudes']);

            $tPlongitudes = explode(",", $db_movement[$i]['tPlongitudes']);

            $lat = $tPlatitudes[0];

            $lng = $tPlongitudes[0];

            $address = $generalobjAdmin->getaddress($lat, $lng);

            if ($db_movement[$i]['fDistance'] > 0.1) {

                $fDistance = $db_movement[$i]['fDistance'];

            } else {

                $fDistance = round($db_movement[$i]['fDistance']);

            }

            $fDistance = $generalobjAdmin->getUnitToMiles($db_movement[$i]['fDistance'], 'Miles');

            $data_movement .= $db_movement[$i]['Driver'] . "\t";

            $data_movement .= $db_movement[$i]['vRideNo'] . "\t";

            $data_movement .= $fDistance . "\t";

            $data_movement .= "Period 1 \t";

            $data_movement .= $db_movement[$i]['tDate'] . "\t";

            $time = $generalobjAdmin->TimeDifference($db_movement[$i]['dStartTime'], $db_movement[$i]['dEndTime']);

            $data_movement .= $time . "\t";

            if ($address) {

                $data_movement .= $address;

            } else {

                $data_movement .= '--';

            }

            $data_movement .= "\n";

        }

        $data_movement = str_replace("\r", "", $data_movement);

        //echo "<pre>";print_r($header);die;

        ob_clean();

        header("Content-type: application/octet-stream");

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Pragma: no-cache");

        header("Expires: 0");

        print "$header\n$data_movement";

        exit;

    }

}

// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 1 End

// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 2 Start

if ($section == 'movement_report_arriving') {

    $ssql = "";

    $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : "";

    if ($searchDriver != "") {

        $ssql .= " AND t.iDriverId ='" . $searchDriver . "'";

    }

    if ($startDate != '') {

        $ssql .= " AND Date(tDate) >='" . $startDate . " 00:00:00'";

    }

    if ($endDate != '') {

        $ssql .= " AND Date(tDate) <='" . $endDate . " 23:59:59'";

    }

    $sql = "SELECT tl.*,rd.vName, rd.vLastName, t.vRideNo,t.iDriverId,t.fDistance,t.tStartDate AS dStartTime,t.tEndDate AS dEndTime,concat(rd.vName,' ',rd.vLastName) as Driver FROM trips_locations tl, register_driver as rd, trips as t WHERE  t.iDriverId = rd.iDriverId AND tl.iTripId = t.iTripId AND t.iActive = 'Arrived' $ssql ORDER BY iTripId DESC, iTripLocationId";

    $db_movement = $obj->MySQLSelect($sql);

    if ($type == 'XLS') {

        $filename = $section . "_" . date('Ymd') . ".xls";

        $flag = false;

        $header .= "Driver" . "\t";

        $header .= "Trip No." . "\t";

        $header .= "Distance (Mile)" . "\t";

        $header .= "Type" . "\t";

        $header .= "Date" . "\t";

        $header .= "Total Time" . "\t";

        $header .= "Location" . "\t";

        for ($i = 0; $i < count($db_movement); $i++) {

            $tPlatitudes = explode(",", $db_movement[$i]['tPlatitudes']);

            $tPlongitudes = explode(",", $db_movement[$i]['tPlongitudes']);

            $lat = $tPlatitudes[0];

            $lng = $tPlongitudes[0];

            $address = $generalobjAdmin->getaddress($lat, $lng);

            if ($db_movement[$i]['fDistance'] > 0.1) {

                $fDistance = $db_movement[$i]['fDistance'];

            } else {

                $fDistance = round($db_movement[$i]['fDistance']);

            }

            $fDistance = $generalobjAdmin->getUnitToMiles($db_movement[$i]['fDistance'], 'Miles');

            $data_movement .= $db_movement[$i]['Driver'] . "\t";

            $data_movement .= $db_movement[$i]['vRideNo'] . "\t";

            $data_movement .= $fDistance . "\t";

            $data_movement .= "Period 2 \t";

            $data_movement .= $db_movement[$i]['tDate'] . "\t";

            $time = $generalobjAdmin->TimeDifference($db_movement[$i]['dStartTime'], $db_movement[$i]['dEndTime']);

            $data_movement .= $time . "\t";

            if ($address) {

                $data_movement .= $address;

            } else {

                $data_movement .= '--';

            }

            $data_movement .= "\n";

        }

        $data_movement = str_replace("\r", "", $data_movement);

        //echo "<pre>";print_r($data_movement);die;

        ob_clean();

        header("Content-type: application/octet-stream");

        // header('Content-Type: text/html; charset=utf-8');

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        header("Pragma: no-cache");

        header("Expires: 0");

        print "$header\n$data_movement";

        exit;

    }

}

// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 2 End

// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 3 Start

if ($section == 'movement_report_ontrip') {

    $ssql = "";

    $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : "";

    if ($searchDriver != "") {

        $ssql .= " AND tl.iDriverId ='" . $searchDriver . "'";

    }

    if ($startDate != '') {

        $ssql .= " AND Date(tDate) >='" . $startDate . " 00:00:00'";

    }

    if ($endDate != '') {

        $ssql .= " AND Date(tDate) <='" . $endDate . " 23:59:59'";

    }

    $sql = "SELECT tl.*,rd.vName, rd.vLastName, t.vRideNo,t.iDriverId,t.fDistance,t.tStartDate AS dStartTime,t.tEndDate AS dEndTime,concat(rd.vName,' ',rd.vLastName) as Driver FROM trips_locations tl, register_driver as rd, trips as t WHERE  t.iDriverId = rd.iDriverId AND tl.iTripId = t.iTripId AND t.iActive = 'On Going Trip' $ssql ORDER BY iTripId DESC, iTripLocationId";

    $db_movement = $obj->MySQLSelect($sql);

    if ($type == 'XLS') {

        $filename = $section . "_" . date('Ymd') . ".xls";

        $flag = false;

        $header .= "Driver" . "\t";

        $header .= "Trip No." . "\t";

        $header .= "Distance (Mile)" . "\t";

        $header .= "Type" . "\t";

        $header .= "Date" . "\t";

        $header .= "Total Time" . "\t";

        $header .= "Location" . "\t";

        for ($i = 0; $i < count($db_movement); $i++) {

            $tPlatitudes = explode(",", $db_movement[$i]['tPlatitudes']);

            $tPlongitudes = explode(",", $db_movement[$i]['tPlongitudes']);

            $lat = $tPlatitudes[0];

            $lng = $tPlongitudes[0];

            $address = $generalobjAdmin->getaddress($lat, $lng);

            if ($db_movement[$i]['fDistance'] > 0.1) {

                $fDistance = $db_movement[$i]['fDistance'];

            } else {

                $fDistance = round($db_movement[$i]['fDistance']);

            }

            $fDistance = $generalobjAdmin->getUnitToMiles($db_movement[$i]['fDistance'], 'Miles');

            $data_movement .= $db_movement[$i]['Driver'] . "\t";

            $data_movement .= $db_movement[$i]['vRideNo'] . "\t";

            $data_movement .= $fDistance . "\t";

            $data_movement .= "Period 3 \t";

            $data_movement .= $db_movement[$i]['tDate'] . "\t";

            $time = $generalobjAdmin->TimeDifference($db_movement[$i]['dStartTime'], $db_movement[$i]['dEndTime']);

            $data_movement .= $time . "\t";

            if ($address) {

                $data_movement .= $address;

            } else {

                $data_movement .= '--';

            }

            $data_movement .= "\n";

        }

        $data_movement = str_replace("\r", "", $data_movement);

        //echo "<pre>";print_r($data_movement);die;

        ob_clean();

        header("Content-type: application/octet-stream");

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Pragma: no-cache");

        header("Expires: 0");

        print "$header\n$data_movement";

        exit;

    }

}

// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 3 End

// Added By Hasmukh On 12-12-2018 For Export Data of Advertisement Banners Start

if ($section == 'advertise_banners') {

    global $tconfig;

    $sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : '';

    $ord = ' ORDER BY iDispOrder ASC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vBannerTitle ASC";

        else

            $ord = " ORDER BY vBannerTitle DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY ePosition ASC";

        else

            $ord = " ORDER BY ePosition DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY iDispOrder ASC";

        else

            $ord = " ORDER BY iDispOrder DESC";

    }

    if ($keyword != '') {

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND vBannerTitle LIKE '%" . $generalobjAdmin->clean($keyword) . "%') AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND vBannerTitle LIKE '%" . $generalobjAdmin->clean($keyword) . "%')";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    $sql = "SELECT iAdvertBannerId AS SrNo,vBannerTitle AS Name,ePosition AS Position,iDispOrder AS DisplayOrder,concat(dStartDate,' To ',dExpiryDate) as TimePeriod,dAddedDate AS AddedDate,iImpression AS TotalImpression,eImpression AS UsedImpression,eStatus AS Status FROM advertise_banners as vc WHERE eStatus != 'Deleted' $ssql $ord";

    // filename for download

    $getUserCount = $obj->MySQLSelect("SELECT * FROM banner_impression WHERE iAdvertBannerId > 0");

//echo "<pre>";

    $usedCountArr = array();

    for ($c = 0; $c < count($getUserCount); $c++) {

        $bannerId = $getUserCount[$c]['iAdvertBannerId'];

        if (isset($usedCountArr[$bannerId]) && $usedCountArr[$bannerId] > 0) {

            $usedCountArr[$bannerId] += 1;

        } else {

            $usedCountArr[$bannerId] = 1;

        }

    }

    echo "<pre>";

    //print_r($usedCountArr);die;

    if ($type == 'XLS') {

        $filename = $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query failed!');

        //print_r($result);die;

        echo implode("\t", array_keys($result[0])) . "\r\n";

        $sr = 1;

        foreach ($result as $value) {

            $bannerUsedCount = "-----";

            $impressionCount = "Unlimited";

            if (isset($usedCountArr[$value['SrNo']]) && $usedCountArr[$value['SrNo']] > 0 && $value['UsedImpression'] == "Limited") {

                $bannerUsedCount = $usedCountArr[$value['SrNo']];

                $impressionCount = $value['TotalImpression'];

            }

            $value['UsedImpression'] = $bannerUsedCount;

            $value['TotalImpression'] = $impressionCount;

            $value['SrNo'] = $sr;

            //print_r($value);die;

            foreach ($value as $key => $val) {

                if ($key == 'Category') {

                    $val = $generalobjAdmin->clearName($val);

                }

                echo $val . "\t";

            }

            echo "\r\n";

            $sr++;

        }

    } else {

        $heading = array('SrNo#', 'Name', 'Position', 'Display Order', 'Time Period', 'Added Date', 'Total Impression', 'Used Impression', 'Status');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Name");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == 'Position' || $column_heading == 'Status') {

                $pdf->Cell(20, 10, $column_heading, 1);

            } else if ($column_heading == 'Display Order' || $column_heading == 'Name' || $column_heading == 'Added Date') {

                $pdf->Cell(35, 10, $column_heading, 1);

            } else {

                $pdf->Cell(45, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        //echo "<pre>";

        //print_r($heading);die;

        $sr = 1;

        foreach ($result as $row) {

            $pdf->Ln();

            unset($row['URL']);

            $bannerUsedCount = "-----";

            $impressionCount = "Unlimited";

            if (isset($usedCountArr[$row['SrNo']]) && $usedCountArr[$row['SrNo']] > 0 && $row['UsedImpression'] == "Limited") {

                $bannerUsedCount = $usedCountArr[$row['SrNo']];

                $impressionCount = $row['TotalImpression'];

            }

            $row['UsedImpression'] = $bannerUsedCount;

            $row['TotalImpression'] = $impressionCount;

            $row['SrNo'] = $sr;

            foreach ($row as $column => $key) {

                $values = $key;

                if ($column == 'Name') {

                    $values = $generalobjAdmin->clearName($key);

                }

                if ($column == 'Position' || $column == 'Status') {

                    $pdf->Cell(20, 10, $values, 1);

                } else if ($column == 'DisplayOrder' || $column == 'Name' || $column == 'AddedDate') {

                    $pdf->Cell(35, 10, $values, 1);

                } else {

                    $pdf->Cell(45, 10, $values, 1);

                }

            }

            $sr++;

        }

        //print_r($pdf);die;

        $pdf->Output($pdfFileName, 'D');

    }

}

// Added By Hasmukh On 12-12-2018 For Export Data of Advertisement Banners End

// Added By Hasmukh On 14-12-2018 For Export Data of Newsletter Start

if ($section == 'newsletter') {

    $tbl_name = 'newsletter';

    $ord = ' ORDER BY iNewsLetterId DESC';

    if ($sortby == 1) {

        if ($order == 0)

            $ord = " ORDER BY vName ASC";

        else

            $ord = " ORDER BY vName DESC";

    }

    if ($sortby == 2) {

        if ($order == 0)

            $ord = " ORDER BY vEmail ASC";

        else

            $ord = " ORDER BY vEmail DESC";

    }

    if ($sortby == 3) {

        if ($order == 0)

            $ord = " ORDER BY eStatus ASC";

        else

            $ord = " ORDER BY eStatus DESC";

    }

    if ($sortby == 4) {

        if ($order == 0)

            $ord = " ORDER BY tDate ASC";

        else

            $ord = " ORDER BY tDate DESC";

    }

    $ssql = " WHERE eStatus != 'Deleted'";

    if ($keyword != '') {

        $keyword_new = $keyword;

        $chracters = array("(", "+", ")");

        $removespacekeyword = preg_replace('/\s+/', '', $keyword);

        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

        if (is_numeric($keyword_new)) {

            $keyword_new = $keyword_new;

        } else {

            $keyword_new = $keyword;

        }

        if ($option != '') {

            if ($eStatus != '') {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (vName LIKE '%" . $keyword_new . "%'  OR vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            } else {

                $ssql .= " AND (vName LIKE '%" . $keyword_new . "%'  OR vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";

            }

        }

    } else if ($eStatus != '' && $keyword == '') {

        $ssql .= " AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

    }

    //added by SP for status on 28-06-2019

    $sql = "SELECT vName AS Name,vEmail AS Email,eStatus as Status,tDate AS Date,vIP AS IP FROM " . $tbl_name . " $ssql $ord";

    if ($type == 'XLS') {

        $filename = $tbl_name . "_" . $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query Failed!');

        echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if ($key == 'Name') {

                    $val = $generalobjAdmin->clearCmpName($val);

                }

                if ($key == 'Email') {

                    $val = $generalobjAdmin->clearEmail($val);

                }

                

                if ($key == 'Date') {

                    $val = $generalobjAdmin->DateTime($val, 'No'); 

                }

                echo $val . "\t";

            }

            echo "\r\n";

        }

    } else {

        $heading = array($langage_lbl_admin['LBL_USER_NAME_HEADER_SLIDE_TXT'], $langage_lbl_admin['LBL_EMAIL_LBL_TXT'], $langage_lbl_admin['LBL_DATE_SIGNUP'], 'IP');

        $result = $obj->ExecuteQuery($sql);

        while ($row = mysqli_fetch_assoc($result)) {

            $resultset[] = $row;

        }

        $result = $resultset;

        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);

        $pdf = $configPdf['pdf'];

        $language = $configPdf['language'];

        $pdfFileName = $file . $configPdf['pdfName'];

        //$pdf = new FPDF('P', 'mm', 'Letter');

        //$pdf->AddPage();

        $pdf->SetFillColor(36, 96, 84);

        $pdf->SetFont($language, 'b', 15);

        $pdf->Cell(100, 16, "Newsletter");

        $pdf->Ln();

        $pdf->SetFont($language, 'b', 9);

        $pdf->Ln();

        foreach ($heading as $column_heading) {

            if ($column_heading == $langage_lbl_admin['LBL_DATE_SIGNUP'] || $column_heading == $langage_lbl_admin['LBL_EMAIL_LBL_TXT']) {

                $pdf->Cell(55, 10, $column_heading, 1);

            } else {

                $pdf->Cell(40, 10, $column_heading, 1);

            }

        }

        $pdf->SetFont($language, '', 9);

        foreach ($result as $row) {

            $pdf->Ln();

            foreach ($row as $column => $key) {

                if ($column == $langage_lbl_admin['LBL_DATE_SIGNUP']) {

                    $key = $generalobjAdmin->DateTime($key);

                    $pdf->Cell(55, 10, $key, 1);

                } if ($column == $langage_lbl_admin['LBL_EMAIL_LBL_TXT']) {

                    $key = $generalobjAdmin->clearEmail($key);

                    $pdf->Cell(55, 10, $key, 1);

                } if ($column == $langage_lbl_admin['LBL_USER_NAME_HEADER_SLIDE_TXT']) {

                    $key = $generalobjAdmin->clearName($key);

                    $pdf->Cell(40, 10, $key, 1);

                } else {

                    $pdf->Cell(40, 10, $key, 1);

                }

            }

        }

        $pdf->Output($pdfFileName, 'D');

    }

}

// Added By Hasmukh On 14-12-2018 For Export Data of Newsletter End

if($section == 'driversubscription') {

    

    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;

    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

    $ord = ' ORDER BY iDriverSubscriptionPlanId DESC';

    

    $option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";

    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

    $searchDriver = isset($_REQUEST['searchDriver']) ? stripslashes($_REQUEST['searchDriver']) : "";

    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";

    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

    $defaultDetails = $obj->MySQLSelect("SELECT * FROM `language_master` WHERE `eDefault` ='Yes' AND eStatus = 'Active'");

    //$currencySymbol = $obj->MySQLSelect("SELECT vSymbol FROM currency WHERE eDefault = 'Yes'")[0]['vSymbol'];

    $vcode = $defaultDetails[0]['vCode'];

    $currencySymbol = $defaultDetails[0]['vCurrencySymbol'];

    $ssql = '';

    if ($keyword != '') {

        $keyword_new = $keyword;

        $chracters = array("(", "+", ")");

        $removespacekeyword = preg_replace('/\s+/', '', $keyword);

        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

        if (is_numeric($keyword_new)) {

            $keyword_new = $keyword_new;

        } else {

            $keyword_new = $keyword;

        }

        if ($option != '') {

            $option_new = $option;

            if($option_new=='providerName') {

                $ssql .= " AND (rd.vName LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR rd.vLastName LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR CONCAT( vName,  ' ', vLastName ) LIKE  '%".$generalobjAdmin->clean($keyword_new)."%' )";

            } else {

                $ssql .= " AND d." . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }

        } else { 

                $ssql .= " AND (d.vPlanName LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR d.ePlanValidity LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";

                $ssql .= " OR (rd.vName LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR rd.vLastName LIKE '%".$generalobjAdmin->clean($keyword_new)."%' OR CONCAT( vName,  ' ', vLastName ) LIKE  '%".$generalobjAdmin->clean($keyword_new)."%')";

        }

    }

    if($searchDriver!='') {

        $ssql .= " AND rd.iDriverId = $searchDriver";

    }

    // End Search Parameters

    //Pagination Start

    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

    $tblPlan = 'driver_subscription_plan';

    $tblDetails = 'driver_subscription_details';

    //$getField = "eSubscriptionStatus, p.vPlanName, p.vPlanDescription,p.vPlanPeriod,p.ePlanValidity,CONCAT('$currencySymbol',p.fPrice) as fPlanPrice,d.tSubscribeDate,d.tExpiryDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays, d.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) as name";

    $getField = "d.eSubscriptionStatus, d.vPlanName, d.vPlanDescription,d.vPlanPeriod,d.ePlanValidity,CONCAT('$currencySymbol',d.fPrice) as fPlanPrice,d.tSubscribeDate,d.tExpiryDate,d.tClosedDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays,d.tSubscribeDate, d.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) as name";

    //$sql = "SELECT $getField FROM $tblDetails d INNER JOIN $tblPlan p ON d.iDriverSubscriptionPlanId = p.iDriverSubscriptionPlanId  LEFT JOIN register_driver rd ON rd.iDriverId=d.iDriverId WHERE 1 $ssql ORDER BY d.tSubscribeDate DESC, d.tExpiryDate DESC";

    $sql = "SELECT $getField FROM $tblDetails d LEFT JOIN register_driver rd ON rd.iDriverId=d.iDriverId WHERE 1 $ssql ORDER BY d.tSubscribeDate DESC, d.tExpiryDate DESC";

    if ($type == 'XLS') {

        $filename = $tblDetails . "_" . $timestamp_filename . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Content-Type: application/vnd.ms-excel");

        //echo "\xEF\xBB\xBF";

        $flag = false;

        $result = $obj->MySQLSelect($sql) or die('Query Failed!');

        //echo implode("\t", array_keys($result[0])) . "\r\n";

        foreach ($result[0] as $key => $val) {

           if($key == 'eSubscriptionStatus' || $key == 'planLeftDays' || $key == 'iDriverId') {

                continue;

            } 

            echo $key . "\t";

        }

        echo "\r\n";

        foreach ($result as $value) {

            foreach ($value as $key => $val) {

                if($key == 'eSubscriptionStatus' || $key == 'planLeftDays' || $key == 'iDriverId') {

                    continue;

                }

                

                if ($key == 'vPlanDescription') {

                    $val = str_replace(",","|",$val);

                }

                if ($key == 'vPlanName') {

                    $val = str_replace(",","|",$val);

                }

                if ($key == 'name') {

                    $val = $generalobj->clearName(" " . $val);

                }

                

                

                if ($key == 'vPlanPeriod') {

                    

                    if($val=='Weekly') {

                        $val = $langage_lbl_admin['LBL_SUB_WEEKS'];

                    }

                    if($val=='Monthly') {

                        $val = $langage_lbl_admin['LBL_SUB_MONTH'];

                    }

                    

                }

                

                /*if ($key == 'Subscribed Date') {

                    $val = $generalobjAdmin->DateTime($val);

                }*/

                echo $val . "\t";

            }

            echo "\r\n";

        }

    }

}

?>