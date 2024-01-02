<?php
              error_reporting(1);
              $CONF = array();

              $CONF['host'] = 'localhost';
              $CONF['user'] =  'root';
              $CONF['pass'] =  '';
              $CONF['name'] =   'new_doodhbazar';
              $ip =  "http://192.168.43.113/doodhbazar";
              $host = "http://localhost/doodhbazar";

 
              $CONF['url'] =$host;
              $CONF['email'] = 'vishal.dh8320@gmail.com';
              $CONF['theme_path'] = 'theme';


          
              $action = array(
                            'login'            => 'login',
                            'register'         => 'register',
                            'home'             => 'home',
                            'list'             => 'list',
                            'add_user'         => 'add_user',
                            'settings'         => 'settings',
                            'manage_user'      => 'manage_user',
                            'calculate'       => 'calculate',
                            'info'            => 'info',
                            'global_admin'    => 'admin',
                            'acc_status'      => 'acc_status',
                            'subscription'    => 'subscription',
                            'welcome'         => 'welcome'
                            );
       
          define('COOKIE_PATH',preg_replace('|https?://[^/]+|i','',$CONF['url']).'/');       

?>