<?php
#https://www.zorowtech.com/developers/tutorial/#
require('zorowpesa.php');
$mpesa = new ZorowPesa("CC4F0E40028B", MPESA_DEMO);
$mpesa->loadResources();

if($_POST){
  $rest = $mpesa->setupPayment("111","1245", 121, "KES", "http://cubetaxiplus.bbcsproducts.com/assets/libraries/MPesa/success.php");
  echo $rest;exit; 
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <title></title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://www.zorowtech.com/mpesaapi/css/mpesaapi.css">
  <script type="text/javascript" src="https://www.zorowtech.com/mpesaapi/js/mpesaapi.js"></script>
  <script type="text/javascript" src="https://www.zorowtech.com/mpesaapi/js/config.js"></script>	

  </head>
  <body>
    <div class="col-md-12">
    
      
     <form name="frmmpesa" id="frmmpesa" method="post" action=""> 
      <input type="hidden" id="uid" value="12345">
      <input type="hidden" id="pid" value="12345">
      <input type="hidden" id="cost" value="10">
      <input type="hidden" id="apikey" value="CC4F0E40028B">
      <input type="hidden" id="paymentcode" value="821AB9093CA2">
      <input type="hidden" id="status" value="1">
      <input type="hidden" id="callback" value="http://cubetaxiplus.bbcsproducts.com/assets/libraries/MPesa/success.php">
    <br>
    
    <input type="button" value="PAY WITH MPESA" id="mpesabtn" class="mpesabtn">
     </form>
     
    </div>
  </body>
</html>

