<?php
            // require_once(__DIR__ . '/../include/autoload.php');
          function PageMain(){
            global $db, $CONF, $TMPL, $LNG, $user;
            if(empty($user)){
              header('location:'.permalink($CONF['url'].'/index.php?a=login'));
              
           }else{
            
            $subs_time = (empty($user['subs_time']) || $user['subs_time'] == '0000-00-00 00:00:00')
            ? date('d-m-Y h:iA', strtotime($user['joined']))
            : date('d-m-Y h:iA', strtotime($user['subs_time']));
     

            if(date('d-m-Y h:iA') > $user['subs_date'] && ($user['subs_type'] == 0 ||$user['subs_type']== 2)){
                $title = sprintf($LNG['subs_expired_title'],$user["fname"],$subs_time);
                $class = 'color-red';
                $description = $LNG['subs_expired_des'];
                // $logout_url = permalink($CONF['url'].'/index.php?a=acc_status&b=logout'); 
                // $TMPL['logout'] = '<span class="logout_btn"><a href="{$logout_url}">Logout</a></span>';
            }else{
                $title = sprintf($LNG['subs_active_title'],$user["fname"]);
                $class = 'color-green';
                $description = sprintf($LNG['subs_active_des'],$subs_time);
            }

            if(isset($_GET['b']) && $_GET['b'] == 'logout'){
                $log = new user();
                $log->db = $db;
                $log->uname = $user['uname'];
                $log->logOut(true);
                header('location:'.permalink($CONF['url'].'/index.php?a=login'));
              }
           
            
            $TMPL['title'] = $title;
            $TMPL['class']  = $class;
            $TMPL['description'] = $description;
            $skin = new skin('home/subs_expired');
            return $skin->make();
        }
          }


?>