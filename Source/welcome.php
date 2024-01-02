<?php
                require_once(__DIR__ . '/../include/autoload.php');
              
          function PageMain(){
            global $TMPL, $LNG, $CONF, $db, $user;
            $TMPL['title'] = 'Doodhbazar - Milk Business growing Network, Get help to spread your bussiness more and more';
            $TMPL['description'] = 'Doodhbazar is a online Portal where all milkmen registered their milk business. All milkmen can daily upload their milk data and calculate their milk daily to daily and analysis their profit and loss.';
            $TMPL['keywords'] = 'doodhbazar,milkbook, milkmen portal, online milk dairy,milk business';
            $TMPL['author'] = 'A DOODHBAZAR vishal bhardwaj\'s production.';
            

           $TMPL['login_url'] = permalink($CONF['url'].'/index.php?a=login');
           $TMPL['registration_url'] = permalink($CONF['url'].'/index.php?a=register');
          
            $TMPL['footer_url'] = permalink($CONF['url'].'/index.php?a=info&b=vishal-bhardwaj');
            // profile_card for founder & ceo


            $image = permalink($CONF['url'].'/image.php?t=pvt&zc=3&src=1676313672274.jpeg');

            $html =   '
                        <div class="card">
                        <img src="'.$image.'" alt="Vishal Bhardwaj">
                        <h1>Vishal Bhardwaj</h1>
                        <p class="title">Founder of Doodhbazar</p>
                        <p style="font-size: 1em;">(Get help to increase your bussiness)</p>
                        <div style="margin: 10px 0; padding:10px">
                        <a href="https://www.facebook.com/vishal1bhardwaj" target="_blank"><i class="fa-brands fa-square-facebook" style="color: #1877f2;"></i></a> 
                        <a href="https://www.instagram.com/vi.shalbhardwaj" target="_blank"><i class="fa-brands fa-instagram"style="color: #eb0f0f;"></i></a> 
                            
                        <a href="https://www.twitter.com/vishal_bhrdwaj" target="_blank"><i class="fa-brands fa-twitter" style="color: #1d9bf0;"></i></a> 
                        </div>
                        <p><a href = "'.$TMPL['footer_url'].'">About All</a></p>
                        </div>

               .';
            $TMPL['author_profile'] = $html;
           $skin = new skin('welcome/content');
            return $skin->make();

          }




            ?>
