<?php
echo $response = json_encode(file_get_contents($_REQUEST['url']));
exit;
?>