<?php
   function PageMain(){
     global $db, $CONF, $TMPL, $user, $LNG;


     if(empty($user)){
      header('location:'.permalink($CONF['url'].'/index.php?a=login'));
     }else{
      $TMPL['title'] = 'Doodhbazar as '.$user['fname'].' Calculate|Upload milk data ';
      $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
      $TMPL['keywords'] = 'doodhbazar,milkbook, milkmen portal, online milk dairy,milk business';
      $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';

      $skin = new skin('milk_men/calculate');
      $TMPL_old = $TMPL;
      $TMPL = array();
   
    $TMPL['profile_pic'] = '<img src="' . $CONF['url'] . '/image.php?t=mm&amp;w=50&h=50&src=' . $user['profile_pic'] .'" title="' . $user['fname'] . ' profile photo">';
    $TMPL['user_id'] = $user['id'];
    $menu = '';






    $menu = $skin->make();
    $TMPL = $TMPL_old;
    unset($TMPL_old);
    return $menu;
   }
 
   }








?>