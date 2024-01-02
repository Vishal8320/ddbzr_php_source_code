<?php
              require_once(__DIR__ . '/../include/autoload.php');
    function pageMain(){
     global $CONF, $TMPL, $LNG, $db, $user;
     if(empty($user)){
      header('location:'.permalink($CONF['url'].'/index.php?a=login'));
   }else{


    

    $TMPL = array();
     if(isset($_GET['b']) && $_GET['b'] == 'milk_rates'){
        $skin = new skin('settings/milk_rates'); $page = '';
        if(isset($_SESSION['page'])){
          unset($_SESSION['page']);
        }
        


     }elseif(isset($_GET['b']) && $_GET['b'] == 'security'){
      $skin = new skin('settings/security'); $page = '';

      $TMPL['welcome'] = '{$_GET["b"]} Setting';
     }elseif(isset($_GET['b']) && $_GET['b'] == 'deactivate'){
      $skin = new skin('settings/deactivate');

      $TMPL['welcome'] = '{$_GET["b"]} Setting';
     }else{
      $skin = new skin('settings/general'); $page = '';
     }
     $page .= $skin->make();
     
	  $TMPL['settings'] = $page;
     $links = array(
                   '' => array('mmen_setting_general','setting'),
                   '&b=security' => array('mmen_setting_security','security'),
                   '&b=milk_rates' => array('mmen_setting_m_rates','milk_rates'),
                   '&b=deactivate' => array('mmen_setting_deactivate','deactivate')
     );
     if(isset($_GET['b'])){
      $TMPL['welcome'] = $LNG['mmen_setting_{$_GET["b"]}'];
    }else{
      $TMPL['welcome'] = $LNG['mmen_setting_general'];
    }
     $TMPL['menu'] = '';
     foreach($links as $link => $title){
     $class = '';
     
    if(isset($_GET['b']) && $link == '&b='.$_GET['b']){
      $TMPL['title'] = $LNG[$title[0]];
       $class = 'sidebar-link-active';
     }elseif(empty($link) && empty($_GET['b'])){
         $class = 'sidebar-link-active';
       }
      $TMPL['menu'] .= '<div class="sidebar-link '.$class.'"><a href="'.permalink($CONF['url'].'/index.php?a=settings'.$link).'" rel="loadpage">'.$LNG[$title[0]].'</div>';
      
     }
     $skin = new skin('settings/content');

     $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
     $TMPL['keywords'] = 'doodhbazar,milkbook, milkmen portal, online milk dairy,milk business';
     $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';
     return $skin->make();

   }
     
    }


?>