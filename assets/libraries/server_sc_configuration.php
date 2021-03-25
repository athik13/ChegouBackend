<?php

if (strpos($_SERVER["HTTP_HOST"], 'bbcsproducts.net') !== false) {
    $IS_INHOUSE_DOMAINS = true;

    $tconfig["tsite_sc_protocol"] = "http://"; // Protocol to access Socket Cluster.
    $tconfig["tsite_sc_host"] = "67.205.155.33"; // In which socket cluster is installed.
    $tconfig["tsite_host_sc_port"] = "1331"; // In which socket cluster is running on.
    $tconfig["tsite_host_sc_path"] = "/socketcluster/"; // This path should not change.

    /* Yalgaar settings url */
    $tconfig["tsite_yalgaar_url"] = "http://142.93.244.42:8081";
    /* Yalgaar settings url */

    //google api replacement start
    $tconfig["tsite_gmap_replacement_protocol"] = "http://";
    $tconfig["tsite_gmap_replacement_host"] = "www.bbcsproducts.net";
    $tconfig["tsite_host_gmap_replacement_port"] = "3001";
    $tconfig["tsite_host_gmap_replacement_path"] = "/";
    //google api replacement end
}else if ($_SERVER["HTTP_HOST"] == "192.168.1.131") {
	$IS_INHOUSE_DOMAINS = true;
	
	$tconfig["tsite_sc_protocol"] = "http://"; // Protocol to access Socket Cluster.
	$tconfig["tsite_sc_host"] = "192.168.1.131"; // In which socket cluster is installed.
	$tconfig["tsite_host_sc_port"] = "1033"; // In which socket cluster is running on.
	$tconfig["tsite_host_sc_path"] = "/socketcluster/"; // This path should not change.

	/* Yalgaar settings url */
	$tconfig["tsite_yalgaar_url"] = "http://142.93.244.42:8081";
	/* Yalgaar settings url */

	//google api replacement start
	$tconfig["tsite_gmap_replacement_protocol"] = "http://";
	$tconfig["tsite_gmap_replacement_host"] = "192.168.1.131";
	$tconfig["tsite_host_gmap_replacement_port"] = "3001";
	$tconfig["tsite_host_gmap_replacement_path"] = "/";
	//google api replacement end
}else if ($_SERVER["HTTP_HOST"] == "192.168.1.151") {
	$IS_INHOUSE_DOMAINS = true;
	
	$tconfig["tsite_sc_protocol"] = "http://"; // Protocol to access Socket Cluster.
	$tconfig["tsite_sc_host"] = "192.168.1.151"; // In which socket cluster is installed.
	$tconfig["tsite_host_sc_port"] = "1033"; // In which socket cluster is running on.
	$tconfig["tsite_host_sc_path"] = "/socketcluster/"; // This path should not change.

	/* Yalgaar settings url */
	$tconfig["tsite_yalgaar_url"] = "http://142.93.244.42:8081";
	/* Yalgaar settings url */

	//google api replacement start
	$tconfig["tsite_gmap_replacement_protocol"] = "http://";
	$tconfig["tsite_gmap_replacement_host"] = "192.168.1.151";
	$tconfig["tsite_host_gmap_replacement_port"] = "3001";
	$tconfig["tsite_host_gmap_replacement_path"] = "/";
	//google api replacement end
} else if ($_SERVER["HTTP_HOST"] == "192.168.1.141") {
	$tconfig["tsite_sc_protocol"] = "http://"; // Protocol to access Socket Cluster.
	$tconfig["tsite_sc_host"] = "192.168.1.141"; // In which socket cluster is installed.
	$tconfig["tsite_host_sc_port"] = "7822"; // In which socket cluster is running on.
	$tconfig["tsite_host_sc_path"] = "/socketcluster/"; // This path should not change.

	/* Yalgaar settings url */
	$tconfig["tsite_yalgaar_url"] = "http://192.168.1.141:7822";
	/* Yalgaar settings url */

	//google api replacement start
	$tconfig["tsite_gmap_replacement_protocol"] = "http://";
	$tconfig["tsite_gmap_replacement_host"] = "192.168.1.141";
	$tconfig["tsite_host_gmap_replacement_port"] = "3001";
	$tconfig["tsite_host_gmap_replacement_path"] = "/";
	//google api replacement end
} else if (strpos($_SERVER["HTTP_HOST"], 'webprojectsdemo.com') !== false) {
	
	$IS_INHOUSE_DOMAINS = false;
	$tconfig["tsite_sc_protocol"] = "http://"; // Protocol to access Socket Cluster.
	$tconfig["tsite_sc_host"] = "173.231.205.149"; // In which socket cluster is installed.
	$tconfig["tsite_host_sc_port"] = "1522"; // In which socket cluster is running on.
	$tconfig["tsite_host_sc_path"] = "/socketcluster/"; // This path should not change.
	/* Yalgaar settings url */
	$tconfig["tsite_yalgaar_url"] = "http://142.93.244.42:8081";
	/* Yalgaar settings url */
	//google api replacement start
	$tconfig["tsite_gmap_replacement_protocol"] = "http://";
	$tconfig["tsite_gmap_replacement_host"] = "173.231.205.149";
	$tconfig["tsite_host_gmap_replacement_port"] = "3001";
	$tconfig["tsite_host_gmap_replacement_path"] = "/";
	
} else if (strpos($_SERVER["HTTP_HOST"], 'mobileappsdemo.com') !== false) {
	
	$IS_INHOUSE_DOMAINS = false;
	$tconfig["tsite_sc_protocol"] = "http://"; // Protocol to access Socket Cluster.
	$tconfig["tsite_sc_host"] = "142.93.244.42"; // In which socket cluster is installed.
	$tconfig["tsite_host_sc_port"] = "8000"; // In which socket cluster is running on.
	$tconfig["tsite_host_sc_path"] = "/socketcluster/"; // This path should not change.
	/* Yalgaar settings url */
	$tconfig["tsite_yalgaar_url"] = "http://142.93.244.42:8081";
	/* Yalgaar settings url */
	//google api replacement start
	$tconfig["tsite_gmap_replacement_protocol"] = "http://";
	$tconfig["tsite_gmap_replacement_host"] = "173.231.205.149";
	$tconfig["tsite_host_gmap_replacement_port"] = "3001";
	$tconfig["tsite_host_gmap_replacement_path"] = "/";
	
}

?>