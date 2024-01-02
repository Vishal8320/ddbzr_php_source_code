<?php
            require_once(__DIR__ . '/../include/autoload.php');
          function PageMain(){
            global $db, $CONF, $TMPL, $LNG, $user;
            if(empty($user)){
               header('location:'.permalink($CONF['url'].'/index.php?a=login'));
          }else{
            $currentDate = date("Y-m-d");
            $TMPL['title'] = 'Doodhbazar as '.$user['fname'].' - Home|Dashboard';
            $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
            $TMPL['keywords'] = 'doodhbazar,milkbook, milkmen portal, online milk dairy,milk business';
            $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';
            $TMPL['from_date'] = $currentDate;
            $TMPL['to_date'] = $currentDate;
            $TMPL['title'] = 'Doodhbazar Home';
            $TMPL['profile_pic'] = '<img src="' . $CONF['url'] . '/image.php?t=mm&amp;w=50&h=50&src=' . $user['profile_pic'] .'" title="' . $user['fname'] . ' profile photo">';
          
            $subscription = new subscription();
            $subscription->db =           $db;
            $subscription->url =          $CONF['url'];
            $subscription->mmen_id =      $user['id'];
            $subscription->mmen_name  =   $user['fname'];
            $subscription->subs_time =    $user['subs_time'];
            $subscription->subs_type =    $user['subs_type'];
            $result = $subscription->show_subs();

            $TMPL['subs_header'] = $result['header'];
            $TMPL['subs_contain'] = $result['contain'];
            $TMPL['subs_desclaimer'] = $result['desclaimer'];
           
          if(isset($_GET['b']) && $_GET['b'] == 'logout'){
            $log = new user();
            $log->db = $db;
            $log->uname = $user['uname'];
            $log->logOut(true);
            header('location:'.permalink($CONF['url'].'/index.php?a=login'));
          }
            $skin = new skin('home/content');
            return $skin->make();
        }
          }


?>