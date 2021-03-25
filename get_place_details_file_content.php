<?php
// get place data using place id from google
echo $response = json_encode(file_get_contents($_REQUEST['url']));
exit;
?>