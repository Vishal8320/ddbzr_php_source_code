<?php
            // require_once(__DIR__ . '/../include/autoload.php');
          function PageMain(){
            global $db, $CONF, $TMPL, $LNG, $user;
            if(empty($user)){
              header('location:'.permalink($CONF['url'].'/index.php?a=login'));
              
           }else{
             
             switch($user['acc_status']){
                case 0:
                 $status = 'pending';
                 break;
                 case 1:
                 $status = 'active';
                 break;
                 case 2:
                 $status = 'reject';
                 break;
                 case 3:
                 $status = 're-submit';
                 break;
                 case 4:
                 $status = 'temp_block';
                  break;
                  case 5:
                  $status = 'kyc_require';
                  break;
                  case 6:
                  $status = 'illegal_activity';
                  break;
             }
             if($status == 'active'){
                $title = sprintf($LNG['active_title'],$user["fname"]);
                $class = 'color-green';
                $description = $LNG['active_des'];

            }elseif($status == 'pending'){
                $title = sprintf($LNG['pending_title'],$user["fname"]);
                $class = 'color-orange';
                $description = $LNG['pending_des'];

            }elseif($status == 'reject'){
                $title = sprintf($LNG['reject_title'],$user["fname"]);
                $class = 'color-red';
                $description = $LNG['reject_des'];

            }elseif($status == 're-submit'){
                $title = sprintf($LNG['re-submit_title'],$user["fname"]);
                $class = 'color-orange';
                $description = $LNG['re-submit_des'];

            }elseif($status == 'temp_block'){
                $title = sprintf($LNG['temp_block_title'],$user["fname"]);
                $class = 'color-red';
                $description = $LNG['temp_block_des'];

            }elseif($status == 'kyc_require'){
                $title = sprintf($LNG['kyc_require_title'],$user["fname"]);

            }elseif($status == 'illegal_activity'){
                $title = sprintf($LNG['illegal_activity_title'],$user["fname"]);
                $class = 'color-red';
            }
            if(isset($_GET['b']) && $_GET['b'] == 'logout'){
                $log = new user();
                $log->db = $db;
                $log->uname = $user['uname'];
                $log->logOut(true);
                header('location:'.permalink($CONF['url'].'/index.php?a=login'));
              }
            $TMPL['logout_url'] = permalink($CONF['url'].'/index.php?a=acc_status&b=logout');  
            $TMPL['title'] = $title;
            $TMPL['class']  = $class;
            $TMPL['description'] = $description;
            $skin = new skin('home/acc_status');
            return $skin->make();
        }
          }


?>