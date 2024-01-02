<?php


function PageMain() {
    global $TMPL, $LNG, $CONF, $db, $settings;

    $select_pages = $db->query("SELECT * FROM `info_pages` ORDER BY `id` ASC");

    $page = array();

    while ($row = $select_pages->fetch_assoc()) {
        $page[$row['url']] = array(skin::parse($row['title']), skin::parse($row['content']), $row['public']);
    }

    $skin = new skin('info/sidebar');
    $sidebar = '';
    $TMPL['links'] = '';
    foreach ($page as $url => $value) {
        if ($value[2]) {
            $class = '';
            if ($_GET['b'] == $url) {
                $class = ' sidebar-link-active';
            }
            $TMPL['links'] .= '<div class="sidebar-link' . $class . '"><a href="' . permalink($CONF['url'] . '/index.php?a=info&b=' . $url) . '" rel="loadpage">' . skin::parse($value[0]) . '</a></div>';
        }
    }
    $sidebar = $skin->make();

    if (!empty($_GET['b']) && isset($page[$_GET['b']][0])) {
        $b = $_GET['b'];


        $image = permalink($CONF['url'].'/image.php?t=pvt&zc=3&src=IMG-4623-832019-yellow.jpg');

        $html =   ' <center>
                    <div class="card_2">
                    <img src="'.$image.'" alt="Vishal Bhardwaj">
                    <h1>Vishal Bhardwaj</h1>
                    <p class="title">Founder of Doodhbazar</p>
                    <div style="margin: 10px 0; padding:10px">
                    <a href="https://www.facebook.com/vishal1bhardwaj" target="_blank"><i class="fa-brands fa-square-facebook fa-2x" style="color: #1877f2;"></i></a> 
                    <a href="https://www.instagram.com/vi.shalbhardwaj" target="_blank"><i class="fa-brands fa-instagram fa-2x"style="color: #eb0f0f;"></i></a> 
                        
                    <a href="https://www.twitter.com/vishal_bhrdwaj" target="_blank"><i class="fa-brands fa-twitter fa-2x" style="color: #1d9bf0;"></i></a> 
                    </div>
                    </div></center>

           .';
        // $TMPL['author_profile'] = $html;
        $_GET['b'] == 'vishal-bhardwaj' ?  $TMPL['author_profile'] = $html :  $TMPL['author_profile'] = '';
        $TMPL['sidebar'] = $sidebar;
        $TMPL['url'] = $CONF['url'];
        $TMPL['title'] = skin::parse($page[$b][0]) . ' - ' . $settings['title'];
        $TMPL['content'] = skin::parse($page[$b][1]);
        $TMPL['header'] = '<strong>' . skin::parse($page[$b][0]) . '</strong>';
        $skin = new skin("info/content");
        return $skin->make();
    } else {
        header("Location: " . $CONF['url']);
    }
}

?>
