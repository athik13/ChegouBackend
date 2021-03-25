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
/* error_reporting(0);
  $error = $_REQUEST['error'];
  $success = $_REQUEST['success'];
  $message1 = $_REQUEST['message'];
  $status = $_REQUEST['status']; */
?> 

<div class="what-geting">
    <img src="assets/img/error.svg" alt="Failed">
    <strong>Failed</strong>
</div>  
