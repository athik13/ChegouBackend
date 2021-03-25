<?php



namespace Admin\library;



/**

 * 

 */

class User {



    private $is_login;

    private $permissions = [];

    private $roles = [];

    private $debug = false;

    public $id;

    public $role_id;

    public $locations;



    function __construct() {

        $this->checkSession();

        $this->getRoles();

        $this->getPermission();

    }



    public function isLogin($redirect = false) {



        if (!$this->is_login && $redirect == true) {

            $this->redirect('index.php');

        }

        return $this->is_login;

    }



    public function redirect($path = "dashboard.php") {

        global $tconfig;

        ob_get_clean();

        if (ONLYDELIVERALL == "Yes") {

            $path = "store-dashboard.php";

        }

        if (!$this->is_login) {

            $path = "index.php";

            if(isset($_SERVER['REQUEST_URI'])){

                $_SESSION['login_redirect_url'] = $_SERVER['REQUEST_URI'];

            }

        }

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'; //check file is from ajax then session is not set bc it is not redirect after login

        if (!$isAjax){

            $_SESSION['login_redirect_url'] = $_SERVER['REQUEST_URI']; //added by SP for redirection on admin after login on 15-7-2019, here put bc when page open from admin side and logout then open same link

        }

        header("Location:" . $tconfig["tsite_url_main_admin"] . $path);

        exit;

    }



    public function hasRole($role) {





        //return true;

        return $this->hasRoles($role);

    }



    public function hasPermission($permission_name) {



        if ($this->debug == true) {

            $bool = $this->hasPermissions($permission_name) ? "true" : "false";

            echo "<pre> {$permission_name} : {$bool} </pre>";

        }

        return $this->hasPermissions($permission_name);

    }



    public function hasRoles($roles) {



        if (!is_array($roles)) {

            $roles = [$roles];

        }



        $has_role = false;



        foreach ($roles as $key => $role) {

            if (is_numeric($role) && in_array($role, array_keys($this->roles))) {

                $has_role = true;

            } else if (in_array($role, $this->roles)) {

                $has_role = true;

            }

        }



        return $has_role;

    }



    function errorMessage($message = "You are not authorized.") {

        

    }



    public function hasPermissions($permission_name) {

        //return true;

        if (!is_array($permission_name)) {

            $permission_name = [$permission_name];

        }

        $hasPermission = false;

        foreach ($permission_name as $key => $role) {

            if (in_array($role, $this->permissions)) {

                $hasPermission = true;

            }

        }

        return $hasPermission;

    }



    private function checkSession() {

        if (isset($_SESSION['sess_iAdminUserId']) && !empty($_SESSION['sess_iAdminUserId'])) {

            $this->id = $_SESSION['sess_iAdminUserId'];

            $this->is_login = true;

            $this->locations = \Models\Administrator::find($this->id)->locations->pluck('iLocationId')->toArray();

        } else {

            $this->is_login = false;

        }

        $this->role_id = isset($_SESSION['sess_iGroupId']) ? $_SESSION['sess_iGroupId'] : 0;



        if ($this->role_id == 0) {

            $this->is_login = false;

        }

    }



    public function getLocations() {

        return $this->locations;

    }



    /* private function getUser(){

      $sql = "SELECT * FROM administrators WHERE iAdminId=".$this->id."";

      $row = $obj->MySQLSelect($sql);

      return $row[0];

      } */



    private function getRoles() {

        global $obj;

        $sql = "SELECT ag.iGroupId, ag.vGroup FROM administrators as a LEFT JOIN admin_groups as ag ON a.iGroupId = ag.iGroupId where a.iGroupId = {$this->role_id}";



        $row = $obj->MySQLSelect($sql);



        if ($row) {

            foreach ($row as $key => $value) {

                $this->roles[$value['iGroupId']] = $value['vGroup'];

            }

        }

    }



    private function getPermission() {

        global $obj,$generalobj;

        $sql = "

			SELECT ap.id, ap.permission_name, ap.eFor

			FROM 

				admin_group_permission as agp 

				LEFT JOIN admin_permissions as ap ON ap.id = agp.permission_id 

			WHERE 

				agp.group_id = {$this->role_id}";



        $permissions = $obj->MySQLSelect($sql);

        //Added By HJ As Per Disucss with KS On 06-12-2019 For Check Uberx Service Status Start 

        $uberxService = $generalobj->CheckUfxServiceAvailable();

        $isDeliveryModuleAvailable  = isDeliveryModuleAvailable();
		
        $IS_FLY_MODULE_AVAIL  = checkFlyStationsModule();

        $newTmpArr = array();

        for ($i = 0; $i < count($permissions); $i++) {

            if ($uberxService == "No" && $permissions[$i]['eFor'] == "UberX") {
                continue;
            }

            if($IS_FLY_MODULE_AVAIL == false && $permissions[$i]['eFor'] == "Fly"){
                continue;
            }  

            $newTmpArr[] = $permissions[$i];

        }

        $permissions = array_values($newTmpArr);

        //echo "<pre>";print_r($permissions);die;

        //Added By HJ As Per Disucss with KS On 06-12-2019 For Check Uberx Service Status End

        if ($permissions) {

            $this->permissions = array_map(function($item) {

                return $item['permission_name'];

            }, $permissions);

        }

    }



}

