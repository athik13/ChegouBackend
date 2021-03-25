<?php
include_once('common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

$generalobj->check_member_login();

$iMemberId = $_SESSION['sess_iCompanyId'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action == 'photo') {
    if (isset($_POST['img_path'])) {
        $img_path = $_POST['img_path'];
    }
    $temp_gallery = $img_path . '/';
    $image_object = $_FILES['photo']['tmp_name'];
    $image_name = $_FILES['photo']['name'];

    if( empty($image_name)) {
        $image_name = $_POST['driver_doc_hidden']; 
    }

    if ($image_name == "" || $image_name == "NONE") {
        $var_msg = $langage_lbl['LBL_DOC_UPLOAD_ERROR_'];
        header("location:dashboard.php?success=0&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
        exit;
    }

    if ($image_name != "" || $image_name != "NONE") {

        if ($_SESSION['sess_user'] == 'driver') {
            $check_file_query = "select iDriverId,vImage from register_driver where iDriverId=" . $_SESSION['sess_iUserId'];
        } else if ($_SESSION['sess_user'] == 'company') {
            $check_file_query = "select iCompanyId,vImage from company where iCompanyId=" . $_SESSION['sess_iUserId'];
        }
        $check_file = $obj->sql_query($check_file_query);
        $check_file['vImage'] = $img_path . '/' . $_SESSION['sess_iUserId'] . '/' . $check_file[0]['vImage'];

        if ($check_file['vImage'] != '' && file_exists($check_file['vImage'])) {
            unlink($img_path . '/' . $_SESSION['sess_iUserId'] . '/' . $check_file[0]['vImage']);
            unlink($img_path . '/' . $_SESSION['sess_iUserId'] . '/1_' . $check_file[0]['vImage']);
            unlink($img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $check_file[0]['vImage']);
            unlink($img_path . '/' . $_SESSION['sess_iUserId'] . '/3_' . $check_file[0]['vImage']);
        }
        $filecheck = basename($_FILES['photo']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
            $flag_error = 1;
            $var_msg = $langage_lbl['LBL_UPLOAD_IMG_ERROR'];
        }

        if ($flag_error == 1) {
            $generalobj->getPostForm($_POST, $var_msg, "dashboard.php?success=0&var_msg=" . $var_msg);
            exit;
        } else {
            if ($_SESSION['sess_user'] == 'driver') {
                $Photo_Gallery_folder = $img_path . '/' . $_SESSION['sess_iUserId'] . '/';
            }
            if ($_SESSION['sess_user'] == 'company') {
                $Photo_Gallery_folder = $img_path . '/' . $_SESSION['sess_iUserId'] . '/';
            }
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }

            $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
            if ($img1 != '') {
                if (is_file($Photo_Gallery_folder . $img1)) {
                    include_once(TPATH_CLASS . "/SimpleImage.class.php");
                    $img = new SimpleImage();
                    list($width, $height, $type, $attr) = getimagesize($Photo_Gallery_folder . $img1);
                    if ($width < $height) {
                        $final_width = $width;
                    } else {
                        $final_width = $height;
                    }
                    $img->load($Photo_Gallery_folder . $img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $img1);

                    $img1 = $generalobj->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                }
            }
            $vImage = $img1;
            $var_msg = "Profile image uploaded successfully";
            if ($_SESSION['sess_user'] == 'driver') {
                $tbl = 'register_driver';
                $where = " WHERE `iDriverId` = '" . $_SESSION['sess_iUserId'] . "'";
            }
            if ($_SESSION['sess_user'] == 'company') {
                $tbl = 'company';
                $where = " WHERE `iCompanyId` = '" . $_SESSION['sess_iUserId'] . "'";
            }

            $q = "UPDATE ";


            $query = $q . " `" . $tbl . "` SET 	
	       `vImage` = '" . $vImage . "'
	       " . $where;
            $obj->sql_query($query);
            header("location:dashboard.php?success=1&var_msg=" . $var_msg);
            exit;
        }
    }
}

?>