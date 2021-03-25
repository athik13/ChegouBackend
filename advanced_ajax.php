<?php 
session_start();
$language =  $_SESSION['sess_lang']; 
$searching_string = $_REQUEST['query_text'];
$session_token = "Passenger_".(rand(10,100))."_". (rand(1000000000000,9999999999999));
$array = array(
    'language_code' => $language,
    'search_query' => $searching_string,
    'session_token' => $session_token
);
echo json_encode($array);
?>


