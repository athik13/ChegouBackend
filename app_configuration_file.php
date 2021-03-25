<?php
	
include_once('common.php');

if (isset($_POST['submit'])) {
    $postData = trim($_POST['appdata']);
    if ($postData != "") {
        //echo "<pre>";print_r($_POST);die;
        $url = $tconfig["tsite_url"]."webservice_shark.php?" . $postData;
        echo $url;
        die;
    }
}
?>
<html>
    <head>
        <title>App Configuration Setting</title>
    </head>
    <body>
        <h1>Post Data</h1>
        <form action="webservice_shark.php" method="post">
            <textarea required="" style="margin: 0px; width: 2083px; height: 468px;" id="appdata" name="APP_CONFIG_PARAMS_PACKAGE"></textarea><br><br>
            <input style="height: 50px;width: 100;background:wheat" type="submit" name="submit" value="Next >>">
        </form>
    </body>
</html>