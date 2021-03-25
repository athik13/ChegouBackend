<?php
include_once '../common.php';

if (!isset($generalobjAdmin)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjAdmin = new General_admin();
}

if (!$userObj->hasPermission('view-city')) {
    $userObj->redirect();
}

$script = 'city';

$post = false;
if (isset($_POST['import'])) {
    $post = true;
    $file = $_FILES['file'];
    try {

        if (!isset($file) || $file['error'] > 0) {
            throw new Exception("Please upload excel file.", 1);
        }

        $all_states = Models\State::get()
                    ->map(function($item){ 
                        $item->vState = strtolower($item->vState); 
                        return $item; 
                    });
        $state_country = $all_states->pluck('iCountryId', 'iStateId')->toArray();     
        $states = $all_states->pluck('iStateId', 'vState')->toArray();
      


        $inputFileName = $file['tmp_name'];
        $valid = false;
        $types = array('Excel2007', 'Excel5');
        foreach ($types as $type) {
            $reader = PHPExcel_IOFactory::createReader($type);
            if ($reader->canRead($inputFileName)) {
                $valid = true;
                break;
            }
        }
        if (!$valid) {
            throw new Exception("Please upload valid excel file.", 1);
        }

        $objPHPExcel   = PHPExcel_IOFactory::load($inputFileName);
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet     = $objPHPExcel->getActiveSheet();
        $sheetData = sheet_to_array($sheet);

        $file_head = array_keys($sheetData[0]);
        $require_keys = ["city_name", "state_name", "status"];

        foreach ($require_keys as $k => $v) {
           if(!in_array($v, $file_head)){
            throw new Exception("Please upload valid excel file. ", 1);
           }
        }


    } catch(Exception $e) {
        setMessage( $e->getMessage(), 'danger');
        header("location:city_import.php");
        exit();
    }


    $doc = new PHPExcel();
    $doc->setActiveSheetIndex(0);
    $doc_sheet = $doc->getActiveSheet();

    $highestColumn = $sheet->getHighestColumn();
    $headingsArray = $sheet->rangeToArray('A1:' . $highestColumn . '1');

    $not_inserted_array = [];
    if(!in_array("Error", $headingsArray[0])){
        $headingsArray[0][] =  "Error";
    }
    $not_inserted_array[] = $headingsArray[0];
    
    $column_map = array_keys($sheetData[0]);
    if(!in_array('error', $column_map)){
        $column_map[] = "error";
    }

    $insert_data = [];
    $has_error = false;

    foreach ($sheetData as $key => $value) {
        $state_name = trim(strtolower($value['state_name']));
        $city_name = trim($value['city_name']);

        $status = isset($value['status']) ? $value['status'] : "Active";

        try {
            if(isset($states[$state_name])){
                $state_id = $states[$state_name];
            }else{
                throw new Exception("State Not Found", 1);
            }

            $city = Models\City::where([
                "vCity"      => $city_name,
                "iCountryId" => $state_country[$state_id],
                "iStateId"   => $state_id,
            ])->first();


            if(!$city){
                $insert_data[] = [
                    "vCity"      => $city_name,
                    "iCountryId" => $state_country[$state_id],
                    "iStateId"   => $state_id,
                    "eStatus"    => $status,
                ];
            }


        } catch (Exception $e) {
            $has_error = true;
            $row = [];
            foreach ($column_map as $column_key => $column_name) {
                if($column_name != 'error'){
                    $cell_value = isset($value[$column_name]) ? $value[$column_name] : "";
                }else{
                    $cell_value = $e->getMessage();
                }
                $row[$column_key] = $cell_value;
            }
            $not_inserted_array[] = $row;
        }
    }




    if(count($insert_data) > 0){
        Models\City::insert($insert_data);
    }

    if($has_error){
        //ArrayToExcelSheet($doc_sheet,$not_inserted_array);
        //downloadExcel($doc); 
    }

}

?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> | City Import</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once 'global_files.php';?>
    </head>
    <!-- END  HEAD-->

    <!-- BEGIN BODY-->
    <body class="padTop53" >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once 'header.php';?>
            <?php include_once 'left_menu.php';?>

            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?php echo $langage_lbl_admin['LBL_CAR_CITY_ADMIN']; ?> Import</h2>
                            <a href="city.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>

                    <hr>
                    <?php echo getMessage(); ?>
                    
                    <?php if($post){ ?> 
                        <?php if(count($insert_data) > 0){ ?>
                            <div class="alert alert-success"><?php echo count($insert_data) ?> Records are successfully imported</div>
                        <?php } elseif(!$has_error){?>
                            <div class="alert alert-success">All Records are successfully imported</div>
                        <?php } ?>
                    <?php } ?>

                    <div class="row">
                        <div class="col-sm-12">

                            <p class="alert alert-warning"><a class="btn btn-sm btn-info" href="library/assets/city_sample.xlsx" download="<?php echo $langage_lbl_admin['LBL_CAR_CITY_ADMIN']; ?>">Click here</a> &nbsp;to download sample excel file.</p>
                            <br>
                            <form accept="" method="post" enctype="multipart/form-data">
                                <!-- <input type="hidden" name="debug"> -->
                                <div class="form-group">
                                    <label >Upload <?php echo $langage_lbl_admin['LBL_CAR_CITY_ADMIN']; ?> Excel File</label>
                                    <input class="form-control-file" type="file" name="file">
                                </div>
                                <button type="submit" name="import" class="btn btn-primary mb-2">Import</button>
                            </form>
                        </div>
                    </div>
                    <?php if($has_error){?>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="alert alert-danger"><?php echo count($not_inserted_array) - 1 ?> Records are not Imported. Please check below for reason</div>
                            <table class="table">
                                <?php foreach ($not_inserted_array as $key => $row) {
                                    echo "<tr>";
                                    foreach ($row as  $value) {
                                        if($key == 0){
                                            echo "<th>{$value}</th>";
                                        }else{
                                            echo "<td>{$value}</td>";
                                        }
                                    }
                                    echo "</tr>";
                                }
                            ?>
                            </table>
                        </div>
                    </div>
                    <?php } ?>

                </div>
            </div>
        </div>
        <?php include_once 'footer.php';?>
    </body>
</html>