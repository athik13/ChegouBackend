<? $aboutactivetab = 'webtab-52';?>
<div id="tabs">
    <ul class="nav nav-tabs" >
        <li class="">
            <a class="pagerlink" data-toggle="tab" href="#"></a>
        </li>
        <li class="<?php if($aboutactivetab=='webtab-52') { ?> active <?php }  ?>">
            <a class="pagerlink" data-toggle="tab" href="#webtab-52">Web Page</a>
        </li>
        <li class="<?php if($aboutactivetab=='mobiletab-1') { ?> active <?php }  ?>">
            <a class="pagerlink" data-toggle="tab" href="#mobiletab-1">App Page</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="webtab-52" class="tab-pane <?php if($aboutactivetab=='webtab-52') { ?>active<?php } ?>">
            <? if ($action == 'Edit') {
                $id = $iPageId = "52";
                $sql = "SELECT * FROM " . $tbl_name . " WHERE iPageId = '" . $id . "'";
                $db_data = $obj->MySQLSelect($sql);

                $vLabel = $id;
                if (count($db_data) > 0) {
                    for ($i = 0; $i < count($db_master); $i++) {
                        foreach ($db_data as $key => $value) {
                            $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
                            $$vPageTitle = $value[$vPageTitle];
                            $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                            $$tPageDesc = $value[$tPageDesc];
                            if($cubexthemeon == 'Yes' && $iPageId==52) {
                                $pageSubtitle = $value['pageSubtitle'];
                                $pageSubtitleArr = json_decode($pageSubtitle, true);
                            }
                            $vPageName = $value['vPageName'];
                            $vTitle = $value['vTitle'];
                            $tMetaKeyword = $value['tMetaKeyword'];
                            $tMetaDescription = $value['tMetaDescription'];
                            $vImage = $value['vImage'];
                            $vImage1 = $value['vImage1'];
                            $vImage2 = $value['vImage2'];
                            $iOrderBy = $value['iOrderBy']; //added by SP for pages orderby,active/inactive functionality
                        }
                    }
                }
            } ?>
            <form method="post" action="" name="_page_form" id="_page_form"  enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $id; ?>"/>
                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                <input type="hidden" name="backlink" id="backlink" value="page.php"/>
                <div class="row">
                    <div class="col-lg-12">
                        <label>Page/Section</label>
                    </div>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" name="vPageName"  id="vPageName" value="<?= htmlspecialchars($vPageName); ?>" placeholder="Page Name">
                    </div>
                </div>
                    <? $style_v = "";
                    if (in_array($iPageId, array('29', '30','53'))) {
                        $style_v = "style = 'display:none;'";
                    }
                    if ($count_all > 0) {
                        for ($i = 0; $i < $count_all; $i++) {
                            $vCode = $db_master[$i]['vCode'];
                            $vLTitle = $db_master[$i]['vTitle'];
                            $eDefault = $db_master[$i]['eDefault'];

                            $vPageTitle = 'vPageTitle_' . $vCode;
                            $tPageDesc = 'tPageDesc_' . $vCode;

                            if($style_v=='') {
                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                            }
                            ?>
                            <div class="row" <?= $style_v ?>>
                                <div class="col-lg-12">
                                    <label>Page Title (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="<?= $vPageTitle; ?>"  id="<?= $vPageTitle; ?>" value="<?= htmlspecialchars($$vPageTitle); ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>>
                                </div>
                            </div>
                            <!--- Editor -->
                            <?php
                                $vPageSubTitleS = "vPageSubTitle_$vCode";
                                $vPageSubTitle = "vPageSubTitle[$vCode]";
                                $pagedescarr = json_decode($$tPageDesc,true);
                                $Firstdescval = $pagedescarr['FirstDesc'];
                                $Secdescval = $pagedescarr['SecDesc'];
                                $Thirddescval = $pagedescarr['ThirdDesc'];    
                                $tPageSecDesc = 'tPageSecDesc_' . $vCode;    
                                $tPageThirdDesc = 'tPageThirdDesc_' . $vCode;    
                            ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Page Sub Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="<?= $vPageSubTitle; ?>"  id="<?= $vPageSubTitleS; ?>" placeholder="<?= $vPageSubTitleS; ?> Value" <?= $required; ?>><?= $pageSubtitleArr["pageSubtitle_".$vCode]; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="row" <?= $style_v ?>>
                                    <div class="col-lg-12">
                                        <label> Page First Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDesc; ?>"  id="<?= $tPageDesc; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $Firstdescval; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="row" <?= $style_v ?>>
                                    <div class="col-lg-12">
                                        <label> Page Second Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="<?= $tPageSecDesc; ?>"  id="<?= $tPageSecDesc; ?>"  placeholder="<?= $tPageSecDesc; ?> Value" <?= $required; ?>> <?= $Secdescval; ?></textarea>
                                    </div>
                                </div>

                                <div class="row" <?= $style_v ?>>
                                    <div class="col-lg-12">
                                        <label> Page Third Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="<?= $tPageThirdDesc; ?>"  id="<?= $tPageThirdDesc; ?>"  placeholder="<?= $tPageThirdDesc; ?> Value" <?= $required; ?>> <?= $Thirddescval; ?></textarea>
                                    </div>
                                </div>

                            <?php
                        }
                    }

                    if (!in_array($iPageId, array('23', '24', '25', '26', '27','48','49','50'))) { ?>
                        <div class="row" <?= $style_v ?>>
                            <div class="col-lg-12">
                                <label>Meta Title</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vTitle"  id="vTitle" value="<?= htmlspecialchars($vTitle); ?>" placeholder="Meta Title">
                            </div>
                        </div>
                        <div class="row" <?= $style_v ?>>
                            <div class="col-lg-12">
                                <label>Meta Keyword</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="tMetaKeyword"  id="tMetaKeyword" value="<?= htmlspecialchars($tMetaKeyword); ?>" placeholder="Meta Keyword">
                            </div>
                        </div>
                        <div class="row" <?= $style_v ?>>
                            <div class="col-lg-12">
                                <label>Meta Description</label>
                            </div>
                            <div class="col-lg-6">
                                <textarea class="form-control" rows="10" name="tMetaDescription"  id="<?= $tMetaDescription; ?>"  placeholder="<?= $tMetaDescription; ?> Value" <?= $required; ?>> <?= $tMetaDescription; ?></textarea>
                            </div>
                        </div>
                        <?php
                    }

                    if (!in_array($iPageId, array('1', '2', '7', '4', '3', '6', '23', '27', '33'))) { 
                        if ($cubexthemeon == 'Yes' && in_array($iPageId, $pageidCubexImage)) { ?>
                        <br><br>
                        <?php if($iPageId!=50) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Image (Left side shown)</label>
                            </div>
                            <div class="col-lg-6">
                                <? if ($vImage != '') { ?>
                                    <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;height:100px;"></a>
                                <? } ?>
                                <input type="file" name="vImage" id="vImage" />
                            </div>
                        </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Background Image</label>
                            </div>
                            <div class="col-lg-6">
                                <? if ($vImage1 != '') { ?>
                                    <a target="_blank" href="<?= $images . $vImage1; ?>"><img src="<?= $images . $vImage1; ?>" style="width:200px;height:100px;"></a>
                                <? } ?>
                                <input type="file" name="vImage1" id="vImage1" />
                            </div>
                        </div>
                    <?php } else if($cubexthemeon == 'Yes' && $iPageId==52) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>First Image (Left side shown)</label>
                                </div>
                                <div class="col-lg-6">
                                    <? if ($vImage != '') { ?>
                                        <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;height:100px;"></a>
                                    <? } ?>
                                    <input type="file" name="vImage" id="vImageaaa2" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Second Image (Right side shown)</label>
                                </div>
                                <div class="col-lg-6">
                                    <? if ($vImage1 != '') { ?>
                                        <a target="_blank" href="<?= $images . $vImage1; ?>"><img src="<?= $images . $vImage1; ?>" style="width:200px;height:100px;"></a>
                                    <? } ?>
                                    <input type="file" name="vImage1" id="vImagea1" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Third Image (Left side shown)</label>
                                </div>
                                <div class="col-lg-6">
                                    <? if ($vImage2 != '') { ?>
                                        <a target="_blank" href="<?= $images . $vImage2; ?>"><img src="<?= $images . $vImage2; ?>" style="width:200px;height:100px;"></a>
                                    <? } ?>
                                    <input type="file" name="vImage2" id="vImagea2" />
                                </div>
                            </div>
                    <?php } else {
                            $style_vimage = "";
                            if (!in_array($iPageId, array('53'))) {
                                $style_vimage = "style = 'display:none;'";
                            }
                            ?>
                            <div class="row" style="<?= $style_vimage ?>">
                                <div class="col-lg-12">
                                    <label>Image</label>
                                </div>
                                <div class="col-lg-6">
                                    <? if ($vImage != '') { ?>
                                        <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;height:100px;"></a>
                                    <? } ?>
                                    <input type="file" name="vImage" id="vImage" />
                                </div>
                            </div>
                    <?php } ?>                                        
                    <?php } if($iPageId!='48' && $iPageId != '49' && $iPageId != '50') { ?>
                    <!-- added by SP for pages orderby,active/inactive functionality  -->
                    <div class="row" <?= $style_v ?>>
                        <div class="col-lg-12">
                            <label>Display Order</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="number" class="form-control" name="iOrderBy" id="iOrderBy" value="<?= $iOrderBy; ?>" placeholder="Page displayed according to this number" min="0">
                        </div>
                    </div>  
                    <?php } ?>        
                            
                    <div class="row">
                        <div class="col-lg-12">
                            <?php if (($action == 'Edit' && $userObj->hasPermission('edit-pages')) || ($action == 'Add' && $userObj->hasPermission('create-pages'))) { ?>
                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Static Page">
                                <input type="reset" value="Reset" class="btn btn-default">
                            <?php } ?>
                            <!-- <a href="javascript:void(0);" onclick="reset_form('_page_form');" class="btn btn-default">Reset</a> -->
                            <a href="page.php" class="btn btn-default back_link">Cancel</a>
                        </div>
                    </div>
            </form>
        </div>

        <div id="mobiletab-1" class="tab-pane <?php if($aboutactivetab=='mobiletab-1') { ?>active<?php }?>">
            <? if ($action == 'Edit') {
                $id = $iPageId = "1";
                $sql = "SELECT * FROM " . $tbl_name . " WHERE iPageId = '" . $id . "'";
                $db_data = $obj->MySQLSelect($sql);

                $vLabel = $id;
                if (count($db_data) > 0) {
                    for ($i = 0; $i < count($db_master); $i++) {
                        foreach ($db_data as $key => $value) {
                            $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
                            $$vPageTitle = $value[$vPageTitle];
                            $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                            $$tPageDesc = $value[$tPageDesc];
                            $vPageName = $value['vPageName'];
                            $vTitle = $value['vTitle'];
                            $tMetaKeyword = $value['tMetaKeyword'];
                            $tMetaDescription = $value['tMetaDescription'];
                            $vImage = $value['vImage'];
                            $vImage1 = $value['vImage1'];
                            $vImage2 = $value['vImage2'];
                            $iOrderBy = $value['iOrderBy']; //added by SP for pages orderby,active/inactive functionality
                        }
                    }
                }
            } ?>
            <form method="post" action="" name="_page_form" id="_page_form"  enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $id; ?>"/>
                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                <input type="hidden" name="backlink" id="backlink" value="page.php"/>
                <div class="row">
                    <div class="col-lg-12">
                        <label>Page/Section</label>
                    </div>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" name="vPageName_<?= $id?>"  id="vPageName_<?= $id?>" value="<?= htmlspecialchars($vPageName); ?>" placeholder="Page Name">
                    </div>
                </div>
                    <? $style_v = "";
                    if (in_array($iPageId, array('29', '30','53'))) {
                        $style_v = "style = 'display:none;'";
                    }
                    if ($count_all > 0) {
                        for ($i = 0; $i < $count_all; $i++) {
                            $vCode = $db_master[$i]['vCode'];
                            $vLTitle = $db_master[$i]['vTitle'];
                            $eDefault = $db_master[$i]['eDefault'];

                            $vPageTitle = 'vPageTitle_' . $vCode;
                            $tPageDesc = 'tPageDesc_' . $vCode;

                            if($style_v=='') {
                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                            }
                            ?>
                            <div class="row" <?= $style_v ?>>
                                <div class="col-lg-12">
                                    <label>Page Title (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="<?= $vPageTitle.'_'.$id; ?>"  id="<?= $vPageTitle.'_'.$id; ?>" value="<?= htmlspecialchars($$vPageTitle); ?>" placeholder="<?= $vPageTitle; ?> Value" <?= $required; ?>>
                                </div>
                            </div>
                            <!--- Editor -->
                            <div class="row" <?= $style_v ?>>
                                <div class="col-lg-12">
                                    <label> Page Description (<?= $vLTitle; ?>) <?= $required_msg; ?></label>
                                </div>
                                <div class="col-lg-12">
                                    <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDesc.'_'.$id; ?>"  id="<?= $tPageDesc.'_'.$id; ?>"  placeholder="<?= $tPageDesc; ?> Value" <?= $required; ?>> <?= $$tPageDesc; ?></textarea>
                                </div>
                            </div>
                            <!--- Editor -->
                            <? 
                        }
                    }

                    if (!in_array($iPageId, array('23', '24', '25', '26', '27','48','49','50'))) {
                        ?>
                        <div class="row" <?= $style_v ?>>
                            <div class="col-lg-12">
                                <label>Meta Title</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vTitle_<?= $id?>"  id="vTitle_<?= $id?>" value="<?= htmlspecialchars($vTitle); ?>" placeholder="Meta Title">
                            </div>
                        </div>
                        <div class="row" <?= $style_v ?>>
                            <div class="col-lg-12">
                                <label>Meta Keyword</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="tMetaKeyword_<?= $id?>"  id="tMetaKeyword_<?= $id?>" value="<?= htmlspecialchars($tMetaKeyword); ?>" placeholder="Meta Keyword">
                            </div>
                        </div>
                        <div class="row" <?= $style_v ?>>
                            <div class="col-lg-12">
                                <label>Meta Description</label>
                            </div>
                            <div class="col-lg-6">
                                <textarea class="form-control" rows="10" name="tMetaDescription_<?= $id?>"  id="<?= $tMetaDescription."_".$id; ?>"  placeholder="<?= $tMetaDescription; ?> Value" <?= $required; ?>> <?= $tMetaDescription; ?></textarea>
                            </div>
                        </div>
                        <?php
                    } 
                    if($iPageId!='48' && $iPageId != '49' && $iPageId != '50') { ?>
                    <!-- added by SP for pages orderby,active/inactive functionality  -->
                    <div class="row" <?= $style_v ?>>
                        <div class="col-lg-12">
                            <label>Display Order</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="number" class="form-control" name="iOrderBy_<?= $id?>" id="iOrderBy_<?= $id?>" value="<?= $iOrderBy; ?>" placeholder="Page displayed according to this number" min="0">
                        </div>
                    </div>  
                    <?php } ?>        
                            
                    <div class="row">
                        <div class="col-lg-12">
                            <?php if (($action == 'Edit' && $userObj->hasPermission('edit-pages')) || ($action == 'Add' && $userObj->hasPermission('create-pages'))) { ?>
                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Static Page">
                                <input type="reset" value="Reset" class="btn btn-default">
                            <?php } ?>
                            <a href="page.php" class="btn btn-default back_link">Cancel</a>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>
<script>

/*var somethingChanged = false;
$('.tab-pane.active input').change(function() { 
    somethingChanged = true;
    if(somethingChanged == true){
        $( "a.pagerlink" ).click(function() {
          somethingChanged = false;
          confirm("Press a button!");
        });
    }
});
alert(somethingChanged);*/
/*var tabValue = $("#tabs .nav.nav-tabs li.active a").attr("href");
alert(tabValue);*/
/*$('a.pagerlink').click(function() { 
    var tabValue = $(this).attr('href');
    var slug = tabValue.split('-').pop();
    passVariable(slug);
});
function passVariable(slug){
    // get the current url and append variable
    var url = document.location.href;
    // to prevent looping
    var exists = document.location.href.indexOf('&tabid=');
    var newurl = replaceUrlParam(url,"tabid",slug);
    console.log(newurl);
    window.location = newurl;
    if(exists < 0){
          // redirect passing variable
    }
}
function replaceUrlParam(url, paramName, paramValue)
{
    if (paramValue == null) {
        paramValue = '';
    }
    var pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
    if (url.search(pattern)>=0) {
        return url.replace(pattern,'$1' + paramValue + '$2');
    }
    url = url.replace(/[?#]$/,'');
    return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
}
*/
/*
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};*/
</script>