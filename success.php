<style>
    @import url('https://fonts.googleapis.com/css?family=Montserrat:100,300,400,500,600,700&display=swap');
    .what-geting {
        min-height: 100%;
        min-width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .what-geting img {
        width: 150px;
    }
    .what-geting strong {
        font-size: 40px;
        text-align: center;
        margin: 30px 0 0 0;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
    }
    body {
        margin:0;
    }
</style>
<?php
include_once("common.php");
   $success = (isset($_REQUEST['success'])) ? $_REQUEST['success'] : '0';    // Facebook,Twitter,Google
   if($success == 1){ 
    echo json_encode($_REQUEST);  exit;
     //echo "<pre>";print_r($_REQUEST);exit;
   }else{            
    echo json_encode($_REQUEST);  exit;
     //echo "<pre>";print_r($_REQUEST);exit;
   }
?> 

<div class="what-geting">
    <img src="assets/img/checked.svg" alt="Success">
    <strong>Success</strong>
</div>  
