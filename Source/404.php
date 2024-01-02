<?php
function PageMain(){
    global $TMPL, $CONF;
    
        $skin = new skin('welcome/404');
        $TMPL_old = $TMPL;
        $TMPL = array();
        $menu = '';
        $TMPL['url'] = $CONF['url'];


        $menu .= $skin->make();
        $TMPL = $TMPL_old;
        unset($TMPL_old);
        return $menu;

  }









?>