<?php

         function PageMain(){
         global $db, $CONF, $TMPL, $user, $LNG, $settings;
         if(empty($user)){
            header('location:'.permalink($CONF['url'].'/index.php?a=login'));
         }else{
            $TMPL['title'] = 'Doodhbazar - Customers account creating.';
            $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
            $TMPL['keywords'] = 'doodhbazar,milkbook, milkmen portal, online milk dairy,milk business';
            $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';

            
            $skin = new skin('milk_men/add_user');
            $TMPL_old = $TMPL;
            $TMPL = array(); $menu = '';
           

           
            $TMPL['user_id'] = $user['id'];
            $TMPL['url'] = $CONF['url'];
            $TMPL['years'] = generateDate_reg(0);
            $TMPL['months'] = generateDate_reg(1);
            $TMPL['days'] = generateDate_reg(2);
            $TMPL['name'] = $settings['name'];
            $TMPL['uname'] = $settings['uname'];
            $TMPL['locality'] = $settings['locality'];
            $TMPL['pincode'] = $settings['pincode'];
            $TMPL['state'] = load_state();
            $TMPL['profile_pic'] = '<img src="' . $CONF['url'] . '/image.php?t=mm&amp;w=50&h=50&src=' . $user['profile_pic'] .'" title="' . $user['fname'] . ' profile photo">';
            
           $home = new mm_home();
           $home->db = $db;
           $home->url = $CONF['url'];
           $home->user_id = $user['id'];
           $sidebar = $home->total_users_sidebar();
           $relationship_customers = $home->filter_customers(2)['total_customers'];  // all customers where not registered by user but they had taken/given milk

          // $relationship_customers = $relationship_customers ?? 0; // If $relationship_customers is null or zero, set it to zero

           // Return $relationship_customers if it has a non-zero value, otherwise return zero
           $output = ($relationship_customers !== null && $relationship_customers !== '0') ? $relationship_customers : 0;
           $customer = ($output === "1") ? 'customer' : 'customers';

           $TMPL['relationship_customers'] = "You have a relationship with<span style='color:#5155c9;font-weight:600'> (".$output.")</span> ".$customer." who have bought/sold milk from you, but they are not registered by you.";
           $TMPL['sidebar1'] = $sidebar;
            $menu = $skin->make();
            $TMPL = $TMPL_old;
            unset($TMPL_old);
            return $menu;
            
         }
        
       }

?>       