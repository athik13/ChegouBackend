<?php 
$sql="select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
$db_lng_mst=$obj->MySQLSelect($sql);
$count_lang = count($db_lng_mst);
?>
<!-- -->
<div class="lang-part-top">
<div class="lang-part-top-inner">
  <div class="phone-part"> 
  	<span>
	  	<b><img src="assets/img/home-new/phone.png" alt="" /> +123-456-7890</b>
	  	<b><img src="assets/img/home-new/mgs.png" alt="" /> <a href="#">info@cubejek.com</a></b>
  	</span>
  </div>
  <div class="lang-part">
    <div class="special-offer-left">
      <form action="action.php" method="post" class="se-in">
        <select name="timepass" class="custom-select">
          <option>USD</option>
          <option>USD</option>
          <option>USD</option>
          <option>USD</option>
        </select>
      </form>
      <form action="action.php" method="post" class="se-in">
        <select name="timepass" class="custom-select" id="Languageids">
            <?php foreach ($db_lng_mst as $key => $value) { ?>
            <option id="<?php echo $value['vCode']; ?>" value="<?php echo $value['vCode']; ?>" <?if($_SESSION['sess_lang']==$value['vCode']) { ?> selected="selected" <?} ?> ><?php echo ucfirst(strtolower($value['vTitle'])); ?></option>
            <?php } ?>
        </select>
      </form>
    </div>
  </div>
  <div style="clear:both;"></div>
</div>
</div>
<!-- -->
