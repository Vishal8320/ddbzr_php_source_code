<?php
            // require_once(__DIR__ . '/../include/autoload.php');
          function PageMain(){
            global $db, $CONF, $TMPL, $LNG, $user;
            if(empty($user)){
              header('location:'.permalink($CONF['url'].'/index.php?a=login'));
              
           }else{
                $TMPL['title'] = 'Creating a Customer list and delivered Fastest milk entery.';
                $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
                $TMPL['keywords'] = 'doodhbazar, milkbook, milkmen portal, online milk dairy, milk business';
                $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';


                $skin = new skin('milk_men/list');
                $TMPL_old = $TMPL;
                $TMPL = array();
                $TMPL['user_id'] = $user['id'];
                $menu = '';
                

               
            if(isset($_GET['b']) && $_GET['b'] == 'add_milk'){
              $TMPL['title'] = 'Delivered milk via Wishlist/Masterlist [Fastest milk Entery]';
              $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
              $TMPL['keywords'] = 'doodhbazar, milkbook, milkmen portal, online milk dairy, milk business';
              $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';
                 
                 
                 $skin = new skin('milk_men/add_milk');
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