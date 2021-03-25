<?php
include_once('../common.php');
include_once('../assets/libraries/class.general.php');
define("CLIENT_ID", $LINKEDIN_APP_ID);
define("CLIENT_SECRET", $LINKEDIN_APP_SECRET_KEY);
define("REDIRECT_URI", $tconfig['tsite_url'] . 'linkedin-login/linkedin-app.php');
define("SCOPE", 'r_emailaddress r_liteprofile w_member_social');

include_once "linkedin_outh2.php";

$oauth_problem = isset($_GET['oauth_problem']) ? $_GET['oauth_problem'] : '';

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
if ($oauth_problem == "user_refused") {
    $link = $tconfig['tsite_url'] . 'linkedin-login/linkedin-app.php?status=2';
    header("Location:" . $link);
    exit;
}

if ($status == "1" || $status == "0" || $status == "2") {
    if ($status == "1") {
        $_SESSION = array();
        // Unset token and user data from session
        unset($_SESSION['oauth_status']);
        unset($_SESSION['userData']);
        unset($_SESSION['oauth_access_token']);
        unset($_SESSION['oauth_verifier']);

// Destroy entire session
        session_destroy();
//header("Location: ../index.php");
//exit;
        /*unset($_SESSION["member_id"]);
        unset($_SESSION);
        session_destroy();
        $_SESSION['oauth_verifier']  = '';
        $_SESSION['oauth_access_token'] = '';
        //print_R($_SESSION); print_R($_REQUEST); exit;
        $_REQUEST['data'] = '';
        $_REQUEST['status'] = '';
        $_GET["action"] = '';
        header("Location: " . REDIRECT_URI);
            exit;*/
    }
} else {
    
    if (empty($_GET["action"])) {
        
        $linkedin = new LinkedIn(CLIENT_ID, CLIENT_SECRET, REDIRECT_URI);
        $linkedin->getRequestToken(); 
        $_SESSION['requestToken'] = serialize($linkedin->request_token); 
        
        if(!empty($_GET['code'])) { 
		$data = $linkedin->linkedin_auth_get();  
	} else {
		$data = $linkedin->getAuthorizationCode();
	} 
        if (isset($_REQUEST['oauth_verifier'])){
            $_SESSION['oauth_verifier']     = $_REQUEST['oauth_verifier'];
            $linkedin->request_token    =   unserialize($_SESSION['requestToken']);
            $linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
            $linkedin->getAccessToken($_SESSION['oauth_verifier']);				 
            $_SESSION['oauth_access_token'] = serialize($linkedin->access_token);
            header("Location: " . REDIRECT_URI);
            exit;
        } else {  
            $linkedin->request_token    =   unserialize($_SESSION['requestToken']);
            $linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
            $linkedin->access_token     =   unserialize($_SESSION['oauth_access_token']);
        }  
                                  
        if ($data) { 
            $data['firstName'] = $data['localizedFirstName'];
            $data['lastName'] = $data['localizedLastName'];
            $data['pictureUrls'] = $data['profile_pic'];
            $data['emailAddress'] = $data['email'];
            $data['id'] = strtolower($data['id']);
            
            $data = json_encode($data, TRUE);
            $_SESSION = array();
            unset($_SESSION);
            session_destroy();

            if (!empty($data)) {
                $link = $tconfig['tsite_url'] . 'linkedin-login/linkedin-app.php?status=1&data=' . urlencode($data);
            } else {
                $link = $tconfig['tsite_url'] . 'linkedin-login/linkedin-app.php?status=0';
            }
            header("Location:" . $link);
            exit;
        } else {
            //$error = $client->error;
        }
    } else {
        $_SESSION = array();
        unset($_SESSION);
        session_destroy();
    }
}
// http://192.168.1.131/cubejekdev/linkedin-login/linkedin-app.php
?>