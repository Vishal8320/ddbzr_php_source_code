
<?php

$time_start = microtime(true);
require_once(__DIR__ . '/include/autoload.php');
    global $user,$db;

	$Actionpage = array_flip($action);
	// echo $action[$_GET['a']];
	// die;
    if(isset($_GET['a']) && isset($action[$_GET['a']])) {
		
		    if(!empty($user)){
				
				// if not subsciption inserted to the user
				if(empty($user['s_id'])){
					
				 subscription::update_subs(0,$db,$user['id'],2,str_datetime($user['joined'],'+30 days'));
				
				}
				if($user['acc_status'] != 1){ // if user is not active 

					$allowed_page = [$Actionpage['info'],$Actionpage['admin']]; // allowed only can reach to these pages
	
					if(isset($_GET['a']) && in_array($_GET['a'],$allowed_page )) {
						$page_name = $action[$_GET['a']];
					}else{
						$page_name = $action['acc_status'];
					}
	
				}elseif(date('Y-m-d H:i:s') > $user['subs_time'] && ($user['subs_type'] == null || $user['subs_type'] == 0 || $user['subs_type'] == 1 || $user['subs_type'] == 2 )){
					
					$allowed_page =  [$Actionpage['info'],$Actionpage['admin']]; // allowed only can reach to these pages

					if(isset($_GET['a']) && in_array($_GET['a'],$allowed_page )) {
						$page_name = $action[$_GET['a']];

					}else{
						$page_name =  $action['subscription'];
					}
	
				}else{

					$page_name = $action[$_GET['a']];
				}


				// user logged code ended
			}else{
				$page_name = $action[$_GET['a']];
			} 
			
			
   }elseif (isset($_GET['a']) && $_GET['a'] !== '' && !isset($action[$_GET['a']])){

	 $page_name = '404';
	 
   }else{
		$page_name = 'welcome';	
	}


require_once("./Source/{$page_name}.php");

$TMPL['content'] = PageMain();

$TMPL['url'] = $CONF['url'];

if(!isAjax()) {
	$TMPL['token_id'] = GenerateToken_2();
}
if(isAjax()) {
	echo json_encode(array('content' => PageMain(), 'title' => $TMPL['title']));
	mysqli_close($db);
	return;
}

if(!empty($user['uname'])){
	$TMPL['top_menus'] = menu($user);
	$TMPL['url_logo'] = permalink($CONF['url'].'/index.php?a=home');
}else{
	$TMPL['top_menus'] = menu(false);
	$TMPL['url_logo'] = permalink($CONF['url'].'/index.php?a=login');
}



$TMPL['footer_url'] = permalink($CONF['url'].'/index.php?a=info&b=vishal-bhardwaj');
// profile_card for founder & ceo


$image = permalink($CONF['url'].'/image.php?t=mm&zc=3&src=SAVE_20230125.jpg');

$html =   '
			<div class="card">
			<img src="'.$image.'" alt="'.$author.'">
			<h1>'.$author.'</h1>
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
$TMPL['info_urls'] = info_urls();

$TMPL['top_menus'] = menu($user);
$TMPL['language'] = getLanguage($CONF['url'], null, 1);
$TMPL['year'] = date('Y');
$TMPL['footer'] = $settings['title'];

$TMPL['powered_by'] = 'A <a href="'.$TMPL['footer_url'].'" target="_blank">'.$author.'</a> production.';

$TMPL['head_office'] = 'Head Office village Dhareru(119),Bhiwani,Haryana';

$skin = new skin('wrapper');
echo $skin->make();
mysqli_close($db);

?>