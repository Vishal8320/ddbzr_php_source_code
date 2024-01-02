<?php
                require_once(__DIR__ . '/states.php');
                
                
                function getSettings() {
                    $querySettings = "SELECT * from `settings`";
                    return $querySettings;
                }
                function menu($user) {
                    global $TMPL, $LNG, $CONF, $db, $settings, $plugins;
                
                    $admin_url = (isset($_SESSION['is_admin']) ? '<a href="'.$CONF['url'].'/index.php?a=global_admin" rel="loadpage"><div class="menu_btn" id="admin_btn" title="'.$LNG['Login or Register'].'"><img src="'.$CONF['url'].'/'.$CONF['theme_url'].'/images/icons/admin.png"></div></a>' : '');
                  if(isset($_SESSION['page'])){
                        return '<div class="adjust-menus menu-image" style="margin:10px 20px 0 auto"><b>Do not skip any form fields. Go with Step by Step</b></div>';
                   }elseif($user !== false) {
                        $skin = new skin('shared/menu'); 
                        $menu = '';
                        $TMPL_old = $TMPL;
                        $TMPL = array();
                
                       // $TMPL['realname'] = realName($user['username'], $user['first_name'], $user['last_name']);
                        $TMPL['avatar'] = permalink($CONF['url'].'/image.php?t=mm&w=50&h=50&src='.$user['profile_pic']);
                        $TMPL['username'] = $user['uname'];
                        $TMPL['url'] = $CONF['url'];
                        $TMPL['theme_url'] = $CONF['theme_url'];
                
                       /**
                        * Array Map
                        * array => { url, name, dynamic load, class type}
                        */

                        $links = array(
                                       
                                        array('list',                   $LNG['list'],  2,0),
                                        array('list&b=add_milk',        $LNG['use_list'],  3,0),
                                        array('settings',               $LNG['settings'],       1, 0),
                                        array('home&b=logout',          $LNG['logout'],         0, 0));

                        $TMPL['menu_home'] = permalink($CONF['url'].'/index.php?a=home');
                        $TMPL['menu_add_user'] = permalink($CONF['url'].'/index.php?a=add_user');
                        $TMPL['menu_manage'] = permalink($CONF['url'].'/index.php?a=manage_user');
                        $TMPL['menu_calculate'] = permalink($CONF['url'].'/index.php?a=calculate');

                        $TMPL['links'] = $divider = '';
                        //  echo "<pre>";
                        
                        foreach($links as $element => $value) {
                            $color_class = ($value[2] == 2 ? 'color-blue' : ($value[2] == 0 ? 'color-red' : ($value[2] == 3 ? 'color-blue_shadow' : null)));

                            if($value) {
                                $TMPL['links'] .= $divider.'<a href="'.permalink($CONF['url'].'/index.php?a='.$value[0]).'" '.($value[2] ? ' rel="loadpage"' : '').'><div class="menu-dd-row '.$color_class.(($value[3] == 1) ? ' menu-dd-extra' : '').(($value[3] == 2) ? ' menu-dd-mobile' : '').'">'.$value[1].'</div></a>';
                                $divider = '<div class="menu-divider '.(($value[3] == 2) ? ' menu-dd-mobile' : '').'"></div>';
                            }
                        }
                        // die;
                        $TMPL['user_login'] = $admin_url;
                
                        $menu = $skin->make();
                        $TMPL = $TMPL_old; unset($TMPL_old);
                        return $menu;
                    }else{
                        // Else show the LogIn Register button
                        return '<a href="'.permalink($CONF['url'].'/index.php?a=login').'" rel="loadpage" title="'.$LNG['connect'].'"><div class="adjust-menus menu-image"><div class="topbar-button">'.$LNG['connect'].'</div></div></a>'.$admin_url;
                    }
                
                }
            function notificationBox($type, $message, $extra = null) {
                // Extra 1: Add the -modal class name
                if($extra == 1) {
                    $extra = ' notification-box-extra';
                }
                return '<div class="notification-box'.$extra.' notification-box-'.$type.'">
                        <p>'.$message.'</p>
                        <div class="notification-close notification-close-'.$type.'"></div>
                        </div>';
            }
      /*
            class admin{

                public $con;
                public $db;
                public $username;
                public $password;

                
                public function checkuser($username = null, $password = null){

                    global $LNG;
                    $this->username = $username;
                    $this->password = $password;
                    // $query = sprintf('SELECT * from user WHERE uname = "%s" AND pass = "%s"', $username, $password);

                    // $runQuery = mysqli_query($this->con, $query) or die('Query faild');
                    // if ($runQuery) {
                    //     $numrows = mysqli_num_rows($runQuery);
                    //     echo $numrows;
                    // }
                    $query = sprintf('SELECT * from admin WHERE uname = "%s" AND pass = "%s"', $username, $password);
                    $runQuery = $this->db->query($query) or die('Query Faild');
                    if($runQuery->num_rows == 0){
                        return 0;
                    }else{
                        
                        $result = $runQuery->fetch_assoc();
                        $_SESSION['aid'] = $result['aid'];
                        $_SESSION['uname'] = $result['uname'];
                        $_SESSION['pass'] = $result['pass'];
                        $_SESSION['token_id'] = $this->checktokenid();
                        return 1;

                    }

                }





                public function checktokenid()
                {
                    
                    if (!isset($_SESSION['token_id'])){
                        $token = GenrateToken();

                        // Prepare to update the database with the salted code
                        $stmt = $this->db->prepare("UPDATE `admin` SET `login_token` = '{$this->db->real_escape_string($token)}' WHERE `uname` = '{$this->db->real_escape_string(mb_strtolower($this->username))}'");

                        // Execute the statement
                        $stmt->execute();

                        // Save the affected rows
                        $affected = $stmt->affected_rows;

                        // Close the query
                        $stmt->close();

                        // If there was anything affected return 1
                        if ($affected) {
                            return $token;
                        } else {
                            return false;
                        }
                    }
                }
               public function getadmindetails($username){
                   $this->username = $username;
                   $query = sprintf('SELECT * FROM admin WHERE uname = "%s"',$this->username);
                   $runQuery = $this->db->query($query);
                   $raw = $runQuery->fetch_assoc();
                   if($raw['login_token']==$_SESSION['token_id']){
                    return $raw;
                   }else{
                    return '';
                   }
                   
    
               } 
               public function logOut($rt = null) {
                    if($rt == true) {
                        $this->resetToken();
                    }
                    unset($_SESSION['username']);
                    unset($_SESSION['password']);
                    unset($_SESSION['token_id']);
                }
            
                public function resetToken() {
                    $this->db->query(sprintf("UPDATE `mmen` SET `login_token` = '%s' WHERE `uname` = '%s'", GenrateToken(), $this->db->real_escape_string($this->username)));
                }
            }
            */

               class reg_mmen{
                public $db;
                public $fname;
                public $lname;
                public $gender;
                public $date;
                public $month;
                public $year;
                public $password;
                public $p_number;
                public $uname;
                public $state;
                public $district;
                public $sub_district;
                public $area;
                public $pincode;
                public $dairy_name;
                public $milk_distribute_type;
                public $locality;
                public $profile_pic;
                public $captcha;
                public $otp_val;
                
                public function step1_validation(){
                    global $LNG, $settings;
                    $error = array();
                    if(empty($this->fname)){
                        $error[] .= $LNG['fname_empty'];
                    }
                    if(empty($this->gender)){
                        $error[] .= $LNG['gender_empty'];
                    }elseif(!is_numeric($this->gender)){
                        $error[] .= 'Gender should be numeric';
                    }elseif(is_numeric($this->gender) && $this->gender > 2){
                        $error[] .= 'invalid Gender';
                    }
                    if(empty($this->date)){
                        $error[] .= $LNG['date_empty'];
                    }
                    if(empty($this->month)){
                        $error[] .= $LNG['month_empty'];
                    }
                    if(empty($this->year)){
                        $error[] .= $LNG['year_empty'];
                    }
                     $now_year = date('Y');
                     $past_10 = $now_year - 10;
                     $range = range($past_10,$now_year);
                     if (in_array($this->year, $range)) {
                        $error[] .= $LNG['dob_year_error'];
                      }
                    // $alphabetic = ctype_alpha($this->fname);
                    $alphabetic = preg_match('/^[A-Za-z ]+$/', $this->fname);
                    // checking fname
                    if ($alphabetic && !is_numeric($this->fname)) {
                      $length = strlen($this->fname);
    
                    if ($length < 3) {
                        $error[] .= sprintf($LNG['fname_min'],$length);
                    } elseif ($length > $settings['name']) {
                        $error[] .= sprintf($LNG['fname_max'],$settings['name'],$length);
                    }
                    } else {
                      $error[] .= $LNG['fname_not_alpha'];
                    }
                    // checking lname if lname is not empty
    
                    if(!empty($this->lname)){
                        $alphabetic2 = preg_match('/^[A-Za-z ]+$/', $this->lname);
                    if ($alphabetic2 && !is_numeric($this->lname)) {
                    $length1 = strlen($this->lname);
    
                    if ($length1 < 3) {
                        $error[] .= sprintf($LNG['lname_min'],$length1);
                    } elseif ($length1 > $settings['name']) {
                        $error[] .= sprintf($LNG['lname_max'],$settings['name'],$length1);
                    }
                    } else {
                      $error[] .= $LNG['lname_not_alpha'];
                    }
                    if (trim($this->fname) === trim($this->lname)) {
                        $error[] .= $LNG['fname_lname_same'];
                      }
                }
                    
                    return $error;
                }
                public function step_2_validation(){
                        global $LNG, $settings;
                        $error = array();
                        $p_num = $this->p_number;
                        $uname = $this->uname;
                        $password = $this->password;
                        if(empty($p_num)){
                            $error[] .= $LNG['p_num_empty'];
                        }elseif (!is_numeric($p_num)) {
                            $error[] .= $LNG['p_num_numeric'];
                        }elseif (strlen($p_num) < 10 || strlen($p_num) > 10) {
                         $error[] .= sprintf($LNG['p_num_length'],10,strlen($p_num));
                        }
                            
                        if(empty($uname)){
                            $error[] .= $LNG['uname_empty'];
                        }elseif(strlen($uname) < 3 || strlen($uname) > $settings['uname']) {
                            $error[] .= sprintf($LNG['uname_length'],$settings['uname'],strlen($uname));
                        }elseif (preg_match('/^_+$/', $uname)) {
                            $error[] .= $LNG['uname_underscore'];
                        }elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $uname)) {
                            $error[] .= $LNG['uname_contain'];
                        }elseif(is_numeric($uname)) {
                            $error[] .= $LNG['uname_numeric'];
                        }


                        if(empty($password)){
                            $error[] .= $LNG['pass_empty'];
                        }elseif( strlen($password) < 6){
                            $error[] .= $LNG['pass_length'];
                        }elseif( $password == $this->fname){
                            $error[] .= $LNG['pass_length'];
                        }elseif( $password == $p_num){
                            $error[] .= $LNG['pass_p_number'];
                        }
                        
                        return $error;
                        
                        
                }
                
                public function validate_address(){
                    global $LNG;
                    $error = array();
    
                    if(empty($this->state)){
                        $error[] .= $LNG['state_empty'];
                    }elseif(empty($this->district)){
                        $error[] .= $LNG['district_empty'];
                    }elseif(empty($this->sub_district)){
                        $error[] .= $LNG['sub_district_empty'];
                    }elseif(empty($this->area)){
                        $error[] .= $LNG['area_empty'];
                    }elseif(empty($this->pincode)){
                        $error[] .=$LNG['pincode_empty'];
                    }elseif(empty($this->dairy_name)){
                        $error[] .=$LNG['diary_name_empty'];
                    }elseif(empty($this->milk_distribute_type)){
                        $error[] .=$LNG['milk_distribute_type_empty'];
                    }elseif(empty($this->locality)){
                        $error[] .=$LNG['locality_empty'];
                    }else{

                        if(!is_numeric($this->state)){
                            $error[] .= 'Invalid State';
                        }elseif(!is_numeric($this->district)){
                            $error[] .= 'Invalid district';
                        }elseif(!is_numeric($this->sub_district)){
                            $error[] .= 'Invalid Sub - district';
                        }elseif(!is_numeric($this->area)){
                            $error[] .= 'Invalid Area';
                        }elseif(!is_numeric($this->pincode)){
                            $error[]  .= 'Invalid Pincode';
                        }elseif(is_numeric($this->dairy_name)){
                            $error[]  .= 'Dairy Name May Be alphabetic or alphanumeric';
                        }elseif(strlen($this->dairy_name) > 50 || strlen($this->dairy_name) < 10){
                            $error[]  .= 'Invalid Dairy Name length';
                        }
                        elseif(!is_numeric($this->milk_distribute_type)){
                            $error[]  .= 'milk distribute type only be numeric';
                        }elseif(is_numeric($this->milk_distribute_type) && $this->milk_distribute_type > 3){
                            $error[]  .= 'Invalid milk distribute type ';
                        }
                        else{
                            if(strlen($this->pincode) > 6){
                                $error[]  .= 'Invalid Pincode length';
                            }
                        }
                    }
                    
                    return $error;
                }
                public function process_step1(){
    
                    $arr = $this->step1_validation(); // Must be stored in a variable before executing an empty condition
                         if(empty($arr)){
                        return 1;
                        }else{
                            foreach($arr as $err) {
                                return notificationBox('error',$err, 1); //  the error value for translation file
                              }
                          }
                }
                // public function checking_uname(){
                //     $query = sprintf("SELECT `uname` FROM `m_customers` WHERE `uname` = '%s';",$this->db->real_escape_string($this->uname));
                //     $result = $this->db->query($query);
                //     if($result && !$result->num_rows > 0){
                //          $result->free(); // Free the result set
                //         return 1;
                //     }
                // }
                public function checking_uname() {
                    $query1 = sprintf("SELECT `uname` FROM `m_customers` WHERE `uname` = '%s'", $this->db->real_escape_string($this->uname));
                    $query2 = sprintf("SELECT `uname` FROM `mmen` WHERE `uname` = '%s';", $this->db->real_escape_string($this->uname));
                
                    // Combine both queries into a single string separated by semicolon
                    $combinedQuery = $query1 . ' UNION ' . $query2;
                    
                    if ($this->db->multi_query($combinedQuery)) {
                        // Iterate through the result sets
                        do {
                            // Check if the current result set has rows
                            if ($result = $this->db->store_result()) {
                                if ($result->num_rows > 0) {
                                    $result->free(); // Free the result set

                                    return 2; // Username already exists
                                }
                                $result->free(); // Free the result set
                            }
                        } while ($this->db->next_result());

                        return 1; // Username is available
                    }
                
                    return 0; // Error executing the queries
                }
                
                public function checking_p_number(){
                    $query = sprintf("SELECT `p_number` FROM `mmen` WHERE `p_number` = '%s';",$this->db->real_escape_string($this->p_number));
                    $result = $this->db->query($query);
                    if($result && $result->num_rows == 0){
                        // $result->free(); // Free the result set
                        return 1;
                    }
                }
                public function validate_uname(){
                    global $LNG, $settings;
                    $error = array();
                    $uname = $this->uname;
                    if(empty($uname)){
                        $error[] .= $LNG['uname_empty'];
                    }
                    if (strlen($uname) < 3 || strlen($uname) > $settings['uname']) {
                     $error[] .= sprintf($LNG['uname_length'],$settings['uname'],strlen($uname));
                    }
                    if (preg_match('/^_+$/', $uname)) {
                        $error[] .= $LNG['uname_underscore'];
                      }

                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $uname)) {
                        $error[] .= $LNG['uname_contain'];
                    }
    
                    if (is_numeric($uname)) {
                        $error[] .= $LNG['uname_numeric'];
                    }
    
                    return $error;
    
                }
                // validate phone number if the phone number is valid.
                public function validate_p_number(){
                    global $LNG, $settings;
                    $error = array();
                    $p_num = $this->p_number;
                    if(empty($p_num)){
                        $error[] .= $LNG['p_num_empty'];
                    }
                    if (!is_numeric($p_num)) {
                        $error[] .= $LNG['p_num_numeric'];
                    }
                    if (strlen($p_num) < 10 || strlen($p_num) > 10) {
                     $error[] .= sprintf($LNG['p_num_length'],10,strlen($p_num));
                    }
    
                    return $error;
    
                }
                // process Username checking if any error from validation or if available or not available
                public function process_uname(){
                    global $LNG, $action, $all_states_name, $all_districts_name,$all_sub_districts_name;
                    $arr = $this->validate_uname(); // Must be stored in a variable before executing an empty condition
                    if(empty($arr)){
                        $check_uname = $this->checking_uname();

                        $other_values = ['vishal','vishalbhardwaj','vishaldhareru','doodhbazar','doodhwala','milkdairy',
                                      'milkmen','milkman','dudhiya','doodhiya','doodhlelo','profile','edit_profile','download','help','report','language',
                                      'view_data','view','search','find','pincode','buffalo'.'admin','global_admin','local_admin',
                                    ];
                        $username = strtolower($this->uname);
                        $actionLower = array_map('strtolower', $action);
                        $secondArrayLower = array_map('strtolower', $other_values);
                        $all_states_lower =  array_map('strtolower', $all_states_name);
                        $all_districts_lower =  array_map('strtolower', $all_districts_name);
                        $all_sub_districts_lower =  array_map('strtolower', $all_sub_districts_name);

                   if ((!array_key_exists($username, $actionLower) && !in_array($username, $secondArrayLower) && !in_array($username, $all_states_lower) && !in_array($username, $all_districts_lower) && !in_array($username, $all_sub_districts_lower)) && $check_uname == 1) {
                        return 1;
                    } else {
                        return $LNG['uname_taken'];
                    }
                   }else{
                       foreach($arr as $err) {
                           return $err; //  the error value for translation file
                         }
                     }
                }
                public function process_p_number(){
                    global $LNG;
                    $arr = $this->validate_p_number(); // Must be stored in a variable before executing an empty condition
                    if(empty($arr)){
                        $check_p_num = $this->checking_p_number();
                        if($check_p_num){
                            return 1;
                        }else{
                          return 2;
                        }
                   }else{
                       foreach($arr as $err) {
                           return $err; //  the error value for translation file
                         }
                     }
                }
                public function final_validate(){
                   
                    $step1 = $this->step1_validation(); // Must be stored in a variable before executing an empty condition
                    $step2 = $this->step_2_validation(); // Must be stored in a variable before executing an empty condition
                    $v_address = $this->validate_address();
                    
                    $check_uname = $this->checking_uname();
                    $check_p_num = $this->checking_p_number();

                    if( !$step1 && !$step2 && !$v_address && $check_uname == 1 && $check_p_num == 1 ){
                        return 1;
                    }else{
                        return 2;
                    }
                }
              public function find_locality($state,$district,$sub_district,$area){
                if($area == 1){
                    $query = sprintf("SELECT `village_name` FROM `villages` WHERE `state_id` = %s AND `district_id` = %s AND `sub_district_id` = %s",$state,$district,$sub_district);
                }elseif($area == 2){
                    $query = sprintf("SELECT `colony_name` FROM `colonies` WHERE `state_id` = %s AND `district_id` = %s AND `sub_district_id` = %s",$state,$district,$sub_district);
                }
                
                $run = $this->db->query($query);
                    $result = array();
    
                    if ($run->num_rows > 0) {
                        while ($row = $run->fetch_assoc()) {
                            $villageName = preg_replace('/\s*\([^)]*\)/', '', $row['village_name']);
                            $trimmedName = trim($villageName);
                            $result[] = $trimmedName;
                        }
                    }
                // $run->free(); // Free the result set
                return $result;
              }
    
            public function insert_new_locality($locality_name, $state, $district, $sub_district, $area) {
                global $user;
                if ($area == 1) {
                    $query = sprintf("INSERT INTO new_villages(village_name,state_id,district_id,sub_district_id) VALUES ('%s','%s','%s','%s')", $this->db->real_escape_string($locality_name), $state, $district, $sub_district);
                } elseif ($area == 2) {
                    $query = sprintf("INSERT INTO colonies(colony_name,state_id,district_id,sub_district_id) VALUES ('%s','%s','%s','%s')", $this->db->real_escape_string($locality_name), $state, $district, $sub_district);
                }
                $result = $this->db->query($query);
                
                if ($result !== false) {
                    // if ($result instanceof mysqli_result) {
                    //     $result->free();
                    // }
                    return $this->db->affected_rows;
                } else {
                    error_log("Database query error: " . $this->db->error);
                    return 0;
                }
            }
            
            
              public function update_locality(){
                $state = $this->state;
                $district = $this->district;
                $sub_district = $this->sub_district;
                $area = $this->area;
    
                $data = $this->find_locality($state,$district,$sub_district,$area);
    
                if($data){
                    if ($area == 1) {
                        if (!in_array($this->locality, $data)) {
                          
                            $this->insert_new_locality($this->locality, $state, $district, $sub_district, $area);
                        }
                    } elseif ($area == 2) {
                        if (!in_array($this->locality, $data)) {
                           
                            $this->insert_new_locality($this->locality, $state, $district, $sub_district, $area);
                        }
                    }
                    
                }else{
                   
                    $this->insert_new_locality($this->locality,$state,$district,$sub_district,$area);
                }
                
              }
                public function otp_verification($p_number){
                    $otp = generateOTP();
                    $message_id = message_id('verification_code');
    
                    // $uname = "\"@".$this->uname."\"";
    
                    $array = [$otp];
                    if($otp){
                        $sms = new send_sms();
                       $output = $sms->process($message_id,$p_number,$array);
                    }else{
                        $output = '{"status":"failed_otp"}';
                    }
                    return $output;
                }
                public function post_snap($string){
                    $value = mt_rand().'_'.mt_rand().'_'.mt_rand().'.png';
                    $image = base64Image($string, $value);
                    
                    if($image['data']){
                        file_put_contents(__DIR__ . '/../uploads/avatars/milkmen/'.$value, $image['data']);
                        return $value;
                    }else{
                        return false;
                    }
    
                }
                        //                 public function post_snap($string)
                        // {
                        //     $value = mt_rand().'_'.mt_rand().'_'.mt_rand().'.png';

                        //     $imageData = base64_decode($string);
                            
                        //     if ($imageData) {
                        //         $filepath = __DIR__ . '/../uploads/avatars/milkmen/' . $value;

                        //         if (file_put_contents($filepath, $imageData)) {
                        //             return $value;
                        //         }
                        //     }

                        //     return false;
                        // }

                public function verify_snap(){
                    if(!empty($this->profile_pic)){
                        $upload = $this->post_snap($this->profile_pic);
                    }else{
                        $upload = 'default.png';
                    }
                    return $upload;
                }
                function formatDateString($dateString) {
                    // Create a DateTime object from the input date string
                    $date = DateTime::createFromFormat('j-n-Y', $dateString);
                    
                    // Format the date to the desired format
                    $formattedDate = $date->format('d-m-Y');
                    
                    return $formattedDate;
                }
                
                public function make_dob(){
                    $year = $this->year;
                    $month = $this->month;
                    $date = $this->date;
    
                    $marge_date = $year.'-'.$month.'-'.$date;
                    return $marge_date;
                }    
     
                public function Query() {
                    $token = GenerateToken_2();
                    
                    $query = sprintf("INSERT INTO `mmen` (`fname`, `lname`, `uname`, `p_number`, `dob`, `gender`, `pass`, `profile_pic`, `state`, `district`, `sub_district`, `area`, `pincode`, `locality`, `dairy_name`, `distribute_type`, `login_token`,`joined`)
                    VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s')",
                    $this->db->real_escape_string(mb_strtolower(trim($this->fname))),
                    $this->db->real_escape_string(mb_strtolower(trim($this->lname))),
                    $this->db->real_escape_string(mb_strtolower(trim($this->uname))),
                    $this->db->real_escape_string(trim($this->p_number)),
                    $this->db->real_escape_string(trim($this->make_dob())),
                    $this->db->real_escape_string(trim($this->gender)),
                    $this->db->real_escape_string(trim($this->password)),
                    $this->db->real_escape_string($this->verify_snap($this->profile_pic)),
                    $this->db->real_escape_string(trim($this->state)),
                    $this->db->real_escape_string(trim($this->district)),
                    $this->db->real_escape_string(trim($this->sub_district)),
                    $this->db->real_escape_string(trim($this->area)),
                    $this->db->real_escape_string(trim($this->pincode)),
                    $this->db->real_escape_string(mb_strtolower(trim($this->locality))),
                    $this->db->real_escape_string(mb_strtolower(trim($this->dairy_name))),
                    $this->db->real_escape_string(mb_strtolower($this->milk_distribute_type)),
                    $this->db->real_escape_string($token),
                    date('Y-m-d H:i:s')
                   );
                    $result = $this->db->query($query);
                    
                    if ($result !== false) {
                        // if ($result instanceof mysqli_result) {
                        //     $result->free();
                        // }
                        return true;
                    } else {
                        error_log("Database query error: " . $this->db->error);
                        return false;
                    }
                }
                
                
                public function process(){
                    global $user, $LNG;
                    
                    $incorrect_otp = " 
                    <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                    <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                    <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                    <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
                   </svg>
                   <div class='failed-title'>Incorrect Verification code</div>     
                                     ";
                    $validation_error = "
    
                    <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                    <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                    <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                    <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
                   </svg>
                   <div class='failed-title'>Something going Wrong in Validation</div>              
                                        ";
                    $captcha_error = "
                                 <div class='failed-title'>Invalid Captcha code</div> 
                                        ";
                    $success = "   <div class='success-container'>
                    <div class='circle'></div>
                    <div class='ring'></div>
                    <div class='checkmark'></div>
                    <div class='fading-circles'>
                      <div class='circle-animation circle1'></div>
                      <div class='circle-animation circle2'></div>
                      <div class='circle-animation circle3'></div>
                      <div class='circle-animation circle4'></div>
                      <div class='circle-animation circle5'></div>
                      <div class='circle-animation circle6'></div>
                      <div class='circle-animation circle7'></div>
                      <div class='circle-animation circle8'></div>
                      <div class='circle-animation circle9'></div>
                      <div class='circle-animation circle10'></div>
                    </div>
                  </div> 
                              ";                                     
                    if($this->captcha == $_SESSION['captcha']){
                      
                    if($this->final_validate() == 1){
                        $verify_otp = ($_SESSION['otp']) ? verifyOTP($this->otp_val) : true;
                        if($verify_otp == true){
                         $this->password = password_hash($this->password, PASSWORD_DEFAULT);
                         
                         $create_acc = $this->Query();
                         if($create_acc && $this->db->affected_rows > 0){
                            $msg_status = null;
                            $this->update_locality();

                            // $message_id = message_id('c_acc_created');
    
                            //  $uname = "\"$this->uname\"";
                            //  $dob =  $this->formatDateString($this->date.'-'.$this->month.'-'.$this->year);
    
                            //     $array = [$uname,$dob];
                            //     $sms = new send_sms();
                            //     $msg_status = $sms->process($message_id,$this->p_number,$array);

                                 // Set a session and log-in the user

                                $_SESSION['username'] = $this->uname;
                                $_SESSION['password'] = $this->password;

                            $output = ['status' => true, 'sms'=>$msg_status, 'error' => '', 'message' => $success];
                         }
    
                        }else{
                            $output = ['status' => false, 'error' => 'otp_verify_failed', 'message' => $incorrect_otp];
                        }
                    }else{
                        
                        $output = ['status' => false, 'error' => 'not_validate', 'message' => $validation_error];
                    }
                         
                    }else{
                        $output = ['status' => false, 'error' => 'invalid_captcha', 'message' => $captcha_error];
                    }
    
                    return $output;
                }
               }

                
            class User {
                public $db; 		// Database Property
                public $url; 		// Installation URL Property
                public $uname;	// Username Property
                public $pass;	// Password Property
                public $remember;	// Option to remember the usr / pwd (_COOKIE) Property
                public $token;		// The Remember Me token
            
               /**
                * The authentication process
                *
                * @param	string	$type	0: checks if the user is already logged-in; 1: log-in form process
                */
                function auth($type = null) {
                    global $LNG;
                    
                    if(isset($_COOKIE['username']) && isset($_COOKIE['userToken'])) {
                        $this->uname = $_COOKIE['username'];
                        $auth = $this->get(1);
            
                        if($auth['uname']) {
                            $logged = true;
                        } else {
                            $logged = false;
                        }
                    } elseif(isset($_SESSION['username']) && isset($_SESSION['password'])) {
                        $this->uname = $_SESSION['username'];
                        $this->pass = $_SESSION['password'];
                        $auth = $this->get();
            
                        if($this->pass == $auth['pass']) {
                            $logged = true;
                        } else {
                            $logged = false;
                        }
                    } elseif($type) {
                        $auth = $this->get();
                        
                        if(!empty($auth['pass']) && password_verify($this->pass, $auth['pass'])) {
                            
                            // if($auth['suspended'] == 2) {
                            //     return sprintf($LNG['acc_for_verification'], $this->url.'/index.php?a=welcome&activate=resend&username='.$this->uname);
                            // } elseif($auth['suspended'] == 1) {
                            //     return $LNG['login_faild'];
                            // }
                        
                            if($this->remember == 1) {
                                setcookie("username", $auth['uname'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
                                setcookie("userToken", $auth['login_token'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
                            }
                            $_SESSION['username'] = $auth['uname'];
                            $_SESSION['password'] = $auth['pass'];
            
                            $logged = true;
                            session_regenerate_id();
                        } else {

                            return $LNG['login_faild'];
                        }
                        
                    }
            
                    if(isset($logged) && $logged == true) {
                        return $auth;
                    } elseif(isset($logged) && $logged == false) {
                        $this->logOut();
                        return $LNG['login_faild'];
                    }
            
                    return false;
                }
            
                function get($type = null) {

                    if($type) {
                        $extra = sprintf(" AND `login_token` = '%s'", $this->db->real_escape_string($_COOKIE['userToken']));
                    } else {
                        $extra = '';
                    }
                    // If the username input string is an e-mail, switch the query
                    if (filter_var($this->db->real_escape_string($this->uname), FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_OCTAL)) {

                        $query = sprintf("SELECT m.*, s.* FROM `mmen` m LEFT JOIN `subscription` s ON m.id = s.mmen_id
                           WHERE m.`p_number` = '%s' %s
                           AND (s.mmen_id IS NULL OR s.mmen_id = m.id)",
                           $this->db->real_escape_string(trim($this->uname)),
                           $extra);

                    } else {

                        $query = sprintf("SELECT m.*, s.* FROM `mmen` m LEFT JOIN `subscription` s ON m.id = s.mmen_id
                         WHERE m.`uname` = '%s' %s
                         AND (s.mmen_id IS NULL OR s.mmen_id = m.id)",
                         $this->db->real_escape_string(strtolower((trim($this->uname)))),
                         $extra);

                    }
                    
                    // If the query can't be executed (e.g: use of special characters in inputs)
                    if(!$result = $this->db->query($query)) {
                        return 0;
                    }
            
                    $user = $result->fetch_assoc();
            
                    return $user;
                }
            
                function logOut($rt = null) {
                    if($rt == true) {
                        $this->resetToken();
                    }
                    setcookie("userToken", '', time()-3600, COOKIE_PATH);
                    setcookie("username", '', time()-3600, COOKIE_PATH);
                    unset($_SESSION['username']);
                    unset($_SESSION['password']);
                    unset($_SESSION['token_id']);
                    
                }
            
                function resetToken() {
                    $this->db->query(sprintf("UPDATE `mmen` SET `login_token` = '%s' WHERE `uname` = '%s'", GenerateToken_2(), $this->db->real_escape_string($this->uname)));
                }
            }
        // Update Setting
        class update_ad_settings{
              public $db;           // store database
              public $url;          // store configure url
              public $mm_id;       // store milkman id
              public $dm_max;    // store direct milk rate limit
              public $mfr_max;    // store fat rate limit
              public $dm_min;  // store minimum rate value store
              public $mfr_min;  // store minimum fat rate value store
              public $b_dm;         // store taking direct milk 
              public $s_dm;         // store giving direct milk rate
              public $b_mfr;         // store taking milk fat rate
              public $s_mfr;         // store giving milk fat rate
              public $b_animal;     // store animal name of taking milk
              public $s_animal;     // store animal name of giving milk
              public $password;     // store password for user
              public $delete_id;    // store delele row id number
              public $delete_table_stg;    // store delete rate setting table name

              public $milk_type;
              public $animal;
              public $milk_way;

              function validate_float($floatvals,$floatlimit)
              {
                // validate if value is float or not if not convert into float and check floatlimit 
                // it return data into array form

                $validated = array();
                foreach($floatvals as $floatval) {
                 if (!is_float($floatval)) {
                 if ($floatval <= 10) {
                $floatval = floatval($floatval);
                } elseif ($floatval >= 100 || $floatval <= 100) {
                $floatval = $floatval / 10;
                if(!is_float($floatval)) {
                    $floatval = floatval($floatval);
                }
                if (is_float($floatval) && $floatval >= $floatlimit) {
                    $floatval =  $floatlimit;
                }

                } 
                    } else {
                        if (is_float($floatval) && $floatval >= $floatlimit) {
                            $floatval =  $floatlimit;
                        }
                    }
                    $validated[] = $floatval;
                }
               return $validated;
              }


                function check_val($val, $ratelimit){
                    // checking value is greater then ratelimit if yes set it to ratelimit
                $result = array();
                foreach ($val as $v) {
                    if(is_float($v)){
                    $vall = intval($v);
                    $v = ($vall > $ratelimit) ? ($ratelimit) : ($vall);
                    }elseif($v > $ratelimit){
                    $v = $ratelimit;
                    }
                    $result[] = $v;
                }
                return $result;
                }

          
          
          function validate_inputs(){
            // validate all values when submit save setting
            global $LNG;
            $error = array();
           
                 
                    $count_dm = ($this->b_dm) ? count($this->b_dm) : '';
                    $count_mf = ($this->b_mfr) ? count($this->b_mfr) : '';
                    $count_animal = ($this->b_animal) ? count($this->b_animal) : '';
                    $max_count = max($count_dm, $count_mf, $count_animal);

                    $count_s_dm = ($this->s_dm) ? count($this->s_dm) : '';
                    $count_s_mf = ($this->s_mfr) ? count($this->s_mfr) : '';
                    $count_animal_s = ($this->s_animal) ? count($this->s_animal) : '';
                    $max_count_g = max($count_s_dm, $count_s_mf, $count_animal_s);
                    
                     // code for taking milk 
                    if(isset($this->b_animal)){
                        foreach($this->b_animal as $value){
                           
                            if($value  != ''){
                                
                                for ($i=0; $i < $max_count; $i++) {
                       
                                    if (empty($this->b_dm[$i]) || empty($this->b_animal[$i])) {
                                       $error[] = $LNG['b_fields_properly_not_field'];   
                                    }

                                }
                            }
                        }
                   
                   }

                   if(isset($this->s_animal)){
                    foreach($this->s_animal as $value){
                        if($value != ''){
                            
                            for ($v=0; $v < $max_count_g; $v++) {
                                if(empty($this->s_dm[$v]) || empty($this->s_animal[$v])) {
                                    $error[] = $LNG['s_fields_properly_not_field'];   
                                 }
                                }
                        }
                       
                    }
                } 
          
            $t_dm = ($this->b_dm) ? $this->b_dm : [];
            $g_dm = ($this->s_dm) ? $this->s_dm : [];
            $merged_array = array_merge($t_dm, $g_dm);

            $merged_array = array_filter(array_unique($merged_array));

            

                if (!empty($merged_array)) {
                    foreach ($merged_array as $value) {
                        if ($value < $this->dm_min) {
                            $error[] = sprintf($LNG['minimum_d_ratelimit'],$this->dm_min);
                        }elseif($value  > $this->dm_max){
                            $error[] = sprintf($LNG['maximum_d_ratelimit'],$this->dm_max);
                        }
                    }
                }

                $t_mf = ($this->b_mfr) ? $this->b_mfr : [];
                $g_mf = ($this->s_mfr) ? $this->s_mfr : [];    
            $store_value = array_merge($t_mf,$g_mf);
            $store_value = array_filter(array_unique($store_value));

                if (!empty($store_value)) {
                    foreach ($store_value as $value) {
                       
                        if($value < $this->mfr_min){
                            $error[] = sprintf($LNG['minimum_f_ratelimit'],$this->mfr_min);
                        }elseif($value > $this->mfr_max){
                            $error[] = sprintf($LNG['maximum_f_ratelimit'],$this->mfr_max);
                        }
                        
                    }
                }
            
            return $error;
          }
          function animal_query(){
            // load all mik animal data write a query for fetching animals
            $query = "SELECT `a_id`,`name` FROM `m_animal`";
            return $this->db->query($query);
            }
            function load_animal(){
                // fetch all milk animal data with the help for loop.
                $rows = '';
                $result = $this->animal_query();
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                      $rows .= "
                      <option value='".$row['a_id']."'>".$row['name']."</option>
                      ";
                      $rows++;
                    }
                }else{
                    $rows .= "Finished";
                }
                return $rows;
                
            }
            function load_animal_by_name(){
                // load all milk animal by value name

                $rows = '';
                $result = $this->animal_query();
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                      $rows .= "
                      <option value='".$row['name']."'>".$row['name']."</option>
                      ";
                      $rows++;
                    }
                }else{
                    $rows .= "Finished";
                }
                return $rows;
                
            }
         function load_input_fields(){
           // dynamically load setting input fields if some inputs fields available to be filled.
           $num_rows = $this->check_num_rows();
           $check_b_rows = $num_rows['animal_rows'] - $num_rows['buying_rows'];
           $load_animal = $this->load_animal();
           $max_val = $this->max_val_check();
           $bm_max = $max_val['bm_max_value'];
           $sm_max = $max_val['sm_max_value'];

           if($check_b_rows != 0 && $check_b_rows > 1){ 

                 $buying_input = '
                  
                  <table class="stg-table" id="table_fields">
                  <tr>
                      <th> Choose </th>
                      <th> Direct Milk Rate </th>
                      <th>Milk Fat Rate</th>
                      <th>Action</th>
                  </tr>
                   <tr>
                      <td><select name="b_animal[]">
                          <option value="">Select Animal</option>
                          '.$load_animal.'
                       </select></td>
                      <td>
                          <input type="number" id="dm1" name="b_dm[]" step="any">
                          <div id="msg_1" class="page-input-sub-error"></div>
                         </td>
                      <td><input type="number" id="mfr1" name="b_mfr[]" placeholder="optional" step="any">
                          <div id="msg1" class="page-input-sub-error"></div>
                          </td>
                      <td><div class="add_more" id="add_more" onclick="add_inputs(1,'.json_encode([$bm_max, $sm_max]).')">Add More</div></td>
                   </tr>
                 </table>
                  
                                   ';
           }elseif($check_b_rows != 0 && $check_b_rows == 1){
                 $buying_input = ' <table class="stg-table" id="table_fields">
                  <tr>
                      <th> Choose </th>
                      <th> Direct Milk Rate</th>
                      <th>Milk Fat Rate</th>
                      <th>Action</th>
                  </tr>
                   <tr>
                      <td><select name="b_animal[]">
                          <option value="">Select Animal</option>
                          '.$load_animal.'
                       </select></td>
                      <td>
                          <input type="number" id="dm1" name="b_dm[]" step="any">
                          <div id="msg_1" class="page-input-sub-error"></div>
                         </td>
                      <td><input type="number" id="mfr1" name="b_mfr[]"  placeholder="optional"  step="any">
                          <div id="msg1" class="page-input-sub-error"></div>
                          </td>
                      <td></td>
                   </tr>
                 </table>';
           }elseif($check_b_rows == 0 && $check_b_rows != 1){
                  $buying_input = '</b>Delete Old Data After that Add New Data.</b>';
           }
           // Now load for Giving
           $check_s_rows = $num_rows['animal_rows'] - $num_rows['selling_rows'];

            if($check_s_rows != 0 && $check_s_rows > 1){
                  $selling_input = '
                   
                   <table class="stg-table" id="table_fields_s">
                   <tr>
                       <th> Choose </th>
                       <th> Direct Milk Rate </th>
                       <th>Milk Fat Rate</th>
                       <th>Action</th>
                   </tr>
                    <tr>
                       <td><select name="s_animal[]">
                           <option value="">Select Animal</option>
                           '.$load_animal.'
                        </select></td>
                       <td>
                           <input type="number" id="dm2" name="s_dm[]" step="any">
                           <div id="msg_2" class="page-input-sub-error"></div>
                          </td>
                       <td><input type="number" id="mfr2" name="s_mfr[]" placeholder="optional" step="any">
                           <div id="msg2" class="page-input-sub-error"></div>
                           </td>
                       <td><div class="add_more" id="add_more_s" onclick="add_inputs(2,'.json_encode([$bm_max, $sm_max]).')">Add More</div></td>
                    </tr>
                  </table>
                   
                                    ';
            }elseif($check_s_rows != 0 && $check_s_rows == 1){
                   $selling_input = '
                    
                    <table class="stg-table" id="table_fields_s">
                    <tr>
                        <th> Choose </th>
                        <th> Direct Milk Rate </th>
                        <th>Milk Fat Rate</th>
                        <th>Action</th>
                    </tr>
                     <tr>
                        <td><select name="s_animal[]">
                            <option value="">Select Animal</option>
                            '.$load_animal.'
                         </select></td>
                        <td>
                            <input type="number" id="dm2" name="s_dm[]" step="any">
                            <div id="msg_2" class="page-input-sub-error"></div>
                           </td>
                        <td><input type="number" id="mfr2" name="s_mfr[]" placeholder="optional" step="any">
                            <div id="msg2" class="page-input-sub-error"></div>
                            </td>
                        <td></td>
                     </tr>
                   </table> 

                                    ';
            }elseif($check_s_rows == 0 && $check_s_rows != 1){
                   $selling_input = '<b> Delete 1st any Row Data After that try.</b>';
            }
            return ['buying_input' => $buying_input, 'selling_input' =>$selling_input];
           
         }
         function check_num_rows(){
            global $user;
            // fetch all milk animal data from the table 
            $query = "SELECT * FROM `m_animal`";
            // execute the query
            $run = $this->db->query($query);
            if($query){
                // store all number of rows in a variable 
                $m_animal_numrows = $run->num_rows;
            }
            // checking taking,giving milk setting and what number of rows are stored 

            $bm_stg = sprintf("SELECT * FROM `buying_milk_stg` WHERE `mm_id` = %s",$user['id']);
            $sm_stg = sprintf("SELECT * FROM `selling_milk_stg` WHERE `mm_id` = %s",$user['id']);
            $run2 = $this->db->query($bm_stg);
            $run3 = $this->db->query($sm_stg);
            if($run2){
                $bm_stg_num_rows = $run2->num_rows;
            }
            if($run3){
                $sm_stg_num_rows = $run3->num_rows;
            }
            return ['animal_rows' => $m_animal_numrows, 'buying_rows' => $bm_stg_num_rows, 'selling_rows' => $sm_stg_num_rows];
         }
         function max_val_check(){
            // checking maximum value to load maximum input in input fields
            $num_rows = $this->check_num_rows();

            if($num_rows['animal_rows'] >= $num_rows['buying_rows'] ){
                $bm_max = $num_rows['animal_rows'] - $num_rows['buying_rows'];
                $bm_max -= 1;
            }
            if($num_rows['animal_rows'] >= $num_rows['giving_rows']){
                $sm_max = $num_rows['animal_rows'] - $num_rows['selling_rows'];
                $sm_max -= 1;
            }
           $send_bm_max = "<input type='hidden' id='bm_max' value='".$bm_max."'>";
           $send_sm_max = "<input type='hidden' id='sm_max' value='".$sm_max."'>";
     
           return ['bm_max' => $send_bm_max,'sm_max' => $send_sm_max,'sm_max_value' => $sm_max,'bm_max_value' => $bm_max];
         }
        //  function validate_query_type(){
        //     $output = '';
        //     if(($this->array_validater($this->b_dm)==true || $this->array_validater($this->b_mfr)==true) && $this->array_validater($this->s_dm) ==false or NULL || $this->array_validater($this->s_mfr)==false or NULL){
        //        $output = 1;
        //     }elseif(($this->array_validater($this->s_dm) == true || $this->array_validater($this->s_mfr)== true) && $this->array_validater($this->b_dm)==false or NULL || $this->array_validater($this->b_mfr)==false or NULL){
        //        $output = 2;
        //     }elseif(($this->array_validater($this->s_dm)==true || $this->array_validater($this->s_mfr)==true) && $this->array_validater($this->b_dm)==true || $this->array_validater($this->b_mfr)==true){
        //        $output = 3;
        //     }
        //     return $output;
        //  }

        
        function validate_query_type() {
            $output = '';
        
            if ($this->array_validater($this->b_dm) || $this->array_validater($this->b_mfr)) {
                if ($this->array_validater($this->s_dm) || $this->array_validater($this->s_mfr)) {
                    $output = 3;
                } else {
                    $output = 1;
                }
            } elseif ($this->array_validater($this->s_dm) || $this->array_validater($this->s_mfr)) {
                $output = 2;
            }
        
            return $output;
        }
        
        function array_validater($array = []) {
            // validate if array and its values
            foreach($array as $value) {
                if (!empty($value) || $value === '0') {
                    return true;
                }else{
                    return false;
                }
            }
            
        }
        
        // function array_validater($array = []) {
        //     // validate if array and its values
        //     foreach ($array as $key => $value) {
        //         if (empty($value)) {
        //             return false;
        //         }
        //     }
        
        //     return true;
        // }
        
          function query_ad_setting(){
                  global $user;
                $type = $this->validate_query_type();
                
                $b_dm_values = $this->check_val($this->b_dm,$this->dm_max); // assuming $t_dm is an array
                $b_mf_values = $this->validate_float($this->b_mfr,$this->mfr_max); // assuming $t_mf is an array
                $s_dm_values = $this->check_val($this->s_dm,$this->dm_max);
                $s_mf_values =  $this->validate_float($this->s_mfr,$this->mfr_max); 
                $b_animal_values = $this->b_animal; // assuming $t_animal is an array
                $s_animal_values = $this->s_animal; // assuming $t_animal is an array
                
                $query = '';
                if($type == 1 ){
                    // Create an array of value sets
                        $values = array();
                        for ($i = 0; $i < count($b_dm_values); $i++) {
                            $values[] = sprintf("(%s,%s,%s,%s)",
                                $this->db->real_escape_string($user['id']),
                                $this->db->real_escape_string($b_dm_values[$i]),
                                $this->db->real_escape_string($b_mf_values[$i]),
                                $this->db->real_escape_string($b_animal_values[$i])
                            );
                        }

                        // Generate the INSERT INTO query with the value sets
                        $query .= sprintf("INSERT INTO `buying_milk_stg` (`mm_id`,`b_dm`,`b_mfr`,`m_animal`) VALUES %s;",
                            implode(",", $values)
                        );

                    //$query = sprintf("INSERT INTO `buying_milk_stg` (`mm_id`,`t_dm`,`t_mf`,`m_animal`) VALUES (%s,%s,%s,%s);",$this->db->real_escape_string($user['id']),$this->check_val($this->b_dm,$this->ratelimit),$this->validate_float($this->t_mf,10.0),$this->b_animal);
                }elseif($type==2){
                        // Create an array of value sets
                        $values = array();
                        for ($i = 0; $i < count($s_dm_values); $i++) {
                            $values[] = sprintf("(%s,%s,%s,%s)",
                                $this->db->real_escape_string($user['id']),
                                $this->db->real_escape_string($s_dm_values[$i]),
                                $this->db->real_escape_string($s_mf_values[$i]),
                                $this->db->real_escape_string($s_animal_values[$i])
                            );
                        }

                        // Generate the INSERT INTO query with the value sets
                        $query .= sprintf("INSERT INTO `selling_milk_stg` (`mm_id`,`s_dm`,`s_mfr`,`m_animal`) VALUES %s;",
                            implode(",", $values)
                        );
                        
                   // $query = sprintf("INSERT INTO `selling_milk_stg` (`mm_id`,`g_dm`,`g_mf`,`m_animal`) VALUES (%s,%s,%s,%s);",$this->db->real_escape_string($user['id']),$this->db->real_escape_string($this->check_val($this->s_dm,$this->ratelimit)),$this->db->real_escape_string($this->validate_float($this->g_mf,10.0)),$this->db->real_escape_string($this->s_animal));
                }else{

                    $value = array();
                    // for taking milk-----------------------------

                    for ($i = 0; $i < count($b_dm_values); $i++) {
                        $value[] = sprintf("(%s,%s,%s,%s)",
                            $this->db->real_escape_string($user['id']),
                            $this->db->real_escape_string($b_dm_values[$i]),
                            $this->db->real_escape_string($b_mf_values[$i]),
                            $this->db->real_escape_string($b_animal_values[$i])
                        );
                    }

                    // Generate the INSERT INTO query with the value sets
                    $query .= sprintf("INSERT INTO `buying_milk_stg` (`mm_id`,`b_dm`,`b_mfr`,`m_animal`) VALUES %s;",
                        implode(",", $value)
                    );

                     // for giving milk----------------------- 
                     $values = array();
                        for ($i = 0; $i < count($s_dm_values); $i++) {
                            $values[] = sprintf("(%s,%s,%s,%s)",
                                $this->db->real_escape_string($user['id']),
                                $this->db->real_escape_string($s_dm_values[$i]),
                                $this->db->real_escape_string($s_mf_values[$i]),
                                $this->db->real_escape_string($s_animal_values[$i])
                            );
                        }

                        // Generate the INSERT INTO query with the value sets
                        $query .= sprintf("INSERT INTO `selling_milk_stg` (`mm_id`,`s_dm`,`s_mfr`,`m_animal`) VALUES %s;",
                            implode(",", $values)
                        );
                  
                //    $query = $query1.$query2;
                }
                
                return ['query'=>$query,'type' =>$type];
            
          }
          public function validate_return_query($query, $type){
            if ($type == 3) {
             return $this->db->multi_query($query);
            } elseif ($type == 1 || $type == 2) {
              return $this->db->query($query);
            } else {
                return false;
            }
        }
        function get_stg_query($type){
            global $user;
            // create a function to fetch user setting from datebase
            // if type==1 setting from taking table and type==2 setting from giving table
            if($type==1){
                $query = sprintf("SELECT `bm_id`, `b_dm`, `b_mfr`, `name`, ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) AS `SERIAL_NO` FROM `buying_milk_stg` RIGHT OUTER join `m_animal` on `m_animal` = `a_id` WHERE `mm_id` = %s",$user['id']);
            }elseif($type==2){
                $query = sprintf("SELECT `sm_id`,`s_dm`, `s_mfr`, `name`, ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) AS `SERIAL_NO` FROM `selling_milk_stg` RIGHT OUTER join `m_animal` on `m_animal` = `a_id` WHERE `mm_id` = %s",$user['id']);
            }
            return $this->db->query($query);
            
        }

        function user_stg(){
            // save query into taking_stg and giving_stg variable
            global $CONF;
            $buying_stg = $this->get_stg_query(1);
            $selling_stg = $this->get_stg_query(2);
            $buying = $selling = '';
            if($buying_stg && $buying_stg->num_rows > 0){
                while($row = $buying_stg->fetch_array()) {
                    $buying .= '
                             
                            <tbody class="user-stg">
                            <tr id="'.$row['bm_id'].'">
                            <td><b>'.$row['SERIAL_NO'].'</b></td>
                            <td>'.$row['b_dm'].'</td>
                            <td>'.(isset($row['b_mfr']) ? $row['b_mfr'] : 'NuLL').'</td>
                            <td>'.$row['name'].'</td>
                            <td><div class="delete" id="bm" data-id="'.$row['bm_id'].'" onclick="delete_stg('.$row['bm_id'].', 1)"><span class="delete-icon-show"><span></div>
                            </td>
                            </tr> 
                            </tbody>
                                ';

                    $buying++;
                }
            }else{
                $buying .= '';
            }

            if($selling_stg && $selling_stg->num_rows > 0){
                while($row = $selling_stg->fetch_array()) {
                    $selling .= '
                            <tbody class="user-stg">
                            <tr >
                            <td><b>'.$row['SERIAL_NO'].'</b></td>
                            <td>'.$row['s_dm'].'</td>
                            <td>'.(isset($row['s_mfr']) ? $row['s_mfr'] : 'NuLL').'</td>
                            <td>'.$row['name'].'</td>
                            <td><div class="delete" id="sm" data-id="'.$row['sm_id'].'" onclick="delete_stg('.$row['sm_id'].', 2)"><span class="delete-icon-show"><span></div>
                            </td>
                            </tr>
                            </tbody>
                               ';

                    $selling++;
                }
            }else{
              
                $selling .= '';
            }
            
            return ['buying_stg' => $buying,'selling_stg' => $selling];
        }
        function delete_query($type){
            // to delete user data from setting
            global $user;
            if($type==1){
                $query = sprintf("DELETE FROM buying_milk_stg WHERE `buying_milk_stg`.`bm_id` = %s && `mm_id` = %s",$this->delete_id,$user['id']);
            }elseif($type==2){
                $query = sprintf("DELETE FROM selling_milk_stg WHERE `selling_milk_stg`.`sm_id` = %s && `mm_id` = %s",$this->delete_id,$user['id']);
            }else{
                return false;
            }
            return $this->db->query($query);
        }
        
         function process($type = null){
            global $user, $LNG, $CONF;
               $output = '';
               if($type){
                
                $checking_rate_found = $this->query_checking_rates_found($this->milk_type,$this->animal,$this->mm_id);
                if($checking_rate_found){
                    $output = ['status' => $checking_rate_found, 'url' => ''];
                }else{
                    $output = ['status' => 0, 'url' => permalink($CONF['url'].'/index.php?a=settings&b=milk_rates')];
                }

               }else{
               $arr = $this->validate_inputs(); // Must be stored in a variable before executing an empty condition
                 if(empty($arr) && $this->password = password_verify($this->password, $user['pass'])) {
                    
                // If there is no error message then execute the query;
               // $query = $this->query_ad_setting();
               // $result = $this->validate_return_query($query['query'],$query['type']) or die("query faild");
                try {
                    $query = $this->query_ad_setting();
                    $result = $this->validate_return_query($query['query'],$query['type']);
                } catch (Exception $e) {
                    //echo "Query failed: " . $e->getMessage();
                }
                if($result){
                    $output = notificationBox('info', $LNG['saved'],1);
                }else{
                    $output = notificationBox('error', 'Something Went Wrong. (duplicate Values or Input Fields are Empty)',1);
                }
                }elseif($this->password = !password_verify($this->password, $user['pass'])){
                    $output = notificationBox('error', $LNG['invalid_pass']);
                }
                else{
                    foreach($arr as $err) {
                        $output = notificationBox('error',$err, 1); //  the error value for translation file
                      }
                  }
                }    
                  return $output;
                 
        }
        // here code for calculating milk or uploading milk data checking if rates are saved or not before calculate milk
        public function query_checking_rates_found($milk_type,$animal,$mm_id){
            // (`b_dm` IS NOT NULL OR `b_dm` <> 0)
            // (`b_mfr` IS NOT NULL OR `b_mfr` <> 0)
           switch($milk_type){
            case 'b_dm':
                $query = sprintf("SELECT `b_dm` FROM `buying_milk_stg` WHERE `mm_id` = '%s' AND `m_animal` = '%s' AND (`b_dm` IS NOT NULL AND `b_dm` <> 0)",$this->db->real_escape_string($mm_id),$this->db->real_escape_string($animal));
                break;
            case 'b_mfr':
                $query = sprintf("SELECT `b_mfr` FROM `buying_milk_stg` WHERE `mm_id` = '%s' AND `m_animal` = '%s'  AND (`b_mfr` IS NOT NULL AND `b_mfr` <> 0)",$this->db->real_escape_string($mm_id),$this->db->real_escape_string($animal));
                break;
            case 's_dm':
                $query = sprintf("SELECT `s_dm` FROM `selling_milk_stg` WHERE `mm_id` = '%s' AND `m_animal` = '%s' AND (`s_dm` IS NOT NULL AND `s_dm` <> 0)",$this->db->real_escape_string($mm_id),$this->db->real_escape_string($animal));
                break;
            case 's_mfr':
                $query = sprintf("SELECT `s_mfr` FROM `selling_milk_stg` WHERE `mm_id` = '%s' AND `m_animal` = '%s' AND (`s_mfr` IS NOT NULL AND `s_mfr` <> 0)",$this->db->real_escape_string($mm_id),$this->db->real_escape_string($animal));
                break;
           }
            $result = $this->db->query($query);
            
            if($result && $result->num_rows > 0){
                return 1;
            }
        }
        }
        // For Creating customer 
        class m_customer{
            public $db;
            public $fname;
            public $lname;
            public $p_number;
            public $uname;
            public $pincode;
            public $locality;
            public $profile_pic;
            public $captcha;
            public $otp_val;
            
            public function step1_validation(){
                global $LNG, $settings;
                $error = array();
                if(empty($this->fname)){
                    $error[] .= $LNG['fname_empty'];
                }elseif(empty($this->pincode)){
                    $error[] .=$LNG['pincode_empty'];
                }elseif(empty($this->locality)){
                    $error[] .=$LNG['locality_empty'];
                }else{
                 
                // $alphabetic = ctype_alpha($this->fname);
                $alphabetic = preg_match('/^[A-Za-z ]+$/', $this->fname);
                // checking fname
                if ($alphabetic) {
                $length = strlen($this->fname);

                if ($length < 3) {
                    $error[] .= sprintf($LNG['fname_min'],$length);
                } elseif ($length > $settings['name']) {
                    $error[] .= sprintf($LNG['fname_max'],$settings['name'],$length);
                }
                } else {
                  $error[] .= $LNG['fname_not_alpha'];
                }
                // checking lname if lname is not empty

                if(!empty($this->lname)){
                    $alphabetic2 = preg_match('/^[A-Za-z ]+$/', $this->lname);
                if ($alphabetic2) {
                $length1 = strlen($this->lname);

                if ($length1 < 3) {
                    $error[] .= sprintf($LNG['lname_min'],$length1);
                } elseif ($length1 > $settings['name']) {
                    $error[] .= sprintf($LNG['lname_max'],$settings['name'],$length1);
                }
                } else {
                  $error[] .= $LNG['lname_not_alpha'];
                }
                if (trim($this->fname) === trim($this->lname)) {
                    $error[] .= $LNG['fname_lname_same'];
                  }
                  if(!is_numeric($this->pincode)){
                    $error[]  .= 'Invalid Pincode';
                }else{
                    if(strlen($this->pincode) != 6){
                        $error[]  .= 'Invalid Pincode length';
                    }
                }
            }
        }
                return $error;
            }
            public function process_step1(){

                $arr = $this->step1_validation(); // Must be stored in a variable before executing an empty condition
                     if(empty($arr)){
                    return 1;
                    }else{
                        foreach($arr as $err) {
                            return notificationBox('error',$err, 1); //  the error value for translation file
                          }
                      }
            }
            // public function checking_uname(){
            //     $query = sprintf("SELECT `uname` FROM `m_customers` WHERE `uname` = '%s';",$this->db->real_escape_string($this->uname));
            //     $result = $this->db->query($query);
            //     if($result && !$result->num_rows > 0){
            //         // $result->free(); // Free the result set
            //         return 1;
            //     }
            // }
            public function checking_uname() {
                $query1 = sprintf("SELECT `uname` FROM `m_customers` WHERE `uname` = '%s'", $this->db->real_escape_string($this->uname));
                $query2 = sprintf("SELECT `uname` FROM `mmen` WHERE `uname` = '%s';", $this->db->real_escape_string($this->uname));
            
                // Combine both queries into a single string separated by semicolon
                $combinedQuery = $query1 . ' UNION ' . $query2;
                
                if ($this->db->multi_query($combinedQuery)) {
                    // Iterate through the result sets
                    do {
                        // Check if the current result set has rows
                        if ($result = $this->db->store_result()) {
                            if ($result->num_rows > 0) {
                                $result->free(); // Free the result set

                                return 2; // Username already exists
                            }
                            $result->free(); // Free the result set
                        }
                    } while ($this->db->next_result());

                    return 1; // Username is available
                }
            
                return 0; // Error executing the queries
            }
            public function checking_p_number(){
                $query = sprintf("SELECT `p_number` FROM `m_customers` WHERE `p_number` = '%s';",$this->db->real_escape_string($this->p_number));
                $result = $this->db->query($query);

                if($result && !$result->num_rows > 0){

                    return 1; // if phone number is not used
                }
            }
            // public function checking_p_number() {
            //     $query = sprintf("SELECT `p_number` FROM `m_customers` WHERE `p_number` = '%s';", $this->db->real_escape_string($this->p_number));
            //     $result = $this->db->query($query);

            //     if ($result) {
            //         $result->data_seek(0); // Reset the result pointer
            //         if ($result->num_rows > 0) {
            //             // Rows found
            //             $result->free_result(); // Free the stored result set
            //             return 0; // Phone number is already used
            //         } else {
            //             // No rows found
            //             $result->free_result(); // Free the stored result set
            //             return 1; // Phone number is not used
            //         }
            //     }
            
            //     return -1; // Error occurred during query execution
            // }
            
            public function validate_uname(){ 
                global $LNG, $settings;
                $error = array();
                $uname = $this->uname;
                if(empty($uname)){
                    $error[] .= $LNG['uname_empty'];
                }
                if (strlen($uname) < 3 || strlen($uname) > $settings['uname']) {
                 $error[] .= sprintf($LNG['uname_length'],$settings['uname'],strlen($uname));
                }
                if (preg_match('/^_+$/', $uname)) {
                    $error[] .= $LNG['uname_underscore'];
                  }
                // if (preg_match('/_[0-9]+$/', $uname)) {
                //     $error[] .= $LNG['uname_underscore_num'];
                //   }
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $uname)) {
                    $error[] .= $LNG['uname_contain'];
                }

                if (is_numeric($uname)) {
                    $error[] .= $LNG['uname_numeric'];
                }

                return $error;

            }
            // validate phone number if the phone number is valid.
            public function validate_p_number(){
                global $LNG, $settings;
                $error = array();
                $p_num = $this->p_number;
                if(!empty($p_num)){
                    if (!is_numeric($p_num)) {
                        $error[] .= $LNG['p_num_numeric'];
                    }
                    if (strlen($p_num) < 10 || strlen($p_num) > 10) {
                     $error[] .= sprintf($LNG['p_num_length'],10,strlen($p_num));
                    }
                }
                

                return $error;

            }
            // process Username checking if any error from validation or if available or not available
            public function process_uname(){
                global $LNG, $action, $all_states_name, $all_districts_name,$all_sub_districts_name;
                $arr = $this->validate_uname(); // Must be stored in a variable before executing an empty condition
                if(empty($arr)){
                    $check_uname = $this->checking_uname();
                    $other_values = ['vishal','vishalbhardwaj','vishaldhareru','doodhbazar','doodhwala','milkdairy',
                                      'milkmen','milkman','dudhiya','doodhiya','doodhlelo'
                                    ];
                        $username = strtolower($this->uname);
                        $actionLower = array_map('strtolower', $action);
                        $secondArrayLower = array_map('strtolower', $other_values);
                        $all_states_lower =  array_map('strtolower', $all_states_name);
                        $all_districts_lower =  array_map('strtolower', $all_districts_name);
                        $all_sub_districts_lower =  array_map('strtolower', $all_sub_districts_name);

                   if ((!array_key_exists($username, $actionLower) && !in_array($username, $secondArrayLower) && !in_array($username, $all_states_lower) && !in_array($username, $all_districts_lower) && !in_array($username, $all_sub_districts_lower)) && $check_uname == 1) {
                        return 1;
                    } else {
                        return $LNG['uname_taken'];
                    }

               }else{
                   foreach($arr as $err) {
                       return $err; //  the error value for translation file
                     }
                 }
            }
            public function process_p_number(){
                global $LNG;
                $arr = $this->validate_p_number(); // Must be stored in a variable before executing an empty condition
                if(empty($arr)){
                    $check_p_num = $this->checking_p_number();
                    if($check_p_num){
                        return 1;
                    }else{
                      return 2;
                    }
               }else{
                   foreach($arr as $err) {
                       return $err; //  the error value for translation file
                     }
                 }
            }
            public function final_validate(){
                $step1 = $this->step1_validation(); // Must be stored in a variable before executing an empty condition
                $v_uname = $this->validate_uname();
                $v_phone = $this->validate_p_number();

                $check_uname = $this->checking_uname();
                $check_p_num = $this->checking_p_number();

                if( !$step1 && !$v_uname && !$v_phone && $check_uname == 1 && $check_p_num == 1 ){
                    return 1;
                }else{
                    return 2;
                }
                

            }

            public function otp_verification($p_number){
                $otp = generateOTP();
                $message_id = message_id('customer_otp');

                $uname = "\"@".$this->uname."\"";

                $array = [$uname,$otp];
                if($otp){
                    $sms = new send_sms();
                   $output = $sms->process($message_id,$p_number,$array);
                }else{
                    $output = '{"status":"failed_otp"}';
                }
                return $output;
            }
             public function post_snap($string){
                $value = mt_rand().'_'.mt_rand().'_'.mt_rand().'.png';
                $image = base64Image($string, $value);
                if($image['data']){
                   $path = __DIR__ . '/../uploads/avatars/customers/'.$value;
                    if(file_put_contents($path, $image['data'])){
                      return $value;
                    }else{
                      return false;
                    }
                      
                  }else{
                      return false;
                  }
               

            }
            public function verify_snap(){
                if(!empty($this->profile_pic)){
                    $upload = $this->post_snap($this->profile_pic);
                }else{
                    $upload = 'default.png';
                }
                return $upload;
            }
            function formatDateString($dateString) {
                // Create a DateTime object from the input date string
                $date = DateTime::createFromFormat('j-n-Y', $dateString);
                
                // Format the date to the desired format
                $formattedDate = $date->format('d-m-Y');
                
                return $formattedDate;
            }  
 
            public function Query() {
                global $user;

                $query = sprintf("INSERT INTO `m_customers` (`fname`, `lname`, `uname`, `p_number`,`profile_pic`, `pincode`, `locality`, `joined`, `by`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                    $this->db->real_escape_string(mb_strtolower(trim($this->fname))),
                    trim($this->lname) !== '' ? $this->db->real_escape_string(mb_strtolower(trim($this->lname))) : 'NULL',
                    $this->db->real_escape_string(mb_strtolower(trim($this->uname))),
                    trim($this->p_number) !== '' ? $this->db->real_escape_string(trim($this->p_number)) : 'NULL',
                    $this->db->real_escape_string($this->verify_snap($this->profile_pic)),
                    $this->db->real_escape_string(trim($this->pincode)),
                    $this->db->real_escape_string(mb_strtolower(trim($this->locality))),
                    date('Y-m-d H:i:s'),
                    $user['id']
                );

                // Replace 'NULL' with NULL
                $query = str_replace("'NULL'", "NULL", $query);

                $result = $this->db->query($query);
                
                if ($result !== false) {
                    // if ($result instanceof mysqli_result) {
                    //     $result->free();
                    // }
                    return true;
                } else {
                    error_log("Database query error: " . $this->db->error);
                    return false;
                }
            }
            
            function process(){
                global $user, $LNG;
                
                $incorrect_otp = " 
                <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
               </svg>
               <div class='failed-title'>Incorrect Verification code</div>     
                                 ";
                $validation_error = "

                <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
               </svg>
               <div class='failed-title'>Something going Wrong in Validation</div>              
                                    ";
                $captcha_error = "
                             <div class='failed-title'>Invalid Captcha code</div> 
                                    ";
                $success = "   <div class='success-container'>
                <div class='circle'></div>
                <div class='ring'></div>
                <div class='checkmark'></div>
                <div class='fading-circles'>
                  <div class='circle-animation circle1'></div>
                  <div class='circle-animation circle2'></div>
                  <div class='circle-animation circle3'></div>
                  <div class='circle-animation circle4'></div>
                  <div class='circle-animation circle5'></div>
                  <div class='circle-animation circle6'></div>
                  <div class='circle-animation circle7'></div>
                  <div class='circle-animation circle8'></div>
                  <div class='circle-animation circle9'></div>
                  <div class='circle-animation circle10'></div>
                </div>
              </div> 
                          ";                                   
                if($this->final_validate() == 1){

                     $create_acc = $this->Query();
                     if($create_acc && $this->db->affected_rows >0){

                        // $message_id = message_id('c_acc_created');

                        //  $uname = "\"$this->uname\"";

                        //     $array = [$uname];
                        //     $sms = new send_sms();
                        //     $msg_status = $sms->process($message_id,$this->p_number,$array);
                        $msg_status = false;

                        $output = ['status' => true, 'sms'=> $msg_status, 'error' => '', 'message' => $success];
                     }

                }else{
                    $output = ['status' => false, 'error' => 'not_validate', 'message' => $validation_error];
                }

                return $output;
            }


        }
        // Create a class for m_men HomePage 
        // Showing Analyics for MilkMen

        class mm_home{
            public $db;
            public $url;
            public $username;
            public $user_id;
            public $image;
             
            public function fetch_total_customers(){
                global $user;
                $fetch = array();
               $query = $this->db->query(sprintf("SELECT `c_id`,`fname` FROM m_customers WHERE `by` = %s",$this->db->real_escape_string($user['id'])));
               $query2 = $this->db->query(sprintf("SELECT `c_id`,`fname` FROM m_customers WHERE `by` = %s LIMIT 2",$this->db->real_escape_string($user['id'])));
                if($query->num_rows > 0){
                    $fetch['total'] = $query->num_rows;
                    $fetch['total-'] = $fetch['total'] - 2;
                    $names = '';
                    
                    while($row = $query2->fetch_assoc()){
                        
                        $names .= $row['fname'].',';
                    }
                    $fetch['name'] = explode(',',$names,3);
                    array_pop($fetch['name']);  // remove last empty value from array
                }else{
                    $fetch['total'] = 0;
                    $fetch['name'] = '';
                }
                return $fetch;
            }
          public function filter_customers($type){
            if($type==1){
                $query = sprintf("SELECT count(c_id) as total_without_pic FROM `m_customers` WHERE `profile_pic` = '%s' AND `by` = '%s'",$this->db->real_escape_string('default.png'),$this->db->real_escape_string($this->user_id));
            }elseif($type=2){
                // $query = sprintf("SELECT count(c_id) as total_customers
                // FROM (
                //     SELECT c_id
                //     FROM m_customers
                //     LEFT JOIN taking_milk ON m_customers.c_id = taking_milk.to AND taking_milk.by = '%s'
                //     WHERE taking_milk.to IS NOT NULL AND m_customers.by != '%s' -- Add the condition here
                //     UNION
                //     SELECT c_id
                //     FROM m_customers
                //     LEFT JOIN selling_milk ON m_customers.c_id = selling_milk.to AND selling_milk.by = '%s'
                //     WHERE selling_milk.to IS NOT NULL AND m_customers.by != '%s' -- Add the condition here
                // ) AS subquery;",$this->user_id,$this->user_id,$this->user_id,$this->user_id,);
            
            $query = sprintf("SELECT COUNT(c_id) AS total_customers
                FROM (
                    SELECT c_id
                    FROM m_customers
                    LEFT JOIN buying_milk ON m_customers.c_id = buying_milk.to AND buying_milk.by = '%s'
                    WHERE buying_milk.to IS NOT NULL
                    UNION
                    SELECT c_id
                    FROM m_customers
                    LEFT JOIN selling_milk ON m_customers.c_id = selling_milk.to AND selling_milk.by = '%s'
                    WHERE selling_milk.to IS NOT NULL
                ) AS subquery
                WHERE c_id NOT IN (SELECT c_id FROM m_customers WHERE `by` = '%s')",
                $this->user_id, $this->user_id,$this->user_id);
            }
            $result = $this->db->query($query);
            $output = $result->fetch_assoc();
            return $output;
                
          }  
          public function showUsers() {
                global $user;
                $output = '';
                $query2 = $this->db->query(sprintf("SELECT `profile_pic` FROM m_customers WHERE `by` = %s && `profile_pic` != '' LIMIT 3",$this->db->real_escape_string($user['id'])));
                if($query2->num_rows > 0){
                    $fetch = '';
                    while($row = $query2->fetch_assoc()) {
                        $fetch .= $row['profile_pic'].',';
                    }
                   
                }
                $image = explode(',',$fetch,4);
                // remove last empty value from array
                 array_pop($image);
                if(is_array($image)){
                    foreach($image as $images){
                       $output .= '<img src="'.permalink($this->url.'/image.php?t=c&w=200&h=200&src='.$images).'">'; 
                    }
                }
                 return $output;
                
            }
          public  function total_users_sidebar(){
                global $LNG;
            $total_count = $this->fetch_total_customers();
            $total_without_photo = $this->filter_customers(1)['total_without_pic'];
            $customer = ( $total_without_photo === '1') ? 'customer' : 'customers';
            $show_customers = ($total_without_photo != null && $total_without_photo != 0)
                ? '<div class="sidebar-avatar-edit"><span style="color:#5155c9">'.$total_without_photo.'</span> your '.$customer.' has not uploaded their photo.</div>'
                : '';
            $show_customers_name = ($total_count['total'] != null && $total_count['total'] != 0)
                ? '<div class="sidebar-avatar-edit"><span style="color:#5155c9">'.$total_count['name'][0].' & '.$total_count['name'][1].'</span> are recently joined by you.</div>'
                : '';
            
		    $widget =  '<div class="sidebar-container widget-welcome">
						<div class="sidebar-content">
							<div class="sidebar-header">Total '.$total_count['total'].' User Registered by you.</div>
							<div class="sidebar-inner">
								<div class="sidebar-avatar">'.$this->showUsers().'</div>
								<div class="sidebar-avatar-desc">
									'.$show_customers_name.'
                                    <br>
									'.$show_customers.'
								</div>
							</div>
						</div>
					</div>';
		    return $widget;
            }
         public function check_date($new_date,$old_date){
                $nd = date_create($old_date);
                $od = date_create($new_date);
                $diff = date_diff($nd,$od);
                (int)$day = $diff->format("%R%a");

            }

         
        }
        class search_history{
          public $db;
          public $search_val;
          public $search_by;

          public function query_s_history($search_type,$search_from,$search_by,$search_val){
            $rows = 0;
            $status = false;
            $query = sprintf("INSERT INTO `search_history` (`search_type`,`search_from`,`search_by`,`search_value`) VALUES ('%s','%s','%s','%s')",
                                $this->db->real_escape_string(trim($search_type)),
                                $this->db->real_escape_string(trim($search_from)),
                                $this->db->real_escape_string(trim($search_by)),
                                $this->db->real_escape_string(trim($search_val)));
                                
            $result = $this->db->query($query);
            if($result){
                $rows = $this->db->affected_rows;
                $status = true;
            }
            return ['status' => $status,'type' => 'insert', 'rows' => $rows];
          }

          public static function insert_s_history($search_type, $search_from, $search_by, $search_val,$db) {
            $query = sprintf("SELECT `sh_id` FROM `search_history` WHERE `search_type` = '%s' AND `search_from` = '%s' AND `search_by` = '%s' AND `search_value` = '%s'",
                            $db->real_escape_string(trim($search_type)),
                            $db->real_escape_string(trim($search_from)),
                            $db->real_escape_string(trim($search_by)),
                            $db->real_escape_string(trim($search_val)));
                            
             $result = $db->query($query);

             if($result){
                $rows = $result->num_rows;
                if($rows == 0){
                    $searchHistoryObj = new search_history();
                    $searchHistoryObj->db = $db;
                    $output = $searchHistoryObj->query_s_history($search_type, $search_from, $search_by, $search_val);
                }
             }
             return $output ?? null;
        }
        
        }

        class calculate{
            public $db;
            public $url;
            public $user_id;
            public $search_val;
            public $tgm;
            public $m_animal;
            public $milk_type;

            public $d_rate;
            public $dm_minimum;
            public $dm_limit;

            public $mf;
            public $mf_limit;
            public $mf_minimum;

            public $mf_rate;
            public $mfr_limit;
            public $mfr_minimum;

            public $weight;
            public $w_limit;
            public $w_minimum;
            
            public $date;
            public $time;
            public $total;
            public $by;
            public $to;
            public $cleared;

            public function validate_inputs(){
                global $LNG;
                $error = array();
               
                if(empty($this->tgm) || empty($this->m_animal)|| empty($this->milk_type)){
                    $error[] = sprintf($LNG['Select_box_empty']);
                }
                switch($this->milk_type){
                    case 'b_dm':
                    case 's_dm':
                    if(empty($this->d_rate)){
                        $error[] = sprintf($LNG['dm_empty']);
                    }

                    if(check_dm($this->d_rate,$this->dm_minimum,$this->dm_limit)==1){
                        $error[] = sprintf($LNG['dm_invalid']);
                    }elseif(check_dm($this->d_rate,$this->dm_minimum,$this->dm_limit)==2){
                        $error[] = sprintf($LNG['dm_minimum'],$this->dm_minimum);
                    }elseif(check_dm($this->d_rate,$this->dm_minimum,$this->dm_limit)==3){
                        $error[] = sprintf($LNG['dm_maximum'],$this->dm_limit);
                    }
                    break;

                    case 'b_mfr':
                    case 's_mfr';

                    if(empty($this->mf_rate)){
                        $error[] = sprintf($LNG['mf_empty']);
                    }
                    // Checking Machine Fat is Valid or No.
                    if(check_mf($this->mf,$this->mf_minimum,$this->mf_limit)==1){
                        $error[] = sprintf($LNG['mf_invalid']);
                    }elseif(check_mf($this->mf,$this->mf_minimum,$this->mf_limit)==2){
                        $error[] = sprintf($LNG['mf_decimal']);
                    }elseif(check_mf($this->mf,$this->mf_minimum,$this->mf_limit)==3){
                        $error[] = sprintf($LNG['mf_minimum'],$this->mf_minimum);
                    }elseif(check_mf($this->mf,$this->mf_minimum,$this->mf_limit)==4){
                        $error[] = sprintf($LNG['mf_maximum'],$this->mf_limit);
                    }
                    // Checking Milk Fat Rate is Valid or Not.
                    if(check_mfr($this->mf_rate,$this->mfr_minimum,$this->mfr_limit)==1){
                        $error[] = sprintf($LNG['mfr_invalid']);
                    }elseif(check_mfr($this->mf_rate,$this->mfr_minimum,$this->mfr_limit)==2){
                        $error[] = sprintf($LNG['mfr_decimal']);
                    }elseif(check_mfr($this->mf_rate,$this->mfr_minimum,$this->mfr_limit)==3){
                        $error[] = sprintf($LNG['mfr_minimum'],$this->mf_minimum);
                    }elseif(check_mfr($this->mf_rate,$this->mfr_minimum,$this->mfr_limit)==4){
                        $error[] = sprintf($LNG['mfr_maximum'],$this->mf_limit);
                    }
                    break;
                }

                if(empty($this->weight)){
                    $error[] = sprintf($LNG['weight_empty']);
                }
                if(empty($this->search_val)){
                    $error[] = sprintf($LNG['p_num_empty']);
                }
                // checking inputs values are valid ?
                if(check_weight($this->weight,$this->w_minimum,$this->w_limit) == 1){
                    $error[] = sprintf($LNG['invalid_weight']);
                }elseif(check_weight($this->weight,$this->w_minimum,$this->w_limit) == 2){
                    $error[] = sprintf($LNG['weight_decimal_invalid']);
                }elseif(check_weight($this->weight,$this->w_minimum,$this->w_limit) == 3){
                    $error[] = sprintf($LNG['minimum_weight'],$this->w_minimum);
                }elseif(check_weight($this->weight,$this->w_minimum,$this->w_limit) == 4){
                    $error[] = sprintf($LNG['weight_limit'], $this->w_limit);
                }
                return $error;
            }
  
            public function validate_phone(){
               
                $error = array();

                if($this->search_term(null,$this->search_val)['x'] !== 1){
                    $error[] .= 'cal_search_not_reg';
                }

                if($this->search_term(1,$this->search_val)['x']!==1){
                    $error[] .= 'cal_search_limit';
                }
                if(empty($this->search_val)){
                    $error[] .= 'cal_search_empty';
                }

               
                return $error;
            }
            public function search_term($type=null,$search_val){
                
                $searchVal = trim(mb_strtolower($search_val));
                $escapedSearchVal = $this->db->real_escape_string($searchVal);
                $x = 0;
                $output = null;
                if(!$type){

                    if (preg_match('/^\d{10}$/', $escapedSearchVal)) {
                        $query = sprintf("SELECT m.`c_id`, m.`fname`, m.`lname`, m.`uname`, m.`p_number`, m.`profile_pic`, m.`locality`, w.*
                                          FROM m_customers m
                                          LEFT JOIN list w ON m.c_id = w.to AND w.by = '%s'
                                          WHERE m.p_number = '%s';",$this->user_id,$escapedSearchVal);
                    } else {
                        $query = sprintf("SELECT m.`c_id`, m.`fname`, m.`lname`, m.`uname`, m.`p_number`, m.`profile_pic`, m.`locality`, w.*
                                          FROM m_customers m
                                          LEFT JOIN list w ON m.c_id = w.to AND w.by = '%s'
                                          WHERE m.uname = '%s';",$this->user_id,$escapedSearchVal);
                    }

                    $result = $this->db->query($query);
                    
                    if ($result && $result->num_rows > 0) {
                        $x = 1;
                        $output = $result->fetch_assoc();
                    }
                
                    
            }else{
                   
                if (preg_match('/^[a-zA-Z0-9_]{3,15}$/', $this->search_val)) {
                    $x = 1;
                    }
            }
            return ['x' => $x, 'data' => $output];
            }

            public function mmen_setting($type){
                global $user;
                // Get animal From User Setting Save
                $array = [];
                if($type==1){
                    $query = sprintf("SELECT `m_animal`,`name` FROM `buying_milk_stg` LEFT JOIN `m_animal` ON `buying_milk_stg`.`m_animal` = `m_animal`.`a_id` WHERE `mm_id` = %s;", $user['id']);
                }elseif($type==2){
                    $query = sprintf("SELECT `m_animal`,`name` FROM `selling_milk_stg` LEFT JOIN `m_animal` ON `selling_milk_stg`.`m_animal` = `m_animal`.`a_id` WHERE `mm_id` = %s;", $user['id']);
                }
                    $result = $this->db->query($query);
                    while ($row = $result->fetch_assoc()) {
                       $rows[] = $row;
                    }
                    return $rows;
              }
              function load_animal(){
                // fetch all milk animal data with the help for loop.
                $rows = '';
                $result = $this->mmen_setting($this->tgm);

                if($result){
                    while($row = $result){
                      $rows .= "
                      <option value='".$row['a_id']."'>".$row['name']."</option>
                      ";
                      $rows++;
                    }
                }else{
                    $rows .= "Please Setting Up Advance Setting.";
                }
                return $rows;
                
            } 
            // public function get_milk_rate($type,$animal){
            //     global $user;
            //     if($type==1){
            //         $query = sprintf("SELECT `b_dm`, `b_mfr` FROM `buying_milk_stg` WHERE `mm_id` = %s AND `m_animal` = %s",$user['id'],$animal);
            //     }elseif($type==2){
            //         $query = sprintf("SELECT `s_dm`, `s_mfr` FROM `selling_milk_stg` WHERE `mm_id` = %s AND `m_animal` = %s",$user['id'],$animal);
            //     }
               
            //     $run = $this->db->query($query);
            //     if($run && $run->num_rows > 0){
            //         $result = $run->fetch_assoc();
            //     }
            //     return ['dm' => ($result['b_dm']) ? ($result['b_dm']) : ($result['s_dm']) , 'mfr' => ($result['b_mfr']) ? ($result['b_mfr']) : $result['s_mfr']];
                
            // }
            public function check_milk_type($bsm,$milk_type){
                global $settings;
                $stg = get_milk_rate($this->db,$this->user_id,$bsm,$this->m_animal);
                if($bsm == 1){
                    switch($milk_type){
                        case "b_dm":
                            $output = "
                            <div class='message-form-des'>5
                              <input type='number' name='dm' id='dm_id' onkeyup='checking_inputs(\"dm\",\"#dm_id\",[".$settings['d_rate_minimum'].",".$settings['d_rate_maximum']."],\"dm_error\")' value='". ($stg['dm'] ?? 'not data found') . "' type='any' placeholder='No data Found.' disabled>
                              <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='dm_error'></div>
                              <div class='switch-holder'>
                            <div class='switch-label'>
                            <i class='fa-solid fa-pencil'></i><span>Personal Rate</span>
                            </div>
                            <div class='switch-toggle'>
                                <input type='checkbox' id='personal_rate'>
                                <label for='personal_rate'></label>
                            </div>
                        </div>

                              
                              </div>
                            <div class='message-form-des'>6
                               <input type='number' name='weight' id='weight_id' onkeyup='checking_inputs(\"weight\",\"#weight_id\",[".$settings['weight_minimum'].",".$settings['weight_maximum']."],\"weight_error\")' type='any' placeholder='weight like 1.000'>
                               <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='weight_error'></div>
                               <div id='weight_msg' class='page-input-sub' style='margin:-10px 0px 0px 30px'>In Weight After Decimal there are 3 Digit should be. </div>
                                  </div>
                                       ";
                            break;

                        case "b_mfr":
                            $output = "<div class='message-form-des'>5
                            <input type='number' name='mfr' id='mfr_id' onkeyup='checking_inputs(\"mfr\",\"#mfr_id\",[".$settings['f_rate_minimum'].",".$settings['f_rate_maximum']."],\"mfr_error\")' value='". ($stg['mfr'] ?? 'no data found') . "'  type='any' value='{$stg['mfr']}' placeholder='No Data Found' disabled>
                            <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='mfr_error'></div>

                            <div class='switch-holder'>
                            <div class='switch-label'>
                            <i class='fa-solid fa-pencil'></i><span>Personal Rate</span>
                            </div>
                            <div class='switch-toggle'>
                                <input type='checkbox' id='personal_rate'>
                                <label for='personal_rate'></label>
                            </div>
                            </div>

                            <div class='message-form-des'>6
                            <input type='number' name='fat' id='fat_id'  onkeyup='checking_inputs(\"mf\",\"#fat_id\",[".$settings['mf_minimum'].",".$settings['mf_maximum']."],\"fat_error\")' type='any' placeholder='Fat From machine'>
                            <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='fat_error'></div>
                            <div id='fat_msg' class='page-input-sub' style='margin:-10px 0px 0px 30px'>Whatever You Recieve From Machine {$settings['mf_minimum']} to {$settings['mf_maximum']}</div>  
                            </div>
                          <div class='message-form-des'>7
                             <input type='number' name='weight' id='weight_id' onkeyup='checking_inputs(\"weight\",\"#weight_id\",[".$settings['weight_minimum'].",".$settings['weight_maximum']."],\"weight_error\")' type='any' placeholder='weight like 1.000'>
                             <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='weight_error'></div>
                             <div id='weight_msg' class='page-input-sub' style='margin:-10px 0px 0px 30px'>In Weight After Decimal there are 3 Digit should be. </div>
                                </div>";
                            break;

                    }
                }elseif($bsm == 2){
                    switch($milk_type){
                        case "s_dm":
                            $output = "
                            <div class='message-form-des'>5
                            <input type='number' name='dm' id='dm_id' onkeyup='checking_inputs(\"dm\",\"#dm_id\",[".$settings['d_rate_minimum'].",".$settings['d_rate_maximum']."],\"dm_error\")' value='". ($stg['dm'] ?? 'not data display') . "' value='{$stg['dm']}' step='any' placeholder='No data found.' disabled>
                            <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='dm_error'></div>

                            <div class='switch-holder'>
                            <div class='switch-label'>
                            <i class='fa-solid fa-pencil'></i><span>Personal Rate</span>
                            </div>
                            <div class='switch-toggle'>
                                <input type='checkbox' id='personal_rate'>
                                <label for='personal_rate'></label>
                                </div>
                            </div>

                          <div class='message-form-des'>6
                             <input type='number' name='weight' id='weight_id' onkeyup='checking_inputs(\"weight\",\"#weight_id\",[".$settings['weight_minimum'].",".$settings['weight_maximum']."],\"weight_error\")' step='any' placeholder='weight like 1.000'/>
                             <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='weight_error'></div>
                             <div id='weight_msg' class='page-input-sub' style='margin:-10px 0px 0px 30px'>In Weight After Decimal there are 3 Digit should be. </div>
                                </div>
                                        ";
                            break;

                        case "s_mfr":
                            $output = "
                            <div class='message-form-des'>5
                            <input type='number' name='mfr' id='mfr_id'  onkeyup='checking_inputs(\"mfr\",\"#mfr_id\",[".$settings['f_rate_minimum'].",".$settings['f_rate_maximum']."],\"mfr_error\")' value='". ($stg['mfr'] ?? 'not data display') . "' value='{$stg['mfr']}' step='any' placeholder='No data found.' disabled>
                            <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='mfr_error'></div>

                            <div class='switch-holder'>
                            <div class='switch-label'>
                            <i class='fa-solid fa-pencil'></i><span>Personal Rate</span>
                            </div>
                            <div class='switch-toggle'>
                                <input type='checkbox' id='personal_rate'>
                                <label for='personal_rate'></label>
                                </div>
                            </div>

                            <div class='message-form-des'>6
                            <input type='number' name='fat' id='fat_id'  onkeyup='checking_inputs(\"mf\",\"#fat_id\",[".$settings['mf_minimum'].",".$settings['mf_maximum']."],\"fat_error\")'  step='any'>
                            <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='fat_error'></div>
                            <div id='fat_msg' class='page-input-sub' style='margin:-10px 0px 0px 30px'>like 1.0 to 12.0</div>  
                            </div>
                          <div class='message-form-des'>7
                             <input type='number' name='weight' id='weight_id' onkeyup='checking_inputs(\"weight\",\"#weight_id\",[".$settings['weight_minimum'].",".$settings['weight_maximum']."],\"weight_error\")' step='any' placeholder='weight like 1.000'>
                             <div class='page-input-sub-error' style='margin:-5px 0px 0px 30px' id='weight_error'></div>
                             <div id='weight_msg' class='page-input-sub' style='margin:-10px 0px 0px 30px'>In Weight After Decimal there are 3 Digit should be. </div>
                                </div>
                                     ";
                            break;

                    }
                }
                return $output;
            }

            public function get_c_id(){
                $query = sprintf("SELECT `c_id` FROM `m_customers` WHERE `p_number` = '%s' OR `uname` = '%s' ",$this->db->real_escape_string($this->search_val),$this->db->real_escape_string($this->search_val));
                
                $run_sql = $this->db->query($query);
                $result = null; // Initialize the result variable
                if($run_sql->num_rows > 0){
                    $result = $run_sql->fetch_assoc();
                }
                
                return $result; 
            }
            public function calculate_submit($tgm,$milk_type){
                global $user;
                $data = $this->get_c_id();
                $mf_total = round($this->mf * $this->mf_rate * $this->weight,2);
                $dm_total = round($this->d_rate * $this->weight,2);
                $c_id = $data['c_id'];
                $cleared = $this->cleared ?? 0;
                if($tgm == 1){

                    switch($milk_type){
                        case "b_dm":
                            $query = sprintf("INSERT INTO `buying_milk` ( `milk_animal`, `milk_type`, `d_rate`, `weight`, `date`, `total`, `by`, `to`, `cleared`) VALUES (%s, '%s', %s, %s, '%s', %s, %s, %s, %s)",$this->db->real_escape_string($this->m_animal),$this->db->real_escape_string('dm'),$this->db->real_escape_string($this->d_rate),$this->db->real_escape_string($this->weight),$this->db->real_escape_string(date("Y-m-d H:i:s")),$this->db->real_escape_string($dm_total),$this->db->real_escape_string($user['id']),$this->db->real_escape_string($c_id),$this->db->real_escape_string($cleared));
                            break;
                        case "b_mfr": 
                            $query = sprintf("INSERT INTO `buying_milk` ( `milk_animal`, `milk_type`, `fat`, `fat_rate`, `weight`, `date`, `total`, `by`, `to`, `cleared`) VALUES (%s, '%s', %s, %s, %s, '%s', %s, %s, %s,%s)",$this->db->real_escape_string($this->m_animal),$this->db->real_escape_string('mf'),$this->db->real_escape_string($this->mf),$this->db->real_escape_string($this->mf_rate),$this->db->real_escape_string($this->weight),$this->db->real_escape_string(date("Y-m-d H:i:s")),$this->db->real_escape_string($mf_total),$this->db->real_escape_string($user['id']),$this->db->real_escape_string($c_id),$this->db->real_escape_string($cleared));
                            break;
                    } 
                }elseif($tgm == 2){
                    switch($milk_type){
                    case "s_dm":
                        $query = sprintf("INSERT INTO `selling_milk` ( `milk_animal`, `milk_type`, `d_rate`, `weight`, `date`,`total`, `by`, `to`, `cleared`) VALUES (%s, '%s', %s, %s, '%s', %s, %s, %s, %s)",$this->db->real_escape_string($this->m_animal),$this->db->real_escape_string('dm'),$this->db->real_escape_string($this->d_rate),$this->db->real_escape_string($this->weight),$this->db->real_escape_string(date("Y-m-d H:i:s")),$this->db->real_escape_string($dm_total),$this->db->real_escape_string($user['id']),$this->db->real_escape_string($c_id),$this->db->real_escape_string($cleared));
                        break;
                    case "s_mfr": 
                        $query = sprintf("INSERT INTO `selling_milk` ( `milk_animal`, `milk_type`, `fat`, `fat_rate`, `weight`, `date`, `total`, `by`, `to`, `cleared`) VALUES (%s, '%s', %s, %s, %s, '%s', %s, %s, %s,%s)",$this->db->real_escape_string($this->m_animal),$this->db->real_escape_string('mf'),$this->db->real_escape_string($this->mf),$this->db->real_escape_string($this->mf_rate),$this->db->real_escape_string($this->weight),$this->db->real_escape_string(date("Y-m-d H:i:s")),$this->db->real_escape_string($mf_total),$this->db->real_escape_string($user['id']),$this->db->real_escape_string($c_id),$this->db->real_escape_string($cleared));
                        break;

                }
                }
                $result = $this->db->query($query);
                if ($result) {
                    if ($this->db->affected_rows > 0) {
                        return 1; // Query executed successfully and affected rows > 0
                    } else {
                        return 3; // Query executed successfully but no rows affected
                    }
                } else {
                    if ($this->db->errno == 2006) {
                        return 2; // Query timed out
                    } else {
                        return 3; // Query failed
                    }
                }
               
                

            }


            public function process($type){
                global $LNG;
                if($type == 1){
                    $arr = $this->validate_phone();
                   
                    if(empty($arr)){
                        
                        $result = $this->search_term(null,$this->search_val)['data'];

                        if(!empty($result['l_id'])){
                            $wishlist_available = "<div style='color:red;font-weight:600;'>This customer Added in your Wishlist</div>";
                        }
                        
                        $p_number = ($result['p_number']) ? formatPhoneNumber($result['p_number']): 'No_Phone';
                        $output['table'] = "<div id='calculate_table' class='table-data''><table style='width:100%'>
                            <table>
                            <tbody>
                            <tr>
                            <th>Profile Pic</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Phone</th>
                            <th>Locality</th>
                            </tr> 
                            
                              <tr>
                              <td><img src=".permalink($this->url.'/image.php?t=c&w=70&h=70&src='.$result['profile_pic'])."></td>
                              <td>".formatFullName($result['fname'],$result['lname'])."</td>
                              <td>".$result['uname']."</td>
                              <td>".$p_number."</td>
                              <td>".formatlocality($result['locality'])."</td>
                              </tr>
                      </tbody>
                      </table></div>".$wishlist_available;
                      $output['input_fields'] = $this->check_milk_type($this->tgm,$this->milk_type);
                    }else{
                        foreach($arr as $err) {
                            $output['error'] = notificationBox('error',$LNG[$err], 1); //  the error value for translation file
                          }
                      }

                }elseif($type==2){
                    $arr = $this->validate_inputs();
                    
                    if(empty($arr)){
                        $result = $this->calculate_submit($this->tgm,$this->milk_type);
                        if($result ==1){
                            $output['submited'] = "
                            <div class='success-container'>
                            <div class='circle'></div>
                            <div class='ring'></div>
                            <div class='checkmark'></div>
                            <div class='fading-circles'>
                              <div class='circle-animation circle1'></div>
                              <div class='circle-animation circle2'></div>
                              <div class='circle-animation circle3'></div>
                              <div class='circle-animation circle4'></div>
                              <div class='circle-animation circle5'></div>
                              <div class='circle-animation circle6'></div>
                              <div class='circle-animation circle7'></div>
                              <div class='circle-animation circle8'></div>
                              <div class='circle-animation circle9'></div>
                              <div class='circle-animation circle10'></div>
                              </div>
                              </div>   
                              <div class='success-title'>".$LNG['saved']."</div>     
                                                  ";
                        }elseif($result ==2){
                            $output['submited'] = "<div class='failed-title'>Failed: Request timeout Please try again</div>";
                        }elseif($result ==3){
                            $output['submited'] = "<div class='failed-title'>Query Failed</div>";
                        }
                    }else{
                        
                        foreach($arr as $err) {
                            $output['submit_error'] = "
                                                        <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                                                        <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                                                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                                                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
                                                       </svg>
                                                       <div class='failed-title'>$err</div>
                                                      ";
                            
                            
                            
                            // notificationBox('error',$err, 1); //  the error value for translation file
                        
                          }
                    }
                }
                return $output;
            }
        }

        class user_list{
            public $db;
            public $url;
            public $search_val;
            public $milk_about;
            public $animal_type;

            public $d_rate;
            public $weight;

            public $dm_min;
            public $dm_limit;

            public $weight_min;
            public $weight_max;
            public $customer_id;
            public $user_id;

            public function search_term($search_term) {
                $searchVal = trim(mb_strtolower($search_term));
                $escapedSearchVal = $this->db->real_escape_string($searchVal);
                $rows = 0;
                $data = null;
            
                if (preg_match('/^[a-z0-9_]+$/i', $searchVal)) {
                  if (preg_match('/^\d{10}$/', $escapedSearchVal)) {
                        $query = sprintf("SELECT m.`c_id`, m.`fname`, m.`lname`, m.`uname`, m.`p_number`, m.`profile_pic`, m.`locality`, w.*
                                          FROM m_customers m
                                          LEFT JOIN list w ON m.c_id = w.to AND w.by = '%s'
                                          WHERE m.p_number = '%s';",$this->user_id,$escapedSearchVal);
                    } else {
                        $query = sprintf("SELECT m.`c_id`, m.`fname`, m.`lname`, m.`uname`, m.`p_number`, m.`profile_pic`, m.`locality`, w.*
                                          FROM m_customers m
                                          LEFT JOIN list w ON m.c_id = w.to AND w.by = '%s'
                                          WHERE m.uname = '%s';",$this->user_id,$escapedSearchVal);
                    }
                    
                    $result = $this->db->query($query);
            
                    if ($result && $result->num_rows > 0) {
                        $rows = 1;
                        $data = $result->fetch_assoc();
                        if (isset($data['p_number'])) {
                            // Update the "p_number" value using the formatPhoneNumber() function
                            $formattedPhoneNumber = formatPhoneNumber($data['p_number']);
                            $data['p_number'] = $formattedPhoneNumber;
                        }
                    }
                }
            
                return ['rows' => $rows, 'data' => $data];
            }
            
            public function validate_list(){
                 global $LNG;
                  $error = array();

                  // checking customer exist or not
                  if(empty($this->customer_id)){
                    $error[] .= 'Customer is not Defined';
                  }elseif(!is_numeric($this->customer_id)){
                    $error[] .= 'Invalid Customer';
                  }
                  // checking milk type
                  if(empty($this->milk_about)){
                    $error[] .= 'Milk Type Empty';
                  }elseif(!empty($this->milk_about) && $this->milk_about > 2){
                    $error[] .= 'Invalid Milk type';
                  }
                  // checking animal
                  if(empty($this->animal_type)){
                    $error[] .= 'Milk Animal Empty';
                  }elseif($this->animal_type > 2){
                    $error[] .= 'Invalid milk Animal';
                  }

                  // checking direct rate
                  if(empty($this->d_rate)){
                    $error[] = sprintf($LNG['dm_empty']);
                    }elseif(check_dm($this->d_rate,$this->dm_min,$this->dm_limit)==1){
                        $error[] = sprintf($LNG['dm_invalid']);
                    }elseif(check_dm($this->d_rate,$this->dm_min,$this->dm_limit)==2){
                        $error[] = sprintf($LNG['dm_minimum'],$this->dm_min);
                    }elseif(check_dm($this->d_rate,$this->dm_min,$this->dm_limit)==3){
                        $error[] = sprintf($LNG['dm_maximum'],$this->dm_limit);
                    }
                    // checking weight
            
                    if(empty($this->weight)){
                        
                        $error[] = sprintf($LNG['weight_empty']);
                    }elseif(check_weight($this->weight,$this->weight_min,$this->weight_max) == 1){
                    $error[] = sprintf($LNG['invalid_weight']);
                    }elseif(check_weight($this->weight,$this->weight_min,$this->weight_max) == 2){
                        $error[] = sprintf($LNG['weight_decimal_invalid']);
                    }elseif(check_weight($this->weight,$this->weight_min,$this->weight_max) == 3){
                        $error[] = sprintf($LNG['minimum_weight'],$this->weight_min);
                    }elseif(check_weight($this->weight,$this->weight_min,$this->weight_max) == 4){
                        $error[] = sprintf($LNG['weight_limit'], $this->weight_max);
                    }   
    
                    return $error;
            }
            public function customer_list(){
                $query = sprintf('SELECT * FROM `list` WHERE `to` = %s AND `by` = %s',$this->db->real_escape_string($this->customer_id),$this->db->real_escape_string($this->user_id));
                $result = $this->db->query($query);
                 $num_rows = $result->num_rows;
                if($result && $num_rows > 0){
                   $output = $result->fetch_assoc();
                }

                return $output;
                
            }
            public function query_list($type=null,$milk_about,$milk_animal,$by,$to){
                if(!$type){
                    $query = sprintf("INSERT INTO `list` (`milk_about`,`milk_animal`,`milk_type`,`d_rate`,`weight`, `date`,`by`,`to`) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s')",
                $this->db->real_escape_string($milk_about),
                $this->db->real_escape_string($milk_animal),
                $this->db->real_escape_string('dm'),
                $this->db->real_escape_string($this->d_rate),
                $this->db->real_escape_string($this->weight),
                $this->db->real_escape_string(date('Y-m-d H:i:s')),
                $this->db->real_escape_string($by),
                $this->db->real_escape_string($to),
                 );
                }else{
                    $query = sprintf("UPDATE `list` SET `milk_about` = '%s', `milk_animal` = '%s', `d_rate` = '%s', `weight` = '%s', `date` = '%s' WHERE `to` = '%s' AND `by` = '%s'",
                    $this->db->real_escape_string($milk_about),
                    $this->db->real_escape_string($milk_animal),
                    $this->db->real_escape_string($this->d_rate),
                    $this->db->real_escape_string($this->weight),
                    $this->db->real_escape_string(date('Y-m-d H:i:s')),
                    $this->db->real_escape_string($this->customer_id),
                    $this->db->real_escape_string($this->user_id),
                 );
                }
                 $result = $this->db->query($query);
                 return $result;
                 
            }
            
            function getlist($start,$per_page) {
                global $CONF, $LNG;
            
                $startClause = '';
                if ($start != 0) {
                    $startClause = 'AND `w_id` < \'' . $this->db->real_escape_string($start) . '\'';
                }
            
                $query = sprintf("SELECT
                list.l_id,
                list.milk_about,
                list.milk_animal,
                list.d_rate,
                list.weight,
                list.by,
                list.to,
                m_customers.c_id,
                m_customers.fname,
                m_customers.lname,
                m_customers.p_number,
                m_customers.uname,
                m_customers.profile_pic
                FROM
                    list
                LEFT JOIN
                    m_customers
                ON
                    list.to = m_customers.c_id
                WHERE list.by = '%s' %s
                ORDER BY list.l_id DESC
                LIMIT %s",
                $this->user_id,
                $startClause,
                $this->db->real_escape_string($per_page + 1)
                );
                $result = $this->db->query($query);
                $rows = [];
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                $wishlist = $loadmore = '';
                if(!empty($rows)){
    
                if (array_key_exists($per_page, $rows)) {
                    $loadmore = 1;
                    // Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
                    array_pop($rows);
                }
               
                foreach ($rows as $row) {
                    $r_num = rand(100, 999);
                    $wishlist .= '
                    
                    <form method="post" id="list_submit'.$r_num.'" autocomplete="off">
                        <div class="message-content">
                            <div class="message-inner bg-grey">
                                <div id="loading_bar" class="d-none">
                                    <div class="load_more">
                                        <div class="preloader preloader-center"></div>
                                    </div>
                                </div>
                                <div class="profile-section" id="profile_section">
                                    <div class="profile-photo" id="profile_pic">
                                        <img src="'.$this->url.'/image.php?src='.$row['profile_pic'].'&t=c&w=100&h=100">
                                    </div>
                                    <div class="profile-details">
                                        <div class="profile-name" id="full_name">'.formatFullName($row['fname'],$row['lname']).'</div>
                                        <div class="profile-username" id="uname">@'.$row['uname'].'</div>
                                        <div class="profile-phone" id="p_num">+91 '.formatPhoneNumber($row['p_number']).'</div>
                                        <div class="profile-locality" id="locality">'.$row['locality'].'</div>
                                        <input type="hidden" name="customer_id" id="customer_id'.$r_num.'" value="'.$row['c_id'].'">
                                    </div>
                                </div>
                                <div id="error_msg" class="d-none">
                                    <b>No User Found try with correct username or Phone number</b>
                                </div>
                                <div id="submit_status'.$r_num.'"></div>
                                <!-- here wishlist input form fields -->
                                <div class="form-section">
                                    <div class="column">
                                        <select class="select-box" id="milk_about'.$r_num.'" name="milk_about">
                                            <option value="0">Milk type</option>
                                            <option value="1" '.(($row['milk_about'] == 1) ? 'selected="selected"' : '').'>Buy milk</option>
                                            <option value="2" '.(($row['milk_about'] == 2) ? 'selected="selected"' : '').'>Sale Milk</option>
                                        </select>
                                        <select class="select-box" id="load-animals'.$r_num.'" name="m_animal" onchange="load_rates(\'#milk_about'.$r_num.'\', \'#load-animals'.$r_num.'\', \'#d_rate'.$r_num.'\')">
                                            <option value="0">select animal</option>
                                            <option value="1" '.(($row['milk_animal'] == 1) ? 'selected="selected"' : '').'>buffalo</option>
                                            <option value="2" '.(($row['milk_animal'] == 2) ? 'selected="selected"' : '').'>cow</option>
                                        </select>
                                    </div>
                                    <div class="column">
                                        <input type="number" class="input-box" id="d_rate'.$r_num.'" name="d_rate" value="'.$row['d_rate'].'" placeholder="Direct Rate">
                                        <input type="number" class="input-box" id="weight'.$r_num.'" name="weight" value="'.$row['weight'].'" placeholder="weight">
                                    </div>
                                </div>
                                <div class="button-section">
                                    <button class="save-button" id="save_btn" onclick="insert_list(\'#list_submit'.$r_num.'\', [\'#customer_id'.$r_num.'\', \'#submit_status'.$r_num.'\'], event)">Update</button>
                                    <button class="delete-button" id="delete_btn" onclick="delete_list(\'#customer_id'.$r_num.'\',[\'#milk_about'.$r_num.'\',\'#load-animals'.$r_num.'\',\'#d_rate'.$r_num.'\',\'#weight'.$r_num.'\',\'#submit_status'.$r_num.'\'],event)">Delete</button>
                                </div>
                                <!-- form section for wishlist ended -->
                            </div>
                        </div>
                    </form><br>';
                

            
                    $last = $row['w_id'];
                }
                if ($loadmore) {
                     $wishlist .= '<div class="btn-wide btn-normal" id="more_list" onclick="load_wishlist(' . ($last) . ','.$per_page.')">' . $LNG['load_more'] . '</div>';
                 }
    
                }else{
                    $wishlist .= '<div class="error"><b>No record found</b></div>';
                 }
                    // Return the user list
                    return $wishlist;
        }
            public function get_all_list($user_id){
                $query = sprintf("SELECT
                list.l_id,
                list.milk_about,
                list.milk_animal,
                list.d_rate,
                list.weight,
                list.by,
                list.to,
                m_customers.c_id,
                m_customers.fname,
                m_customers.lname,
                m_customers.p_number,
                m_customers.uname,
                m_customers.profile_pic
                FROM
                    list
                LEFT JOIN
                    m_customers
                ON
                    list.to = m_customers.c_id
                WHERE list.by = '%s'
                ORDER BY list.l_id DESC ",
                $user_id
                );
                $result = $this->db->query($query);
                $rows = [];
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                $wishlist = '';
                if(!empty($rows)){
               
                foreach ($rows as $row) {
                    $r_num = uniqid(); // Unique random number
                    $wishlist .= '
                       <fieldset id="fieldset'.$r_num.'" disabled>
                        <div class="message-content">
                            <div class="message-inner bg-grey">
                                <div class="profile-section">
                                    <div class="profile-photo">
                                        <img src="'.$this->url.'/image.php?src='.$row['profile_pic'].'&t=c&w=100&h=100" style="border: 3px solid #fff;">
                                    </div>
                                    <div class="profile-details">
                                        <div class="profile-name">'.formatFullName($row['fname'],$row['lname']).'</div>
                                        <div class="profile-username">@'.$row['uname'].'</div>
                                        <div class="profile-phone">+91 '.formatPhoneNumber($row['p_number']).'</div>
                                        <div class="profile-locality">'.$row['locality'].'</div>
                                        <input type="hidden" name="customer_id[]" id="customer_id'.$r_num.'" value="'.$row['c_id'].'">
                                        <input type="hidden" name="customer_uname[]" value="'.$row['uname'].'">
                                    </div>
                                </div>
                                <!-- here wishlist input form fields -->
                                <div class="form-section">
                                    <div class="column">
                                        <select class="select-box" id="milk_about'.$r_num.'" name="milk_about[]" required>
                                            <option value="0">Milk type</option>
                                            <option value="1" '.(($row['milk_about'] == 1) ? 'selected="selected"' : '').'>Buy milk</option>
                                            <option value="2" '.(($row['milk_about'] == 2) ? 'selected="selected"' : '').'>Sale Milk</option>
                                        </select>
                                        <select class="select-box" id="load-animals'.$r_num.'" name="m_animal[]" onchange="load_rates(\'#milk_about'.$r_num.'\', \'#load-animals'.$r_num.'\', \'#d_rate'.$r_num.'\')">
                                            <option value="0">select animal</option>
                                            <option value="1" '.(($row['milk_animal'] == 1) ? 'selected="selected"' : '').'>buffalo</option>
                                            <option value="2" '.(($row['milk_animal'] == 2) ? 'selected="selected"' : '').'>cow</option>
                                        </select>
                                    </div>
                                    <div class="column">
                                        <input type="text" class="input-box" id="d_rate'.$r_num.'" name="d_rate[]" value="'.$row['d_rate'].'" placeholder="Direct Rate" required>
                                        <input type="text" class="input-box" id="weight'.$r_num.'" name="weight[]" value="'.$row['weight'].'" placeholder="weight" required>
                                    </div>
                                </div>
                                <center><div class="form-checkbox">
                                <input id="checkbox'.$r_num.'" type="checkbox" class="switch"  onclick="update_checkbox(this,\'#money_received'.$r_num.'\')">
                                <input type="hidden" id="money_received'.$r_num.'"  name="money_received[]" value="0">
                                <label for="checkbox'.$r_num.'">Money Received?</label>
                                </div></center>
                                </fieldset>
                                <div class="button-section">
                                 <input  id="checkbox'.$r_num.'" type="checkbox" class="Lock-red" style=" width: 40px !important" onclick="ignore_inputs(this, \'#fieldset'.$r_num.'\')">
                                </div>
                                <!-- form section for wishlist ended -->
                            </div>
                        </div>
                    <br>';
                }
    
                }else{
                    $wishlist .= '<div class="error"><b>No Customer list found.</b></div>';
                 }

                 return $wishlist;

            }

            public function delete_from_list($to,$by){
                $query = sprintf(
                        "DELETE FROM `list` WHERE `to` = '%s' AND `by` = '%s' ",
                         $this->db->real_escape_string($to),$this->db->real_escape_string($by));
                $result = $this->db->query($query);
                if($result){
                    $deletedRows = $this->db->affected_rows;
                    if ($deletedRows > 0) {
                        return ['status' => true, 'deleted_rows' => $deletedRows, 'msg' => 'DELETED_SUCESSFULL'];
                    } else {
                        return ['status' => false, 'deleted_rows' => $deletedRows, 'msg' => 'DELETED_UNSUCESSFULL'];
                    }
                }else{
                    return ['status' => false, 'deleted_rows' => 0, 'msg' => 'QUERY_FAILED'];
                }
            }
            
            public function process($type){
                if($type==1){
                    $output = $this->search_term($this->search_val);
                }elseif($type==2){
                    $error = $this->validate_list();
                    if(empty($error)){
                        if(empty($this->customer_list())){
                            $query = $this->query_list(0,$this->milk_about,$this->animal_type,$this->user_id,$this->customer_id);
                            if($query && $this->db->affected_rows > 0){
                                $output = ['status' => true, 'error' => '', 'msg' => 'LIST_INSERTED'];
                            }else{
                                $output = ['status' => false, 'error' => 'could not update', 'msg' => 'WISHLIST_NOT_INSERTED' ];
                            }

                        }else{
                            $query = $this->query_list(1,$this->milk_about,$this->animal_type,$this->user_id,$this->customer_id);
                            if($query && $this->db->affected_rows > 0){
                                $output = ['status' => true, 'error' => '','msg' => 'LIST_UPDATE' ];
                            }else{
                                
                                $output = ['status' => false, 'error' => 'could not update', 'msg' => 'LIST_NOT_UPDATE' ];
                            }
                        }
                        
                    }else{
                        foreach($error as $err) {
                            $output = ['status' => false, 'error' => $err ];
                          }
                    }
                    
                }

                return $output;
            }
        }

        class add_milk_wishlist{
            public $db;
            public $url;
            public $user_id;
            public $customer_id;
            public $customer_uname;
            public $milk_about;
            public $milk_animal;
            public $d_rate;
            public $weight;

            public $dm_min;
            public $dm_limit;

            public $weight_min;
            public $weight_max;

            public function data_array() {
                // Initialize the main array to store data for buy_milk and sold_milk
                $data = array(
                    'buying_milk' => array(),
                    'selling_milk' => array(),
                    'user_info' => array()
                );
            
                // Check if milk_about is not empty and proceed
                if (!empty($_POST['milk_about'])) {
                    // Loop through the submitted data
                    for ($i = 0; $i < count($_POST['milk_about']); $i++) {
                        // Check the value of milk_about
                        $milk_about = $_POST['milk_about'][$i];
                        $milk_animal = $_POST['m_animal'][$i];
                        $d_rate = $_POST['d_rate'][$i];
                        $weight = $_POST['weight'][$i];
                        $money_received =  $_POST['money_received'][$i];
                        // Create an array with the current form data
                        $current_data = array(
                            'customer_id' => $_POST['customer_id'][$i],
                            'mmen_id'  => $this->user_id,
                            'customer_uname' => $_POST['customer_uname'][$i],
                            'milk_animal' => $milk_animal,
                            'd_rate' => $d_rate,
                            'weight' => $weight,
                            'money_received' => $money_received
                        );
            
                        // Add the current data to the appropriate array based on milk_about value
                        if ($milk_about == 1) {
                            $data['buying_milk'][] = $current_data;
                        } elseif ($milk_about == 2) {
                            $data['selling_milk'][] = $current_data;
                        }
                    }
                    
                }
                $data['user_info'] = [
                    'DEVICE_INFO' => 
                    [
                        'TYPE' =>                     $_POST['DEVICE_TYPE'],
                        'SCREEN_RESOLUTION' =>        $_POST['DEVICE_SCREEN_RESOLUTION'],
                        'RAM' =>                      $_POST['RAM'],
                        'LANGUAGE' =>                 $_POST['LANGUAGE'],
                        'VIEWPORT_WIDTH' =>           $_POST['VIEWPORT_WIDTH'],
                        'STORAGE_PERMISSION' =>       $_POST['STORAGE_PERMISSION'],
                        'CAMERA_PERMISSION' =>        $_POST['CAMERA_PERMISSION'],
                        'BROWSER_INFO' =>             $_POST['BROWSER_INFO'],
                        'TIME_ZONE' =>                $_POST['TIME_ZONE'],
                        'DEVICE_TIME' =>              $_POST['DEVICE_TIME'],
                        'MOUSE' =>                    $_POST['DEVICE_MOUSE'],
                        'TOUCH_SCREEN' =>             $_POST['DEVICE_TOUCH_SCREEN'],
                    ],
                    'LOCATION_DATA' =>
                    [
                        'LATITUDE' =>                $_POST['LATITUDE'],
                        'LONGITUDE' =>               $_POST['LONGITUDE'],
                        'LOCATION' =>                json_decode($_POST['LOCATION_DATA']),
                    ]
                    ];
                return $data;
            }
            
        public function validate_inputs($data){
            $errors = array();
        
            // Check if both 'buying_milk' and 'selling_milk' keys are empty arrays
            if (empty($data['buying_milk']) && empty($data['selling_milk'])) {
                $errors[] = "Both Buy milk and Sold milk data are missing.";
            }
        
            // Check 'buying_milk' key
            if (!empty($data['buying_milk'])) {
                foreach ($data['buying_milk'] as $index => $buy_milk_data) {
                    $customer_id = $buy_milk_data['customer_id'];
                    $customer_uname = $buy_milk_data['customer_uname'];
        
                    if (empty($customer_id)) {
                        $errors[] = "Incomplete data found for Buying milk entry #" . ($index + 1) . " where customer id is blank.";
                    } else {
                        $d_rate_error = check_dm($buy_milk_data['d_rate'], $this->dm_min, $this->dm_limit);
                        if ($d_rate_error != 0) {
                            $errors[] = "Invalid Direct Milk rate for Buy milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                        }
        
                        $weight_error = check_weight($buy_milk_data['weight'], $this->weight_min, $this->weight_max);
                        if ($weight_error != 0) {
                            $errors[] = "Invalid weight for Buying milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                        }
        
                        if (!isset($buy_milk_data['milk_animal']) || !in_array($buy_milk_data['milk_animal'], array(1, 2))) {
                            $errors[] = "Invalid or missing 'milk_animal' for Buy milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                        }
                    }
                }
            }
        
            // Check 'selling_milk' key
            if (!empty($data['selling_milk'])) {
                foreach ($data['selling_milk'] as $index => $sold_milk_data) {
                    $customer_id = $sold_milk_data['customer_id'];
                    $customer_uname = $sold_milk_data['customer_uname'];
                    if (empty($customer_id)) {
                        $errors[] = "Incomplete data found for Selling milk entry #" . ($index + 1) . " where customer id is blank.";
                    } else {
                        $d_rate_error = check_dm($sold_milk_data['d_rate'], $this->dm_min, $this->dm_limit);
                        if ($d_rate_error != 0) {
                            $errors[] = "Invalid Direct milk for Selling milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                        }
        
                        $weight_error = check_weight($sold_milk_data['weight'], $this->weight_min, $this->weight_max);
                        if ($weight_error != 0) {
                            $errors[] = "Invalid weight for Selling milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                        }
        
                        if (!isset($sold_milk_data['milk_animal']) || !in_array($sold_milk_data['milk_animal'], array(1, 2))) {
                            $errors[] = "Invalid or missing 'milk_animal' for Selling milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                        }
                    }
                }
            }
        
            // Return the array of errors
            return $errors;
        }

        public function wishlist_query($data) {
            $buying_milk_query = '';
            $selling_milk_query = '';
        
            if (!empty($data['buying_milk'])) {
                $buying_milk_query_values = [];
                foreach ($data['buying_milk'] as $buying_milk_data) {
                    $customer_id = $this->db->real_escape_string(trim($buying_milk_data['customer_id']));
                    $mmen_id = $this->db->real_escape_string(trim($buying_milk_data['mmen_id']));
                    $milk_animal = $this->db->real_escape_string(trim($buying_milk_data['milk_animal']));
                    $d_rate = $this->db->real_escape_string(trim($buying_milk_data['d_rate']));
                    $weight = $this->db->real_escape_string(trim($buying_milk_data['weight']));
                    $total = round($d_rate * $weight, 2);
                    $money_received = $this->db->real_escape_string($buying_milk_data['money_received']);
                    $date = date('Y-m-d H:i:s');

                    $buying_milk_query_values[] = "('$milk_animal', 'wishlist_dm', '$d_rate', '$weight', '$date', '$total', '0', $money_received, '$mmen_id', '$customer_id')";
                }
        
                if (!empty($buying_milk_query_values)) {
                    $buy_milk_values_string = implode(',', $buying_milk_query_values);
                    $buying_milk_query = "INSERT INTO buying_milk (`milk_animal`,`milk_type`, `d_rate`, `weight`, `date`, `total`, `edited`, `cleared`, `by`,`to`) VALUES $buy_milk_values_string";
                }
            }
        
            if (!empty($data['selling_milk'])) {
                $selling_milk_query_values = [];
                foreach ($data['selling_milk'] as $selling_milk_data) {
                    $customer_id = $this->db->real_escape_string(trim($selling_milk_data['customer_id']));
                    $mmen_id = $this->db->real_escape_string(trim($selling_milk_data['mmen_id']));
                    $milk_animal = $this->db->real_escape_string(trim($selling_milk_data['milk_animal']));
                    $d_rate = $this->db->real_escape_string(trim($selling_milk_data['d_rate']));
                    $weight = $this->db->real_escape_string(trim($selling_milk_data['weight']));
                    $total = round($d_rate * $weight, 2);

                    $money_received = $this->db->real_escape_string($selling_milk_data['money_received']);
                    $date = date('Y-m-d H:i:s');
                    $selling_milk_query_values[] = "('$milk_animal', 'wishlist_dm', '$d_rate', '$weight', '$date', '$total', '0', $money_received, '$mmen_id', '$customer_id')";
                }
        
                if (!empty($selling_milk_query_values)) {
                    $sold_milk_values_string = implode(',', $selling_milk_query_values);
                    $selling_milk_query = "INSERT INTO selling_milk (`milk_animal`,`milk_type`, `d_rate`, `weight`, `date`, `total`, `edited`, `cleared`, `by`,`to`) VALUES $sold_milk_values_string";
                }
            }
        
            return ['buying_milk' => $buying_milk_query, 'selling_milk' => $selling_milk_query];
        }
        
        
        public function do_query($data) {
            $queries = $this->wishlist_query($data);
            // Start the transaction
            $this->db->begin_transaction();
        
            $status = true;
            $affected_rows = 0;
            $msg = 'INSERTED';
            $error = '';
        
            if (!empty($queries['buying_milk'])) {
                $query_buying = $queries['buying_milk'];
                $result_buying = $this->db->query($query_buying);
        
                if (!$result_buying) {
                    $status = false;
                    $msg = 'NOT_INSERTED';
                    $error = 'QUERY_FAILED';
                } else {
                    $affected_rows += $this->db->affected_rows;
                }
            }
        
            if (!empty($queries['selling_milk'])) {
                $query_selling = $queries['selling_milk'];
                $result_selling = $this->db->query($query_selling);
        
                if (!$result_selling) {
                    $status = false;
                    $msg = 'NOT_INSERTED';
                    $error = 'QUERY_FAILED';
                } else {
                    $affected_rows += $this->db->affected_rows;
                }
            }
        
            // Commit or rollback the transaction based on status
            if ($status) {
                $this->db->commit();
            } else {
                $this->db->rollback();
            }
        
            return ['status' => $status, 'rows' => $affected_rows, 'msg' => $msg, 'error' => $error];
        }
        

        // Wishlist history method using the existing database connection
        public function wishlist_delivered_history($customer_id, $customer_uname, $total) {
            $id = implode(', ', $customer_id);
            $uname = implode(', ', $customer_uname);
            $data = $this->data_array();

            $json_data = $this->history_info($data);

        
            $query = sprintf("INSERT INTO `list_history` (`c_id`, `type`, `c_uname`, `total`, `more_info`, `date`, `by`) VALUES ('%s', '%s', '%s', '%s','%s','%s','%s')",
                $this->db->real_escape_string($id),
                $this->db->real_escape_string('wishlist'),
                $this->db->real_escape_string($uname),
                $this->db->real_escape_string($total),
                $this->db->real_escape_string($json_data),
                $this->db->real_escape_string(date('Y-m-d H:i:s')),
                $this->db->real_escape_string($this->user_id)
            );
            // Execute the main query
            $result = $this->db->query($query);

            if ($result) {
                $status = true;
                $affected_rows = $this->db->affected_rows;
                $msg = 'INSERTED';
                $error = '';
            } else {
                $status = false;
                $affected_rows = 0;
                $msg = 'NOT_INSERTED';
                $error = 'QUERY_FAILED';
            }

            return ['status' => $status, 'rows' => $affected_rows, 'msg' => $msg, 'error' => $error];
        }
            public function history_info($data) {


            // Calculate the values for the 'BOUGHT_MILK' sub-array
            $buying_milk_count = array_count_values(array_column($data['buying_milk'], 'milk_animal'));
            $buying_milk_quentity = [];
            $total_d_rate_buying = 0;
            $total_weight_buying = 0;
            $buying_milk_c_uname = [];
            $buying_milk_c_id = [];
            $buying_milk_money_received = [];
            $total_amount_buying = $total_amount_buying_money_received = 0;
            foreach ($data['buying_milk'] as $item) {
                $animal = ($item['milk_animal'] == '1') ? 'BUFFALO' : (($item['milk_animal'] == '2') ? 'COW' : 'OTHERS');
                if (!isset($buying_milk_quentity[$animal])) {
                    $buying_milk_quentity[$animal] = 0;
                }
                $buying_milk_quentity[$animal] += $item['weight'];
                $total_d_rate_buying += $item['d_rate'];
                $total_weight_buying += $item['weight'];
                $buying_milk_c_uname[] = $item['customer_uname'];
                $buying_milk_c_id[] = $item['customer_id'];
                $buying_milk_money_received[] = $item['money_received'];

                $total_amount_buying += round($item['d_rate'] * $item['weight'], 2);
                // Calculate the total amount for money_received
                $total_amount_buying_money_received += $item['money_received'] > 0 ? round($item['d_rate'] * $item['weight'], 2) : 0;
            }
            
            $buying_milk_total = [
                'TOTAL_RECORDS' => count($data['buying_milk']),
                'TOTAL_D_RATE' => $total_d_rate_buying,
                'TOTAL_WEIGHT' => $total_weight_buying,
                'TOTAL_AMOUNT' => $total_amount_buying,
                'TOTAL_ANIMAL' => [
                    'COUNT' => [
                        'BUFFALO' => $buying_milk_count[1] ?? 0,
                        'COW' => $buying_milk_count[2] ?? 0,
                        'OTHERS' => $buying_milk_count[0] ?? 0,
                    ],
                    'QUENTITY' => [
                        'BUFFALO' => $buying_milk_quentity['BUFFALO'] ?? 0,
                        'COW' => $buying_milk_quentity['COW'] ?? 0,
                        'OTHERS' => $buying_milk_quentity['OTHERS'] ?? 0,
                    ],
                ],
                'C_UNAME' => $buying_milk_c_uname,
                'C_ID' => $buying_milk_c_id,
                'MONEY_RECEIVED' => [
                    'TOTAL_RECORDS' => count(array_filter($buying_milk_money_received, function ($value) { return $value > 0; })),
                    'TOTAL_AMOUNT' => $total_amount_buying_money_received,
                    'FROM_C_UNAME' => array_filter($buying_milk_c_uname, function ($value, $key) use ($buying_milk_money_received) { return $buying_milk_money_received[$key] > 0; }, ARRAY_FILTER_USE_BOTH),
                    'FROM_C_ID' => array_filter($buying_milk_c_id, function ($value, $key) use ($buying_milk_money_received) { return $buying_milk_money_received[$key] > 0; }, ARRAY_FILTER_USE_BOTH),
                ],
                // Rest of the data for 'BOUGHT_MILK'
            ];
            
            // Calculate the values for the 'SOLD_MILK' sub-array (similar to BOUGHT_MILK)
            $selling_milk_count = array_count_values(array_column($data['selling_milk'], 'milk_animal'));
            $selling_milk_quentity = [];
            $total_d_rate_selling = 0;
            $total_weight_selling = 0;
            $selling_milk_c_uname = [];
            $selling_milk_c_id = [];
            $selling_milk_money_received = [];
            $total_amount_selling = $total_amount_selling_money_received = 0;
            foreach ($data['selling_milk'] as $item) {
                $animal = ($item['milk_animal'] == '1') ? 'BUFFALO' : (($item['milk_animal'] == '2') ? 'COW' : 'OTHERS');
                if (!isset($selling_milk_quentity[$animal])) {
                    $selling_milk_quentity[$animal] = 0;
                }
                $selling_milk_quentity[$animal] += $item['weight'];
                $total_d_rate_selling += $item['d_rate'];
                $total_weight_selling += $item['weight'];
                $selling_milk_c_uname[] = $item['customer_uname'];
                $selling_milk_c_id[] = $item['customer_id'];
                $selling_milk_money_received[] = $item['money_received'];
                
                $total_amount_selling += round($item['d_rate'] * $item['weight'], 2);
                // Calculate the total amount for money_received
                $total_amount_selling_money_received += $item['money_received'] > 0 ? round($item['d_rate'] * $item['weight'], 2) : 0;
            }
            
            $selling_milk_total = [
                'TOTAL_RECORDS' => count($data['selling_milk']),
                'TOTAL_D_RATE' => $total_d_rate_selling,
                'TOTAL_WEIGHT' => $total_weight_selling,
                'TOTAL_AMOUNT' =>  $total_amount_selling,
                'TOTAL_ANIMAL' => [
                    'COUNT' => [
                        'BUFFALO' => $selling_milk_count[1] ?? 0,
                        'COW' => $selling_milk_count[2] ?? 0,
                        'OTHERS' => $selling_milk_count[0] ?? 0,
                    ],
                    'QUENTITY' => [
                        'BUFFALO' => $selling_milk_quentity['BUFFALO'] ?? 0,
                        'COW' => $selling_milk_quentity['COW'] ?? 0,
                        'OTHERS' => $selling_milk_quentity['OTHERS'] ?? 0,
                    ],
                ],
                'C_UNAME' => $selling_milk_c_uname,
                'C_ID' => $selling_milk_c_id,
                'MONEY_RECEIVED' => [
                    'TOTAL_RECORDS' => count(array_filter($selling_milk_money_received, function ($value) { return $value > 0; })),
                    'TOTAL_AMOUNT' =>  $total_amount_selling_money_received,
                    'FROM_C_UNAME' => array_filter($selling_milk_c_uname, function ($value, $key) use ($selling_milk_money_received) { return $selling_milk_money_received[$key] > 0; }, ARRAY_FILTER_USE_BOTH),
                    'FROM_C_ID' => array_filter($selling_milk_c_id, function ($value, $key) use ($selling_milk_money_received) { return $selling_milk_money_received[$key] > 0; }, ARRAY_FILTER_USE_BOTH),
                ],
                // Rest of the data for 'SOLD_MILK'
            ];
            
            // Calculate the values for the 'TOTAL_DATA' section
            $total_animal_count = [
                'COUNT' => [
                    'BUFFALO' => ($buying_milk_count[1] ?? 0) + ($selling_milk_count[1] ?? 0),
                    'COW' => ($buying_milk_count[2] ?? 0) + ($selling_milk_count[2] ?? 0),
                    'OTHERS' => ($buying_milk_count[0] ?? 0) + ($selling_milk_count[0] ?? 0),
                ],
                'QUENTITY' => [
                    'BUFFALO' => ($buying_milk_quentity['BUFFALO'] ?? 0) + ($selling_milk_quentity['BUFFALO'] ?? 0),
                    'COW' => ($buying_milk_quentity['COW'] ?? 0) + ($selling_milk_quentity['COW'] ?? 0),
                    'OTHERS' => ($buying_milk_quentity['OTHERS'] ?? 0) + ($selling_milk_quentity['OTHERS'] ?? 0),
                ],
            ];
            
            $total_data = [
                'TOTAL_RECORDS' => $buying_milk_total['TOTAL_RECORDS'] + $selling_milk_total['TOTAL_RECORDS'],
                'TOTAL_D_RATE' => $buying_milk_total['TOTAL_D_RATE'] + $selling_milk_total['TOTAL_D_RATE'],
                'TOTAL_WEIGHT' => $buying_milk_total['TOTAL_WEIGHT'] + $selling_milk_total['TOTAL_WEIGHT'],
                'TOTAL_AMOUNT' => $buying_milk_total['TOTAL_AMOUNT'] + $selling_milk_total['TOTAL_AMOUNT'],
                'TOTAL_ANIMAL' => $total_animal_count,
                'MONEY_RECEIVED' => [
                    'TOTAL_RECORDS' => $buying_milk_total['MONEY_RECEIVED']['TOTAL_RECORDS'] + $selling_milk_total['MONEY_RECEIVED']['TOTAL_RECORDS'],
                    'TOTAL_AMOUNT' => $buying_milk_total['MONEY_RECEIVED']['TOTAL_AMOUNT'] + $selling_milk_total['MONEY_RECEIVED']['TOTAL_AMOUNT'],
                    'FROM_C_UNAME' => array_merge($buying_milk_total['MONEY_RECEIVED']['FROM_C_UNAME'], $selling_milk_total['MONEY_RECEIVED']['FROM_C_UNAME']),
                    'FROM_C_ID' => array_merge($buying_milk_total['MONEY_RECEIVED']['FROM_C_ID'], $selling_milk_total['MONEY_RECEIVED']['FROM_C_ID']),
                ],
                // Rest of the data for 'TOTAL_DATA'
            ];
            
            // Calculate the value for the 'PROFIT' section
            $profit_type = ($buying_milk_total['TOTAL_AMOUNT'] >= $selling_milk_total['TOTAL_AMOUNT']) ? 'LOSS' : 'PROFIT';
            $profit_from = ($profit_type === 'PROFIT') ? 'SOLD_MILK' : 'BOUGHT_MILK';
            $profit_dual_data = (count($data['buying_milk']) > 0 && count($data['selling_milk']) > 0) ? 'YES' : 'NO';
            $profit_total = abs($buying_milk_total['TOTAL_AMOUNT'] - $selling_milk_total['TOTAL_AMOUNT']);
            
            $profit_data = [
                'PROFIT' => [
                    'TYPE' => $profit_type,
                    'FROM' => $profit_from,
                    'DUAL_DATA' => $profit_dual_data,
                    'TOTAL' => $profit_total,
                ],
            ];

            $date = [
                'DATE' =>  date('Y-m-d H:i:s'),
                'TIMESTAMP'=> time()
            ];
            // Combine all the data into the final $result array
            $result = [
                'BOUGHT_MILK' => $buying_milk_total,
                'SOLD_MILK' => $selling_milk_total,
                'TOTAL_DATA' => $total_data,
                'DATA_DIFFERENCE' => $profit_data,
                'TIME' => $date,
                'USER_INFO' => $data['user_info']
                // Rest of the data (if any)
            ];
            // Determine 'type' and 'available'
        // Determine 'type' and 'available'
        $count = (count($data['buying_milk']) > 0 && count($data['selling_milk']) > 0) ? 2 : 1;
        $available = (count($data['buying_milk']) > 0 && count($data['selling_milk']) > 0) ? 'BOTH' : (count($data['buying_milk']) > 0 ? 'BOUGHT_MILK' : 'SOLD_MILK');

        // Wrap everything in the $final_result array
        $final_result = [
            'count' => $count,
            'available' => $available,
            'data' => $result,
        ];

        return json_encode($final_result,JSON_UNESCAPED_UNICODE| JSON_UNESCAPED_SLASHES);
        }

       
        public function get_wishlist_history($user_id){
            $query = sprintf('SELECT `c_uname`, `type`, `total`, `date` FROM list_history WHERE `by` = \'%s\' ORDER BY `date` DESC LIMIT 3 ',$this->db->real_escape_string($user_id));
            $result = $this->db->query($query);
            if($result) {
                while ($row = $result->fetch_assoc()) {
                    $wishlist_history[] = $row;
                }
            $formated = $this->format_wishlist_Array($wishlist_history);
                $result->free_result(); // Free the result set
            }
            return $formated;
        }
        public function format_wishlist_Array($data) {
            foreach ($data as &$item) {
                // Convert date format
                $item['date'] = date("j M h:iA", strtotime($item['date']));

                // Convert comma-separated c_uname values to an array
                $item['c_uname'] = explode(', ', $item['c_uname']);
            }
            unset($item); // Unset reference to the last element to avoid potential side effects

            return $data;
        }

        public function process(){
            $data = $this->data_array();
            $error = $this->validate_inputs($data);
            // $this->wishlist_delivered_history($this->customer_id,$this->customer_uname,4);
            // die;
            if(empty($error)){
                
                $insert = $this->do_query($data);
                if($insert['status'] === true && $insert['rows'] > 0){
                    $this->wishlist_delivered_history($this->customer_id,$this->customer_uname,$insert['rows']);

                    $status = true;
                    $title = 'MILK_DATA_INSERTED';
                    $rows = $insert['rows'];
                    $errr = '';
                }
            }else{
                foreach($error as $err) {
                    $status = false;
                    $title = 'MILK_DATA_NOT_INSERTED';
                    $rows = '';
                    $errr = $err;
                  }
            }
            return ['status' => $status, 'title' => $title, 'rows' => $rows, 'error' => $errr];
        }
        
        
        }

        class add_milk_masterlist{
            public $db;
            public $url;
            public $user_id;
            public $search_val;

            public $customer_id;
            public $customer_uname;
            public $milk_about;
            public $milk_animal;
            public $d_rate;
            public $weight;

            public $dm_min;
            public $dm_limit;

            public $weight_min;
            public $weight_max;

            public $money_received;
            public $is_locked;

            public function search_term($search_term) {
                $searchVal = trim(mb_strtolower($search_term));
                $escapedSearchVal = $this->db->real_escape_string($searchVal);
                $rows = 0;
                $data = null;
            
                if (preg_match('/^[a-z0-9_]+$/i', $searchVal)) {
                    if (preg_match('/^\d{10}$/', $escapedSearchVal)) {
                        $query = sprintf("SELECT m.`c_id`, m.`fname`, m.`lname`, m.`uname`, m.`p_number`, m.`profile_pic`, m.`locality`, w.*
                                          FROM m_customers m
                                          LEFT JOIN list w ON m.c_id = w.to AND w.by = '%s'
                                          WHERE m.p_number = '%s' AND w.to IS NOT NULL;", $this->user_id, $escapedSearchVal);
                    } else {
                        $query = sprintf("SELECT m.`c_id`, m.`fname`, m.`lname`, m.`uname`, m.`p_number`, m.`profile_pic`, m.`locality`, w.*
                                          FROM m_customers m
                                          LEFT JOIN list w ON m.c_id = w.to AND w.by = '%s'
                                          WHERE m.uname = '%s' AND w.to IS NOT NULL;", $this->user_id, $escapedSearchVal);
                    }

                    $result = $this->db->query($query);
                
                    if ($result && $result->num_rows > 0) {
                        $rows = 1;
                        $data = $result->fetch_assoc();
                        if (isset($data['p_number'])) {
                            // Update the "p_number" value using the formatPhoneNumber() function
                            $formattedPhoneNumber = formatPhoneNumber($data['p_number']);
                            $data['p_number'] = $formattedPhoneNumber;
                        }
                    }
                    
                }
                
            
                return ['rows' => $rows, 'data' => $data];
            }
            public function data_array($array) {
                $milk_about =                $array['milk_about'];
                $milk_animal =               $array['milk_animal'];
                $d_rate =                    $array['d_rate'];
                $weight =                    $array['weight'];
                $customer_id =               $array['c_id'];
                $customer_uname =            $array['c_uname'];
                $mman_id =                   $array['mman_id'];

                $money_received =             $array['money_received'] ?? 0;
                $is_locked =                  $array['is_locked'] ?? 0 ;
                // Initialize the main array to store data for buy_milk and sold_milk
                $data = array(
                    'buying_milk' => array(),
                    'selling_milk' => array(),
                    // 'user_info' => array()
                );
            
                // Check if milk_about is not empty and proceed
                if (!empty($milk_about)) {
                    // Loop through the submitted data
                    for ($i = 0; $i < count($milk_about); $i++) {
                        // Check the value of milk_about
                        // $milk_about_ = $milk_about[$i];
                        // $milk_animal_ = $milk_animal[$i];
                        // $d_rate_ = $d_rate[$i];
                        // $weight_ = $weight[$i];
                        // $money_received_ =  $money_received[$i];
                        // $is_locked_ =  $is_locked[$i];
                        // Create an array with the current form data
                        $current_data = array(
                            'customer_id' => $customer_id[$i],
                            'mman_id'  => $mman_id[$i],
                            'customer_uname' => $customer_uname[$i],
                            'milk_animal' => $milk_animal[$i],
                            'd_rate' => $d_rate[$i],
                            'weight' => $weight[$i],
                            'money_received' => $money_received[$i],
                            'is_locked' => $is_locked[$i],
                        );
            
                        // Add the current data to the appropriate array based on milk_about value
                        if ($milk_about[$i] == 1) {
                            $data['buying_milk'][] = $current_data;
                        } elseif ($milk_about[$i] == 2) {
                            $data['selling_milk'][] = $current_data;
                        }
                    }
                    
                }
                $data['user_info'] = [
                    'DEVICE_INFO' => 
                    [
                        'TYPE' =>                     $_POST['DEVICE_TYPE'],
                        'SCREEN_RESOLUTION' =>        $_POST['DEVICE_SCREEN_RESOLUTION'],
                        'RAM' =>                      $_POST['RAM'],
                        'LANGUAGE' =>                 $_POST['LANGUAGE'],
                        'VIEWPORT_WIDTH' =>           $_POST['VIEWPORT_WIDTH'],
                        'STORAGE_PERMISSION' =>       $_POST['STORAGE_PERMISSION'],
                        'CAMERA_PERMISSION' =>        $_POST['CAMERA_PERMISSION'],
                        'BROWSER_INFO' =>             $_POST['BROWSER_INFO'],
                        'TIME_ZONE' =>                $_POST['TIME_ZONE'],
                        'DEVICE_TIME' =>              $_POST['DEVICE_TIME'],
                        'MOUSE' =>                    $_POST['DEVICE_MOUSE'],
                        'TOUCH_SCREEN' =>             $_POST['DEVICE_TOUCH_SCREEN'],
                    ],
                    'LOCATION_DATA' =>
                    [
                        'LATITUDE' =>                $_POST['LATITUDE'],
                        'LONGITUDE' =>               $_POST['LONGITUDE'],
                        'LOCATION' =>                json_decode($_POST['LOCATION_DATA']),
                    ]
                    ];
                return $data;
            }
            public function get_data_from_type($type){
                $db_list_array = [];
                $form_list_array = [];

                if($type){
                    $list_db = $this->call_list($this->user_id);
                    foreach ($list_db as $item) {
                        $db_list_array['milk_about'][] = $item['milk_about'];
                        $db_list_array['milk_animal'][] = $item['milk_animal'];
                        $db_list_array['d_rate'][] = $item['d_rate'];
                        $db_list_array['weight'][] = $item['weight'];
                        $db_list_array['c_id'][] = $item['to'];
                        $db_list_array['c_uname'][] = $item['uname'];
                        $db_list_array['mman_id'][] = $item['by'];
                        $db_list_array['money_received'][] = 0;
                        $db_list_array['is_locked'][] = 0;
    
                        // You can add more keys similarly
                       
                    }
                    return $this->data_array($db_list_array);

                    }else{
                       
                        $form_list_array = [
                            'milk_about' => $this->milk_about,
                            'milk_animal' => $this->milk_animal,
                            'd_rate' => $this->d_rate,
                            'weight' => $this->weight,
                            'c_id' => $this->customer_id,
                            'c_uname' => $this->customer_uname,
                            'mman_id' => !empty($this->milk_about) ? array_fill(0, count($this->milk_about), $this->user_id) : array(),
                            'money_received' => $this->money_received,
                            'is_locked' => $this->is_locked,
                        ];
                        return $this->data_array($form_list_array);
                    }
                }
            
            public function final_data() {
               $db_data = $this->get_data_from_type(1);
               $form_data = $this->get_data_from_type(0);
                
                return ['db_data' => $db_data, 'form_data' => $form_data];
                
            }
            public function filter_original_data($final_data){
                $filtered_buying_milk = [];
                $filtered_selling_milk = [];

                foreach ($final_data['db_data']['buying_milk'] as $db_buying_milk_item) {
                    $customer_id = $db_buying_milk_item['customer_id'];
                    $exists_in_form = false;

                    foreach ($final_data['form_data']['buying_milk'] as $form_buying_milk_item) {
                        if ($form_buying_milk_item['customer_id'] === $customer_id) {
                            $exists_in_form = true;
                            break;
                        }
                    }

                    if (!$exists_in_form) {
                        $filtered_buying_milk[] = $db_buying_milk_item;
                    }
                }

                foreach ($final_data['db_data']['selling_milk'] as $db_selling_milk_item) {
                    $customer_id = $db_selling_milk_item['customer_id'];
                    $exists_in_form = false;

                    foreach ($final_data['form_data']['selling_milk'] as $form_selling_milk_item) {
                        if ($form_selling_milk_item['customer_id'] === $customer_id) {
                            $exists_in_form = true;
                            break;
                        }
                    }

                    if (!$exists_in_form) {
                        $filtered_selling_milk[] = $db_selling_milk_item;
                    }
                }

                $filtered_final_data = [
                    'buying_milk' => array_merge($filtered_buying_milk, $final_data['form_data']['buying_milk']),
                    'selling_milk' => array_merge($filtered_selling_milk, $final_data['form_data']['selling_milk']),
                ];

                foreach ($filtered_final_data as $type => $data) {
                    $filtered_original = array_filter($data, function ($item) {
                        return $item['is_locked'] != 1;
                    });
                    
                    $original_data[$type] = array_values($filtered_original);
                }
                return $original_data;
            }
            public function get_original_data(){
                $final_data = $this->final_data();
                $original_data = $this->filter_original_data($final_data);
                return $original_data;
            }
            
            public function validate_inputs($data){
                $errors = array();
            
                // Check if both 'buying_milk' and 'selling_milk' keys are empty arrays
                // if (empty($data['buying_milk']) && empty($data['selling_milk'])) {
                //     $errors[] = "Both Buy milk and Sold milk data are missing.";
                // }
            
                // Check 'buying_milk' key
                if (!empty($data['buying_milk'])) {
                    foreach ($data['buying_milk'] as $index => $buy_milk_data) {
                        $customer_id = $buy_milk_data['customer_id'];
                        $customer_uname = $buy_milk_data['customer_uname'];
            
                        if (empty($customer_id)) {
                            $errors[] = "Incomplete data found for Buying milk entry #" . ($index + 1) . " where customer id is blank.";
                        } else {
                            $d_rate_error = check_dm($buy_milk_data['d_rate'], $this->dm_min, $this->dm_limit);
                            if ($d_rate_error != 0) {
                                $errors[] = "Invalid Direct Milk rate for Buy milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                            }
            
                            $weight_error = check_weight($buy_milk_data['weight'], $this->weight_min, $this->weight_max);
                            if ($weight_error != 0) {
                                $errors[] = "Invalid weight for Buying milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                            }
            
                            if (!isset($buy_milk_data['milk_animal']) || !in_array($buy_milk_data['milk_animal'], array(1, 2))) {
                                $errors[] = "Invalid or missing 'milk_animal' for Buy milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                            }
                        }
                    }
                }
            
                // Check 'selling_milk' key
                if (!empty($data['selling_milk'])) {
                    foreach ($data['selling_milk'] as $index => $sold_milk_data) {
                        $customer_id = $sold_milk_data['customer_id'];
                        $customer_uname = $sold_milk_data['customer_uname'];
                        if (empty($customer_id)) {
                            $errors[] = "Incomplete data found for Selling milk entry #" . ($index + 1) . " where customer id is blank.";
                        } else {
                            $d_rate_error = check_dm($sold_milk_data['d_rate'], $this->dm_min, $this->dm_limit);
                            if ($d_rate_error != 0) {
                                $errors[] = "Invalid Direct milk for Selling milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                            }
            
                            $weight_error = check_weight($sold_milk_data['weight'], $this->weight_min, $this->weight_max);
                            if ($weight_error != 0) {
                                $errors[] = "Invalid weight for Selling milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                            }
            
                            if (!isset($sold_milk_data['milk_animal']) || !in_array($sold_milk_data['milk_animal'], array(1, 2))) {
                                $errors[] = "Invalid or missing 'milk_animal' for Selling milk entry #" . ($index + 1) . " where customer @" . $customer_uname;
                            }
                        }
                    }
                }
            
                // Return the array of errors
                return $errors;
            }
            public function call_list($user_id) {
                $query = sprintf("SELECT
                list.milk_about,
                list.milk_animal,
                list.d_rate,
                list.weight,
                list.by,
                list.to,
                m_customers.uname
                FROM
                    list
                LEFT JOIN
                    m_customers
                ON
                    list.to = m_customers.c_id
                WHERE list.by = '%s'
                ORDER BY list.l_id DESC ",
                $user_id
                );

                $result = $this->db->query($query);
            
                if ($result) {
                    $updatedRows = [];
                    while ($row = $result->fetch_assoc()) {
                        // Update the desired keys here
                        // $row['customer_id'] = $row['to']; // Update 'by' to 'customer_id'
                        // unset($row['to']); // Remove 'customer_id'
                        // $row['mmen_id'] = $row['by']; // Update 'mmen_id' to 'by'
                        // unset($row['by']); // Remove 'by'
                        // You can continue updating other keys as needed
            
                        $updatedRows[] = $row;
                    }
                    
                    return $updatedRows;
                }
                return []; // Return empty array if no results
            }
            public function history_info($data) {


                // Calculate the values for the 'BOUGHT_MILK' sub-array
                $buying_milk_count = array_count_values(array_column($data['buying_milk'], 'milk_animal'));
                $buying_milk_quentity = [];
                $total_d_rate_buying = 0;
                $total_weight_buying = 0;
                $buying_milk_c_uname = [];
                $buying_milk_c_id = [];
                $buying_milk_money_received = [];
                $total_amount_buying = $total_amount_buying_money_received = 0;
                foreach ($data['buying_milk'] as $item) {
                    $animal = ($item['milk_animal'] == '1') ? 'BUFFALO' : (($item['milk_animal'] == '2') ? 'COW' : 'OTHERS');
                    if (!isset($buying_milk_quentity[$animal])) {
                        $buying_milk_quentity[$animal] = 0;
                    }
                    $buying_milk_quentity[$animal] += $item['weight'];
                    $total_d_rate_buying += $item['d_rate'];
                    $total_weight_buying += $item['weight'];
                    $buying_milk_c_uname[] = $item['customer_uname'];
                    $buying_milk_c_id[] = $item['customer_id'];
                    $buying_milk_money_received[] = $item['money_received'];
    
                    $total_amount_buying += round($item['d_rate'] * $item['weight'], 2);
                    // Calculate the total amount for money_received
                    $total_amount_buying_money_received += $item['money_received'] > 0 ? round($item['d_rate'] * $item['weight'], 2) : 0;
                }
                
                $buying_milk_total = [
                    'TOTAL_RECORDS' => count($data['buying_milk']),
                    'TOTAL_D_RATE' => $total_d_rate_buying,
                    'TOTAL_WEIGHT' => $total_weight_buying,
                    'TOTAL_AMOUNT' => $total_amount_buying,
                    'TOTAL_ANIMAL' => [
                        'COUNT' => [
                            'BUFFALO' => $buying_milk_count[1] ?? 0,
                            'COW' => $buying_milk_count[2] ?? 0,
                            'OTHERS' => $buying_milk_count[0] ?? 0,
                        ],
                        'QUENTITY' => [
                            'BUFFALO' => $buying_milk_quentity['BUFFALO'] ?? 0,
                            'COW' => $buying_milk_quentity['COW'] ?? 0,
                            'OTHERS' => $buying_milk_quentity['OTHERS'] ?? 0,
                        ],
                    ],
                    'C_UNAME' => $buying_milk_c_uname,
                    'C_ID' => $buying_milk_c_id,
                    'MONEY_RECEIVED' => [
                        'TOTAL_RECORDS' => count(array_filter($buying_milk_money_received, function ($value) { return $value > 0; })),
                        'TOTAL_AMOUNT' => $total_amount_buying_money_received,
                        'FROM_C_UNAME' => array_filter($buying_milk_c_uname, function ($value, $key) use ($buying_milk_money_received) { return $buying_milk_money_received[$key] > 0; }, ARRAY_FILTER_USE_BOTH),
                        'FROM_C_ID' => array_filter($buying_milk_c_id, function ($value, $key) use ($buying_milk_money_received) { return $buying_milk_money_received[$key] > 0; }, ARRAY_FILTER_USE_BOTH),
                    ],
                    // Rest of the data for 'BOUGHT_MILK'
                ];
                
                // Calculate the values for the 'SOLD_MILK' sub-array (similar to BOUGHT_MILK)
                $selling_milk_count = array_count_values(array_column($data['selling_milk'], 'milk_animal'));
                $selling_milk_quentity = [];
                $total_d_rate_selling = 0;
                $total_weight_selling = 0;
                $selling_milk_c_uname = [];
                $selling_milk_c_id = [];
                $selling_milk_money_received = [];
                $total_amount_selling = $total_amount_selling_money_received = 0;
                foreach ($data['selling_milk'] as $item) {
                    $animal = ($item['milk_animal'] == '1') ? 'BUFFALO' : (($item['milk_animal'] == '2') ? 'COW' : 'OTHERS');
                    if (!isset($selling_milk_quentity[$animal])) {
                        $selling_milk_quentity[$animal] = 0;
                    }
                    $selling_milk_quentity[$animal] += $item['weight'];
                    $total_d_rate_selling += $item['d_rate'];
                    $total_weight_selling += $item['weight'];
                    $selling_milk_c_uname[] = $item['customer_uname'];
                    $selling_milk_c_id[] = $item['customer_id'];
                    $selling_milk_money_received[] = $item['money_received'];
                    
                    $total_amount_selling += round($item['d_rate'] * $item['weight'], 2);
                    // Calculate the total amount for money_received
                    $total_amount_selling_money_received += $item['money_received'] > 0 ? round($item['d_rate'] * $item['weight'], 2) : 0;
                }
                
                $selling_milk_total = [
                    'TOTAL_RECORDS' => count($data['selling_milk']),
                    'TOTAL_D_RATE' => $total_d_rate_selling,
                    'TOTAL_WEIGHT' => $total_weight_selling,
                    'TOTAL_AMOUNT' =>  $total_amount_selling,
                    'TOTAL_ANIMAL' => [
                        'COUNT' => [
                            'BUFFALO' => $selling_milk_count[1] ?? 0,
                            'COW' => $selling_milk_count[2] ?? 0,
                            'OTHERS' => $selling_milk_count[0] ?? 0,
                        ],
                        'QUENTITY' => [
                            'BUFFALO' => $selling_milk_quentity['BUFFALO'] ?? 0,
                            'COW' => $selling_milk_quentity['COW'] ?? 0,
                            'OTHERS' => $selling_milk_quentity['OTHERS'] ?? 0,
                        ],
                    ],
                    'C_UNAME' => $selling_milk_c_uname,
                    'C_ID' => $selling_milk_c_id,
                    'MONEY_RECEIVED' => [
                        'TOTAL_RECORDS' => count(array_filter($selling_milk_money_received, function ($value) { return $value > 0; })),
                        'TOTAL_AMOUNT' =>  $total_amount_selling_money_received,
                        'FROM_C_UNAME' => array_filter($selling_milk_c_uname, function ($value, $key) use ($selling_milk_money_received) { return $selling_milk_money_received[$key] > 0; }, ARRAY_FILTER_USE_BOTH),
                        'FROM_C_ID' => array_filter($selling_milk_c_id, function ($value, $key) use ($selling_milk_money_received) { return $selling_milk_money_received[$key] > 0; }, ARRAY_FILTER_USE_BOTH),
                    ],
                    // Rest of the data for 'SOLD_MILK'
                ];
                
                // Calculate the values for the 'TOTAL_DATA' section
                $total_animal_count = [
                    'COUNT' => [
                        'BUFFALO' => ($buying_milk_count[1] ?? 0) + ($selling_milk_count[1] ?? 0),
                        'COW' => ($buying_milk_count[2] ?? 0) + ($selling_milk_count[2] ?? 0),
                        'OTHERS' => ($buying_milk_count[0] ?? 0) + ($selling_milk_count[0] ?? 0),
                    ],
                    'QUENTITY' => [
                        'BUFFALO' => ($buying_milk_quentity['BUFFALO'] ?? 0) + ($selling_milk_quentity['BUFFALO'] ?? 0),
                        'COW' => ($buying_milk_quentity['COW'] ?? 0) + ($selling_milk_quentity['COW'] ?? 0),
                        'OTHERS' => ($buying_milk_quentity['OTHERS'] ?? 0) + ($selling_milk_quentity['OTHERS'] ?? 0),
                    ],
                ];
                
                $total_data = [
                    'TOTAL_RECORDS' => $buying_milk_total['TOTAL_RECORDS'] + $selling_milk_total['TOTAL_RECORDS'],
                    'TOTAL_D_RATE' => $buying_milk_total['TOTAL_D_RATE'] + $selling_milk_total['TOTAL_D_RATE'],
                    'TOTAL_WEIGHT' => $buying_milk_total['TOTAL_WEIGHT'] + $selling_milk_total['TOTAL_WEIGHT'],
                    'TOTAL_AMOUNT' => $buying_milk_total['TOTAL_AMOUNT'] + $selling_milk_total['TOTAL_AMOUNT'],
                    'TOTAL_ANIMAL' => $total_animal_count,
                    'MONEY_RECEIVED' => [
                        'TOTAL_RECORDS' => $buying_milk_total['MONEY_RECEIVED']['TOTAL_RECORDS'] + $selling_milk_total['MONEY_RECEIVED']['TOTAL_RECORDS'],
                        'TOTAL_AMOUNT' => $buying_milk_total['MONEY_RECEIVED']['TOTAL_AMOUNT'] + $selling_milk_total['MONEY_RECEIVED']['TOTAL_AMOUNT'],
                        'FROM_C_UNAME' => array_merge($buying_milk_total['MONEY_RECEIVED']['FROM_C_UNAME'], $selling_milk_total['MONEY_RECEIVED']['FROM_C_UNAME']),
                        'FROM_C_ID' => array_merge($buying_milk_total['MONEY_RECEIVED']['FROM_C_ID'], $selling_milk_total['MONEY_RECEIVED']['FROM_C_ID']),
                    ],
                    // Rest of the data for 'TOTAL_DATA'
                ];
                
                // Calculate the value for the 'PROFIT' section
                $profit_type = ($buying_milk_total['TOTAL_AMOUNT'] >= $selling_milk_total['TOTAL_AMOUNT']) ? 'LOSS' : 'PROFIT';
                $profit_from = ($profit_type === 'PROFIT') ? 'SOLD_MILK' : 'BOUGHT_MILK';
                $profit_dual_data = (count($data['buying_milk']) > 0 && count($data['selling_milk']) > 0) ? 'YES' : 'NO';
                $profit_total = abs($buying_milk_total['TOTAL_AMOUNT'] - $selling_milk_total['TOTAL_AMOUNT']);
                
                $profit_data = [
                    'PROFIT' => [
                        'TYPE' => $profit_type,
                        'FROM' => $profit_from,
                        'DUAL_DATA' => $profit_dual_data,
                        'TOTAL' => $profit_total,
                    ],
                ];
    
                $date = [
                    'DATE' =>  date('Y-m-d H:i:s'),
                    'TIMESTAMP'=> time()
                ];
                // Combine all the data into the final $result array
                $result = [
                    'BOUGHT_MILK' => $buying_milk_total,
                    'SOLD_MILK' => $selling_milk_total,
                    'TOTAL_DATA' => $total_data,
                    'DATA_DIFFERENCE' => $profit_data,
                    'TIME' => $date,
                    'USER_INFO' => $data['user_info']
                    // Rest of the data (if any)
                ];
                // Determine 'type' and 'available'
            // Determine 'type' and 'available'
            $count = (count($data['buying_milk']) > 0 && count($data['selling_milk']) > 0) ? 2 : 1;
            $available = (count($data['buying_milk']) > 0 && count($data['selling_milk']) > 0) ? 'BOTH' : (count($data['buying_milk']) > 0 ? 'BOUGHT_MILK' : 'SOLD_MILK');
    
            // Wrap everything in the $final_result array
            $final_result = [
                'count' => $count,
                'available' => $available,
                'data' => $result,
            ];
    
            return json_encode($final_result,JSON_UNESCAPED_UNICODE| JSON_UNESCAPED_SLASHES);
            }
            public function masterlist_delivered_history($total,$data) {
                $customer_ids = '';
                $customer_unames = '';

                foreach ($data['buying_milk'] as $entry) {
                    $customer_ids .= $entry['customer_id'] . ',';
                    $customer_unames .= $entry['customer_uname'] . ',';
                }

                foreach ($data['selling_milk'] as $entry) {
                    $customer_ids .= $entry['customer_id'] . ',';
                    $customer_unames .= $entry['customer_uname'] . ',';
                }

                $id = rtrim($customer_ids, ',');
                $uname = rtrim($customer_unames, ',');

                $json_data = $this->history_info($data);
    
            
                $query = sprintf("INSERT INTO `list_history` (`c_id`, `type`, `c_uname`, `total`, `more_info`, `date`, `by`) VALUES ('%s', '%s', '%s', '%s','%s','%s','%s')",
                    $this->db->real_escape_string($id),
                    $this->db->real_escape_string('masterlist'),
                    $this->db->real_escape_string($uname),
                    $this->db->real_escape_string($total),
                    $this->db->real_escape_string($json_data),
                    $this->db->real_escape_string(date('Y-m-d H:i:s')),
                    $this->db->real_escape_string($this->user_id)
                );
                // Execute the main query
                $result = $this->db->query($query);
    
                if ($result) {
                    $status = true;
                    $affected_rows = $this->db->affected_rows;
                    $msg = 'INSERTED';
                    $error = '';
                } else {
                    $status = false;
                    $affected_rows = 0;
                    $msg = 'NOT_INSERTED';
                    $error = 'QUERY_FAILED';
                }
    
                return ['status' => $status, 'rows' => $affected_rows, 'msg' => $msg, 'error' => $error];
            }
            public function masterlist_query($data) {
                $buying_milk_query = '';
                $selling_milk_query = '';
                
                if (!empty($data['buying_milk'])) {
                    $buying_milk_query_values = [];
                    foreach ($data['buying_milk'] as $buying_milk_data) {
                        $customer_id = $this->db->real_escape_string(trim($buying_milk_data['customer_id']));
                        $mmen_id = $this->db->real_escape_string(trim($buying_milk_data['mman_id']));
                        $milk_animal = $this->db->real_escape_string(trim($buying_milk_data['milk_animal']));
                        $d_rate = $this->db->real_escape_string(trim($buying_milk_data['d_rate']));
                        $weight = $this->db->real_escape_string(trim($buying_milk_data['weight']));
                        $total = round($d_rate * $weight, 2);
                        $money_received = $this->db->real_escape_string($buying_milk_data['money_received']);
                        $date = date('Y-m-d H:i:s');
                        $buying_milk_query_values[] = "('$milk_animal', 'masterlist_dm', '$d_rate', '$weight', '$date', '$total', '0', $money_received, '$mmen_id', '$customer_id')";
                    }
            
                    if (!empty($buying_milk_query_values)) {
                        $buy_milk_values_string = implode(',', $buying_milk_query_values);
                        $buying_milk_query = "INSERT INTO buying_milk (`milk_animal`,`milk_type`, `d_rate`, `weight`, `date`, `total`, `edited`, `cleared`, `by`,`to`) VALUES $buy_milk_values_string";
                    }
                }
            
                if (!empty($data['selling_milk'])) {
                    $selling_milk_query_values = [];
                    foreach ($data['selling_milk'] as $selling_milk_data) {
                        $customer_id = $this->db->real_escape_string(trim($selling_milk_data['customer_id']));
                        $mmen_id = $this->db->real_escape_string(trim($selling_milk_data['mman_id']));
                        $milk_animal = $this->db->real_escape_string(trim($selling_milk_data['milk_animal']));
                        $d_rate = $this->db->real_escape_string(trim($selling_milk_data['d_rate']));
                        $weight = $this->db->real_escape_string(trim($selling_milk_data['weight']));
                        $total = round($d_rate * $weight, 2);
                        $money_received = $this->db->real_escape_string($selling_milk_data['money_received']);
                        $date = date('Y-m-d H:i:s');
                        $selling_milk_query_values[] = "('$milk_animal', 'masterlist_dm', '$d_rate', '$weight', '$date', '$total', '0', $money_received, '$mmen_id', '$customer_id')";
                    }
            
                    if (!empty($selling_milk_query_values)) {
                        $sold_milk_values_string = implode(',', $selling_milk_query_values);
                        $selling_milk_query = "INSERT INTO selling_milk (`milk_animal`,`milk_type`, `d_rate`, `weight`, `date`, `total`, `edited`, `cleared`, `by`,`to`) VALUES $sold_milk_values_string";
                    }
                }
            
                return ['buying_milk' => $buying_milk_query, 'selling_milk' => $selling_milk_query];
            }
            
            
            public function do_query($data) {
                $queries = $this->masterlist_query($data);

                // Start the transaction
                $this->db->begin_transaction();
            
                $status = true;
                $affected_rows = 0;
                $msg = 'INSERTED';
                $error = '';
            
                if (!empty($queries['buying_milk'])) {
                    $query_buying = $queries['buying_milk'];

                    $result_buying = $this->db->query($query_buying);
            
                    if (!$result_buying) {
                        $status = false;
                        $msg = 'NOT_INSERTED';
                        $error = 'QUERY_FAILED';
                    } else {
                        $affected_rows += $this->db->affected_rows;
                    }
                }
            
                if (!empty($queries['selling_milk'])) {
                    $query_selling = $queries['selling_milk'];
                    $result_selling = $this->db->query($query_selling);
            
                    if (!$result_selling) {
                        $status = false;
                        $msg = 'NOT_INSERTED';
                        $error = 'QUERY_FAILED';
                    } else {
                        $affected_rows += $this->db->affected_rows;
                    }
                }
            
                // Commit or rollback the transaction based on status
                if ($status) {
                    $this->db->commit();
                } else {
                    $this->db->rollback();
                }
            
                return ['status' => $status, 'rows' => $affected_rows, 'msg' => $msg, 'error' => $error];
            }
            


            public function process($type){
                if($type==1){
                    $output = $this->search_term($this->search_val);
                }else{
                    $form_data = $this->get_data_from_type(0);
                    $error = $this->validate_inputs($form_data);
                            
                    if(empty($error)){
                        $original_data = $this->get_original_data();
                        $insert = $this->do_query($original_data);
                        if($insert['status'] === true && $insert['rows'] > 0){
                            $this->masterlist_delivered_history($insert['rows'],$original_data);
        
                            $status = true;
                            $title = 'MILK_DATA_INSERTED';
                            $rows = $insert['rows'];
                            $errr = '';
                        }
                    }else{
                        foreach($error as $err) {
                            $status = false;
                            $title = 'MILK_DATA_NOT_INSERTED';
                            $rows = '';
                            $errr = $err;
                          }
                    }

                    $output = ['status' => $status, 'title' => $title, 'rows' => $rows, 'error' => $errr];
                    
                }
                return $output;
            }
         
        }
        class manage_customers{
            public $db;
            public $url;
            public $search_val;
            public $customer_c_id;
            public $record_per_page;
            
            public function fetch_customers($type=null){
                // type 1 for search customer by his/her phone number
                // else  set a limit and when pagination will be apply
                // $count count all data after apply that apply pagination

                global $user;
                $limit = $this->record_per_page;
                $offset = (isset($_POST['start'])) ? $_POST['start'] : 0;

                
                if($type == 1){
                    $query = sprintf(" SELECT c_id,
                           DENSE_RANK() OVER (ORDER BY c_id) AS row_num,
                           fname,
                           lname,
                           uname,
                           p_number,
                           profile_pic,
                           locality FROM ( SELECT m.c_id, m.fname, m.lname, m.uname, m.p_number, m.profile_pic, m.locality
                        FROM m_customers m
                        LEFT JOIN buying_milk t ON m.c_id = t.to AND t.by = %s
                        LEFT JOIN selling_milk g ON m.c_id = g.to AND g.by = %s
                        WHERE (m.p_number = '%s' OR m.uname = '%s')
                        UNION SELECT m.c_id, m.fname, m.lname, m.uname, m.p_number, m.profile_pic, m.locality
                        FROM m_customers m
                        LEFT JOIN selling_milk g ON m.c_id = g.to AND g.by = %s
                        LEFT JOIN buying_milk t ON m.c_id = t.to AND t.by = %s
                        WHERE (m.p_number = '%s' OR m.uname = '%s') AND t.to IS NULL ) AS subquery ORDER BY row_num",
                        $this->db->real_escape_string($user['id']),
                        $this->db->real_escape_string($user['id']),
                        $this->db->real_escape_string(strtolower(trim($this->search_val))),
                        $this->db->real_escape_string(strtolower(trim($this->search_val))),
                        $this->db->real_escape_string($user['id']),
                        $this->db->real_escape_string($user['id']),
                        $this->db->real_escape_string(strtolower(trim($this->search_val))),
                        $this->db->real_escape_string(strtolower(trim($this->search_val)))
                        
                      );

                }elseif($type==2){
                    $query = sprintf("SELECT c_id, DENSE_RANK() OVER (ORDER BY c_id) AS row_num, fname, lname, uname, p_number,profile_pic,locality FROM m_customers LEFT OUTER JOIN buying_milk ON (m_customers.c_id = buying_milk.to)
                    LEFT OUTER JOIN selling_milk ON (m_customers.c_id = selling_milk.to) WHERE (buying_milk.by = %s OR selling_milk.by = %s) AND (m_customers.c_id = '%s')",$this->db->real_escape_string($user['id']),$this->db->real_escape_string($user['id']),$this->db->real_escape_string($this->customer_c_id));

                }elseif($type==3){

                    $query = sprintf("SELECT COUNT(*) AS total_rows FROM (  SELECT c_id, DENSE_RANK() OVER (ORDER BY c_id) AS row_num
                      FROM ( SELECT c_id FROM m_customers
                      LEFT OUTER JOIN buying_milk ON (m_customers.c_id = buying_milk.to) WHERE buying_milk.by = %s UNION SELECT c_id FROM m_customers
                      LEFT OUTER JOIN selling_milk ON (m_customers.c_id = selling_milk.to) WHERE selling_milk.by = %s) AS subquery GROUP BY c_id
                    ) AS subquery2;", $this->db->real_escape_string($user['id']), $this->db->real_escape_string($user['id']));
                
                }else{
                $query = sprintf("SELECT c_id, DENSE_RANK() OVER (ORDER BY c_id) AS row_num, fname, lname, uname, p_number, profile_pic, locality
                FROM (
                    SELECT c_id, fname, lname, uname, p_number, profile_pic, locality
                    FROM m_customers
                    LEFT JOIN buying_milk ON m_customers.c_id = buying_milk.to AND buying_milk.by = %s
                    WHERE buying_milk.to IS NOT NULL
                    UNION
                    SELECT c_id, fname, lname, uname, p_number, profile_pic, locality
                    FROM m_customers
                    LEFT JOIN selling_milk ON m_customers.c_id = selling_milk.to AND selling_milk.by = %s
                    WHERE selling_milk.to IS NOT NULL
                ) AS subquery
                ORDER BY row_num ASC, fname ASC LIMIT %s, %s;",$this->db->real_escape_string($user['id']),$this->db->real_escape_string($user['id']),$offset,$limit);

                // $query = sprintf("SELECT DISTINCT c_id, DENSE_RANK() OVER (ORDER BY c_id) AS row_num, fname, lname, uname, p_number,profile_pic,locality FROM m_customers LEFT OUTER JOIN buying_milk ON (m_customers.c_id = buying_milk.to)
                // LEFT OUTER JOIN selling_milk ON (m_customers.c_id = selling_milk.to) WHERE (buying_milk.by = %s OR selling_milk.by = %s) LIMIT %s, %s",$this->db->real_escape_string($user['id']),$this->db->real_escape_string($user['id']),$offset,$limit);
                 }
                $result = $this->db->query($query);
                
                return $result;
            }
            
            public function show_customers($type = null){
                
                if($type == 1){
                    
                    $results = $this->fetch_customers(1);
                    
                    $search_rows = '';
                    if($results && $results->num_rows > 0) {
                        $row = $results->fetch_assoc(); 
                            $p_number = ($row['p_number']) ? formatPhoneNumber($row['p_number']): 'No_Phone';
                            $search_rows .= '
                                    <tr>
                                    <td><img src='.permalink($this->url.'/image.php?t=c&w=50&h=50&src='.$row['profile_pic']).'></td>
                                    <td>'. formatFullName($row['fname'],$row['lname']).'</td>
                                    <td>'.$row['uname'].'</td>
                                    <td>'.$p_number.'</td>
                                    <td>'.formatlocality($row['locality']).'</td>
                                    <td>

                                    <a href="'.permalink($this->url.'/index.php?a=manage_user&b=view-milk&c_id='.$row['c_id']).'" rel="loadpage"> <div class="btn-wide btn-normal" style="width: 20px; font-size: 21px; color: #ff0041;">view</div></a>
                                    </td>
                                    </tr> 
                                 ';
                        
                    }else{
                        
                        $search_rows .= 'No Result Found';
                    }
                    return ['row' => $search_rows, 'c_id' => $row['c_id'],'url' => permalink($this->url.'/index.php?a=manage_user&b=view-milk&c_id='.$row['c_id'])];

                }elseif($type==2){
                    
                    $results = $this->fetch_customers(2);
                    
                    $search_rows = '';
                    if($results && $results->num_rows > 0) {
                        $data = $results->fetch_assoc();
                        $output = "
                        <div class='message-form-user'><img src='". permalink($this->url.'/image.php?t=c&w=50&h=50&src='.$data['profile_pic'])."'>All Milk Data Where <font color='#747fdf'>".formatFullName($data['fname'],$data['lname'])." ({$data['uname']}) </font>bought or sold to you.</div>
                                  ";                     
                    }else{
                        $output = " <div class='message-form-user'><img src='". permalink($this->url.'/image.php?t=c&w=50&h=50&src=default.png')."'>Here All data Where your customer bought/sold milk to you.</div>";
                    }
                    return $output;

                }
                elseif($type==3){

                    $query = $this->fetch_customers(3);
                    $result = $query->fetch_assoc();
                    $rows = "Total number of customers is {$result['total_rows']}.";
                    
                    return $rows;

                }elseif($type==4){
                    $results = $this->fetch_customers(1);
                    
                    $search_rows = '';
                    if($results && $results->num_rows > 0) {
                        $row = $results->fetch_assoc(); 
                        $p_number = ($row['p_number']) ? formatPhoneNumber($row['p_number']): 'No_Phone';
                            $search_rows .= '
                            <div class="search-content"><div class="search-results">
	
                            <div class="notification-inner"><a onclick="manageResults(1)"><strong>See All Customers</strong></a></div>
                            <div class="message-inner">
                            <div class="flex-top-search">
                                    <div><img src='.permalink($this->url.'/image.php?t=c&w=50&h=50&src='.$row['profile_pic']).'></div>
                                    <div>'. formatFullName($row['fname'],$row['lname']).'</div>
                                    <div>'.$row['uname'].'</div>  
                                    <div>'.$p_number.'</div>
                                    <div>'.$row['locality'].'</div>
                                    <a href="'.permalink($this->url.'/index.php?a=manage_user&b=view-milk&c_id='.$row['c_id']).'" rel="loadpage"> <div class="btn-wide btn-normal" style="width: 20px; font-size: 21px; color: #ff0041;">view</div></a>
                                    </div></div>
                            </div></div>
                                 ';
                        
                    }
                    // else{
                        
                    //     $search_rows .= '<div class="header-search">
                    //                      <div class="message-inner">
                    //                       <div class="no_more_data">No Result Found</div>
                    //                       </div></div>';
                    // }
                    return ['row' => $search_rows, 'c_id' => $row['c_id'],'url' => permalink($this->url.'/index.php?a=manage_user&b=view-milk&c_id='.$row['c_id'])];
                }else{
                $results = $this->fetch_customers();
               
                $rows = '';
                $loadmore = '';
                if($results && $results->num_rows > 0){
                    while($row = $results->fetch_array()) {
                        $p_number = ($row['p_number']) ? formatPhoneNumber($row['p_number']): 'No_Phone';
                        $rows .= 
                        '<tr>
                            <td>' . $row['row_num'] . '</td>
                            <td><img src="' . permalink($this->url.'/image.php?t=c&w=50&h=50&src='.$row['profile_pic']) . '"></td>
                            <td>' . formatFullName($row['fname'], $row['lname']) . '</td>
                            <td>' . $row['uname'] . '</td>
                            <td>' .$p_number.  '</td>
                            <td>' . $row['locality'] . '</td>
                            <td>
                                <a href="' . permalink($this->url.'/index.php?a=manage_user&b=view-milk&c_id='.$row['c_id']) . '" rel="loadpage">
                                    <div class="btn-wide btn-normal" style="width: 20px; font-size: 21px; color: #ff0041;">view</div>
                                </a>
                            </td>
                        </tr>';
                    
                     
                        $rows++;
                        
                    }
                    
                }else{
                    $rows .= 'finished';
                }
                return $rows;
                } 

                
                }
                // process cutomer data 

             public function process($type=null){
               // type 1 for single searched customer data
               // type 2 data for sigle searched and search from c_id
               // type 3 count how many customers 
               // type 4 load all customers

                if($type==1){

                    $output = $this->show_customers(1);
                    
                }elseif($type==2){
                    
                    $output = $this->show_customers(2);

                }
                elseif($type==3){

                    $output = $this->show_customers(3);

                }elseif($type==4){
                    $output = $this->show_customers(4);
                }else{

                    $output = $this->show_customers();
                    
                }
                return $output;
             }   
              
            

        }

        // Creating a class to show milk Report 
        // showing result with filters
       Class view_milk{

        public $db;
        public $c_id;
        public $p_number;
        public $dm;
        public $weight;
        public $mf;
        public $mfr;
        public $user_id;
        public $is_cleared;
        public $from_date;
        public $to_date;
        public $milk_table;
        public $milk_type;
        public $milk_id;
        public $to;
        public $record_per_page;
        public $offset;
        // Variable for Validations
        public $dm_min;
        public $dm_max;

        public $mf_min;
        public $mf_max;

        public $mfr_min;
        public $mfr_max;
        
        public $weight_min;
        public $weight_max;

        public function get_date_10(){
            $current_date = date('Y-m-d');
            $past_date = date('Y-m-d', strtotime('-10 days'));
            return ['current_date' => $current_date, 'past_date' => $past_date];
        }
        public function get_date_3(){
            $current_date = date('Y-m-d');
            $past_date = date('Y-m-d', strtotime('-3 days'));
            return ['current_date' => $current_date, 'past_date' => $past_date];
        }
        public function validate_inputs($milk_table,$id){
            global $LNG, $settings;
            $error = array();

            $data = $this->query_edit_btn($milk_table,$id)['data'];

            switch($data['milk_type']){
                case 'dm':
                case 'wishlist_dm':
                case 'masterlist_dm':
                if(empty($this->dm)){
                    $error[] = sprintf($LNG['dm_empty']);
                }
                if(check_dm($this->dm,$this->dm_min,$this->dm_max)==1){
                    $error[] = sprintf($LNG['dm_invalid']);
                }elseif(check_dm($this->dm,$this->dm_min,$this->dm_max)==2){
                    $error[] = sprintf($LNG['dm_minimum'],$this->dm_min);
                }elseif(check_dm($this->dm,$this->dm_min,$this->dm_max)==3){
                    $error[] = sprintf($LNG['dm_maximum'],$this->dm_max);
                }elseif($this->dm == $data['d_rate'] && $this->weight == $data['weight']){
                    $error[] =  $LNG['nothing_changed'];
                }

                break;

                case 'mf':

                if(empty($this->mf)){
                    $error[] = sprintf($LNG['mf_empty']);
                }
                
                // Checking Machine Fat is Valid or No.
                if(check_mf($this->mf,$this->mf_min,$this->mf_max)==1){
                    $error[] = sprintf($LNG['mf_invalid']);
                }elseif(check_mf($this->mf,$this->mf_min,$this->mf_max)==2){
                    $error[] = sprintf($LNG['mf_decimal']);
                }elseif(check_mf($this->mf,$this->mf_min,$this->mf_max)==3){
                    $error[] = sprintf($LNG['mf_minimum'],$this->mf_min);
                }elseif(check_mf($this->mf,$this->mf_min,$this->mf_max)==4){
                    $error[] = sprintf($LNG['mf_maximum'],$this->mf_max);
                }
                // Checking Milk Fat Rate is Valid or Not.
                

                if(empty($this->mf)){
                    $error[] = sprintf($LNG['mf_empty']);
                }
                if(check_mfr($this->mfr,$this->mfr_min,$this->mfr_max)==1){
                    $error[] = sprintf($LNG['mfr_invalid']);
                }elseif(check_mfr($this->mfr,$this->mfr_min,$this->mfr_max)==2){
                    $error[] = sprintf($LNG['mfr_decimal']);
                }elseif(check_mfr($this->mfr,$this->mfr_min,$this->mfr_max)==3){
                    $error[] = sprintf($LNG['mfr_minimum'],$this->mfr_min);
                }elseif(check_mfr($this->mfr,$this->mfr_min,$this->mfr_max)==4){
                    $error[] = sprintf($LNG['mfr_maximum'],$this->mfr_max);
                }elseif($this->mfr == $data['fat_rate'] && $this->mf == $data['fat'] && $this->weight == $data['weight']){
                    $error[] =  $LNG['nothing_changed'];
                    
                }
                break;
            }

            if(empty($this->weight)){
                $error[] = sprintf($LNG['weight_empty']);
            }

            // checking inputs values are valid ?
            if(check_weight($this->weight,$this->weight_min,$this->weight_max) == 1){
                $error[] = sprintf($LNG['invalid_weight']);
            }elseif(check_weight($this->weight,$this->weight_min,$this->weight_max) == 2){
                $error[] = sprintf($LNG['weight_decimal_invalid']);
            }elseif(check_weight($this->weight,$this->weight_min,$this->weight_max) == 3){
                $error[] = sprintf($LNG['minimum_weight'],$this->weight_min);
            }elseif(check_weight($this->weight,$this->weight_min,$this->weight_max) == 4){
                $error[] = sprintf($LNG['weight_limit'], $this->weight_max);
            }

            
            
            return $error;
        }
        public function convertDates($dates) {
            $convertedDates = array();
          
            foreach ($dates as $date) {
              $parts = explode('-', $date);
              if (count($parts) == 3) {
                if (strlen($parts[0]) == 2 && strlen($parts[1]) == 2 && strlen($parts[2]) == 4) {
                  $convertedDates[] = "$parts[2]-$parts[1]-$parts[0]";
                } else if (strlen($parts[0]) == 4 && strlen($parts[1]) == 2 && strlen($parts[2]) == 2) {
                  $convertedDates[] = $date;
                } else {
                  $convertedDates[] = null; // invalid date format
                }
              } else {
                $convertedDates[] = null; // invalid date format
              }
            }
          
            return $convertedDates;
          }

        public function view_milk_query($is_cleared,$milk_type){
            $limit = $this->record_per_page;
            
            $offset = (isset($_POST['start'])) ? $_POST['start'] : 0;

            $date = $this->convertDates([$this->from_date,$this->to_date]);   //formating date
      
         if($milk_type==1){
            if($is_cleared == 'false'){
                $query = sprintf("SELECT DISTINCT id, ROW_NUMBER() OVER (ORDER BY DATE(`date`) DESC) AS `serial_number`,`milk_type`, `d_rate`,`fat_rate`, `fat`, `weight`, `total`, `by`,`to`, `date`, `cleared` FROM `buying_milk` WHERE `by` = %s AND `to` = %s AND (DATE(`date`) BETWEEN '%s' AND '%s') LIMIT %s, %s ", $this->user_id, $this->c_id, $date[0],$date[1],$offset,$limit);
            }elseif($is_cleared == 'true'){
                $query = sprintf("SELECT DISTINCT id, ROW_NUMBER() OVER () AS `serial_number`,`milk_type`, `d_rate`,`fat_rate`, `fat`, `weight`, `total`, `by`,`to`, `date`, `cleared` FROM `buying_milk` WHERE `by` = %s AND `to` = %s AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `cleared` = %s LIMIT %s, %s", $this->user_id, $this->c_id, $date[0],$date[1],1,$offset,$limit);
            }
        }elseif($milk_type == 2){
            
            if($is_cleared == 'false'){
                $query = sprintf("SELECT DISTINCT id, ROW_NUMBER() OVER (ORDER BY DATE(`date`) DESC) AS `serial_number`, `id`,`milk_type`, `d_rate`,`fat_rate`, `fat`, `weight`, `total`, `by`,`to`, `date`,`cleared` FROM `selling_milk` WHERE `by` = %s AND `to` = %s AND (DATE(`date`) BETWEEN '%s' AND '%s') LIMIT %s, %s", $this->user_id, $this->c_id, $date[0],$date[1],$offset,$limit);
            }elseif($is_cleared == 'true'){
                $query = sprintf("SELECT  DISTINCT id, ROW_NUMBER() OVER (ORDER BY DATE(`date`) DESC) AS `serial_number`,`id`,`milk_type`, `d_rate`,`fat_rate`, `fat`, `weight`, `total`, `by`,`to`,`date`, `cleared` FROM `selling_milk` WHERE `by` = %s AND `to` = %s AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `cleared` = %s LIMIT %s, %s", $this->user_id, $this->c_id, $date[0],$date[1],1,$offset,$limit);
            }
            
        } 
        
            $db_query = $this->db->query($query);
            return ['db_query' => $db_query];
        }
        function query_edit_btn($type,$id){
            $date = $this->get_date_3();
            
            $current_date = $date['current_date'];
            $past_date = $date['past_date'];
            $c_id = $this->c_id ?? $_POST['to'];
            if($type==1){
                $query = sprintf("SELECT * FROM `buying_milk` WHERE `by` = %s AND `to` = %s AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `id` = %s AND `cleared` = %s AND `edited` = %s ",$this->db->real_escape_string($this->user_id),$c_id,$past_date,$current_date,$id,0,0);
               
            }else{
                $query = sprintf("SELECT * FROM `selling_milk` WHERE `by` = %s AND `to` = %s AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `id` = %s AND  `cleared` = %s AND `edited` = %s ",$this->db->real_escape_string($this->user_id),$c_id,$past_date,$current_date,$id,0,0);
                
            }

            $result = $this->db->query($query) or $rows = 0;
            $milk_type = '';
            if($result->num_rows > 0){
                $rows = $result->num_rows;
                $data = $result->fetch_array();
                $milk_type .= $data['milk_type'];
               
                $output = '
                <div id="edit-'.$data['id'].'" class="edit-m-btn" onclick="edit_row('.$this->milk_table.', '.$data['id'].')">Edit</div>
                           ';
            }else{
                $output = '---';
            }
             
            
            return [ 'query' => $query, 'rows' => $rows, 'data' => $data, 'milk_type' => $milk_type, 'output' => $output];
            
        }
  

        public function get_view_data(){
            $result = $this->view_milk_query($this->is_cleared,$this->milk_table);
            $total_rows = $result['db_query']->num_rows;
            $rows = '<tbody>';
            $finished = '';
            

            if($result && $total_rows > 0){
                
                
                while($row = $result['db_query']->fetch_array()) {
                $action = $this->query_edit_btn($this->milk_table,$row['id']);
                
                $row['edit'] = $action['output'];

                    

                   if($row['milk_type'] == 'dm'){
                    $milk_type = 'Direct';
                   }elseif($row['milk_type'] == 'mf'){
                    $milk_type = 'Fat';
                   }elseif($row['milk_type'] == 'wishlist_dm'){
                    $milk_type = 'Wishlist';
                   }elseif($row['milk_type'] == 'masterlist_dm'){
                    $milk_type = 'Masterlist';
                   }
                   if($row['cleared'] == 0){
                    $cleared = '---';
                   }elseif($row['cleared'] == 1){
                    $cleared = '<font weight="1px" color="green">Cleared</font>';
                   }
                   $dateObj = new DateTime($row['date']);

                    // Extract the date and time components into separate variables
                    $date = $dateObj->format('d-m-Y');
                    $time = $dateObj->format('h:i a');
                   $formatted_time = "<font color='#e5a432'>".$time."</font>";

                   $null = "<font color = 'red'>---</font>";
                   $rows .= 
                            "
                                
                                <tr>
                                    <td>".$row['serial_number']."</td>
                                    <td>".$milk_type."</td>
                                    <td>".$date."</td>
                                    <td>".$formatted_time."</td>
                                    <td>".($row['d_rate'] ? $row['d_rate'] : $null)."</td>
                                    <td>".($row['fat_rate'] ? $row['fat_rate'] : $null)."</td>
                                    <td>".($row['fat'] ? $row['fat'] : $null)."</td>
                                    <td>".$row['weight']."</td>
                                    <td>".$row['total']."</td>
                                    <td>".$cleared."</td>
                                    <td>".$row['edit']."</td>
                                </tr>
                                
                            ";
                 
                
                   
                }
                
                $rows .= '</tbody>';
                
            }else{
                $finished .= "<div style='color:red'>No Data Found</div>";
            }

            return ['total_rows' => $total_rows, 'rows' => $rows, 'finished' => $finished];

        }
        
       public function update_edit_data($milk_table,$type,$id,$to){
        $date = $this->get_date_3();
        $current_date = $date['current_date'];
        $past_date = $date['past_date'];
        
        if($milk_table==1){
            if($type=='dm' || $type=='wishlist_dm' || $type=='masterlist_dm'){
                // $query = sprintf("UPDATE `taking_milk` SET `d_rate` = %s,`weight` = %s,`edited` = %s WHERE `id` = %s AND `by` = %s AND `to`= %s AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `edited` = %s;",$this->db->real_escape_string($this->dm),$this->db->real_escape_string($this->weight),$this->db->real_escape_string(1),$this->db->real_escape_string($id),$this->db->real_escape_string($this->user_id),$this->db->real_escape_string($to),$past_date,$current_date,$this->db->real_escape_string(0));
                // $query2 = sprintf("INSERT INTO update_milk ( table_name, milk_type, old_d_rate, old_weight, old_total, milk_id, `by`, `to` ) SELECT 'taking_table' AS table_name, milk_type, d_rate, `weight`, total, id, `by`, `to` FROM taking_milk WHERE taking_milk.by = %s AND taking_milk.to = %s AND taking_milk.id = %s  AND (DATE(`date`)` BETWEEN '%s' AND '%s') AND `edited` = %s; ",$this->db->real_escape_string($this->user_id),$this->db->real_escape_string($to),$this->db->real_escape_string($id),$past_date,$current_date,$this->db->real_escape_string(0));
                $query = sprintf(
                    "UPDATE `buying_milk` SET `d_rate` = %s, `weight` = %s, `edited` = %s WHERE `id` = %s AND `by` = %s AND `to` = %s AND DATE(`date`) BETWEEN '%s' AND '%s' AND `edited` = %s;",
                    $this->db->real_escape_string($this->dm),
                    $this->db->real_escape_string($this->weight),
                    $this->db->real_escape_string(1),
                    $this->db->real_escape_string($id),
                    $this->db->real_escape_string($this->user_id),
                    $this->db->real_escape_string($to),
                    $past_date,
                    $current_date,
                    $this->db->real_escape_string(0)
                );
                
                $query2 = sprintf(
                    "INSERT INTO update_milk (table_name, milk_type, old_d_rate, old_weight, old_total, milk_id, `by`, `to`, `date`) 
                    SELECT 'buying_table' AS table_name, `milk_type`, `d_rate`, `weight`, `total`, `id`, `by`, `to`, '%s' 
                    FROM buying_milk 
                    WHERE buying_milk.by = %s AND buying_milk.to = %s AND buying_milk.id = %s 
                    AND DATE(`date`) BETWEEN '%s' AND '%s' AND `edited` = %s;",
                    $this->db->real_escape_string(date('Y-m-d H:i:s')),
                    $this->db->real_escape_string($this->user_id),
                    $this->db->real_escape_string($to),
                    $this->db->real_escape_string($id),
                    $past_date,
                    $current_date,
                    $this->db->real_escape_string(0)
                );
                
                
                
            }elseif($type=='mf'){
                $query = sprintf("UPDATE `buying_milk` SET `fat` = %s,`weight` = %s,`fat_rate` = %s,`edited` = %s WHERE `id` = %s AND `by` = %s AND `to` = %s AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `edited` = %s;",$this->db->real_escape_string($this->mf),$this->db->real_escape_string($this->weight),$this->db->real_escape_string($this->mfr),$this->db->real_escape_string(1),$this->db->real_escape_string($id),$this->db->real_escape_string($this->user_id),$this->db->real_escape_string($to),$past_date,$current_date,$this->db->real_escape_string(0));
                $query2 = sprintf("INSERT INTO update_milk ( table_name, milk_type, old_fat,old_fat_rate, old_weight, old_total, milk_id, `by`, `to`, `date` ) SELECT 'buying_table' AS table_name, `milk_type`, `fat`,`fat_rate`, `weight`, `total`, `id`, `by`, `to`, '%s' FROM buying_milk WHERE buying_milk.by = %s AND buying_milk.to = %s AND buying_milk.id = %s  AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `edited` = %s; ", $this->db->real_escape_string(date('Y-m-d H:i:s')),$this->db->real_escape_string($this->user_id),$this->db->real_escape_string($to),$this->db->real_escape_string($id),$past_date,$current_date,$this->db->real_escape_string(0));
            }
            
        }else{
            if($type=='dm' || $type=='wishlist_dm' || $type=='masterlist_dm'){
                // $query = sprintf("UPDATE `selling_milk` SET `d_rate` = %s,`weight` = %s,`edited` = %s WHERE `id` = %s AND `by` = %s `to` = %s AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `edited` = %s;",$this->db->real_escape_string($this->dm),$this->db->real_escape_string($this->weight),$this->db->real_escape_string(1),$this->db->real_escape_string($id),$this->db->real_escape_string($this->user_id),$this->db->real_escape_string($to),$past_date,$current_date,$this->db->real_escape_string(0));
                // $query2 = sprintf("INSERT INTO update_milk ( table_name, milk_type, old_d_rate, old_weight, old_total, milk_id, `by`, `to` ) SELECT 'giving_table' AS table_name, milk_type, d_rate, `weight`, total, id, `by`, `to` FROM giving_milk WHERE giving_milk.by = %s AND giving_milk.to = %s AND giving_milk.id = %s  AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `edited` = %s; ",$this->db->real_escape_string($this->user_id),$this->db->real_escape_string($to),$this->db->real_escape_string($id),$past_date,$current_date,$this->db->real_escape_string(0));
                $query = sprintf(
                    "UPDATE `selling_milk` SET `d_rate` = %s, `weight` = %s, `edited` = %s WHERE `id` = %s AND `by` = %s AND `to` = %s AND DATE(`date`) BETWEEN '%s' AND '%s' AND `edited` = %s;",
                    $this->db->real_escape_string($this->dm),
                    $this->db->real_escape_string($this->weight),
                    $this->db->real_escape_string(1),
                    $this->db->real_escape_string($id),
                    $this->db->real_escape_string($this->user_id),
                    $this->db->real_escape_string($to),
                    $past_date,
                    $current_date,
                    $this->db->real_escape_string(0)
                );
                
                $query2 = sprintf(
                    "INSERT INTO update_milk (table_name, milk_type, old_d_rate, old_weight, old_total, milk_id, `by`, `to`, `date`) SELECT 'selling_table' AS table_name, milk_type, d_rate, `weight`, total, id, `by`, `to`, '%s' FROM selling_milk WHERE selling_milk.by = %s AND selling_milk.to = %s AND selling_milk.id = %s AND DATE(`date`) BETWEEN '%s' AND '%s' AND `edited` = %s;",
                    $this->db->real_escape_string(date('Y-m-d H:i:s')),
                    $this->db->real_escape_string($this->user_id),
                    $this->db->real_escape_string($to),
                    $this->db->real_escape_string($id),
                    $past_date,
                    $current_date,
                    $this->db->real_escape_string(0)
                );
                
            }elseif($type=='mf'){
                $query = sprintf("UPDATE `selling_milk` SET `fat` = %s,`weight` = %s,`fat_rate` = %s,`edited` = %s WHERE `id` = %s AND `by` = %s AND `to` = %s AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `edited` = %s;",$this->db->real_escape_string($this->mf),$this->db->real_escape_string($this->weight),$this->db->real_escape_string($this->mfr),$this->db->real_escape_string(1),$this->db->real_escape_string($id),$this->db->real_escape_string($this->user_id),$this->db->real_escape_string($to),$past_date,$current_date,$this->db->real_escape_string(0));
                $query2 = sprintf("INSERT INTO update_milk (table_name, milk_type, old_fat,old_fat_rate, old_weight, old_total, milk_id, `by`, `to`, `date`) SELECT 'selling_table' AS table_name, milk_type, fat,fat_rate, `weight`, total, id, `by`, `to`,'%s' FROM selling_milk WHERE selling_milk.by = %s AND selling_milk.to = %s AND selling_milk.id = %s  AND (DATE(`date`) BETWEEN '%s' AND '%s') AND `edited` = %s; ", $this->db->real_escape_string(date('Y-m-d H:i:s')),$this->db->real_escape_string($this->user_id),$this->db->real_escape_string($to),$this->db->real_escape_string($id),$past_date,$current_date,$this->db->real_escape_string(0));
            }
        }
        return $query2.$query;

       }
       public function get_customer_p_num($id){
        $query = sprintf("SELECT `p_number` FROM `m_customers` WHERE `c_id` = '%s'",$this->db->real_escape_string($id));
        $run = $this->db->query($query);
        if($run && $run->num_rows > 0){
            $result = $run->fetch_assoc();
        }
        return $result['p_number'];
       
       }
       public function sms_template($milk_table,$id){
        $action = $this->query_edit_btn($milk_table,$id)['data'];
        switch ($action['milk_type']) {
            case 'dm':
                $old_data = "DM: {$action['d_rate']}, Weight: {$action['weight']}";
                $new_data = "DM: {$this->dm}, W: {$this->weight}, at" . date('H:i');

                $p_num = $this->get_customer_p_num($action['to']);
                break;
            case 'mf':
                $old_data = "MFR:{$action['fat_rate']}, F: {$action['fat']}, W:{$action['weight']}";
                $new_data = "MFR:{$this->mfr}, F: {$this->mf}, W:{$this->weight}";
                $p_num = $this->get_customer_p_num($action['to']);
                break;
        }
        
        return ['array' => [$old_data,$new_data], 'p_num' => $p_num];
          
       }

       public function run_update_editted_data($milk_table, $milk_type, $milk_id, $to)
       {
           global $LNG,$user;
       
           $arr = $this->validate_inputs($milk_table, $milk_id);
       
           if (empty($arr)) {
               $message_id = message_id('update_milk');
               $p_number = $this->sms_template($milk_table, $milk_id)['p_num'];
               $array = $this->sms_template($milk_table, $milk_id)['array'];
       
               $sms = new send_sms();
               $sms->process($message_id, $user['p_number'], $array);
       
               $query = $this->update_edit_data($milk_table, $milk_type, $milk_id, $to);
               
               // Set a timeout of 30 seconds for the query execution
               $this->db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 30);
               
               $result = $this->db->multi_query($query);
               $rows = $this->db->affected_rows ?? 0;
       
               if ($result === true) {
                   // Fetch the next result to free up the previous result set
                   $this->db->next_result();
                   if ($rows > 0) {
                       $success = "
                           <div class='success-container'>
                               <div class='circle'></div>
                               <div class='ring'></div>
                               <div class='checkmark'></div>
                               <div class='fading-circles'>
                                   <div class='circle-animation circle1'></div>
                                   <div class='circle-animation circle2'></div>
                                   <div class='circle-animation circle3'></div>
                                   <div class='circle-animation circle4'></div>
                                   <div class='circle-animation circle5'></div>
                                   <div class='circle-animation circle6'></div>
                                   <div class='circle-animation circle7'></div>
                                   <div class='circle-animation circle8'></div>
                                   <div class='circle-animation circle9'></div>
                                   <div class='circle-animation circle10'></div>
                               </div>
                           </div> 
                           <div class='dialog-box-notification'>
                               ".notificationBox('success', $LNG['saved'], 1)."
                           </div>";
                   } else {
                       $failed = "
                           <div class='dialog-box-notification'>
                               ".notificationBox('error', 'Something Went Wrong', 1)."
                               <div class='message-form-des'>
                                   <div class='circle-border'></div>
                                   <div class='circle'>
                                       <div class='error'></div>
                                   </div>
                                   Failed
                               </div>
                           </div>";
                   }
                  
               } else {
                   // Handle the error here
                   if ($this->db->errno === 2003) {
                       // Connection timeout error
                        $failed = "
                        <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                        <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
                        </svg>
                         <p class='error_p'>Query Timeout: The query took too long to execute</p>
                        </div>
                         </div>
                                ";


                   } else {
                       // Other database error
                      $failed = "
                        <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                        <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
                        </svg>
                         <p class='error_p'>Something Went Wrong</p>
                        </div>
                         </div>
                          ";
                   }
               }
               
           } else {
               foreach ($arr as $err) {
                   $failed = notificationBox('error', $err, 1); // the error value for translation file
               }
           }
       
           return ['success' => $success, 'failed' => $failed];
       }
       




    //    public function run_update_editted_data($milk_table,$milk_type,$milk_id,$to){
    //         global $LNG;
            
    //         $arr = $this->validate_inputs($milk_table,$milk_id);
                   
    //                 if(empty($arr)){
    //                     $message_id = message_id('update_milk');
    //                     $p_number = $this->sms_template($milk_table,$milk_id)['p_num'];
    //                     $array = $this->sms_template($milk_table,$milk_id)['array'];
                        
    //                     $sms = new send_sms();
    //                     $output = $sms->process($message_id,$p_number,$array);

    //                     $query = $this->update_edit_data($milk_table,$milk_type,$milk_id,$to);
    //                     $result = $this->db->multi_query($query);
    //                     $rows = $this->db->affected_rows ?? 0;
    //                        $result->free();
    //                     if($result && ($rows > 0)){
                            

    //                         $success = "
    //                         <div class='success-container'>
    //                         <div class='circle'></div>
    //                         <div class='ring'></div>
    //                         <div class='checkmark'></div>
    //                         <div class='fading-circles'>
    //                           <div class='circle-animation circle1'></div>
    //                           <div class='circle-animation circle2'></div>
    //                           <div class='circle-animation circle3'></div>
    //                           <div class='circle-animation circle4'></div>
    //                           <div class='circle-animation circle5'></div>
    //                           <div class='circle-animation circle6'></div>
    //                           <div class='circle-animation circle7'></div>
    //                           <div class='circle-animation circle8'></div>
    //                           <div class='circle-animation circle9'></div>
    //                           <div class='circle-animation circle10'></div>
    //                         </div>
    //                       </div> 
    //                                   <div class='dialog-box-notification'>
    //                                   ".notificationBox('success', $LNG['saved'],1)."
    //                                   </div>
    //                                   ";
    //                     }else{
                            
    //                         $failed = "
    //                                     <div class='dialog-box-notification'>
    //                                     ".notificationBox('error', 'Something Went Wrong',1)."
    //                                     <div class='message-form-des'>
    //                                      <div class='circle-border'></div>
    //                                         <div class='circle'>
    //                                         <div class='error'></div></div>
    //                                         Failed
    //                                     </div>
    //                                     </div>
    //                                     ";
    //                     }
                        
    //                 }else{

    //                     foreach($arr as $err) {
    //                         $failed = notificationBox('error',$err, 1); //  the error value for translation file
                        
    //                       }
    //                 }

    //         return ['success' => $success, 'failed' => $failed];
    //    }




    //    public function get_total_query($type,$from_date,$to_date){
        
    //     if($type==1){
    //         $query = sprintf("SELECT DISTINCT id, `total` FROM `taking_milk` WHERE `by` = %s AND `to` = %s AND (`date` BETWEEN '%s' AND '%s')", $this->user_id, $this->c_id, $this->from_date,$this->to_date);
    //     }else{
    //         $query = sprintf("SELECT DISTINCT id,`total` FROM `taking_milk` WHERE `by` = %s AND `to` = %s AND (`date` BETWEEN '%s' AND '%s')", $this->user_id, $this->c_id, $this->from_date,$this->to_date);
    //     }
    //     $result = $this->db->query($query);
    //     $rows = '';
    //     if($result && $result->num_rows > 0){
    //         while($row = $result->fetch_array()) {
    //             $rows .= $row;
    //         }

    //     }

    //    }
       public function edit_btn($milk_table,$id){
        global $settings;
        // Edit button
        // $milk_type checking milk is direct milk or by fat rate
        // id for checking milk data id number

        $action = $this->query_edit_btn($milk_table,$id)['data'];  // run query for for edit milk data
        
        switch($action['milk_type']){
            case 'dm':
            case 'masterlist_dm':
            case 'wishlist_dm':
                $output = "
                                <div class='message-form-des' ><table class='edit-milk-data' style='width:100%'><tbody>
                                <tr><th>Direct Milk Rate:</th><td>
                                <input type='number' name='dm' id='dm-".$id."' onkeyup='check_dm(\"#dm-$id\",{$settings['d_rate_minimum']},{$settings['d_rate_maximum']},\"#dm-error$id\",\"#save-$id\")'  value='{$action['d_rate']}' placeholder='Direct Rate'  step='any'>
                                <div class='page-input-sub-error d-none' style='margin:-5px 0px 0px 30px' id='dm-error".$id."'>Direct Rate should be {$settings['d_rate_minimum']} to {$settings['d_rate_maximum']}</div>
                                </td></tr>
                                <tr><th>Weight</th><td>
                                <input type='number' name='weight' id='weight-".$id."' onkeyup='check_weight(\"#weight-$id\",{$settings['weight_minimum']},{$settings['weight_maximum']},\"#mf-error$id\",\"#save-$id\")' value='{$action['weight']}' placeholder='Weight' step='any'>
                                <div class='page-input-sub-error d-none' style='margin:-5px 0px 0px 30px' id='mf-error".$id."'></div>
                                </td></tr>
                                <tr><th>
                                 <div class='show_total' onclick='do_total(\"{$action['milk_type']}\",[\"#dm-$id\",\"#weight-$id\"],[{$settings['d_rate_minimum']},{$settings['d_rate_maximum']},{$settings['weight_minimum']},{$settings['weight_maximum']}],\"#show_total$id\")'>Show Total</div>
                                 </th><td><div id='show_total$id'style='
                                 text-align: center;
                                 font-weight: bold;
                                 font-size: 16px;
                                '></div></td></tr>
                                </tbody></table></div>
                                <div class='save-change' id='save-$id' onclick='update_milk_data($milk_table, \"" . $action['milk_type'] . "\", $id, ".$action['to'].",\"#edit-success\",\"#edit-error\",\"#edit-loader\",\"#save-$id\",[\"#dm-$id\",\"#weight-$id\"])'>Save</div>
                                </div>
                               
                         ";
                break;
            case 'mf':
               
                $output = "
                                
                                
                                <div class='message-form-des' ><table class='edit-milk-data' style='width:100%'><tbody>
                                <tr><th>Milk Fat Rate:</th><td>
                                <input type='number' name='mfr' id='mfr-".$id."' onkeyup='check_mfr(\"#mfr-$id\",{$settings['f_rate_minimum']},{$settings['f_rate_maximum']},\"#mfr-error$id\",\"#save-$id\")'  value='{$action['fat_rate']}' placeholder='Milk Fat Rate' step='any'>
                                <div class='page-input-sub-error d-none' style='margin:-5px 0px 0px 30px' id='mfr-error".$id."'>Milk Rate should be {$settings['f_rate_minimum']}.0 to {$settings['f_rate_maximum']}.0 </div>
                                
                                </td></tr>
                                <tr><th>Milk Fat:</th><td>
                                <input type='number' name='mf'  id='mf-".$id."' onkeyup='check_mf(\"#mf-$id\",{$settings['mf_minimum']},{$settings['mf_maximum']},\"#mf-error$id\",\"#save-$id\")' value='{$action['fat']}' placeholder='Milk Fat' step='any'>
                                <div class='page-input-sub-error d-none' style='margin:-5px 0px 0px 30px' id='mf-error".$id."'>Milk fat should be {$settings['mf_minimum']}.0 to {$settings['mf_maximum']}.0</div></td></tr>

                                <tr><th>Weight:</th><td>
                                <input type='number' name='weight' id='weight-".$id."' onkeyup='check_weight(\"#weight-$id\",{$settings['weight_minimum']},{$settings['weight_maximum']},\"#weight-error$id\",\"#save-$id\")' value='{$action['weight']}' placeholder='Weight' step='any'>
                                <div class='page-input-sub-error d-none' style='margin:-5px 0px 0px 30px' id='weight-error".$id."'></div>
                                </td></tr>

                                <tr><th>
                                 <div class='show_total' onclick='do_total(\"{$action['milk_type']}\",[\"#weight-$id\",\"#mf-$id\",\"#mfr-$id\"],[{$settings['weight_minimum']},{$settings['weight_maximum']},{$settings['mf_minimum']},{$settings['mf_maximum']},{$settings['f_rate_minimum']},{$settings['f_rate_maximum']}],\"#show_total$id\")'>Show Total</div>
                                 </th><td><div id='show_total$id' style='
                                 text-align: center;
                                 font-weight: bold;
                                 font-size: 16px;
                                '></div></td></tr>
                                
                                </tbody></table></div>
                                <div class='save-change' id='save-$id' onclick='update_milk_data($milk_table, \"" . $action['milk_type'] . "\", $id, ".$action['to'].",\"#edit-success\",\"#edit-error\",\"#edit-loader\",\"#save-$id\",[\"#mf-$id\",\"#weight-$id\",\"#mfr-$id\"])'>Save</div>
                                </div>
                                
                         ";
                         break;
            default:
                    
            $output = "
                     <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                        <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
                    </svg>
                    <p class='error_p'>Something Went Wrong</p>
                    
                ";
        
            
        }
        
          return $output;

       } 


       }
       class milk_count{
        public $db; // store database
        public $c_id; // store customer id
        public $record_per_page; // how much record you want per page


        public function milk_count_query($type, $customer) {
            global $user;
        
            $past7days = $this->db->real_escape_string(getPastDate(7));
            $past10days = $this->db->real_escape_string(getPastDate(10));
            $currentDate = $this->db->real_escape_string(date('Y-m-d'));
            $query1 = "";
            $query2 = "";
        
            switch ($type) {
                case 1:
                    // taking table
                    $query1 = sprintf("SELECT ROUND(SUM(`total`), 2) AS `total`, ROUND(SUM(`weight`),3) AS `weight`,  DATE(MIN(`date`)) AS `start_date`, DATE(MAX(`date`)) AS `end_date` FROM `buying_milk` WHERE `by` = %s AND `to` = %s AND (`date` BETWEEN '%s' AND '%s') AND `cleared` = %s", $this->db->real_escape_string($user['id']), $this->db->real_escape_string($customer), $past7days, $currentDate, 0);
        
                    // from giving table
                    $query2 = sprintf("SELECT ROUND(SUM(`total`), 2) AS `total`, ROUND(SUM(`weight`),3) AS `weight`,  DATE(MIN(`date`)) AS `start_date`, DATE(MAX(`date`)) AS `end_date` FROM `selling_milk` WHERE `by` = %s AND `to` = %s AND (`date` BETWEEN '%s' AND '%s') AND `cleared` = %s", $this->db->real_escape_string($user['id']), $this->db->real_escape_string($customer), $past7days, $currentDate, 0);
        
                    break;
        
                case 2:
                    // taking table
                    $query1 = sprintf("SELECT ROUND(SUM(`total`), 2) AS `total`, ROUND(SUM(`weight`),3) AS `weight`,  DATE(MIN(`date`)) AS `start_date`, DATE(MAX(`date`)) AS `end_date` FROM `buying_milk` WHERE `by` = %s AND `to` = %s AND (`date` BETWEEN '%s' AND '%s') AND `cleared` = %s", $this->db->real_escape_string($user['id']), $this->db->real_escape_string($customer), $past10days, $currentDate, 0);
        
                    // from giving table
                    $query2 = sprintf("SELECT ROUND(SUM(`total`), 2) AS `total`, ROUND(SUM(`weight`),3) AS `weight`,  DATE(MIN(`date`)) AS `start_date`, DATE(MAX(`date`)) AS `end_date` FROM `selling_milk` WHERE `by` = %s AND `to` = %s AND (`date` BETWEEN '%s' AND '%s') AND `cleared` = %s", $this->db->real_escape_string($user['id']), $this->db->real_escape_string($customer), $past10days, $currentDate, 0);
        
                    break;
        
                case 3:
                    // taking table
                    $query1 = sprintf("SELECT ROUND(SUM(`total`), 2) AS `total`, ROUND(SUM(`weight`),3) AS `weight`,  DATE(MIN(`date`)) AS `start_date`, DATE(MAX(`date`)) AS `end_date` FROM `buying_milk` WHERE `by` = %s AND `to` = %s AND `cleared` = %s", $this->db->real_escape_string($user['id']), $this->db->real_escape_string($customer), 0);
        
                    // from giving table
                    $query2 = sprintf("SELECT ROUND(SUM(`total`), 2) AS `total`, ROUND(SUM(`weight`),3) AS `weight`,  DATE(MIN(`date`)) AS `start_date`, DATE(MAX(`date`)) AS `end_date` FROM `selling_milk` WHERE `by` = %s AND `to` = %s AND `cleared` = %s", $this->db->real_escape_string($user['id']), $this->db->real_escape_string($customer), 0);
        
                    break;
            }
            $result1 = $this->db->query($query1);
            $result2 = $this->db->query($query2);
            
            $output = array();
        
            if ($result1 && $result2) {
                $taking = $result1->fetch_assoc();
                $giving = $result2->fetch_assoc();
                
                $output = [

                        'bought_milk' => [$taking['total'],$taking['weight'],$taking['start_date'],$taking['end_date']],
                        'sold_milk' => [$giving['total'],$giving['weight'],$giving['start_date'],$giving['end_date']]

                ];
            }
        
            
            
            
            return $output;
        }
        
        

        // public function update_cleared($type,$id){
        //     global $user;

        //     if($type==1){
        //         $query = sprintf("UPDATE `taking_milk` SET `cleared` = %s WHERE `by` = %s AND `to` = %s AND `cleared` = %s",1,$user['id'],$this->db->real_escape_string($id),0);
        //     }elseif($type==2){
        //         $query = sprintf("UPDATE `selling_milk` SET `cleared` = %s WHERE `by` = %s AND `to` = %s AND `cleared` = %s",1,$user['id'],$this->db->real_escape_string($id),0);
        //     }
            
        //     $result = $this->db->query($query);
        //     if($result){
        //       // $num_rows = $result->num_rows;
        //       $output = 'success';
        //       $affected_rows = $this->db->affected_rows;
              
        //     }
        //    return [$output,$affected_rows];
            
        // }
        public function update_cleared($id) {
            global $user;
            $output = '';
        
            // Queries ki array
            $queries = array(
                sprintf("UPDATE `buying_milk` SET `cleared` = %s WHERE `by` = %s AND `to` = %s AND `cleared` = %s", 1, $user['id'], $this->db->real_escape_string($id), 0),
                sprintf("UPDATE `selling_milk` SET `cleared` = %s WHERE `by` = %s AND `to` = %s AND `cleared` = %s", 1, $user['id'], $this->db->real_escape_string($id), 0)
            );
        
            // Timeout duration
            $timeout_seconds = 30; // Example: 30 seconds
        
            // Timeout ko set karein
            $this->db->options(MYSQLI_OPT_READ_TIMEOUT, $timeout_seconds);
        
            // Queries ko multi_query ke through execute karein
            $query = implode("; ", $queries);
            $result = $this->db->multi_query($query);
        
            // Check karein ki queries sahi se execute hui hain ya nahi
            if($result) {
                // Sabhi queries sahi se execute hui hain
                $output = 'success';
            } else {
                // Queries mein samay seema se jyada samay lag raha hai
                $output = 'time_out';
            }
        
            // Free the result
            while ($this->db->next_result()) {
                if ($result = $this->db->store_result()) {
                    $result->free();
                }
            }
        
            // $output aur kisi aur variable ko return karein
            return [$output, $this->db->affected_rows];
        }
        
        
        // public function insert_cleared_history($from_date, $to_date, $total, $weight, $type, $id) {
        //     global $user;
            
        //     try {
        //         $query = sprintf("INSERT INTO `cleared` SET `from_date` = '%s', `to_date` = '%s', `total` = %s, `weight` = %s, `type` = '%s', `by` = %s, `to` = %s",
        //         $from_date,$to_date,$total,$weight,$this->db->real_escape_string($type), $user['id'],$this->db->real_escape_string($id));
        //         $result = $this->db->query($query);
               
        //         if ($result) {
        //             echo 'hh';
        //             die;
        //             return 1; // Query executed successfully
        //         } else {
        //             return 0; // Query failed to execute
        //         }
        //     } catch (Exception $e) {
        //         return 0; // Exception occurred, query failed
        //     }
        // }
        
        // public function insert_cleared_history($from_date,$to_date,$total,$weight,$type,$id){
        //     global $user;
        //     $query = sprintf("INSERT INTO `cleared` SET `from_date` = '%s', `to_date` = '%s', `total` = %s, `weight` = %s, `type` = '%s', `by` = %s, `to` = %s",$from_date,$to_date,$total,$weight,$this->db->real_escape_string($type), $user['id'],$this->db->real_escape_string($id));
            
        //     $result = $this->db->query($query);
        //     if($result && $this->db->affected_rows > 0){
        //         return 1;
        //     }
        //     if ($result = $this->db->store_result()) {
        //         $result->free();
        //     }
        
        // }
        // public function insert_cleared_history($from_date, $to_date, $total, $weight, $type, $id) {
        //     global $user;
        
        //     $query = sprintf("INSERT INTO `cleared` SET `from_date` = '%s', `to_date` = '%s', `total` = %s, `weight` = %s, `type` = '%s', `by` = %s, `to` = %s",
        //         $from_date, $to_date, $total, $weight, $this->db->real_escape_string($type), $user['id'], $this->db->real_escape_string($id));
        
        //     // Use mysqli_multi_query to execute the query
        //     $result = $this->db->multi_query($query);
        
        //     if ($result && $this->db->affected_rows > 0) {
        //         // Fetch and discard the results to avoid the "Commands out of sync" issue
        //         while ($this->db->more_results()) {
        //             $this->db->next_result();
        //             if ($result = $this->db->store_result()) {
        //                 $result->free();
        //             }
        //         }
        
        //         return 1; // Query executed successfully
        //     } else {
        //         // Display the error message
        //         echo 'Query Failed: ' . $this->db->error;
        //         return 0; // Query failed to execute
        //     }
        // }

        public function insert_cleared_history($from_date, $to_date, $total, $weight, $type, $id) {
            global $user;
            while ($this->db->next_result()) {
                if ($result = $this->db->store_result()) {
                    $result->free();
                }
            }
            $query = "INSERT INTO `cleared` SET `from_date` = ?, `to_date` = ?, `cleared_date` = ?, `total` = ?, `weight` = ?, `type` = ?, `by` = ?, `to` = ?";
        
            // Prepare the statement
            $statement = $this->db->prepare($query);
            
            if ($statement) {
                // Bind parameters
                $statement->bind_param("sssddssi", $from_date, $to_date, date('Y-m-d H:i:s'), $total, $weight, $type, $user['id'], $id);
        
                // Execute the statement
                $result = $statement->execute();
        
                if ($result && $statement->affected_rows > 0) {
                    $statement->close();
                    return 1; // Query executed successfully
                } else {
                    // Display the error message
                    echo 'Query Failed: ' . $statement->error;
                    $statement->close();
                    return 0; // Query failed to execute
                }
            } else {
                // Display the error message
                // echo 'Statement Preparation Failed: ' . $this->db->error;
                return 2; // Statement preparation failed
            }
        }
        // public function insert_cleared_history($from_date, $to_date, $total, $weight, $type, $id) {
        //     global $user;
        
            
        //     while ($this->db->next_result()) {
        //         if ($result = $this->db->store_result()) {
        //             $result->free();
        //         }
        //     }
        
        //     // Now you can execute the new query
        //     $query = sprintf("INSERT INTO `cleared` SET `from_date` = '%s', `to_date` = '%s', `total` = %s, `weight` = %s, `type` = '%s', `by` = %s, `to` = %s",
        //         $this->db->real_escape_string($from_date),
        //         $this->db->real_escape_string($to_date),
        //         $this->db->real_escape_string($total),
        //         $this->db->real_escape_string($weight),
        //         $this->db->real_escape_string($type),
        //         $this->db->real_escape_string($user['id']),
        //         $this->db->real_escape_string($id)
        //     );
        
        //     // Execute the query
        //     $result = $this->db->query($query);
        
        //     if ($result) {
        //         if ($this->db->affected_rows > 0) {
        //             return 1; // Query executed successfully
        //         } else {
        //             echo 'No rows were affected by the query.';
        //             return 0; // Query did not affect any rows
        //         }
        //     } else {
        //         // Display the error message
        //         echo 'Query Failed: ' . $this->db->error;
        //         return 0; // Query failed to execute
        //     }
        // }
        
        
        
        public function cleared(){
            global $user;
            $data = $this->filter_total()['all_days'];
            // $total_all_g =  $this->milk_count_query(3,2,$this->c_id);
            $total = $data[0][0];
            $weight = $data[1][0];
            $type = $data[0][1];
            $date = $data[2];
            $formated_date = format_dates($date);
            // $customer = $this->fetch_customer($this->c_id);
            // $p_num = $customer[1];
            $p_num = $user['p_number'];


          $msg_date = "$formated_date[0] To: $formated_date[1]";
          $msg_total = "weight:$weight and Rs:$total";
          $array = [$msg_date,$msg_total];
          $message_id = message_id('milk_cleared');
         
           if( $total && array_filter($date)){
            $update = $this->update_cleared($this->c_id)[0];
            
           if ($update== 'success') {
                
                $sms = new send_sms();
                $send_sms = $sms->process($message_id,$p_num,$array);

             if($this->insert_cleared_history($date[0],$date[1],$total,$weight,$type,$this->c_id) == 1){
              
                $output = '<div class="success-container">
                <div class="circle"></div>
                <div class="ring"></div>
                <div class="checkmark"></div>
                <div class="fading-circles">
                  <div class="circle-animation circle1"></div>
                  <div class="circle-animation circle2"></div>
                  <div class="circle-animation circle3"></div>
                  <div class="circle-animation circle4"></div>
                  <div class="circle-animation circle5"></div>
                  <div class="circle-animation circle6"></div>
                  <div class="circle-animation circle7"></div>
                  <div class="circle-animation circle8"></div>
                  <div class="circle-animation circle9"></div>
                  <div class="circle-animation circle10"></div>
                </div>
              </div> 
              <div class="success-title">Setting Saved Successfully</div>
              '; 
             }else{
                $output = " 
                        <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                        <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                        <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
                        </svg>
                        <div class='failed-title'>Successfully Cleared But record not added in history Take Screenshot your Record and contact to Admin.</div>
               ";   
             }

             } elseif($update = 'time_out') {
                $output = " 
                <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
               </svg>
               <div class='failed-title'>Request time out</div>
               ";  
             }else{
                $output = " 
                    <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                    <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                    <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                    <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
                </svg>
                <div class='failed-title'>Somthing Went Wrong</div>
                ";
             }

           }else{
            $output = " 
                    <svg version='1.1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                    <circle class='path circle' fill='none' stroke='#D06079' stroke-width='6' stroke-miterlimit='10' cx='50' cy='50' r='47'/>
                    <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='26' y1='28' x2='74' y2='72'/>
                    <line class='path line' fill='none' stroke='#D06079' stroke-width='6' stroke-linecap='round' stroke-miterlimit='10' x1='74' y1='28' x2='26' y2='72'/>
                </svg>
                <div class='failed-title'>Invalid Inputs</div>
                ";
           }
           
        
            return ['output' => $output,'sms' => $send_sms];

        } 

        public function compare_values($bought_milk=null, $sold_milk=null) {
            if($bought_milk === '' && $sold_milk === '') {
                return array(0, 'null');
            } else if ($bought_milk === '') {
                return array($sold_milk, 'sold_milk');
            } else if ($sold_milk === '') {
                return array($bought_milk, 'bought_milk');
            } else {
                $result = abs($bought_milk - $sold_milk);
                if ($bought_milk > $sold_milk) {
                    return array($result, 'bought_milk');
                } else if ($sold_milk > $bought_milk) {
                    return array($result, 'sold_milk');
                } else {
                    return array(0, 'equal');
                }
            }
        }
        public function compare_date_range($date1, $date2) {
                    if (empty($date1[0]) && empty($date1[1])) {
                        return $date2;
                    } else if (empty($date2[0]) && empty($date2[1])) {
                        return $date1;
                    } else {
            $diff1 = date_diff(date_create($date1[0]), date_create($date1[1]));
            $diff2 = date_diff(date_create($date2[0]), date_create($date2[1]));
            if ($diff1->days > $diff2->days) {
                return $date1;
            } else {
                return $date2;
            }
          }
        }
        public function format_date_array($date=null) {
            $formatted_dates = array();
            if($date !==null){
                foreach ($date as $d) {
                    if($d !== null) {
                        $formatted_dates[] = date('d-m-Y', strtotime($d));
                    }
                }
            }
            return $formatted_dates;
        }
        
        public function filter_total(){
            

            $data7 = $this->milk_count_query(1,$this->c_id);
            $data10 = $this->milk_count_query(2,$this->c_id);
            $data_all = $this->milk_count_query(3,$this->c_id);
            
            
            // bought_milk last days
            $bought_milk_total_7 = $data7['bought_milk'][0];
            $bought_milk_total_10 = $data10['bought_milk'][0];
            $bought_milk_total_all =$data_all['bought_milk'][0];
            
            $bought_milk_weight_7 = $data7['bought_milk'][1];
            $bought_milk_weight_10 = $data10['bought_milk'][1];
            $bought_milk_weight_all = $data_all['bought_milk'][1];
            // giving last days

            $sold_milk_total_7 =  $data7['sold_milk'][0];
            $sold_milk_total_10 =  $data10['sold_milk'][0];
            $sold_milk_total_all =  $data_all['sold_milk'][0];

            $sold_milk_weight_7 = $data7['sold_milk'][1];
            $sold_milk_weight_10 = $data10['sold_milk'][1];
            $sold_milk_weight_all = $data_all['sold_milk'][1];

              /* date range bought_milk */
              $bought_milk_start_d = $data_all['bought_milk'][2];
              $bought_milk_end_d = $data_all['bought_milk'][3];

            //   $bought_milk_all_date = $this->milk_count_query(3,1,$this->c_id)[0];
            //   $bought_milk_start_d = $bought_milk_all_date['start_date'];   
            //   $bought_milk_end_d = $bought_milk_all_date['end_date'];   

                        /* date range givng */

                $sold_milk_start_d = $data_all['sold_milk'][2];
                $sold_milk_end_d = $data_all['sold_milk'][3]; 

            //  $sold_milk_all_date = $this->milk_count_query(3,2,$this->c_id)[0];
            //   $sold_milk_start_d = $sold_milk_all_date['start_date'];  
            //   $sold_milk_end_d = $sold_milk_all_date['end_date'];

            // Now creating pairs and checking which value bigger actually milk bought_milk or sold_milk

            $total7 = $this->compare_values($bought_milk_total_7,$sold_milk_total_7);
            $total10 = $this->compare_values($bought_milk_total_10,$sold_milk_total_10);
            $total = $this->compare_values($bought_milk_total_all,$sold_milk_total_all);

            $weight7 =  $this->compare_values($bought_milk_weight_7,$sold_milk_weight_7);
            $weight10 = $this->compare_values($bought_milk_weight_10,$sold_milk_weight_10);
            $weight =   $this->compare_values($bought_milk_weight_all,$sold_milk_weight_all);
             
            $all_date = $this->compare_date_range([$bought_milk_start_d,$bought_milk_end_d],[$sold_milk_start_d,$sold_milk_end_d]);

            $output = [

                      '7_days' => [$total7,$weight7],
                      '10_days' => [$total10,$weight10],
                      'all_days' => [$total,$weight,$all_date]

                       ];
              return $output;
            // return ['7_days' => $total7,'10_days' => $total10, 'all_total' => $total,'all_date' => $all_date];
        }
       public function filter_all_total_data(){
        $data = $this->filter_total();
        
         $output = "
                
                    
                     <tr>
                     <th> Last 7 Days </th>
                     <th> {$data['7_days'][0][0]} </th>
                     <th> {$data['7_days'][1][0]} </th>
                     </tr>

                     <tr>
                     <th> Last 10 Days </th>
                     <th>  {$data['10_days'][0][0]} </th>
                     <th>  {$data['10_days'][1][0]} </th>
                     </tr>

                     <tr>
                     <th> All  </th>
                     <th> {$data['all_days'][0][0]} </th>
                     <th> {$data['all_days'][1][0]} </th>
                     </tr>
                           
                
                 ";
                 return $output;
       }
       public function fetch_customer($c_id){
        $query = sprintf("SELECT `fname`,`lname`,`p_number` FROM `m_customers` WHERE `c_id` = %s",$this->db->real_escape_string($c_id));
        $result = $this->db->query($query);
        if($result && $result->num_rows > 0){
            $customer = $result->fetch_assoc();
        }
        $name = formatFullName($customer['fname'],$customer['lname']);
        return [$name,$customer['p_number']];
       }
       public function heighlight_total($type=null){
           global $user,$LNG;
           $data = $this->filter_total()['all_days'];

        //    $date = $this->format_date_array($data[2]);
           foreach ($data[2] as $dates) {
            $date[] = date('d M', strtotime($dates));
        }
        //    print_r($data);
        //    die;
           
        //    $date = $this->format_date_array($this->filter_total()['all_date']);
           $customer = $this->fetch_customer($this->c_id);
           
           
            switch($data[0][1]) {
                case 'bought_milk':
                    if($type==1){
                        $message = "
                        <div class='giving-milk-headline'>"
                        .sprintf($LNG['giving_title'],$user['fname'],$date[0],$date[1],$data[1][0],$data[0][0],$data[0][0],$customer[0]).
                        "</div>
                        <div class='clear_btn_title'> If it's clear click on the below button</div>
                        <center><div class='clear_take_give' id='take_{$data[0][0]}' onclick=\"cleared_btn('#dialog-box','$this->c_id')\">Clear &#8377; {$data[0][0]}</div></center>
                    ";
                    }else{
                        
                        $message = " <div class='giving-milk-headline'>"
                        .sprintf($LNG['confirm_given'],$user['fname'],$data[0][0],$customer[0]).
                        "</div>
                        <center><div class='clear_take_give' id='take_{$data[0][0]}' onclick=\"cleared_confirm('#confirmation','#sucess',$this->c_id)\">confirm</div></center>
                                   ";

                    }
                   
                    break;
                case 'sold_milk':
                    if($type==1){
                        $message = "
                        <div class='taking-milk-headline'>"
                        .sprintf($LNG['taking_title'],$user['fname'],$date[0],$date[1],$data[1][0],$data[0][0],$data[0][0],$customer[0]).
                        "</div>
                        <div class='clear_btn_title'> If it's clear click on the below button</div>
                        <center><div class='clear_take_give' id='take_{$data[0][0]}' onclick=\"cleared_btn('#dialog-box','$this->c_id')\">Clear &#8377; {$data[0][0]}</div></center>
                    ";
                    }else{

                        $message = " <div class='giving-milk-headline'>"
                        .sprintf($LNG['confirm_taken'],$user['fname'],$data[0][0],$customer[0]).
                        "</div>
                        <center><div class='clear_take_give' id='take_{$data[0][0]}' onclick=\"cleared_confirm('#confirmation','#sucess',$this->c_id)\">Confirm</div></center>
                                   ";
                    }
                    
                    break;
                default:
                    $message = "
                        <div class='equal-milk-headline'>"
                        .sprintf($LNG['equal_title'],$user['fname'],$customer[0]).
                        "</div>
                    ";
            }
            
           
           return $message;
       }
    //    public function cleared_history_query($id) {
    //     global $user;
    //     $output = array();
        
    //     $limit = $this->record_per_page;
    //     $offset = isset($_POST['start']) ? $_POST['start'] : 0;
    
    //     $query = sprintf('SELECT ROW_NUMBER() OVER (ORDER BY `cleared_date` DESC) AS `serial_number`, `from_date`, `to_date`, `cleared_date`, `total`, `weight` FROM `cleared` WHERE `by` = %s AND `to` = %s LIMIT %s, %s', $user['id'], $this->db->real_escape_string($id), $offset, $limit);
    //     $result = $this->db->query($query);
    //      $num_rows = $result->num_rows;
    //     if ($result && $num_rows > 0) {
    //         while ($row = $result->fetch_array()) {
    //             $output[] = $row;
    //         }
    //         // $result->free();
    //         $this->db->close();
    //     }
        
    //     return ['num_rows' => $num_rows, 'output' => $output];
    // }

    // public function cleared_history_data() {
    //     $data = $this->cleared_history_query($this->c_id);
    //     $rows = $finished = '';
        
    //     if ($data['num_rows'] > 0) {
    //         while ($row = $data['output']) {
    //             $cleared_date = format_dates([$row['cleared_date']]);
    //             $from_date = format_dates([$row['from_date']]);
    //             $to_date = format_dates([$row['to_date']]);
    //             $rows .= " 
    //                 <tr>
    //                     <th>{$row['serial_number']}</th>
    //                     <th>{$from_date[0]} </th>
    //                     <th>{$to_date[0]} </th>
    //                     <th>{$cleared_date[0]} </th>
    //                     <th>{$row['total']} </th>
    //                 </tr>
    //             ";
    //         }
    //     } else {
    //         $finished .= "<div style='color:red'>No Data Found</div>";
    //     }
    //        echo $rows;     
    //     return ['rows' => $data['num_rows'], 'data' => $rows, 'finished' => $finished];
    // }
    
    
        public function cleared_history_query($id){
            global $user;
            $limit = $this->record_per_page;
            $offset = (isset($_POST['start'])) ? $_POST['start'] : 0;
            $query = sprintf('SELECT  ROW_NUMBER() OVER (ORDER BY `cleared_date` DESC) AS `serial_number`, `from_date`,`to_date`, `cleared_date`,`total`, `weight` FROM `cleared` WHERE `by` = %s AND `to` = %s LIMIT %s, %s',$user['id'],$this->db->real_escape_string($id),$offset,$limit);
            $result = $this->db->query($query);
            $num_rows = $result->num_rows;
            
            return ['num_rows' => $num_rows, 'query' => $result];
        }
        public function cleared_history_data(){
            $data = $this->cleared_history_query($this->c_id);
            $rows = $finished = '';
            if($data['num_rows'] > 0){
                while($row = $data['query']->fetch_array()){
                    $cleared_date = format_dates([$row['cleared_date']]);
                    $from_date = format_dates([$row['from_date']]);
                    $to_date = format_dates([$row['to_date']]);
                    $c_date = date('Y-m-d', strtotime($cleared_date[0]));
                    $c_time = date('h:i a', strtotime($cleared_date[0]));
                    $rows .= " 
                            <tr>
                            <th>{$row['serial_number']}</th>
                            <th>{$row['weight']} </th>
                            <th>{$row['total']} </th>
                            <th>{$from_date[0]} </th>
                            <th>{$to_date[0]} </th>
                            <th>{$c_date} </th>
                            <th>{$c_time} </th>
                            
                            </tr>
                             ";
                }
                
                // $data['query']->free();
            }else{
                $finished .= "<div style='color:red'>No Data Found</div>";
            }
            
            return ['rows' => $data['num_rows'], 'data' => $rows, 'finished' => $finished];
        }
        
        
        public function process($type){
            
            if($type==1){
                $output = $this->filter_all_total_data();
            }elseif($type==2){
                $output = $this->heighlight_total(1);
            }elseif($type==3){
                $output = $this->cleared_history_data();
            }elseif($type==4){
             
                $output = $this->heighlight_total();
            }
            return $output;
        }

    }
      class milkmen_analytics{
        public $db;
        public $mm_id;
        public $from_date;
        public $to_date;
        public $cleared;

       public function query_string($table_name,$cleared,$single,$date){
        
        if($cleared){
           
            if($single){
                
                $query = sprintf("SELECT  ROUND(SUM(`weight`), 2) AS `weight`,  ROUND(SUM(`total`), 2) AS `total` FROM `%s` WHERE `by` = %s AND `cleared` = %s AND Date(`date`) = '%s'",$table_name,$this->db->real_escape_string($this->mm_id),$this->db->real_escape_string(1),$this->db->real_escape_string($date[0]));
            }else{
                
                $query = sprintf("SELECT  ROUND(SUM(`weight`), 2) AS `weight`,  ROUND(SUM(`total`), 2) AS `total` FROM `%s` WHERE `by` = %s AND `cleared` = %s AND (Date(`date`) BETWEEN '%s' AND '%s')",$table_name,$this->db->real_escape_string($this->mm_id),$this->db->real_escape_string(1),$this->db->real_escape_string($date[0]),$this->db->real_escape_string($date[1]));
            }
        }else{
            
            if($single){
              
                $query = sprintf("SELECT ROUND(SUM(`weight`), 2) AS `weight`, ROUND(SUM(`total`), 2) AS `total` FROM `%s` WHERE `by` = %s AND Date(`date`) = '%s'",$table_name,$this->db->real_escape_string($this->mm_id),$this->db->real_escape_string($date[0]));
            }else{
               
                $query = sprintf("SELECT ROUND(SUM(`weight`), 2) AS `weight`, ROUND(SUM(`total`), 2) AS `total` FROM `%s` WHERE `by` = %s AND (Date(`date`) BETWEEN '%s' AND '%s')",$table_name,$this->db->real_escape_string($this->mm_id),$this->db->real_escape_string($date[0]),$this->db->real_escape_string($date[1]));
            }
        }
        
       
        $result = $this->db->query($query);
        if($result && $result->num_rows >0){
            $output = $result->fetch_assoc();
        }
        return $output;
       } 

        public function call_query($cleared=null,$single,$dates){
           
            // var_dump($cleared);
            // die;
            if($cleared === 'true'){
                
                $buying_milk = $this->query_string('buying_milk',1,$single,$dates);
                $selling_milk = $this->query_string('selling_milk',1,$single,$dates);
                
            }else{
                    $buying_milk = $this->query_string('buying_milk',0,$single,$dates);
                    $selling_milk = $this->query_string('selling_milk',0,$single,$dates);
                
            }
            
            return ['buying_milk' => $buying_milk, 'selling_milk' => $selling_milk];
        }


        public function analytics_calculate($single,$dates=null){
            
            $data = $this->call_query($this->cleared,$single,$dates);
             // buying_milk data
           $b_weight = $data['buying_milk']['weight'];
           $s_weight = $data['selling_milk']['weight'];

           // selling_milk  data

           $b_total = $data['buying_milk']['total'];
           $g_total = $data['selling_milk']['total'];
           
           // analytics showing buying_milk/selling_milk liters & total 
           $buying_milk_weight = $b_weight ? $b_weight." ltr bought" : 'bought';
           $selling_milk_weight = $s_weight ? $s_weight." ltr sold" : 'sold';
           
           $buying_milk_total = "₹ ". ($b_total ? $b_total : '0.00') ."";
           $selling_milk_total = "₹ ". ($g_total ? $g_total : '0.00') ."";

                // checking weight
                if($b_weight > $s_weight){
                    $result = $b_weight - $s_weight;
                    $weight = ['buying_milk',$result];
                    
                  }elseif($s_weight > $b_weight){
                   $result = $s_weight - $b_weight;
                   $weight = ['selling_milk',$result];
                  }
       
                  // checking total
       
                  if($b_total > $g_total){
                    $t_result = $b_total - $g_total;
                    $total = ['buying_milk',$t_result];
                    
                  }elseif($g_total > $b_total){
                   $t_result = $g_total - $b_total;
                   $total = ['selling_milk',$t_result];
                  }

        return ['weight' => $weight,'total' => $total,'buying_milk_weight' => $buying_milk_weight,'selling_milk_weight' => $selling_milk_weight,'buying_milk_total'=>$buying_milk_total,'selling_milk_total' =>$selling_milk_total];
        }


        public function output_analytics($type,$dates=null){
        
       if($type ==1){
        $data = $this->analytics_calculate(0,$dates);

        $buying_milk_weight = $data['buying_milk_weight'];
        $selling_milk_weight = $data['selling_milk_weight'];
        
        $buying_milk_total = $data['buying_milk_total'];
        $selling_milk_total = $data['selling_milk_total'];

        // headerline----------------------------------------
      
        // checking weight
          $weight = $data['weight'];
        

        // checking total
          $total = $data['total'];
        if(isset($weight) && isset($total)){
            if($weight[0] == 'buying_milk'){
                $weight_title = "You bought milk extra <b>".round($weight[1],3)."</b> litre";
            }else{
                $weight_title = "You sold milk extra extra <b>".round($weight[1],3)."</b> litre";
            }

            if($total[0]=='buying_milk'){
                $total_title = [1,"and you are Now in Loss <b>₹ ".round($total[1],2)."</b> don't be sad try again."];
            }else{
                $total_title = [0,"and you are Earned <b>₹ ".round($total[1],2)."</b> Now totally in Profit"];
            }

            if($total_title[0] == 1){
                $headline = "
                <div class='analytics red'>$weight_title $total_title[1]</div>
                             ";
            }else{
                $headline = "
                <div class='analytics green'>$weight_title $total_title[1]</div>
                             ";
            }
           
        }else{
            $headline = "<div class='analytics normal'>Sorry, no record found in this date</div>";
        }
          
         $output = ['buying_milk_weight' => $buying_milk_weight,'buying_milk_total' => $buying_milk_total, 'selling_milk_weight' =>$selling_milk_weight, 'selling_milk_total' => $selling_milk_total,'headline'=>$headline];
    }else{

        $array = [
                    ['Today',1,find_date('0')],
                    ['Yesterday',1,find_date('1')],
                    ['This Week',0,find_date('this_week')],
                    ['Last 7 days',0,find_date('7')],
                    ['Last 10 days',0,find_date('10')],
                    ['This Month',0,find_date('this_month')],
                    ['Last Month',0,find_date('last_month')],
                 ];
           
        $sub_head = ''; 

        foreach ($array as $item) {
            $date_name = $item[0];
            $single = $item[1];
            $date = $item[2];
            $data = $this->analytics_calculate($single, $date);
            $total = $data['total'];
            // print_r($total);
            if(isset($total)){
                if($total[0]=='buying_milk'){
                    $total_title = [1,"$date_name you are in loss <b>₹ $total[1]</b>"];
                }else{
                    $total_title = [0,"$date_name you are in Profit By <b>₹ $total[1]</b>"];
                }
    
                if($total_title[0] == 1){
                    $sub_head .= "
                    <div class='analytics red'> $total_title[1]</div>
                                 ";
                }else{
                    $sub_head .= "
                    <div class='analytics green'> $total_title[1]</div>
                                 ";
                }
               
            }else{
                $sub_head .= "<div class='analytics normal'>$date_name record <b>₹ 0.00</b></div>";
            }
        }    
        
        
        
        
        $output = $sub_head;
       }
      
         return $output;
        }
        function process($type){
            if($type==1){
               
                $output = $this->output_analytics(1,convertDates([$this->from_date,$this->to_date]),0);
            }elseif($type==2){

                // last anaylistics
                $output = $this->output_analytics(0);
            }

            return $output;
        }


      }
       class subscription{
        public $db;
        public $url;
        public $mmen_id;
        public $subs_type;
        public $subs_time;
        public $mmen_name;

        public static function update_subs($type,$db,$mmen_id,$subs_type,$subs_time=null) {
            if($type){
                $query = sprintf(
                    "UPDATE `subscription` SET`subs_type` = '%s' WHERE `subscription`.`mmen_id` = '%s';",
                    $db->real_escape_string($subs_type),
                    $db->real_escape_string($mmen_id)
                );
            }else{
                $query = sprintf("INSERT INTO `subscription` (`mmen_id`, `subs_time`, `subs_type`)
                VALUES ('%s', '%s', '%s')",
                $db->real_escape_string($mmen_id),
                $subs_time,
                $subs_type);

            }
            $result = $db->query($query);
        
            if ($result) {
                if ($db->affected_rows > 0) {
                    return 1; // Rows affected, return 1
                } else {
                    return 0; // No rows affected, return 0
                }
            } else {
                return 2; // Query failed, return 2
            }
        }

        public function show_subs(){
              
              $current_time = date('Y-m-d H:i:s');
            if($current_time > $this->subs_time && ($this->subs_type == 1 || $this->subs_type ==2)){
                    self::update_subs(1,$this->db,$this->mmen_id,0);
            }elseif($current_time < $this->subs_time && $this->subs_type == 0){
                self::update_subs(1,$this->db,$this->mmen_id,1);
            }elseif($current_time > $this->subs_time && ($this->subs_type == null)){
                self::update_subs(1,$this->db,$this->mmen_id,0);
            }

            $about_subs_link = permalink($this->url."/index.php?a=info&b=subscription");
            $desclaimer = "We do not want to charge any subscription from you, there are some compulsions behind it, you can <a href='".$about_subs_link."' rel='loadpage'> know here.</a>";

            switch($this->subs_type){
                case null :
                    $header = 'We do not find your subscription';
                    $contain = 'Dear <b>'.$this->mmen_name.'</b> we are sorry to find your subscription.';

                    break;
                case 0 :
                    $header = 'You Subscribed is Expired';
                    $contain = 'Dear <b>'.$this->mmen_name.'</b> your subscription is expired at <subs_date>'.date('d-m-Y h:iA', strtotime($this->subs_time)).'</subs_date> try with new subscription';

                    break;
                case 1 :
                    $header = 'You Subscribed a paid membership';
                    $contain = 'Dear <b>'.$this->mmen_name.'</b> you have a paid subscription and it will be expired on <subs_date>'.date('d-m-Y h:iA', strtotime($this->subs_time)).'</subs_date>';

                    break;
                case 2 :
                    $header = 'You have a free trial subscription';
                    $contain = 'Dear <b>'.$this->mmen_name.'</b> you have a free subscription and it will be expired on <subs_date>'.date('d-m-Y h:iA', strtotime($this->subs_time)).'</subs_date>';
                    break;
                case 3 :
                    $header =  'Your subscription is totally free.';
                    $contain = 'Dear <b>'.$this->mmen_name.'</b> your subscription is till only <subs_date>'.date('d-m-Y h:iA', strtotime($this->subs_time)).'</subs_date> but you can access doodhbazar because you have now unlimited free subscription.';
                    break;
            }

            return ['header'=> $header,'contain' => $contain, 'desclaimer' => $desclaimer];
        }
        
       }
       class load_cites{
        public $db;
        public $state;
        public $district;
        public $sub_district;
        
        public function do_query($type,$data){
            if($type==1){
                $query = sprintf('SELECT `d_id`,`district_name` FROM `districts` WHERE `states_id` = %s',$data);
            }elseif($type==2){
                $query = sprintf('SELECT `sd_id`,`sub_district_name` FROM `sub_districts` WHERE `district_id` = %s',$data);
            }
            
            return $query;
        }
        public function load_district($state,$current=null){
            global $LNG;
            $query = $this->do_query(1,$state);
            $result = $this->db->query($query);
            
            $rows = "<option value=''>{$LNG['select_districts']}</option>";
            if($result && $result->num_rows > 0){
                while($row = $result->fetch_assoc()) {
                    if($row['d_id'] == $current) {
                        $selected = ' selected="selected"';
                    } else {
                        $selected = '';
                    }
                    $rows .= '<option value="'.$row['d_id'].'"'.$selected.'>'.$row['district_name'].'</option>';
    
                }    
           }
           return $rows;
        }
        public function load_sub_district($district,$current=null){
            global $LNG;
            $query = $this->do_query(2,$district);
            $result = $this->db->query($query);
            $rows = "<option value=''>{$LNG['select_sub_districts']}</option>";
            if($result && $result->num_rows > 0){
                while($row = $result->fetch_assoc()) {
                    if($row['sd_id'] == $current) {
                        $selected = ' selected="selected"';
                    } else {
                        $selected = '';
                    }
                    $rows .= '<option value="'.$row['sd_id'].'"'.$selected.'>'.$row['sub_district_name'].'</option>';
                }    
           }
           return $rows;
        }
        public function process($type){
            if($type==1){
              $output =  $this->load_district($this->state);
            }elseif($type==2){
              $output = $this->load_sub_district($this->district);
            }
            return $output;
        }

       }
       class locality{
        public $db;
        public $state;
        public $district;
        public $sub_district;
        public $area;
        public $search_term;

        public function locality_query($type=null,$state,$district,$sub_district,$search_term){
            if($type){
                $query = sprintf("SELECT `village_name`, `local_name` FROM `villages` WHERE (`state_id` = %s AND `district_id` = %s AND `sub_district_id` = %s) AND `village_name` LIKE '%s';", 
                    $this->db->real_escape_string($state),
                    $this->db->real_escape_string($district),
                    $this->db->real_escape_string($sub_district),
                    $this->db->real_escape_string($search_term.'%'));
            } else {
                $query = sprintf("SELECT `colony_name` FROM `colonies` WHERE (`state_id` = %s AND `district_id` = %s AND `sub_district_id` = %s) AND `colony_name` LIKE '%s';",
                    $this->db->real_escape_string($state),
                    $this->db->real_escape_string($district),
                    $this->db->real_escape_string($sub_district),
                    $this->db->real_escape_string($search_term.'%'));
            }

            $result = $this->db->query($query);
            $num_rows = $result->num_rows;
            if($result && $num_rows > 0){
                $output = $result->fetch_all(MYSQLI_ASSOC);
            }
            return ['rows' => $num_rows, 'output' =>$output];
        }
        public function process(){

            if($this->area==1){
                $local = $this->locality_query(1,$this->state,$this->district,$this->sub_district,$this->search_term);
                // For Village
                $output = $local['output'];
            
            $rows = $local['rows'];
            $count = 1;
            $suggestion = '';
            $h = 1;
            $pattern = '/\((\d+)?\)/';

            foreach($output as $locality){
               $class = 'row_' . ($h % 2 + 1);

                $filter_village = trim(preg_replace($pattern, '', $locality['village_name']));
               
               $suggestion .=  "<div class='$class' id='local_$count' village_$count='{$filter_village}' onclick='fill_suggestion(\"#local_$count\", \"#locality_id\",$count)'>{$locality['village_name']}</div>";

                    $count++; // Increment the counter
                    $h++;
                    if ($count >= 4) { // Break the loop after 3 iterations
                        break;
                    }
            }

            }else{
                $local = $this->locality_query(0,$this->state,$this->district,$this->sub_district,$this->search_term);
                // For Colonies
                $output = $local['output'];
            
            $rows = $local['rows'];
            $count = 1;
            $suggestion = '';
            $h = 1;
            $pattern = '/\((\d+)?\)/';

            foreach($output as $locality){
               $class = 'row_' . ($h % 2 + 1);

                $filter_village = trim(preg_replace($pattern, '', $locality['colony_name']));
               $suggestion .=  "<div class='$class' id='local_$count' village_$count='{$filter_village}' onclick='fill_suggestion(\"#local_$count\", \"#locality_id\",$count)'>{$locality['colony_name']}</div>";

                    $count++; // Increment the counter
                    $h++;
                    if ($count >= 4) { // Break the loop after 3 iterations
                        break;
                    }
            }

            }
           
            
            return ['rows' => $rows, 'suggestion' => $suggestion];
            
        }
       }
       class send_sms{

       public function hit_api($message_id,$p_num,$array){
          $a = $this->array_into_readable($array);
            // $url = sprintf("https://www.fast2sms.com/dev/bulkV2?authorization=%s&sender_id=%s&message=%s&variables_values=%s&route=dlt&numbers=%s",'Vkz9BH16LITExd3gvn2YAPGibp8oau4MKRlhDU50qO7wJmWcseirT87ZyPRAlapFwbCch1W2Ln9mzIg4','DUDBZR',$message_id,urlencode($username.'|'.$otp),urlencode($p_num));
            $url = sprintf("https://www.fast2sms.com/dev/bulkV2?authorization=%s&sender_id=%s&message=%s&variables_values=%s&route=dlt&numbers=%s",
                'Vkz9BH16LITExd3gvn2YAPGibp8oau4MKRlhDU50qO7wJmWcseirT87ZyPRAlapFwbCch1W2Ln9mzIg4',
                'DUDBZR',
                $message_id,
                urlencode($this->array_into_readable($array)),
                urlencode($p_num)
            );
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
            echo "cURL Error #:" . $err;
            } else {
            $output = $response;
            }
            return $output;
          }

       public function array_into_readable($array) {
            if (count($array) == 2) {
                return $array[0] . '|' . $array[1];
            } else if (count($array) == 1) {
                return $array[0];
            } else {
                return '';
            }
        }
        
        public function process($message_id,$p_num,$array){

            $output = $this->hit_api($message_id,$p_num,$array);
           return $output;
           
        }
        
        
       }

       class update_mmen {
        public $db; 		// Database Property
        public $url; 		// Installation URL Property
        public $fname;
        public $lname;
        public $username;	// Username Property
        public $mmen_id;
        public $p_number;
        public $gender;
        public $state;
        public $district;
        public $sub_district;
        public $year;
        public $month;
        public $day;
        public $locality;
        public $area;
        public $milk_distribute_type;
        public $pincode;
        public $dairy_name;
        public $acc_status_val;
        public $subs_time;
        public $subs_type;

        public function all_queries($type){
            // type 1 for account status
            if($type == 1){
                $query = sprintf("UPDATE mmen SET `acc_status` = '%s' WHERE `id` = '%s' ",$this->db->real_escape_string($this->acc_status_val),$this->db->real_escape_string($this->mmen_id));
            }elseif($type==2){
                $query = sprintf("UPDATE mmen SET `fname` = '%s', `lname` = '%s', `p_number` = '%s', `dob` = '%s', `gender` = '%s', `state` = '%s', `district` = '%s', `sub_district` = '%s', `area` = '%s', `locality` = '%s', `pincode` = '%s', `distribute_type` = '%s', `dairy_name` = '%s' WHERE `id` = '%s'",
                $this->db->real_escape_string(mb_strtolower(trim($this->fname))),
                $this->db->real_escape_string(mb_strtolower(trim($this->lname))),
                $this->db->real_escape_string($this->p_number),
                $this->db->real_escape_string($this->year.'-'.$this->month.'-'.$this->day),
                $this->db->real_escape_string($this->gender),
                $this->db->real_escape_string($this->state),
                $this->db->real_escape_string($this->district),
                $this->db->real_escape_string($this->sub_district),
                $this->db->real_escape_string($this->area),
                $this->db->real_escape_string($this->locality),
                $this->db->real_escape_string($this->pincode),
                $this->db->real_escape_string($this->milk_distribute_type),
                $this->db->real_escape_string(mb_strtolower(trim($this->dairy_name))),
                $this->db->real_escape_string($this->mmen_id)
                 );

            }elseif($type==3){
                $subs_status = isset($this->subs_type) && !empty($this->subs_type) ? $this->subs_type : 0;
                if(!empty($this->subs_time)){
                    $subs_time = str_datetime(date('Y-m-d H:i:s'),$this->subs_time);
                    $query = sprintf("UPDATE `subscription` SET `subs_time` = '%s', `subs_type` = '%s' WHERE `subscription`.`mmen_id` = '%s'",$subs_time,$subs_status,$this->db->real_escape_string($this->mmen_id));
                }else{
                    $query = sprintf("UPDATE `subscription` SET `subs_type` = '%s' WHERE `subscription`.`mmen_id` = '%s'",$subs_status,$this->db->real_escape_string($this->mmen_id));
                }
            }
            
            $result = $this->db->query($query);
            if($result) {
                $rowsAffected = $this->db->affected_rows;
                if ($rowsAffected > 0) {
                    return 1; // Rows were affected
                } else {
                    return 0; // No rows were affected
                }
            } else {
                return 2; // Error occurred or query failed
            }
        }
        public function update_process($type=null){
            if($type==1){
                $validate = $this->validate_inputs(1);
                if(empty($validate)){
                    $result = $this->all_queries(1);
                }else{
                    foreach($validate as $value){
                        $message = $value;
                    }
                }
               
            }elseif($type==2){
                $validate = $this->validate_inputs(2);

                if(empty($validate)){
                    $validate_p_num = $this->process_p_number();

                    if($validate_p_num == 1){
                        $result = $this->all_queries(2);
                    }else{
                        $message = $validate_p_num;
                    }
                    
                }else{
                    foreach($validate as $value){
                        $message = $value;
                    }
            }
             
        }elseif($type==3){
                $validate = $this->validate_inputs(3);

                if(empty($validate)){
                        $result = $this->all_queries(3); 
                }else{
                    foreach($validate as $value){
                        $message = $value;
                    }
            }
             
        }
        return [ 'status' => $result, 'message' => $message];
      }
        public function validate_inputs($type){
            global $LNG;
            $error = [];
            if($type==1){
                $acc_status = [0,1,2,3,4,5];
                
                if(!in_array($this->acc_status_val,$acc_status)){
                    $error[] = "Invalid Value";
                }
            }elseif($type==2){
                
                if(empty($this->state)){
                    $error[] .= $LNG['state_empty'];
                }elseif(empty($this->district)){
                    $error[] .= $LNG['district_empty'];
                }elseif(empty($this->sub_district)){
                    $error[] .= $LNG['sub_district_empty'];
                }elseif(empty($this->area)){
                    $error[] .= $LNG['area_empty'];
                }elseif(empty($this->pincode)){
                    $error[] .=$LNG['pincode_empty'];
                }elseif(empty($this->dairy_name)){
                    $error[] .=$LNG['diary_name_empty'];
                }elseif(empty($this->milk_distribute_type)){
                    $error[] .=$LNG['milk_distribute_type_empty'];
                }elseif(empty($this->locality)){
                    $error[] .=$LNG['locality_empty'];
                }else{

                    if(!is_numeric($this->state)){
                        $error[] .= 'Invalid State';
                    }elseif(!is_numeric($this->district)){
                        $error[] .= 'Invalid district';
                    }elseif(!is_numeric($this->sub_district)){
                        $error[] .= 'Invalid Sub - district';
                    }elseif(!is_numeric($this->area)){
                        $error[] .= 'Invalid Area';
                    }elseif(!is_numeric($this->pincode)){
                        $error[]  .= 'Invalid Pincode';
                    }elseif(is_numeric($this->dairy_name)){
                        $error[]  .= 'Dairy Name May Be alphabetic or alphanumeric'; 
                    }elseif(!preg_match('/^[A-Za-z ]+$/', $this->dairy_name)){
                        $error[]  .= 'Dairy name is not alphabetic';
                    }elseif(strlen($this->dairy_name) > 50 || strlen($this->dairy_name) < 10){
                        $error[]  .= 'Invalid Dairy Name length';
                    }
                    elseif(!is_numeric($this->milk_distribute_type)){
                        $error[]  .= 'milk distribute type only be numeric';
                    }elseif(is_numeric($this->milk_distribute_type) && $this->milk_distribute_type > 3){
                        $error[]  .= 'Invalid milk distribute type ';
                    }
                    else{
                        if(strlen($this->pincode) > 6){
                            $error[]  .= 'Invalid Pincode length';
                        }
                    }
                }
            }elseif($type==3){
                $subsTime = $this->subs_time;
                if (!empty($subsTime)) {
                    $pattern = '/^([+-])\s*(\d+)\s+days$/i';
                    if (preg_match($pattern, $subsTime, $matches)) {
                        $sign = $matches[1]; // Extracted sign
                        $numericValue = intval($matches[2]); // Extracted numeric value
                
                        if ($numericValue > 90) {
                            $error[]  .= 'could not be greater then 90';
                        } elseif ($sign !== '+' && $sign !== '-') {
                            $error[]  .= 'Invalid sign use + or -';
                        }
                    } else {
                        $error[]  .= 'Invalid time Format';
                    }
                }
            
                if (!empty($this->subs_type) && ($this->subs_type === '0' || $this->subs_type > 3)) {
                    $error[] = 'Invalid Subscription Type';
                }
                
                 
            }
            return $error;
        }

        public function checking_p_number(){
            $query = sprintf("SELECT `p_number` FROM `mmen` WHERE `p_number` = '%s' AND `id` != '%s';",$this->db->real_escape_string($this->p_number),$this->db->real_escape_string($this->mmen_id));
            $result = $this->db->query($query);
            if($result && $result->num_rows == 0){
                // $result->free(); // Free the result set
                return 1;
            }
        }
        public function validate_p_number(){
            global $LNG;
            $error = array();
            $p_num = $this->p_number;
            if(empty($p_num)){
                $error[] .= $LNG['p_num_empty'];
            }
            if (!is_numeric($p_num)) {
                $error[] .= $LNG['p_num_numeric'];
            }
            if (strlen($p_num) < 10 || strlen($p_num) > 10) {
             $error[] .= sprintf($LNG['p_num_length'],10,strlen($p_num));
            }

            return $error;

        }
        public function process_p_number(){
            global $LNG;
            $arr = $this->validate_p_number(); // Must be stored in a variable before executing an empty condition
            if(empty($arr)){
                $check_p_num = $this->checking_p_number();
                if($check_p_num){
                    return 1;
                }else{
                  $new_error = $LNG['p_num_already_mmen'];
                }
           }else{
               foreach($arr as $err) {
                   $new_error = $err; //  the error value for translation file
                 }
             }
             return $new_error;
        }
       }

       class Admin {
        public $db; 		// Database Property
        public $url; 		// Installation URL Property
        public $username;	// Username Property
        public $password;	// Password Property
    
        /**
         * Select an admin
         *
         * @param	int     $type   Switch the query between verification and retrieving
         * @return	array
         */
        public function get($type = null) {
           
            $query = sprintf("SELECT * FROM `global_admin` WHERE `username` = '%s'", $this->db->real_escape_string($this->username));
            
            $result = $this->db->query($query);
            
            // If no admin account has been found
            if($result->num_rows == 0) {
                // Check the user is a moderator
                if(filter_var($this->db->real_escape_string($this->username), FILTER_VALIDATE_EMAIL)) {
                    $query = sprintf("SELECT * FROM `local_admin` WHERE `email` = '%s' AND `user_group` = 1 AND `suspended` = 0", $this->db->real_escape_string($this->username));
                } else {
                    $query = sprintf("SELECT * FROM `local_admin` WHERE `username` = '%s' AND `user_group` = 1 AND `suspended` = 0", $this->db->real_escape_string($this->username));
                }
    
                $result = $this->db->query($query);
               
                if($result->num_rows == 0) {
                    return 0;
                }
            }
            
            $output = $result->fetch_assoc();
            
            return $output;
        }
    
        /**
         * Check whether the user can be authed or not
         *
         * @return	array | bool
         */
        public function auth() {
            // If the user has previously been authenticated
            if(isset($_SESSION['adminUsername']) && isset($_SESSION['adminPassword'])) {
                $this->username = $_SESSION['adminUsername'];
                $this->password = $_SESSION['adminPassword'];
                $auth = $this->get(1);
    
                if($this->password = $auth['password']) {
                    $logged = true;
                } else {
                    return false;
                }
            }
            // If the user is authenticating
            else {
                $auth = $this->get(0);
    
                // Set the sessions
                $_SESSION['adminUsername'] = $this->username;
                
                if(isset($auth['password']) && password_verify($this->password, $auth['password'])) {
                    $_SESSION['adminPassword'] = $auth['password'];
                    
                    // If the user is a moderator, authenticate him as a user too
                    // if(isset($auth['user_group']) && $auth['user_group'] == 1) {
                    //     $log = new User();
                    //     $log->db = $this->db;
                    //     $log->username = $_SESSION['adminUsername'];
                    //     $log->password = $this->password;
                    //     $x = $log->auth(1);
    
                    //     if(!is_array($x)) {
                    //         return false;
                    //     }
                    // }
                    $logged = true;
                    session_regenerate_id();
                }
            }
    
            if(isset($logged)) {
                $_SESSION['is_admin'] = true;
                return $auth;
            }
    
            return false;
        }
    
        /**
         * @param   string  $password
         */
        public  function setPassword($password) {
            $_SESSION['adminPassword'] = password_hash($password, PASSWORD_DEFAULT);
        }
    
       public function logOut() {
            unset($_SESSION['adminUsername']);
            unset($_SESSION['adminPassword']);
            unset($_SESSION['is_admin']);
            unset($_SESSION['token_id']);
        }
       public function process(){
            global $LNG;
            $auth = $this->auth();
            if($auth){
                $status = true;
                $message = 'login_sucessfull';
            }else{
                $status = false;
                $message =  notificationBox('error', $LNG['invalid_user_pw']);
            }
            return ['status'=>$status,'message' => $message,'auth' => $auth,];
        }
    }
    class manageUsers {
        public $db;			// Database Property
        public $url;		// Installation URL Property
        public $per_page;	// Limit per page
    
        function getmmen($start, $type = null,$per_page) {
            global $CONF, $LNG;
        
            $startClause = '';
            if ($start != 0) {
                $startClause = 'AND `id` < \'' . $this->db->real_escape_string($start) . '\'';
            }
        
            $extra = '';
            if ($type == 1) {
                $extra = 'AND `acc_status` = 0';
            } elseif ($type == 2) {
                $extra = 'AND `acc_status` = 1';
            } elseif ($type == 3) {
                $extra = 'AND `acc_status` = 2';
            } elseif ($type == 4) {
                $extra = 'AND `acc_status` = 3';
            } elseif ($type == 5) {
                 $today = date('Y-m-d H:i:s');
                 $extra = "AND (subs.`subs_time` < '$today' OR subs.`subs_time` IS NULL)";
            } elseif ($type == 6) {
                $extra = 'AND `acc_status` = 5';
            }else{
                $extra = '';
            }
        
            // Query the database and get the users
            // $query = sprintf("SELECT * FROM `mmen` WHERE 1 %s %s ORDER BY `id` DESC LIMIT %s", $startClause, $extra, $this->db->real_escape_string($per_page + 1));
            // $query = sprintf("SELECT m.*, s.state_name, d.district_name
            //     FROM mmen AS m
            //     JOIN states AS s ON m.state = s.sid
            //     JOIN districts AS d ON m.district = d.d_id
            //     WHERE 1 %s %s
            //     ORDER BY m.id DESC
            //     LIMIT %s",
            //     $startClause,
            //     $extra,
            //     $this->db->real_escape_string($per_page + 1)
            //  );
            $query = sprintf("SELECT m.*, s.state_name, d.district_name, subs.subs_time,subs.subs_type
            FROM mmen AS m
            JOIN states AS s ON m.state = s.sid
            JOIN districts AS d ON m.district = d.d_id
            LEFT JOIN subscription AS subs ON m.id = subs.mmen_id
            WHERE 1 %s %s
            ORDER BY m.id DESC
            LIMIT %s",
            $startClause,
            $extra,
            $this->db->real_escape_string($per_page + 1)
          );
            $result = $this->db->query($query);
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $users = $loadmore = '';
            if(!empty($rows)){

            if (array_key_exists($per_page, $rows)) {
                $loadmore = 1;
        
                // Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
                array_pop($rows);
            }
            foreach ($rows as $row) {

                switch ($row['acc_status']) {
                    case 0:
                        $acc_status = '<span class="verified-small"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/status_pending.svg" title="' . $LNG['status_pending'] . '"></span>';
                        break;
                    case 1:
                      $acc_status = '<span class="verified-small"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/status_active.svg" title="' . $LNG['status_active'] . '"></span>';
                      break;
                    case 2:
                        $acc_status = '<span class="verified-small"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/status_reject.svg" title="' . $LNG['status_reject'] . '"></span>';
                      break;
                    case 3:
                        $acc_status = '<span class="verified-small"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/status_re_submit.svg" title="' . $LNG['status_re_submit'] . '"></span>';
                      break;
                    case 4:
                        $acc_status = '<span class="verified-small"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/status_expired.svg" title="' . $LNG['status_subs_expired'] . '"></span>';
                      break;
                    case 5:
                        $acc_status = '<span class="verified-small"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/status_temp_blocked.svg" title="' . $LNG['status_temp_blocked'] . '"></span>';
                      break;
                    default:
                      // code for other acc_status values
                      break;
                  }
                
                  if($row['subs_time'] < date('Y-m-d H:i:s') && $row['subs_type'] == 0){
                    $subs_expired = '<span class="verified-small"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/un-paid.png" title="' . $LNG['status_subs_expired'] . '" style="width: 60px;"></span>';
                  }elseif($row['subs_time'] > date('Y-m-d H:i:s') && $row['subs_type'] == 1){
                    $subs_expired = '<span class="user-status"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/paid.png" title="' . $LNG['status_subs_paid'] . '"></span>';
                  }elseif($row['subs_time'] > date('Y-m-d H:i:s') && $row['subs_type'] == 2){
                    $subs_expired = '<span class="user-status"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/trial.png" title="' . $LNG['status_subs_paid'] . '"></span>';
                  }elseif($row['subs_type']==3){
                    $subs_expired = '<span class="user-status"><img src="' . $this->url . '/' . $CONF['theme_url'] . '/images/icons/events/free.png" title="' . $LNG['status_subs_free'] . '"></span>';
                  }

                $users .= '<div class="users-container">
                    <div class="message-content">
                        <div class="message-inner bg-grey">
                            <div class="users-button button-normal"><a href="' . $this->url . '/index.php?a=global_admin&b=users&e=' . $row['id'] . '" rel="loadpage">' . $LNG['view'] . '</a></div>
                            <div class="message-avatar" id="avatar' . $row['id'] . '">
                                <a href="' . permalink($this->url . '/index.php?a=profile&u=' . $row['uname']) . '" rel="loadpage">
                                    <img src="' . $this->url . '/image.php?src=' . $row['profile_pic'] . '&t=mm&w=50&h=50&">
                                </a>
                            </div>
                            <div class="message-top">
                                <div class="message-author" id="author13" rel="loadpage">
                                    <a href="' . permalink($this->url . '/index.php?a=profile&u=' . $row['uname']) . '" rel="loadpage">' . $row['uname'] . $acc_status .$subs_expired . '</a>
                                </div>
                                <div class="message-time">
                                    ' . $row['p_number'] . '
                                </div>
                                <div class="message-time" style="color:#000000">
                                [ '. $row['state_name'] . ', ' .$row['district_name']. ' (' .$row['locality'].') ]
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
        
                $last = $row['id'];
            }
            if ($loadmore) {

                 $users .= '<div class="load_more" id="more_users"><a onclick="load_mmen(' . ($last) . ',' . saniscape($type) .','.$per_page.')" id="load-more">' . $LNG['load_more'] . '</a></div>';
             }

            }else{
                $users .= '<div class="error"><b>No record found</b></div>';
             }
                // Return the user list
                return $users;
    }
        
    
        function getUser($id, $username = null) {
            if($username) {
                // $query = sprintf("SELECT m.id, m.uname, m.fname, m.lname, m.p_number, m.dob, m.gender, m.profile_pic, m.dairy_name, m.pincode, m.acc_status, m.joined, s.state_name, d.district_name, sd.sub_district_name, m.state, m.district, m.sub_district, m.area, m.locality, m.distribute_type
                // FROM `mmen` m
                // JOIN `states` s ON m.state = s.sid
                // JOIN `districts` d ON m.district = d.d_id
                // JOIN `sub_districts` sd ON m.sub_district = sd.sd_id
                // WHERE m.uname = '%s' OR m.p_number = '%s' ",
                // $this->db->real_escape_string($username),
                // $this->db->real_escape_string($username)
                // );
                $query = sprintf("SELECT m.id, m.uname, m.fname, m.lname, m.p_number, m.dob, m.gender, m.profile_pic, m.dairy_name, m.pincode, m.acc_status, m.joined, s.state_name, d.district_name, sd.sub_district_name, m.state, m.district, m.sub_district, m.area, m.locality, m.distribute_type, subs.subs_time, subs.subs_type
                FROM `mmen` m
                JOIN `states` s ON m.state = s.sid
                JOIN `districts` d ON m.district = d.d_id
                JOIN `sub_districts` sd ON m.sub_district = sd.sd_id
                LEFT JOIN `subscription` subs ON m.id = subs.mmen_id
                WHERE m.uname = '%s' OR m.p_number = '%s'",
                $this->db->real_escape_string($username),
                $this->db->real_escape_string($username)
                );

            }else{
                // $query = sprintf("SELECT m.id, m.uname, m.fname, m.lname, m.p_number, m.dob, m.gender, m.profile_pic, m.dairy_name, m.pincode, m.acc_status, m.joined, s.state_name, d.district_name, sd.sub_district_name, m.state, m.district, m.sub_district, m.area, m.locality, m.distribute_type
                // FROM `mmen` m
                // JOIN `states` s ON m.state = s.sid
                // JOIN `districts` d ON m.district = d.d_id
                // JOIN `sub_districts` sd ON m.sub_district = sd.sd_id
                // WHERE m.id= '%s'", $this->db->real_escape_string($id));
                $query = sprintf("SELECT m.id, m.uname, m.fname, m.lname, m.p_number, m.dob, m.gender, m.profile_pic, m.dairy_name, m.pincode, m.acc_status, m.joined, s.state_name, d.district_name, sd.sub_district_name, m.state, m.district, m.sub_district, m.area, m.locality, m.distribute_type, subs.subs_time, subs.subs_type
                FROM `mmen` m
                JOIN `states` s ON m.state = s.sid
                JOIN `districts` d ON m.district = d.d_id
                JOIN `sub_districts` sd ON m.sub_district = sd.sd_id
                LEFT JOIN `subscription` subs ON m.id = subs.mmen_id
                WHERE m.id = '%s'",
                $this->db->real_escape_string($id)
                );

            }
            $result = $this->db->query($query);
    
            // If the user exists
            if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row;
            } else {
                return false;
            }
        }
    
       
    }
    
    function admin_stats($db, $type, $values = null, $category = null, $extra = null) {
        $days = array();
        $days[0] = date('Y-m-d', strtotime('+1 days'));
        $days[1] = date('Y-m-d');
        $days[2] = date('Y-m-d', strtotime('-1 days'));
        $days[3] = date('Y-m-d', strtotime('-2 days'));
        $days[4] = date('Y-m-d', strtotime('-3 days'));
        $days[5] = date('Y-m-d', strtotime('-4 days'));
        $days[6] = date('Y-m-d', strtotime('-5 days'));
        $days[7] = date('Y-m-d', strtotime('-6 days'));
        $hours = ' 00:00:00';
        if($type == 2) {
            $query = "SELECT
            (SELECT count(id) FROM `mmen`) as total_mmen,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = 0) as pending_accounts,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = 1) as approved_accounts,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = 2) as reject_accounts,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = 3) as re_submit_accounts,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = 4) as subs_expired_accounts,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = 5) as temp_block_accounts,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = 6) as kyc_require_accounts,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = 7) as illegal_activity_accounts";
        } elseif($type == 1) {
            // I want to check only active mmen so 
            $query = sprintf("SELECT
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = '1' AND   Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as mmen_today,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = '1' AND   Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as mmen_yesterday,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = '1' AND   Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as mmen_two_days,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = '1' AND   Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as mmen_three_days,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = '1' AND   Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as mmen_four_days,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = '1' AND   Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as mmen_five_days,
            (SELECT count(id) FROM `mmen` WHERE `acc_status` = '1' AND   Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as mmen_six_days,

            (SELECT COUNT(c_id) FROM `m_customers` WHERE Date(`joined`) >= '%s' AND Date(`joined`) < '%s') AS m_customers_today,
            (SELECT COUNT(c_id) FROM `m_customers` WHERE Date(`joined`) >= '%s' AND Date(`joined`) < '%s') AS m_customers_yesterday,
            (SELECT COUNT(c_id) FROM `m_customers` WHERE Date(`joined`) >= '%s' AND Date(`joined`) < '%s') AS m_customers_two_days,
            (SELECT COUNT(c_id) FROM `m_customers` WHERE Date(`joined`) >= '%s' AND Date(`joined`) < '%s') AS m_customers_three_days,
            (SELECT COUNT(c_id) FROM `m_customers` WHERE Date(`joined`) >= '%s' AND Date(`joined`) < '%s') AS m_customers_four_days,
            (SELECT COUNT(c_id) FROM `m_customers` WHERE Date(`joined`) >= '%s' AND Date(`joined`) < '%s') AS m_customers_five_days,
            (SELECT COUNT(c_id) FROM `m_customers` WHERE Date(`joined`) >= '%s' AND Date(`joined`) < '%s') AS m_customers_six_days,

            (SELECT COUNT(id) FROM `buying_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_milk_today,
            (SELECT COUNT(id) FROM `buying_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_milk_yesterday,
            (SELECT COUNT(id) FROM `buying_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_milk_two_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_milk_three_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_milk_four_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_milk_five_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_milk_six_days,

            (SELECT COUNT(id) FROM `selling_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_milk_today,
            (SELECT COUNT(id) FROM `selling_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_milk_yesterday,
            (SELECT COUNT(id) FROM `selling_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_milk_two_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_milk_three_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_milk_four_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_milk_five_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_milk_six_days,

            (SELECT COUNT(uid) FROM `update_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS update_milk_today,
            (SELECT COUNT(uid) FROM `update_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS update_milk_yesterday,
            (SELECT COUNT(uid) FROM `update_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS update_milk_two_days,
            (SELECT COUNT(uid) FROM `update_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS update_milk_three_days,
            (SELECT COUNT(uid) FROM `update_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS update_milk_four_days,
            (SELECT COUNT(uid) FROM `update_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS update_milk_five_days,
            (SELECT COUNT(uid) FROM `update_milk` WHERE Date(`date`) >= '%s' AND Date(`date`) < '%s') AS update_milk_six_days,

            (SELECT count(c_id) FROM `cleared` WHERE Date(`cleared_date`) >= '%s' AND Date(`cleared_date`) < '%s') as total_milk_today,
            (SELECT count(c_id) FROM `cleared` WHERE Date(`cleared_date`) >= '%s' AND Date(`cleared_date`) < '%s') as total_milk_yesterday,
            (SELECT count(c_id) FROM `cleared` WHERE Date(`cleared_date`) >= '%s' AND Date(`cleared_date`) < '%s') as total_milk_two_days,
            (SELECT count(c_id) FROM `cleared` WHERE Date(`cleared_date`) >= '%s' AND Date(`cleared_date`) < '%s') as total_milk_three_days,
            (SELECT count(c_id) FROM `cleared` WHERE Date(`cleared_date`) >= '%s' AND Date(`cleared_date`) < '%s') as total_milk_four_days,
            (SELECT count(c_id) FROM `cleared` WHERE Date(`cleared_date`) >= '%s' AND Date(`cleared_date`) < '%s') as total_milk_five_days,
            (SELECT count(c_id) FROM `cleared` WHERE Date(`cleared_date`) >= '%s' AND Date(`cleared_date`) < '%s') as total_milk_six_days,
            
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_mf_today,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_mf_yesterday,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_mf_two_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_mf_three_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_mf_four_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_mf_five_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_mf_six_days,
              
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_dm_today,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_dm_yesterday,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_dm_two_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_dm_three_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_dm_four_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_dm_five_days,
            (SELECT COUNT(id) FROM `buying_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS bought_dm_six_days,

            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_mf_today,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_mf_yesterday,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_mf_two_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_mf_three_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_mf_four_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_mf_five_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_mf_six_days,

            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_dm_today,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_dm_yesterday,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_dm_two_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_dm_three_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_dm_four_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_dm_five_days,
            (SELECT COUNT(id) FROM `selling_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') AS sold_dm_six_days",
            
            

            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            $days[1].$hours, $days[0].$hours, $days[2].$hours, $days[2].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
            );
            
        } else {
            $queries = '';
            if($extra == 2) {
                $start	= date('Y-m-d', strtotime($_GET['year'].'-'.$_GET['month'].'-'.$_GET['day'])).$hours;
                $end	= date('Y-m-d', strtotime($_GET['year'].'-'.$_GET['month'].'-'.$_GET['day'].' +1days')).$hours;
    
                $queries .= sprintf("(SELECT count(`id`)    FROM `mmen`        WHERE `acc_status` = '1' AND Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as mmen,", $start, $end);
                $queries .= sprintf("(SELECT count(`c_id`)  FROM `m_customers` WHERE  Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as m_customers,", $start, $end);
                $queries .= sprintf("(SELECT COUNT(`id`)    FROM `buying_milk` WHERE  Date(`date`) >= '%s' AND Date(`date`) < '%s') as bought_milk,", $start, $end);
                $queries .= sprintf("(SELECT count(`id`)    FROM `selling_milk` WHERE  Date(`date`) >= '%s' AND Date(`date`) < '%s') as sold_milk,", $start, $end);
                $queries .= sprintf("(SELECT count(`id`)    FROM `buying_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') as bought_milk_mf,", $start, $end);
                $queries .= sprintf("(SELECT count(`id`)    FROM `buying_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') as bought_milk_dm,", $start, $end);
                $queries .= sprintf("(SELECT count(`id`)    FROM `selling_milk` WHERE `milk_type` = 'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') as bought_milk_mf,", $start, $end);
                $queries .= sprintf("(SELECT count(`id`)    FROM `selling_milk` WHERE `milk_type` = 'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') as bought_milk_dm,", $start, $end);
                $queries .= sprintf("(SELECT COUNT(`uid`)   FROM `update_milk` WHERE  Date(`date`) >= '%s' AND Date(`date`) < '%s') as update_milk,", $start, $end);
                $queries .= sprintf("(SELECT COUNT(`c_id`)  FROM `cleared`     WHERE  Date(`cleared_date`) >= '%s' AND Date(`cleared_date`) < '%s') as cleared,", $start, $end);
            
            }
            
            foreach($values as $value) {
                if($extra == 3) {
                    $start	= date('Y-m-d', strtotime($value.'-01-01')).$hours;
                    $end	= date('Y-m-d', strtotime($value.'-01-01 +1years')).$hours;
                } elseif($extra == 1) {
                    $start	= date('Y-m-d', strtotime($_GET['year'].'-'.$value.'-01')).$hours;
                    $end	= date('Y-m-d', strtotime($_GET['year'].'-'.$value.'-'.cal_days_in_month(CAL_GREGORIAN, $value, $_GET['year']).' +1days')).$hours;
                } else {
                    $start	= date('Y-m-d', strtotime($_GET['year'].'-'.$_GET['month'].'-'.$value)).$hours;
                    $end	= date('Y-m-d', strtotime($_GET['year'].'-'.$_GET['month'].'-'.$value.' +1days')).$hours;
                }
    
                if($category == 'mmen') {
                    $queries .= sprintf("(SELECT count(`id`)   FROM `mmen`       WHERE `acc_status` =    '1'  AND Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as value_%s,", $start, $end, $value);
                } elseif($category == 'm_customers') {
                    $queries .= sprintf("(SELECT count(`c_id`) FROM `m_customers` WHERE Date(`joined`) >= '%s' AND Date(`joined`) < '%s') as value_%s,", $start, $end, $value);
                } elseif($category == 'bought_milk') {
                    $queries .= sprintf("(SELECT COUNT(`id`)   FROM `buying_milk` WHERE Date(`date`) >=   '%s' AND Date(`date`) < '%s') as value_%s,", $start, $end, $value);
                } elseif($category == 'sold_milk') {
                    $queries .= sprintf("(SELECT count(`id`)   FROM `selling_milk` WHERE Date(`date`) >=   '%s' AND Date(`date`) < '%s') as value_%s,", $start, $end, $value);
                } elseif($category == 'bought_milk_mf') {
                    $queries .= sprintf("(SELECT count(`id`)   FROM `buying_milk` WHERE `milk_type` =     'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') as value_%s,", $start, $end, $value);
                }elseif($category == 'bought_milk_dm') {
                    $queries .= sprintf("(SELECT count(`id`)   FROM `buying_milk` WHERE `milk_type` =     'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') as value_%s,", $start, $end, $value);
                }elseif($category == 'sold_milk_mf') {
                    $queries .= sprintf("(SELECT count(`id`)   FROM `selling_milk` WHERE `milk_type` =     'mf' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') as value_%s,", $start, $end, $value);
                }elseif($category == 'sold_milk_dm') {
                    $queries .= sprintf("(SELECT count(`id`)   FROM `selling_milk` WHERE `milk_type` =     'dm' AND Date(`date`) >= '%s' AND Date(`date`) < '%s') as value_%s,", $start, $end, $value);
                } elseif($category == 'update_milk') {
                    $queries .= sprintf("(SELECT COUNT(`uid`)  FROM `update_milk` WHERE Date(`date`) >=   '%s' AND Date(`date`) < '%s') as value_%s,", $start, $end, $value);
                } elseif($category == 'cleared') {
                    $queries .= sprintf("(SELECT COUNT(`c_id`) FROM `cleared` WHERE Date(`cleared_date`) >= '%s' AND Date(`cleared_date`) < '%s') as value_%s,", $start, $end, $value);
                }
            }
           
            $query = substr("SELECT ".$queries, 0, -1);
        }
        $result = $db->query($query);
        while($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stats = array();
        foreach($rows[0] as $value) {
            $stats[] = $value;
        }
        return $stats;
    }
    
    function percentage($current, $old) {
        // Prevent dividing by zero
        if($old != 0) {
            $result = number_format((($current - $old) / $old * 100), 0);
        } else {
            $result = 0;
        }
        if($result < 0) {
            return '<span class="negative">'.$result.'%</span>';
        } elseif($result > 0) {
            return '<span class="positive">+'.$result.'%</span>';
        } else {
            return '<span class="neutral">'.$result.'%</span>';
        }
    }
    function parseCallback($matches) {
        // If match www. at the beginning of the string, add http before
        if(substr($matches[1], 0, 4) == 'www.') {
            $url = 'http://'.$matches[1];
        } else {
            $url = $matches[1];
        }
        return '<a href="'.$url.'" target="_blank" rel="nofollow">'.$matches[1].'</a>';
    }
    function generateTimezoneForm($current) {
        global $LNG;
        $rows = '<option value="" '.($current == '' ? ' selected' : '').'>'.$LNG['default'].'</option>';
        foreach(timezone_identifiers_list() as $value) {
            $rows .= '<option value="'.htmlspecialchars($value).'" '.($current == $value ? ' selected' : '').'>'.$value.'</option>';
        }
        
        return $rows;
    }
    function generateDateForm($type, $current) {
        global $LNG;
        $rows = '';
        if($type == 0) {
            $rows .= '<option value="">'.$LNG['year'].'</option>';
            for($i = date('Y'); $i >= (date('Y') - 100); $i--) {
                if($i == $current) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }
                $rows .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
            }
        } elseif($type == 1) {
            $rows .= '<option value="">'.$LNG['month'].'</option>';
            for($i = 1; $i <= 12; $i++) {
                if($i == $current) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }
                $rows .= '<option value="'.$i.'"'.$selected.'>'.$LNG["month_$i"].'</option>';
            }
        } elseif($type == 2) {
            $rows .= '<option value="">'.$LNG['day'].'</option>';
            for($i = 1; $i <= 31; $i++) {
                if($i == $current) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }
                $rows .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
            }
        }
        return $rows;
    }
    function generateDate_reg($type) {
        global $LNG;
        $rows = '';
        if($type == 0) {
            $rows .= '<option value="">'.$LNG['year'].'</option>';
            for($i = date('Y'); $i >= (date('Y') - 100); $i--) {
                // if($i == $current) {
                //     $selected = ' selected="selected"';
                // } else {
                //     $selected = '';
                // }
                $rows .= '<option value="'.$i.'"'.$i.'>'.$i.'</option>';
            }
        } elseif($type == 1) {
            $rows .= '<option value="">'.$LNG['month'].'</option>';
            for($i = 1; $i <= 12; $i++) {
                // if($i == $current) {
                //     $selected = ' selected="selected"';
                // } else {
                //     $selected = '';
                // }
                $rows .= '<option value="'.$i.'"'.$i.'>'.$LNG["month_$i"].'</option>';
            }
        } elseif($type == 2) {
            $rows .= '<option value="">'.$LNG['day'].'</option>';
            for($i = 1; $i <= 31; $i++) {
                // if($i == $current) {
                //     $selected = ' selected="selected"';
                // } else {
                //     $selected = '';
                // }
                $rows .= '<option value="'.$i.'"'.$i.'>'.$i.'</option>';
            }
        }
        return $rows;
    }
    function generateStatsForm($type, $current, $min = null) {
        global $LNG;
        $rows = '';
        if($type == 0) {
            if(empty($min)) {
                $min = date('Y');
            }
            $rows .= '<option value="">'.$LNG['year'].'</option>';
            for($i = date('Y'); $i >= $min; $i--) {
                if($i == $current) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }
                $rows .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
            }
        } elseif($type == 1) {
            $rows .= '<option value="">'.$LNG['month'].'</option>';
            for($i = 1; $i <= 12; $i++) {
                if($i == $current) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }
                $rows .= '<option value="'.$i.'"'.$selected.'>'.$LNG["month_$i"].'</option>';
            }
        } elseif($type == 2) {
            $rows .= '<option value="">'.$LNG['day'].'</option>';
            for($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['year']); $i++) {
                if($i == $current) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }
                $rows .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
            }
        }
        return $rows;
    }
    function saniscape($value) {
        return htmlspecialchars(addslashes($value), ENT_QUOTES, 'UTF-8');
    }
       function generateOTP() {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        if(isset($_SESSION['otp'])){
            return $otp;
            }
            
        }

      function verifyOTP($otp) {
        if (isset($_SESSION['otp']) && $_SESSION['otp'] == $otp) {
            unset($_SESSION['otp']);
            return true;
        } else {
            return false;
        }
    }

       function message_id($type){
        if($type == 'customer_otp'){
            $sms_id = 155157;
        }
        if($type == 'customer_simple_otp'){
            $sms_id = 155158;
        }
        if($type == 'c_acc_created'){
            $sms_id = 156287;
        }
        if($type == 'milk_cleared'){
            $sms_id = 156286;
        }
        if($type == 'update_milk'){
            $sms_id = 156288;
        }
        if($type == 'verification_code'){
            $sms_id = 155593;
        }
        
        return $sms_id;
     }
     function load_state($current=null) {
        global $db;
        $query = 'SELECT * FROM `states`';
        $result = $db->query($query);
        $rows = '';

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                if($row['sid'] == $current) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }
                $rows .= '<option value="'.$row['sid'].'"'.$selected.'>'.$row['state_name'].'</option>';
            }
        }
        return $rows;
        mysqli_close($db);
    }
    
     
     // message template

     // find date
     function find_date($date_val){
        switch($date_val){
            case '0':
                $date = [date("Y-m-d")];
                break;
            case '1':
                $date = [date("Y-m-d", strtotime("-1 day"))];
                break;
            case '7':
                $date = [date("Y-m-d", strtotime("-6 days")), date("Y-m-d")];
                break;
            case '10':
                $date = [date("Y-m-d", strtotime("-9 days")), date("Y-m-d")];
                break;
            case '30':
                $date = [date("Y-m-d", strtotime("-29 days")), date("Y-m-d")];
                break;
            case 'this_week':
                $monday = strtotime("last Monday");
                $thisWeekStart = date("Y-m-d", $monday);
                $thisWeekEnd = date("Y-m-d");
                $date = [$thisWeekStart, $thisWeekEnd];
                break;
            case 'this_month':
                $thisMonthStart = date("Y-m-01");
                $thisMonthEnd = date("Y-m-d");
                $date = [$thisMonthStart, $thisMonthEnd];
                break;
            case 'last_month':
                $lastMonthStart = date("Y-m-01", strtotime("first day of previous month"));
                $lastMonthEnd = date("Y-m-t", strtotime("last day of previous month"));
                $date = [$lastMonthStart, $lastMonthEnd];
                break;
            default:
                $date = [date("Y-m-d")];
                break;
        }
        return $date;
    }
      // Format date
      function format_dates($dates) {
        $formatted_dates = array();
        
        foreach ($dates as $date) {
            $timestamp = strtotime($date);
            
            if ($timestamp === false) {
                $formatted_dates[] = 'Invalid date format: ' . $date;
            } else {
                if (strpos($date, ':') === false) {
                    $formatted_dates[] = date('d-m-Y', $timestamp);
                } else {
                    $formatted_dates[] = date('d-m-Y h:i A', $timestamp);
                }
            }
        }
        
        return $formatted_dates;
    }
    // return formated date if pass in offset like (+2 days, -2days)
    function str_datetime($currentDateTime, $offset){
                // Convert the current date and time to a DateTime object
                $currentDateTimeObj = new DateTime($currentDateTime);

                // Add or subtract the offset to the current date and time
                $currentDateTimeObj->modify($offset);

                // Format the resulting date and time to match the MySQL format
                $formattedDateTime = $currentDateTimeObj->format('Y-m-d H:i:s');

                // Return the formatted date and time
                return $formattedDateTime;
            }
    // format Phone Number

    function formatPhoneNumber($number) {
        $formattedNumber = substr($number, 0, 1) . str_repeat("*", 6) . substr($number, -3);
        return $formattedNumber;
      }
      // formating name
      function formatFullName($fname, $lname = null) {
        // Trim the first name
        $fname = trim($fname);
    
        // Check if last name is provided
        if ($lname !== null) {
            // Concatenate the first and last name with a space
            $name = $fname . ' ' . trim($lname);
        } else {
            // Last name is not provided, use only first name
            $name = $fname;
        }
    
        // Check if the name is longer than 15 characters
        if (strlen($name) > 15) {
            // Truncate the name to 15 characters and add ellipsis at the end
            $name = substr($name, 0, 15) . '...';
        }
    
        // Return the formatted name
        return $name;
    }
      function formatlocality($name) {
        // Check if the name is longer than 15 characters
        if (strlen($name) > 20) {
            // Truncate the name to 15 characters and add ellipsis at the end
            $name = substr($name, 0, 20) . '...';
        }
        // Return the formatted name
        return $name;
    }
    
      // get past date
      function getPastDate($numDays) {
        $pastDate = date('Y-m-d', strtotime('-'.$numDays.' days'));
        return $pastDate;
      }
            function convertDates($dates) {
            $convertedDates = array();
          
            foreach ($dates as $date) {
              $parts = explode('-', $date);
              if (count($parts) == 3) {
                if (strlen($parts[0]) == 2 && strlen($parts[1]) == 2 && strlen($parts[2]) == 4) {
                  $convertedDates[] = "$parts[2]-$parts[1]-$parts[0]";
                } else if (strlen($parts[0]) == 4 && strlen($parts[1]) == 2 && strlen($parts[2]) == 2) {
                  $convertedDates[] = $date;
                } else {
                  $convertedDates[] = null; // invalid date format
                }
              } else {
                $convertedDates[] = null; // invalid date format
              }
            }
          
            return $convertedDates;
          }
       function check_weight($value,$minimum,$limit) {
        // checking Weight is correct or not
        if (!is_float($value) && !is_numeric($value)) {
          return 1;
        }  elseif (is_float($value) && !preg_match('/^\d+(\.\d{1,3})?$/', $value)) {
          return 2;
        } elseif ($value < $minimum) {
          return 3;
        } elseif ($value > $limit) {
          return 4;
        } else {
          return 0;
        }
      }
      
      function check_dm($value,$minimum,$limit) {
        // checking Direct Milk Rate
        if (!is_numeric($value)) {
            return 1;
          } elseif ($value < $minimum) {
            return 2;
          } elseif ($value > $limit) {
            return 3;
          } else {
            return 0;
          }
          }
        function check_mf($value,$minimum,$limit) {
            // checking Milk Fat is Correct or not
        if (!is_float($value) && !is_numeric($value)) {
            return 1;
        } elseif (is_float($value) && !preg_match('/^\d+(\.\d)?$/', $value)) {
            return 2;
        } elseif ($value < $minimum) {
            return 3;
        } elseif ($value > $limit) {
            return 4;
        } else {
            return 0;
        }
        }
        function check_mfr($value,$minimum,$limit) {
            // checking Milk Fat Rate is Correct or not

        if (!is_float($value) && !is_numeric($value)) {
            return 1;
        } elseif (is_float($value) && !preg_match('/^\d+(\.\d)?$/', $value)) {
            return 2;
        } elseif ($value < $minimum) {
            return 3;
        } elseif ($value > $limit) {
            return 4;
        } else {
            return 0;
        }
        }
        function get_milk_rate($db,$user_id,$type,$animal){
            if($type==1){
                $query = sprintf("SELECT `b_dm`, `b_mfr` FROM `buying_milk_stg` WHERE `mm_id` = %s AND `m_animal` = %s",$user_id,$animal);
            }elseif($type==2){
                $query = sprintf("SELECT `s_dm`, `s_mfr` FROM `selling_milk_stg` WHERE `mm_id` = %s AND `m_animal` = %s",$user_id,$animal);
            }
           
            $run = $db->query($query);
            if($run && $run->num_rows > 0){
                $result = $run->fetch_assoc();
            }
            return ['dm' => ($result['b_dm']) ? ($result['b_dm']) : ($result['s_dm']) , 'mfr' => ($result['b_mfr']) ? ($result['b_mfr']) : $result['s_mfr']];
            
        }


        function base64Image($string, $name) {
            $explode = explode(',', $string, 2);

            $image = imagecreatefromstring(base64_decode($explode[0]));
            
           
            // header('Content-Type: image/png');
            if(!$image) {
                
                return false;
            }else{
               
                // Store the image info
            $path = __DIR__ .'/../uploads/media/'.$name;
            imagepng($image, $path);

            $info = getimagesize($path);
            $filesize = filesize($path);
            // Delete the temporary image
            
            unlink($path);
        
            if($info[0] > 0 && $info[1] > 0 && $info['mime'] == 'image/png') {
                // Return the image data
                return array('size' => $filesize, 'data' => base64_decode($explode[0]));
            }
            }
        
            
            return false;
        }
            function getadmindetails($uname){
                global $db;
                $query = sprintf('SELECT * FROM `global_admin` WHERE uname = "%s"',$uname);
                $runQuery = $db->query($query);
                $raw = $runQuery->fetch_assoc();
                if($raw['login_token'] == $_SESSION['token_id']){
                    return $raw; 
                }

                }

                function info_urls() {
                    global $CONF, $db;
                
                    $pages = $db->query("SELECT `url`, `title` FROM `info_pages` WHERE `public` = 1 ORDER BY `id` ASC");
                
                    $output = '';
                    while($row = $pages->fetch_assoc()) {
                        
                        $output .= '<span><a href="'.permalink($CONF['url'].'/index.php?a=info&b='.$row['url']).'" rel="loadpage">'.skin::parse($row['title']).'</a></span>';
                    }
                
                    return $output;
                }

            function isAjax() {
                /*
                 * Check if the request is dynamic (ajax)
                 *
                 * @return bolean
                 */
            
                if(	isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
                    // || isset($_GET['live'])
                    ) {
                    return true;
                } else {
                    return false;
                }
            }
            function getLanguage($url, $ln = null, $type = null) {
                global $settings;
                // Type 1: Output the available languages
            
                // Define the languages folder
                    $lang_folder = __DIR__ .'/../languages/';

                // Open the languages folder
                if($handle = opendir($lang_folder)) {
                    // Read the files (this is the correct way of reading the folder)
                    while(false !== ($entry = readdir($handle))) {
                        // Excluse the . and .. paths and select only .php files
                        if($entry != '.' && $entry != '..' && substr($entry, -4, 4) == '.php') {
                            $name = pathinfo($entry);
                            $languages[] = $name['filename'];
                        }
                    }
                    closedir($handle);
                }
                // Sort the languages by name
                
                sort($languages);
                if($type == 1) {
                    // Add to array the available languages
                    $available = '';
                    foreach($languages as $lang) {
                        // The path to be parsed
                        $path = pathinfo($lang);
            
                        // Add the filename into $available array
                        $available .= '<span><a href="'.permalink($url.'/index.php?lang='.$path['filename']).'" rel="loadpage">'.ucfirst(mb_strtolower($path['filename'])).'</a></span>';
                    }
                    return $available;
                } else {
                    // If get is set, set the cookie and stuff
                    $lang = $settings['language']; // Default Language
                  if(isset($_GET['lang'])) {
                            if(in_array($_GET['lang'], $languages)) {
                                $lang = $_GET['lang'];
                                setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); // Expire in one month
                            } else {
                                setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); // Expire in one month
                            }
                        } elseif(isset($_COOKIE['lang'])) {
                            if(in_array($_COOKIE['lang'], $languages)) {
                                $lang = $_COOKIE['lang'];
                            }
                        } else {
                            setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); // Expire in one month
                        }
            
                        // If the language file doens't exist, fall back to an existent language file
                        if(!file_exists($lang_folder.$lang.'.php')) {
                            $lang = $languages[0];
                        }
                    
            
                        // If the language file doens't exist, fall back to an existent language file
                        if(!file_exists($lang_folder.$lang.'.php')) {
                            $lang = $languages[0];
                        }
                    }
            
                    return $lang_folder.$lang.'.php';
                }
            

            function permalink($url){
                   global $settings;

                    if($settings['permalink']){
                    $path['login'] = 'index.php?a=login';
                    $path['register'] = 'index.php?a=register';
                    $path['home'] = 'index.php?a=home';
                    $path['add_user'] = 'index.php?a=add_user';
                    $path['manage_user'] = 'index.php?a=manage_user';
                    $path['calculate'] = 'index.php?a=calculate';
                    $path['list'] = 'index.php?a=list';

                    $path['settings'] = 'index.php?a=settings';
                    $path['info'] = 'index.php?a=info';
                    $path['lng'] = 'index.php?lang=';
                    $path['image'] = 'index.php?a=image';
                    
                    if (strpos($url, $path['login'])) {
                        $url = str_replace(array($path['login'], '&fp=', '$user'), array('login', '/', '/'), $url);
                    }elseif (strpos($url, $path['register'])) {
                        $url = str_replace(array($path['register'], '&b='), array('register', '/'), $url);
                    }elseif(strpos($url,$path['settings'])){
                        $url = str_replace(array($path['settings'], '&b='), array('settings', '/'), $url);
                    }elseif(strpos($url,$path['info'])){
                        $url = str_replace(array($path['info'], '&b='), array('info', '/'), $url);
                    } elseif (strpos($url, $path['lng'])) {
                        // Replace /lng/xyz with /lang=xyz
                        $url = str_replace(array($path['lng'], '?lang='), array('lng/'), $url);
                    } elseif(strpos($url,$path['home'])){
                        $url = str_replace(array($path['home'], '&b='), array('home', '/'), $url);
                    }elseif(strpos($url,$path['add_user'])){
                        $url = str_replace(array($path['add_user'], '&b='), array('add_user', '/'), $url);
                    }elseif(strpos($url,$path['manage_user'])){
                        $url = str_replace(array($path['manage_user'], '&b=','&c_id'), array('manage_user', '/','?c_id'), $url);
                    }elseif(strpos($url,$path['calculate'])){
                        $url = str_replace(array($path['calculate'], '&b='), array('calculate', '/'), $url);
                    }elseif(strpos($url,$path['list'])){
                        $url = str_replace(array($path['list'], '&b='), array('list', '/'), $url);
                    } elseif(strpos($url, $path['image'])) {
                        $url = str_replace(array($path['image'], '?t=', '&w=', '&h=', '&src='), array('image', '/', '/', '/', '/'), $url);
                    }
                }
                    return $url;
                
            }


            function GenrateToken()
            {
                $token_id = md5(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 10));
                return $token_id;
            }
            function GenerateToken_2($type = null) {
                if($type) {
                    return '<input type="hidden" name="token_id" value="'.$_SESSION['token_id'].'">';
                } else {
                    if(!isset($_SESSION['token_id'])) {
                        $token_id = md5(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10));
                        $_SESSION['token_id'] = $token_id;
                        return $_SESSION['token_id'];
                    }
                    return $_SESSION['token_id'];
                }
            }

?>