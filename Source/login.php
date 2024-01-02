<?php
                // require_once(__DIR__ . '/../include/autoload.php');
              
          function PageMain(){
            global $TMPL, $LNG, $CONF, $db, $user;

            if(!empty($user)){
              header('location:'.permalink($CONF['url'].'/index.php?a=home'));
          }else{
            
            $TMPL['title'] = 'Doodhbazar - Milk Business growing Network, Get help to spread your bussiness more and more';
            $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
            $TMPL['keywords'] = 'doodhbazar,milkbook, milkmen portal, online milk dairy,milk business';
            $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';

            if(isset($_POST['login'])) {
              // Log-in usage
              $log = new User();
              $log->db = $db;
              $log->url = $CONF['url'];
              $log->uname = $TMPL['login_uname'] = $_POST['uname'];
              $log->pass = $TMPL['login_pass'] = $_POST['pass'];
              $log->remember = (isset($_POST['remember']) ? $_POST['remember'] : null);
          
                  $TMPL['remember_checked'] = isset($_POST['remember']) ? ' checked="checked"' : '';
              
              $auth = $log->auth(1);
              // $TMPL['auth'] = print_r($auth);
              
              if(!is_array($auth)) {
                $TMPL['loginMsg'] = notificationBox('error', $auth, 1);
              } else {
                header("Location: ".permalink($CONF['url']."/index.php?a=home"));
              }
            }

          $TMPL['crt_acc_link'] = permalink($CONF['url'].'/index.php?a=register');
          

           $skin = new skin('welcome/login');
            return $skin->make();

           }
          }




            ?>
