<?php

    session_start();
    
  if(!isset($_GET['query'])){
    die("{error: invalid request!}");
    }
    $query = $_GET['query'];
    $logged = ['animal','manage_customers','calculate','ad_setting','view-milk','add_customer'];

// $non_logged = ['reg_mmen'];
$admin = ['admin_login','admin_update_user','xxx'];
     require_once(__DIR__ . '/../include/autoload.php');
       global $user,$CONF, $db, $LNG;
       header('Content-Type: application/json; charset=utf-8');

// $run = 'select sub_district_name from sub_districts';
// $result = $db->query($run);
// $state_array = [];
// foreach($result as $state_name){
//   $state_array[] = $state_name['sub_district_name'];
// }
// echo json_encode($state_array);
// // echo '<pre>';
// // print_r($state_array);
// die;

if(in_array($query, $admin)){
    if(!isset($_SESSION['adminUsername'])){ die('{error: no admin access!}');}
}
if( in_array($query, $logged)){
    if(!isset($user['id'])){ die('{error: Only logged user access!}');}
}


// here code 

switch ($query) {
    case 'load_state':
        $load_cities = new load_cites();
        $load_cities->db = $db;
        $load_cities->state = $_POST['states'];
        $load_cities->district = $_POST['districts'];
        
        if(isset($_GET['b']) && $_GET['b'] == 'districts'){
          $districts =  $load_cities->process(1);
          echo json_encode($districts);
        }elseif(isset($_GET['b']) && $_GET['b'] == 'sub_districts'){
            $sub_districts =  $load_cities->process(2);
             echo json_encode($sub_districts);
        }
        $load_cities->db->close();
        break;

        case 'locality':
            $locality = new locality();
            $locality->db = $db;
            $locality->state = $_POST['states'];
            $locality->district = $_POST['districts'];
            $locality->sub_district = $_POST['sub_districts'];
            $locality->area = $_POST['area_id'];
            $locality->search_term = $_POST['locality'];
            // print_r( $_POST);
            // die;
            $suggestion = $locality->process();
            echo json_encode($suggestion);
            $locality->db->close();
            break;
    case 'animal':
        //xx
        $ad_stg = new update_ad_settings();
        $ad_stg->db = $db;
        $animal = $ad_stg->load_animal();
        $animal_name = $ad_stg->load_animal_by_name();
        $max_val = $ad_stg->max_val_check();

        if(isset($_GET['b']) && $_GET['b'] == 'max_val'){
            echo json_encode(['tm_max' => $max_val['tm_max_value'], 'gm_max' => $max_val['gm_max_value'] ]);
        }elseif(isset($_GET['b']) && $_GET['b'] == 'by_name'){

            echo json_encode(['animal' => $animal_name]);
        }else{
            echo json_encode(['animal' => $animal]);
        }
        $ad_stg->db->close();
       break;
        case 'add_customer':
            $add_c = new m_customer();
            $add_c->db = $db;
            $add_c->fname = $_POST['fname'];
            $add_c->lname = $_POST['lname'];
            $add_c->uname = $_POST['uname'];
            $add_c->p_number = $_POST['p_number'];

            $add_c->pincode = $_POST['pincode'];
            $add_c->locality= $_POST['locality'];
            $add_c->profile_pic = $_POST['store-snap'];
            $add_c->otp_val = $_POST['otp_val'];
            $add_c->captcha = $_POST['captcha'];


            if(isset($_GET['b']) && $_GET['b'] == 'uname'){
                $uname_date = $add_c->process_uname();
                echo json_encode($uname_date);
            }elseif(isset($_GET['b']) && $_GET['b'] == 'p_num'){
                $data = $add_c->process_p_number();
                echo json_encode($data);
            }elseif(isset($_GET['b']) && $_GET['b'] == 'otp_verification'){
                if(isset($add_c->p_number) && $add_c->uname){
                    $output = $add_c->otp_verification($add_c->p_number);
                    echo $output;
                    
                }else{
                  echo  json_encode(['status'=>'failed','message'=>'you do not have permission to access this page']);
                }
            }else{
                // print_r($_POST);
               echo json_encode($add_c->process());
            }
            $add_c->db->close();
        break;
        case 'reg_mmen':
            $reg_mmen = new reg_mmen();
            $reg_mmen->db = $db;
            $reg_mmen->fname = $_POST['fname'];
            $reg_mmen->lname = $_POST['lname'];
            $reg_mmen->gender = $_POST['gender'];
            $reg_mmen->date = $_POST['date'];
            $reg_mmen->month = $_POST['month'];
            $reg_mmen->year = $_POST['year'];
            $reg_mmen->uname = $_POST['uname'];
            $reg_mmen->p_number = $_POST['p_number'];

            $reg_mmen->password = $_POST['password'];

            $reg_mmen->state = $_POST['states'];
            $reg_mmen->district = $_POST['districts'];
            $reg_mmen->sub_district = $_POST['sub_districts'];
            $reg_mmen->area = $_POST['area'];
            $reg_mmen->pincode = $_POST['pincode'];
            $reg_mmen->dairy_name = $_POST['milk_dairy_name'];
            $reg_mmen->milk_distribute_type = $_POST['distribute_type'];
            $reg_mmen->locality= $_POST['locality'];
            $reg_mmen->profile_pic = $_POST['store-snap'];
            $reg_mmen->otp_val = $_POST['otp_val'];
            $reg_mmen->captcha = $_POST['captcha'];


            if(isset($_GET['b']) && $_GET['b'] == 'step1'){
                $data = $reg_mmen->process_step1();
                echo json_encode($data);
            }elseif(isset($_GET['b']) && $_GET['b'] == 'uname'){
                $uname_date = $reg_mmen->process_uname();
                echo json_encode($uname_date);
            }elseif(isset($_GET['b']) && $_GET['b'] == 'p_num'){
                $data = $reg_mmen->process_p_number();
                echo json_encode($data);
            }elseif(isset($_GET['b']) && $_GET['b'] == 'otp_verification'){
                if(isset($reg_mmen->p_number) && $reg_mmen->uname){
                    $output = $reg_mmen->otp_verification($reg_mmen->p_number);
                    echo $output;
                    
                }else{
                  echo  json_encode(['status'=>'failed','message'=>'you do not have permission to access this page']);
                }
            }else{
                
               echo json_encode($reg_mmen->process());
            }
            $reg_mmen->db->close();
            break;     
        case 'manage_customers':
            $customers = new manage_customers();
            $customers->db = $db;
            $customers->url = $CONF['url'];
            $customers->record_per_page = 10;
            $customers->search_val = $_POST['search_val'];

            if(isset($_GET['b']) && $_GET['b'] == 'search') {
                $search = $customers->process(1);
                echo json_encode($search);

            }elseif(isset($_GET['b']) && $_GET['b'] == 'c_search') {
                $c_search = $customers->process(4);
                echo json_encode($c_search);

            }elseif (isset($_GET['b']) && $_GET['b'] == 'about_customer'){

                $customers->customer_c_id = $_POST['c_id']; // set the c_id value here
                $data = $customers->process(2);
                echo json_encode($data);

            }elseif (isset($_GET['b']) && $_GET['b'] == 'count_customers') {
                
                $count_customers = $customers->process(3);
                echo json_encode($count_customers);
                
            }else{
                $all_customers = $customers->process();
                echo json_encode($all_customers);
            }
            $customers->db->close();
            break;
        
        case 'calculate':
            $calculate = new calculate();
            $calculate->db = $db;
            $calculate->user_id = $user['id'];
            $calculate->url = $CONF['url'];
            $calculate->search_val = $_POST['customer_search_terms'];
            $calculate->tgm = $_POST['take_give'];
            $calculate->m_animal = $_POST['animals'];
            $calculate->d_rate = $_POST['dm'];
            $calculate->dm_minimum = $settings['d_rate_minimum'];
            $calculate->dm_limit = $settings['d_rate_maximum'];
            $calculate->weight = $_POST['weight'];
            $calculate->w_limit = $settings['weight_maximum'];
            $calculate->w_minimum = $settings['weight_minimum'];
            $calculate->mf_rate = $_POST['mfr'];
            $calculate->mfr_limit = $settings['f_rate_maximum'];
            $calculate->mfr_minimum =  $settings['f_rate_minimum'];
            $calculate->mf = $_POST['fat'];
            $calculate->mf_limit =  $settings['mf_maximum'];
            $calculate->mf_minimum = $settings['mf_minimum'];
            $calculate->cleared = $_POST['cal_money_receive'];
            if($calculate->tgm == 1){
                $milk_type = $_POST['buying_milk'];
            }else{
                $milk_type = $_POST['selling_milk'];
            }
            $calculate->milk_type = $milk_type;

            if(isset($_GET['b']) && $_GET['b'] == 'submit'){
               
                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    
                    $result = $calculate->process(2);
                    echo json_encode($result);
                }

            }else{

            $result = $calculate->process(1);
            
            if(isset($result['table'])){
              echo json_encode(["table" => $result['table'], "input_fields" => $result['input_fields'], "error" => '']);
            }else if(isset($result['error'])){

               echo json_encode(["table" => '', "error" => $result['error']]); 
            }
          }
          $calculate->db->close();
           break;
           case 'list':
               $user_list = new user_list();
               $user_list->db = $db;
               $user_list->url = $CONF['url'];
               $user_list->milk_about = $_POST['milk_about'];
               $user_list->animal_type = $_POST['m_animal'];

               $user_list->dm_min = $settings['d_rate_minimum'];
               $user_list->dm_limit = $settings['d_rate_maximum'];

               
               $user_list->weight_min = $settings['weight_minimum'];
               $user_list->weight_max = $settings['weight_maximum'];
               $user_list->user_id = $user['id'];
            if(isset($_GET['b']) && $_GET['b'] == 'search_customers'){
                $user_list->search_val = $_POST['search_val'];
                echo json_encode($user_list->process(1));
            }elseif(isset($_GET['b']) && $_GET['b'] == 'milk_rates'){
                
               echo json_encode(get_milk_rate($user_list->db,$user['id'],$user_list->milk_about,$user_list->animal_type));
            }elseif(isset($_GET['b']) && $_GET['b'] == 'insert'){
                $user_list->customer_id = $_POST['customer_id'];
               
                $user_list->d_rate = $_POST['d_rate'];
                $user_list->weight = $_POST['weight'];
               echo json_encode($user_list->process(2));
            }elseif(isset($_GET['b']) && $_GET['b'] == 'fetch_list'){
                if(isset($_POST['start'])) {
                echo json_encode($user_list->getlist($_POST['start'],$_POST['per_page']));
                }
            }elseif(isset($_GET['b']) && $_GET['b'] == 'get_wishlist'){
               
                echo json_encode($user_list->get_all_list($user_list->user_id));
                
            }elseif(isset($_GET['b']) && $_GET['b'] == 'add_milk_from_wishlist'){
               
                
                
            }elseif(isset($_GET['b']) && $_GET['b'] == 'delete_list'){
                echo json_encode($user_list->delete_from_list($_POST['customer_id'],$user_list->user_id));
            }
            
            break;
            case 'wishlist_entery':

                $wishlist = new add_milk_wishlist();
                $wishlist->db = $db;
                $wishlist->url = $CONF['url'];
                $wishlist->user_id = $user['id'];
                $wishlist->milk_about = $_POST['milk_about'];
                $wishlist->milk_animal = $_POST['m_animal'];
                $wishlist->customer_id = $_POST['customer_id'];
                $wishlist->customer_uname = $_POST['customer_uname'];
               
                $wishlist->d_rate = $_POST['d_rate'];
                $wishlist->weight = $_POST['weight'];

                $wishlist->dm_min = $settings['d_rate_minimum'];
                $wishlist->dm_limit = $settings['d_rate_maximum'];

                $wishlist->weight_min = $settings['weight_minimum'];
                $wishlist->weight_max = $settings['weight_maximum'];
                if(isset($_GET['b']) && $_GET['b'] == 'wishlist_history'){
                    echo json_encode($wishlist->get_wishlist_history($wishlist->user_id));
                }else{
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        echo json_encode( $wishlist->process());
                      }
                }
                

            break;
            case 'use_masterlist':
                $masterlist = new add_milk_masterlist();
                $masterlist->db = $db;
                $masterlist->user_id = $user['id'];

                $masterlist->customer_id = $_POST['customer_id'];
                $masterlist->customer_uname = $_POST['customer_uname'];

                $masterlist->milk_about = $_POST['milk_about'];
                $masterlist->milk_animal = $_POST['m_animal'];

                $masterlist->d_rate = $_POST['d_rate'];
                $masterlist->weight = $_POST['weight'];

                $masterlist->money_received = $_POST['money_received'];
                $masterlist->is_locked = $_POST['is_locked'];

                $masterlist->dm_min = $settings['d_rate_minimum'];
                $masterlist->dm_limit =  $settings['d_rate_maximum'];

                $masterlist->weight_min = $settings['weight_minimum'];
                $masterlist->weight_max = $settings['weight_maximum'];

                if(isset($_GET['b']) && $_GET['b'] == 'search_customers'){
                    $masterlist->search_val = $_POST['search_val'];
                    echo json_encode($masterlist->process(1));
                }else{

                   echo json_encode($masterlist->process(0));
                }
            break;
            case 'ad_setting':
                $ad_stg = new update_ad_settings();
                $ad_stg->db = $db; // store in database variable

                
                if(isset($_GET['b']) && $_GET['b'] == 'animal'){
                    $animal = $ad_stg->load_animal();

                    // load milk animals
                    echo json_encode(['animal' => $animal]);
                }elseif($_GET['b']=='max_val'){
                    
                    // checking max value from user setting
                    $max_val = $ad_stg->max_val_check();
                    echo json_encode($max_val);
                }elseif( $_GET['b'] == 'stg_data'){

                    // user milk setting
                    $userSetting = $ad_stg->user_stg();
                    echo json_encode($userSetting);
                }elseif( $_GET['b'] == 'delete'){
                  $ad_stg->delete_id = $_POST['rowid'];
                  $ad_stg->delete_table_stg = $_POST['table'];
                    $ad_stg->delete_query($ad_stg->delete_table_stg);
                }elseif($_GET['b'] == 'stg_inputs'){
                   $result = $ad_stg->load_input_fields();
                   echo json_encode($result);

                }elseif($_GET['b'] == 'checking_milk_rate'){
                    $ad_stg->milk_type = $_POST['milk_type'];
                    $ad_stg->animal = $_POST['milk_animal'];
                    $ad_stg->mm_id = $user['id'];

                    echo json_encode($ad_stg->process(1));
                }else{

                // Save milk setting    
                $ad_stg->b_dm = $_POST['b_dm'];
                $ad_stg->s_dm = $_POST['s_dm'];
                $ad_stg->b_mfr = $_POST['b_mfr'];
                $ad_stg->s_mfr = $_POST['s_mfr'];
                $ad_stg->b_animal = $_POST['b_animal'];
                $ad_stg->s_animal = $_POST['s_animal'];

                $ad_stg->dm_max = $settings['d_rate_maximum'];
                $ad_stg->mfr_max = $settings['f_rate_maximum'];
                $ad_stg->dm_min = $settings['d_rate_minimum'];
                $ad_stg->mfr_min = $settings['f_rate_minimum'];
                $ad_stg->password = $_POST['pass'];
                // print_r($_POST);
                // die;
                $result = $ad_stg->process();

                if($result){
                    echo json_encode(['message' => $result]);
                 }
               }
               $ad_stg->db->close();
            break;
            case 'view-milk':
            $view_milk = new view_milk();
            $view_milk->db = $db;
            $view_milk->user_id = $user['id'];
            $view_milk->c_id = $_GET['c_id'];
            $view_milk->milk_table = $_POST['milk_table'];
            // Some Values assign for validate
            $view_milk->dm_min = $settings['d_rate_minimum'];
            $view_milk->dm_max = $settings['d_rate_maximum'];

            $view_milk->mf_min = $settings['mf_minimum'];
            $view_milk->mf_max = $settings['mf_maximum'];

            $view_milk->mfr_min = floatval($settings['f_rate_minimum']);
            $view_milk->mfr_max = floatval($settings['f_rate_maximum']);

            $view_milk->weight_min = $settings['weight_minimum'];
            $view_milk->weight_max = $settings['weight_maximum'];

            if(isset($_GET['b']) && $_GET['b'] == 'edit-milk'){

                $view_milk->milk_id = $_POST['milk_id'];
              
               $result = $view_milk->edit_btn($view_milk->milk_table,$view_milk->milk_id);
               echo json_encode($result);
            
            }elseif(isset($_GET['b']) && $_GET['b'] == 'update-milk'){
                   $milk_table = $_POST['milk_table'];
                   $milk_type = $_POST['milk_type'];
                   $id = $_POST['id'];
                   $to = $_POST['to'];
                   $view_milk->dm =  floatval($_POST['dm']);
                   $view_milk->mf =  floatval($_POST['mf']);
                   $view_milk->mfr =  floatval($_POST['mfr']);

                   $view_milk->weight = floatval($_POST['weight']);

                   $result = $view_milk->run_update_editted_data($milk_table,$milk_type,$id,$to);
                //    print_r($result);
                  echo json_encode(['success' => $result['success'] ?? '', 'failed' => $result['failed'] ?? '']);
                                
            }else{
                $view_milk->from_date = $_POST['past_date'];
                $view_milk->to_date = $_POST['current_date'];
                $view_milk->is_cleared = $_POST['isChecked'];
                
               
                $view_milk->record_per_page = 10;
                $result = $view_milk->get_view_data();

                echo json_encode($result);
               
                
            }
            $view_milk->db->close();
            break;

        case 'total_count': // count total values of milk taking/giving
            $data = new milk_count();
            $data->db = $db;
            $data->c_id = (int) $_POST['c_id'];
            $data->record_per_page = 10;
            if(isset($_GET['b']) && $_GET['b'] == 'head_message'){
                $message = $data->process(2);
                 echo json_encode($message);
            }elseif(isset($_GET['b']) && $_GET['b'] == 'cleared_history'){
                $data->record_per_page = 8;
                $history = $data->process(3);
                echo json_encode($history);
            }elseif(isset($_GET['b']) && $_GET['b'] == 'confirmation'){
                
                $confirmation = $data->process(4);
                echo json_encode($confirmation);
            }elseif(isset($_GET['b']) && $_GET['b'] == 'clear_record'){
                
                $confirmation = $data->cleared();
                echo json_encode($confirmation);
            }else{
                $total = $data->process(1);
                echo json_encode($total);
            }
            $data->db->close();
        break;

        case 'home_analytics':
            $analytics = new milkmen_analytics();
            $analytics->db = $db;
            $analytics->mm_id = $user['id'];
            $analytics->from_date = $_POST['from_date'];
            
            $analytics->to_date = $_POST['to_date'];

            $analytics->cleared = $_POST['is_cleared'];
            if(isset($_GET['b']) && $_GET['b'] == 'last_analytics'){
                $output = $analytics->process(2);
                echo json_encode($output);
            }else{
                $output = $analytics->process(1);
                echo json_encode($output);
            }
            $analytics->db->close();
            break;
        case 'check_admin':
            
            $admin = new Admin();
            $admin->db = $db;
            $admin->url = $CONF['url'];
            $admin->username = $_POST['username'];
		    $admin->password = $_POST['password'];
            
           echo  json_encode($admin->process());
            break;
        case 'admin_update_user':
               
                // header('Content-Type: text/plain');
              if(isset($_GET['b']) && $_GET['b'] == 'mmen'){
                $user_update = new update_mmen();
                $user_update->db = $db;
                $user_update->mmen_id = $_POST['user_id'];
                $user_update->acc_status_val = $_POST['acc_status'];
                if(isset($_GET['c']) && $_GET['c'] == 'update_profile'){

                    $user_update->mmen_id = $_POST['user_id'];
                    $user_update->fname = $_POST['first_name'];
                    $user_update->lname = $_POST['last_name'];
                    $user_update->p_number = $_POST['p_number'];
                    
                    $user_update->day = $_POST['day'];
                    $user_update->month = $_POST['month'];
                    $user_update->year = $_POST['year'];
                    $user_update->gender = $_POST['gender'];
                    $user_update->state = $_POST['state'];
                    $user_update->district = $_POST['district'];
                    $user_update->sub_district = $_POST['sub_district'];
                    $user_update->area = $_POST['area'];
                    $user_update->pincode = $_POST['pincode'];
                    $user_update->locality = $_POST['locality'];
                    $user_update->milk_distribute_type = $_POST['distribute_type'];
                    $user_update->dairy_name = $_POST['dairy_name'];
                    
                    echo json_encode($user_update->update_process(2));
                }elseif(isset($_GET['b']) && $_GET['c'] == 'subs_update'){
                    
                    $user_update->subs_time = $_POST['acc_subs_time'];
                    $user_update->subs_type = $_POST['acc_subs_type'];
                    echo json_encode($user_update->update_process(3));

                }else{
                    echo json_encode($user_update->update_process(1));
                }

               
            }elseif(isset($_GET['b']) && $_GET['b'] == 'load_mmen'){
                if(isset($_POST['start'])) {
                    $manageUsers = new manageUsers();
                    
                    $manageUsers->db = $db;
                    $manageUsers->url = $CONF['url'];
                    $manageUsers->per_page = $settings['uperpage'];
                    
                    echo $manageUsers->getmmen($_POST['start'], $_POST['filter'],$_POST['per_page']);
                }
            }elseif(isset($_GET['b']) && $_GET['b'] == 'mmen_profile'){

                    $user_update->mmen_id = $_POST['user_id'];
                    $user_update->fname = $_POST['first_name'];
                    $user_update->fname = $_POST['last_name'];
                    $user_update->fname = $_POST['p_number'];
                    
                    $user_update->fname = $_POST['day'];
                    $user_update->fname = $_POST['month'];
                    $user_update->fname = $_POST['year'];
                    $user_update->gender = $_POST['gender'];
                    $user_update->fname = $_POST['state'];
                    $user_update->fname = $_POST['district'];
                    $user_update->fname = $_POST['sub_district'];
                    $user_update->fname = $_POST['area'];
                    $user_update->fname = $_POST['pincode'];
                    $user_update->fname = $_POST['locality'];
                    $user_update->fname = $_POST['distribute_type'];
                    $user_update->fname = $_POST['dairy_name'];
                    // print_r($_POST);
                    echo json_encode($user_update->update_process(2));
            }
            break;    
    default:
    die('{error: invaild request!}');
        # code...
        break;
}


?>