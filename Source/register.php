<?php

function PageMain(){
    global $TMPL, $CONF, $db, $LNG, $user, $settings;
    $skin = new skin('welcome/register');
    if(isset($user['uname'])){
        header('location:'.permalink($CONF['url'].'/index.php?a=home'));
    }
    
    $TMPL = array();
    $TMPL['url'] = $CONF['url'];
    $TMPL['title'] = 'Doodhbazar Milkman Registration - Milk Business growing Network, Get help to spread your bussiness more and more';
    $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
    $TMPL['keywords'] = 'doodhbazar,milkbook, milkmen portal, online milk dairy,milk business';
    $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';
    $TMPL['years'] = generateDate_reg(0);
    $TMPL['months'] = generateDate_reg(1);
    $TMPL['days'] = generateDate_reg(2);
    $TMPL['name'] = $settings['name'];
    $TMPL['uname'] = $settings['uname'];
    $TMPL['locality'] = $settings['locality'];
    $TMPL['pincode'] = $settings['pincode'];
    $TMPL['state'] = load_state();
    $TMPL['home_url'] = permalink($CONF['url'].'/index.php?a=home');

    return $skin->make();
    
    



}





    

?>            


