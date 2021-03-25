<?php
include_once("common.php");
global $generalobj;

$script = "Faq";
$defaultLang = "EN";
if (isset($_SESSION['sess_lang']) && trim($_SESSION['sess_lang']) != "") {
    $defaultLang = $_SESSION['sess_lang'];
}
$meta = $generalobj->getStaticPage(1, $defaultLang);
$meta_arr = $generalobj->getsettingSeo(3);
$iFaqcategoryId = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$Type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
//echo $iFaqcategoryId;die;
//Added By HJ On 17-10-2019 For Solved Isssue 418 Start
$db_faqs = $obj->MySQLSelect("SELECT FQ.iFaqId,FQ.iFaqcategoryId,FQ.iDisplayOrder,FQ.vTitle_" . $defaultLang . " as Que ,FQ.tAnswer_" . $defaultLang . " as Ans FROM faqs FQ INNER JOIN faq_categories FC ON FQ.iFaqcategoryId=FC.iUniqueId WHERE FQ.eStatus='Active' AND FC.eStatus='Active' GROUP BY FQ.iFaqId ORDER BY FC.iDisplayOrder");
//echo "<pre>";print_r($db_faqs);die;
$faqDataArr = $db_faq_categories = $faqCatIdArr = $finalFaqDataArr = array();
$faqCatId = $faqCatIds = "";
for ($f = 0; $f < count($db_faqs); $f++) {
    if ($iFaqcategoryId == "") {
        if ($faqCatId == "" || $faqCatId == $db_faqs[$f]['iFaqcategoryId']) {
            $faqCatId = $db_faqs[$f]['iFaqcategoryId'];
            $faqDataArr[] = $db_faqs[$f];
        }
    } else {
        if ($iFaqcategoryId == $db_faqs[$f]['iFaqcategoryId']) {
            if ($faqCatId == "" || $faqCatId == $db_faqs[$f]['iFaqcategoryId']) {
                $faqCatId = $db_faqs[$f]['iFaqcategoryId'];
                $faqDataArr[] = $db_faqs[$f];
            }
        }
    }
    $faqCatIds .= ",'" . $db_faqs[$f]['iFaqcategoryId'] . "'";
}
//echo "<pre>";print_r($faqDataArr);die;

if ($faqCatIds != "") {
    $faqCatIds = trim($faqCatIds, ",");
    //echo "<pre>";print_r($faqDataArr);die;
    $whereUniqueId .= "AND iUniqueId IN ($faqCatIds)";
    if ($iFaqcategoryId == "") {
        //$whereUniqueId .= "AND iUniqueId IN ($faqCatIds)";
    }
    $db_faq_categories = $obj->MySQLSelect("SELECT * FROM faq_categories WHERE vCode='" . $defaultLang . "' $whereUniqueId AND eStatus='Active' order by iDisplayOrder");
    for ($f = 0; $f < Count($db_faq_categories); $f++) {
        $faqCatIdArr[] = $db_faq_categories[$f]['iUniqueId'];
    }
}
for ($p = 0; $p < count($faqDataArr); $p++) {
    //echo $faqDataArr[$p]['iFaqcategoryId'];die;
    //echo "<pre>";print_r($faqDataArr);die;
    if (in_array($faqCatIdArr, $faqDataArr[$p]['iFaqcategoryId'])) {
        $finalFaqDataArr[] = $faqDataArr[$p];
    }
}
//Commented By HJ On 06-11-2019 For Solved Issue - 441 of Sheet Start
/*if (!in_array($faqCatIdArr, $iFaqcategoryId) && $iFaqcategoryId != "") {
    header("Location:faq");
    exit;
}*/
//Commented By HJ On 06-11-2019 For Solved Issue - 441 of Sheet End
//Added By HJ On 17-10-2019 For Solved Isssue 418 End
//echo "<pre>";print_r($faqCatIdArr);die;
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $meta_arr['meta_title']; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <?php include_once("top/validation.php"); ?>
        <script type="text/javascript" src="assets/js/script.js"></script>
    </head>
    <body>
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <?php include_once("top/header_topbar.php"); ?>
	  <?php if($generalobj->checkXThemOn() == 'Yes') { ?>
	<div class="gen-cms-page">
		<div class="gen-cms-page-inner">
	  <?php } else { ?>
            <div class="page-contant">
                <div class="page-contant-inner"><?php } ?>
                    <h2 class="header-page"><?= $langage_lbl['LBL_FAQ_TEXT']; ?></h2>
                    <div class="faq-page">
                        <div class="faq-top-part">
                            <ul>
                                <?php
                                if (count($db_faq_categories) > 0) {
                                    for ($i = 0; $i < count($db_faq_categories); $i++) {
                                        //echo "<pre>";print_r($db_faq_categories);die;
                                        ?>
                                        <li id="faqCat_<?= $db_faq_categories[$i]['iUniqueId']; ?>" <?php if (trim($Type) == trim($db_faq_categories[$i]['vTitle'])) { ?>class="Active" <?php } ?>>
                                            <a href="javascript:void(0);" onClick="getFaqs('<?= $db_faq_categories[$i]['vTitle']; ?>',<?= $db_faq_categories[$i]['iUniqueId']; ?>)"><?= $db_faq_categories[$i]['vTitle']; ?></a>
                                        </li>
                                        <?php
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="faq-bottom-part" id='cssmenu'>
                            <ul>
                                <?php
                                for ($i = 0; $i < count($faqDataArr); $i++) {
                                    //echo $faqDataArr[$i]['Que']."==".$i."<br>";
                                    //echo "<pre>";print_r($faqDataArr[$i]);die;
                                    $selTabId = $faqDataArr[$i]['iFaqcategoryId'];
                                    ?>
                                    <li class='has-sub'>
                                        <a href="#" class="faq-q">
                                            <span>
                                                <b><?= $langage_lbl['LBL_Q']; ?></b>
                                                <h3><?= $faqDataArr[$i]['Que']; ?></h3>
                                            </span>
                                        </a>
                                        <ul class="faq-ans"  style="display:none">
                                            <li id="faq_<?= $faqDataArr[$i]['iFaqId'] ?>">
                                                <span>  <?= $faqDataArr[$i]['Ans']; ?></span>
                                            </li>
                                        </ul>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <form name="faq" id="faq" action="">
                    <input type="hidden" name="id" id="iUniqueId"  value="">
                    <input type="hidden" name="type" id="CatName"  value="">
                </form>
            </div>
            <?php include_once('footer/footer_home.php'); ?>
            <div style="clear:both;"></div>
        </div>
        <?php include_once('top/footer_script.php'); ?>
        <script type="text/javascript">
            var selTabId = '<?= $selTabId; ?>';
            $("#faqCat_" + selTabId).addClass("Active");
            function FacdeQuestion(id)
            {
                if ($("#faq_" + id).is(":visible")) {
                    $("#faq_" + id).slideToggle("slow");
                } else {
                    $("#faq_" + id).slideToggle("slow");
                }
            }
            function getFaqs(cat, id)
            {
                $("#iUniqueId").val(id);
                $("#CatName").val(cat);
                document.faq.submit();
            }
        </script>
        <!-- End: Footer Script -->
    </body>
</html>
