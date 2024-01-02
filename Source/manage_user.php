<?php

         function PageMain(){
         global $db, $CONF, $TMPL, $user, $LNG;
         if(empty($user)){
          header('location:'.permalink($CONF['url'].'/index.php?a=login'));
       }else{
         $TMPL['title'] = 'Doodhbazar - Managing all milk customers of '.$user['fname'];
         $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
         $TMPL['keywords'] = 'doodhbazar,milkbook, milkmen portal, online milk dairy,milk business';
         $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';

         $skin = new skin('milk_men/manege_user');
         $TMPL_old = $TMPL;
         $TMPL = array();
         $TMPL['user_id'] = $user['id'];
         $TMPL['profile_pic'] = '<img src="' . $CONF['url'] . '/image.php?t=mm&amp;w=50&h=50&src=' . $user['profile_pic'] .'" title="' . $user['fname'] . ' profile photo">';
         $menu = '';
         if(isset($_GET['b']) && $_GET['b'] == 'view-milk'){
           
          $TMPL['title'] = 'Doodhbazar - View Milk Data ';
          $view_milk = new view_milk();
          $view_milk->db = $db;
          $date = $view_milk->get_date_10();

          $TMPL['current_date'] = $date['current_date'];
          $TMPL['past_date'] = $date['past_date'];
          
          $skin = new skin('milk_men/view-milk');
          $TMPL_old = $TMPL;
          
          $menu = '';

         }
         
          $menu .= $skin->make();
          $TMPL = $TMPL_old;
          unset($TMPL_old);
          return $menu;
       }
         
        }
 
 ?>       