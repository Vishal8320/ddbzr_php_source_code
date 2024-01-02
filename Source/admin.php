<?php
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $settings, $action;
	//require_once('./includes/countries.php');

	$admin_pages = ['site_settings', 'themes', 'plugins', 'languages', 'stats', 'users', 'manage_pages', 'manage_groups', 'manage_reports', 'manage_ads', 'info_pages', 'security'];
	$admin_mmen_pages = ['all_mmen' => $LNG['all_mmen'], 'pending' => $LNG['pending'], 'active' => $LNG['active'], 'reject' => $LNG['reject'],'re-submit' => $LNG['re-submit'], 'subs_expired' => $LNG['subs_expired'], 'temp_blocked' => $LNG['temp_block']];

	$admin = new Admin();
	$admin->db = $db;
	$admin->url = $CONF['url'];
	

	if(isset($_POST['login'])) {
		$admin->username = $_POST['username'];
		$admin->password = $_POST['password'];
		
		// Attempt to auth the user
        $auth = $admin->auth();

        // If the user has been logged-in
        if($auth) {
            header("Location: ".$CONF['url']."/index.php?a=global_admin");
        }
        // If the user could not be logged-in
        elseif(isset($_POST['login'])) {
            $TMPL['message'] = notificationBox('error', $LNG['invalid_user_pw']);
            $admin->logOut(false);
        }
	} else {
		if(isset($_SESSION['adminUsername'])) {
			$admin->username = $_SESSION['adminUsername'];
			$admin->password = $_SESSION['adminPassword'];

			// Attempt to auth the user
			$user = $admin->auth();
            if($user == false) {
                $admin->logOut(false);
            }
		}
	}
	if(isset($_SESSION['adminUsername']) && isset($_SESSION['is_admin'])) {
        // Set the content to true, change the $skin to content
        $content = true;

        $TMPL_old = $TMPL;
        $TMPL = array();
        $TMPL['url'] = $CONF['url'];
        $TMPL['token_input'] = GenerateToken_2($_SESSION['token_id']);
        $TMPL['token_id'] = $_SESSION['token_id'];
       
        if(isset($user['user_group']) && $user['user_group']) {
            $admin_pages = ['stats', 'users', 'manage_pages', 'manage_groups', 'manage_reports'];
        }

        if(isset($_GET['b']) && in_array($_GET['b'], $admin_pages)) {
            if($_GET['b'] == 'security' && isset($user['user_group']) == false) {
                $skin = new skin('admin/security');
                $page = '';

                // if(!empty($_POST)) {
                //     $updateSettings = new updateSettings();
                //     $updateSettings->db = $db;
                //     $updated = $updateSettings->query_array('admin', $_POST);

                //     header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=security&m=" . $updated);
                // }

                if(isset($_GET['m']) && $_GET['m'] == 1) {
                    $TMPL['message'] = notificationBox('success', $LNG['password_changed']);
                } elseif(isset($_GET['m']) && $_GET['m'] == 2) {
                    $TMPL['message'] = notificationBox('error', $LNG['wrong_current_password']);
                } elseif(isset($_GET['m']) && $_GET['m'] == 3) {
                    $TMPL['message'] = notificationBox('error', $LNG['password_not_match']);
                } elseif(isset($_GET['m']) && $_GET['m'] == 4) {
                    $TMPL['message'] = notificationBox('error', $LNG['password_too_short']);
                } elseif(isset($_GET['m']) && $_GET['m'] == 0) {
                    $TMPL['message'] = notificationBox('info', $LNG['password_not_changed']);
                }
            } elseif($_GET['b'] == 'stats') {
                $skin = new skin('admin/stats');
                $TMPL_old = $TMPL;
                $TMPL = array(); $page = '';
                
                $page = '';
                $TMPL['url'] = $CONF['url'];
                $Actionpage = array_flip($action);
                $TMPL['admin_type_url'] = $Actionpage['admin'];
                
                // Get the lowest year from the 1st registered user
                $first_user = $db->query('SELECT `joined` FROM `mmen` ORDER BY `id` ASC LIMIT 0,1');
                $result_date = $first_user->fetch_assoc()['joined'];
                
                // Validate the month
                if(isset($_GET['month']) && $_GET['month'] <= 12 && $_GET['month'] > 0) {
                    $_GET['month'] = sprintf("%02d", $_GET['month']);
                } elseif(!empty($_GET['month'])) {
                    $_GET['month'] = date('m');
                }

                // Validate the year
                if(isset($_GET['year']) && $_GET['year'] >= date('Y', strtotime($result_date)) && $_GET['year'] <= date('Y')) {
                    $_GET['year'] = sprintf("%04d", $_GET['year']);
                } elseif(!empty($_GET['year'])) {
                    $_GET['year'] = date('Y');
                }

                if(!(isset($_GET['day']) && checkdate((int)$_GET['month'], (int)$_GET['day'], (int)$_GET['year']))) {
                    unset($_GET['day']);
                }

                // Validate the category
                $categories = array('mmen', 'm_customers', 'bought_milk', 'sold_milk', 'bought_milk_mf', 'bought_milk_dm', 'sold_milk_mf', 'sold_milk_dm','update_milk','cleared',);

                if(isset($_GET['c']) == false || in_array($_GET['c'], $categories) == false) {
                    $_GET['c'] = 'mmen';
                }

                $TMPL['get_c'] = $_GET['c'];

                // Generate the categories menu
                $TMPL['menu_url'] = '<div class="page-inner" style="padding-bottom: 0;padding-top: 0;overflow: auto !important;"><div class="edit-menu">';
                
                foreach($categories as $cat) {
                    $extra_url = '';
                    if(isset($_GET['year']) && !empty($_GET['year'])) {
                        $extra_url .= '&year=' . $_GET['year'];
                    }
                    if(isset($_GET['month']) && !empty($_GET['month'])) {
                        $extra_url .= '&month=' . $_GET['month'];
                    }
                    $TMPL['menu_url'] .= '<a href="' . $CONF['url'] . '/index.php?a=global_admin&b=stats&c=' . $cat . $extra_url . '" rel="loadpage"><div class="edit-menu-item' . ($_GET['c'] == $cat ? ' edit-menu-item-active' : '') . '" id="edit-' . $cat . '">' . $LNG[$cat] . '</div></a>';
                }
                $TMPL['menu_url'] .= '</div></div>';
                
                // Generate the stats form
                $TMPL['year_form'] = generateStatsForm(0, (isset($_GET['year']) ? $_GET['year'] : date('Y')), $result_date);

                if(empty($_GET['year'])) {
                     
                    foreach(range(date('Y'), $result_date, 1) as $year) {
                        $years[] = $year;
                    }
                    $stats = admin_stats($db, 0, $years, $_GET['c'], 3);

                    $i = 0;
                    $y = 0;
                    $TMPL['stats'] = '<div class="admin-stats-container"><div class="admin-stats-column">' . $LNG['date'] . '</div><div class="admin-stats-column admin-stats-center-column">' . $LNG['evolution'] . '</div><div class="admin-stats-column admin-stats-right-column">' . $LNG[$_GET['c']] . '</div></div>';

                    foreach($stats as $x) {
                        $TMPL['stats'] .= '<a href="' . $CONF['url'] . '/index.php?a=global_admin&b=stats&c=' . $_GET['c'] . '&year=' . $years[$y] . '" rel="loadpage"><div class="admin-stats-container' . (($i % 2 == 0) ? ' admin-stats-extra' : '') . '"><div class="admin-stats-column">' . $years[$y] . '</div><div class="admin-stats-column admin-stats-center-column">' . percentage($x, (isset($stats[($y + 1)]) ? $stats[($y + 1)] : null)) . '</div><div class="admin-stats-column admin-stats-right-column">' . $x . '</div></div></a>';
                        $i++;
                        $y++;
                    }
                } elseif(!empty($_GET['day'])) {
                    $TMPL['month_form'] = '<select name="month">' . generateStatsForm(1, $_GET['month']) . '</select>';
                    $TMPL['days_form'] = '<select name="day">' . generateStatsForm(2, $_GET['day']) . '</select>';
                    unset($TMPL['menu_url']);

                    $stats = admin_stats($db, 0, [], null, 2);

                    $i = 12;
                    $y = 0;
                    $TMPL['stats'] = '';
                    foreach($stats as $x) {
                        $TMPL['stats'] .= '<div class="admin-stats-container' . (($i % 2 == 0) ? ' admin-stats-extra' : '') . '"><div class="admin-stats-column">' . $LNG[$categories[$y]] . '</div><div class="admin-stats-column admin-stats-center-column"></div><div class="admin-stats-column admin-stats-right-column">' . $x . '</div></div>';
                        $i--;
                        $y++;
                    }
                } elseif(empty($_GET['month'])) {
                    // Get the number of months in a year
                    $months_array = array("12", "11", "10", "09", "08", "07", "06", "05", "04", "03", "02", "01");
                    $stats = admin_stats($db, 0, $months_array, $_GET['c'], 1);

                    $i = 12;
                    $y = 0;
                    $TMPL['stats'] = '<div class="admin-stats-container"><div class="admin-stats-column">' . $LNG['date'] . '</div><div class="admin-stats-column admin-stats-center-column">' . $LNG['evolution'] . '</div><div class="admin-stats-column admin-stats-right-column">' . $LNG[$_GET['c']] . '</div></div>';
                    foreach($stats as $x) {
                        $TMPL['stats'] .= '<a href="' . $CONF['url'] . '/index.php?a=global_admin&b=stats&c=' . $_GET['c'] . '&month=' . sprintf("%02d", $i) . '&year=' . $_GET['year'] . '" rel="loadpage"><div class="admin-stats-container' . (($i % 2 == 0) ? ' admin-stats-extra' : '') . '"><div class="admin-stats-column">' . $LNG['month_' . ltrim($i, 0)] . ' ' . $_GET['year'] . '</div><div class="admin-stats-column admin-stats-center-column">' . percentage($x, (isset($stats[($y + 1)]) ? $stats[($y + 1)] : null)) . '</div><div class="admin-stats-column admin-stats-right-column">' . $x . '</div></div></a>';
                        $i--;
                        $y++;
                    }
                } else {
                    $TMPL['month_form'] = '<select name="month">' . generateStatsForm(1, $_GET['month']) . '</select>';
                    // Get the number of days in the selected month
                    $days = cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['year']);

                    for($i = $days; $i >= 1; $i--) {
                        $days_array[] = $i;
                    }

                    $stats = admin_stats($db, 0, $days_array, $_GET['c'], 0);

                    $i = $days;
                    $y = 0;
                    $TMPL['stats'] = '<div class="admin-stats-container"><div class="admin-stats-column">' . $LNG['date'] . '</div><div class="admin-stats-column admin-stats-center-column">' . $LNG['evolution'] . '</div><div class="admin-stats-column admin-stats-right-column">' . $LNG[$_GET['c']] . '</div></div>';
                    foreach($stats as $x) {
                        $TMPL['stats'] .= '<a href="' . $CONF['url'] . '/index.php?a=global_admin&b=stats&c=' . $_GET['c'] . '&day=' . sprintf("%02d", $i) . '&month=' . $_GET['month'] . '&year=' . $_GET['year'] . '" rel="loadpage"><div class="admin-stats-container' . (($i % 2 == 0) ? ' admin-stats-extra' : '') . '"><div class="admin-stats-column">' . sprintf("%02d", $i) . '-' . $_GET['month'] . '-' . $_GET['year'] . '</div><div class="admin-stats-column admin-stats-center-column">' . percentage($x, (isset($stats[($y + 1)]) ? $stats[($y + 1)] : null)) . '</div><div class="admin-stats-column admin-stats-right-column">' . $x . '</div></div></a>';
                        $i--;
                        $y++;
                    }
                }

            } elseif($_GET['b'] == 'themes' && isset($user['user_group']) == false) {
                $skin = new skin('admin/themes');
                $page = '';

                // Get the software's info
                include(__DIR__ .'/../info.php');
                $TMPL['soft_url'] = $url;

                $updateSettings = new updateSettings();
                $updateSettings->db = $db;

                $themes = $updateSettings->getThemes();

                $TMPL['themes_list'] = $themes[0];

                if(isset($_GET['theme'])) {
                    // If theme is in array
                    if(in_array($_GET['theme'], $themes[1])) {
                        $updated = $updateSettings->query_array('settings', array('theme' => $_GET['theme'], 'token_id' => $_GET['token_id']));
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=themes");
                    }
                }
            } elseif($_GET['b'] == 'info_pages' && isset($user['user_group']) == false) {
                $skin = new skin('admin/info_pages');
                $page = '';
                $updateSettings = new updateSettings();
                $updateSettings->db = $db;

                if(isset($_GET['id'])) {
                    $TMPL['show'] = '';
                    $TMPL['btn_name'] = $LNG['save_changes'];

                    if(!empty($_POST)) {
                        $TMPL['message'] = $updateSettings->createInfoPage($_POST, 1);
                    }

                    $info_page = $db->query(sprintf("SELECT * FROM `info_pages` WHERE `id` = '%s'", $db->real_escape_string($_GET['id'])));

                    $row = $info_page->fetch_assoc();
                    $row['content_parsed'] = skin::parse($row['content']);
                    $TMPL['page'] = '<div class="message-top message-no-avatar"><div class="message-author"><a href="' . permalink($CONF['url'] . '/index.php?a=info&b=' . $row['url']) . '" target="_blank">' . skin::parse($row['title']) . '</a></div><div class="message-time">' . ((strlen($row['content_parsed']) > 65) ? substr(strip_tags($row['content_parsed']), 0, 65) . '...' : strip_tags($row['content_parsed'])) . '</div></div>';

                    $TMPL['form'] = '&id=' . $row['id'];
                    $TMPL['current_id'] = $row['id'];
                    $TMPL['current_title'] = $row['title'];
                    $TMPL['current_url'] = $row['url'];
                    $TMPL['current_content'] = $row['content'];
                    if($row['public']) {
                        $TMPL['ppon'] = ' selected="selected"';
                    } else {
                        $TMPL['ppoff'] = ' selected="selected"';
                    }
                } else {
                    $TMPL['show'] = ' style="display: none;"';
                    $TMPL['btn_name'] = $LNG['create_page'];

                    if(!empty($_POST)) {
                        $TMPL['message'] = $updateSettings->createInfoPage($_POST, 0);

                        $TMPL['current_title'] = $_POST['page_title'];
                        $TMPL['current_url'] = $_POST['page_url'];
                        $TMPL['current_content'] = $_POST['page_content'];
                        if($_POST['page_public']) {
                            $TMPL['ppon'] = ' selected="selected"';
                        } else {
                            $TMPL['ppoff'] = ' selected="selected"';
                        }
                    }

                    if(isset($_GET['delete']) && $_GET['delete'] && $_GET['token_id'] == $_SESSION['token_id']) {
                        if($updateSettings->deleteInfoPage($_GET['delete'])) {
                            $TMPL['message'] = notificationBox('success', sprintf($LNG['page_deleted'], skin::parse($_GET['deleted'])));
                        }
                    }

                    $pages = $updateSettings->getInfoPages();

                    $TMPL['pages_list'] = $pages;
                }
            } elseif($_GET['b'] == 'languages' && isset($user['user_group']) == false) {
                $skin = new skin('admin/languages');
                $page = '';

                // Get the software's info
                include(__DIR__ .'/../info.php');
                $TMPL['soft_url'] = $url;

                $updateSettings = new updateSettings();
                $updateSettings->db = $db;

                $language = $updateSettings->getLanguages();

                $TMPL['languages_list'] = $language[0];

                if(isset($_GET['language'])) {
                    // If language is in array
                    if(in_array($_GET['language'], $language[1])) {
                        $updated = $updateSettings->query_array('settings', array('language' => $_GET['language'], 'token_id' => $_GET['token_id']));
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=languages");
                    }
                }
            } elseif($_GET['b'] == 'plugins' && isset($user['user_group']) == false) {
                global $plugins;

                // Get the software's info
                include(__DIR__ .'/../info.php');
                $TMPL['soft_url'] = $url;

                // Get the current active plugins
                foreach($plugins as $currplugin) {
                    $active[] = $currplugin['name'];
                }

                $plugin = isset($_GET['settings']) ? $_GET['settings'] : null;
                $fp = __DIR__ . '/../' . $CONF['plugin_path'] . '/' . $plugin . '/';

                $TMPL['settings'] = $TMPL['message'] = '';
                // If the plugin exists and has a settings page
                if(isset($plugin) && in_array($plugin, $active) && file_exists($fp . $plugin . '_settings.php')) {
                    $skin = new skin('admin/plugin_settings');
                    $page = '';

                    // Get the plugin info
                    require_once($fp . 'info.php');

                    $TMPL['plugin'] = '<div class="message-avatar"><img src="' . $CONF['url'] . '/' . $CONF['plugin_path'] . '/' . $plugin . '/icon.png"></div><div class="message-top"><div class="message-author"><a href="' . $url . '" target="_blank" title="' . $LNG['author_title'] . '">' . $name . '</a> ' . $version . '</div><div class="message-time">' . $LNG['by'] . ': <a href="' . $url . '" target="_blank" title="' . $LNG['author_title'] . '">' . $author . '</a></div></div>';

                    // Get the plugin's settings page
                    require_once($fp . $plugin . '_settings.php');
                    $TMPL['settings'] .= call_user_func($plugin . '_settings');

                    // If a post request has been sent with a valid token
                    if(!empty($_POST) && $_POST['token_id'] == $_SESSION['token_id']) {
                        $updated = call_user_func($plugin . '_save', $_POST);
                        // If the plugin has successfully saved an action
                        if($updated) {
                            header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=plugins&settings=" . $plugin . "&m=s");
                        } else {
                            header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=plugins&settings=" . $plugin . "&m=i");
                        }
                    }

                    if(isset($_GET['m']) && $_GET['m'] == 's') {
                        $TMPL['message'] .= notificationBox('success', $LNG['settings_saved']);
                    } elseif(isset($_GET['m']) && $_GET['m'] == 'i') {
                        $TMPL['message'] .= notificationBox('info', $LNG['nothing_changed']);
                    }
                } else {
                    $skin = new skin('admin/plugins');
                    $page = '';
                    $updateSettings = new updateSettings();
                    $updateSettings->db = $db;

                    $pgins = $updateSettings->getPlugins();

                    if(isset($_GET['plugin']) && isset($_GET['plugin_type']) && in_array($_GET['plugin'], $pgins[1])) {
                        $updateSettings->activatePlugin($_GET['plugin'], ['type' => $_GET['plugin_type'], 'priority' => $_GET['plugin_priority']]);
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=plugins");
                    }

                    $pgins = $updateSettings->getPlugins();

                    $TMPL['plugins_list'] = $pgins[0];
                }
            } elseif($_GET['b'] == 'manage_reports') {
                $skin = new skin('admin/manage_reports');
                $page = '';
                list($TMPL['total_reports'], $TMPL['pending_reports'], $TMPL['safe_reports'], $TMPL['deleted_reports']) = admin_stats($db, 2);

                $manageReports = new manageReports();
                $manageReports->db = $db;
                $manageReports->url = $CONF['url'];
                $manageReports->per_page = $settings['uperpage'];

                // Save the array returned into a list
                $TMPL['reports'] = $manageReports->getReports(0);
            } elseif($_GET['b'] == 'manage_pages') {
                $feed = new feed();
                $feed->db = $db;
                $feed->url = $CONF['url'];

                if(isset($_GET['deleted'])) {
                    $TMPL['message'] = notificationBox('success', sprintf($LNG['page_deleted'], htmlspecialchars($_GET['deleted'], ENT_QUOTES, 'UTF-8')));
                }

                if(!empty($_GET['c'])) {
                    $skin = new skin('admin/edit_page');
                    $page = '';
                    $feed->page_data = $feed->pageData($_GET['c']);
                    $feed->id = $feed->page_data['by'];

                    $updateUserSettings = new updateUserSettings();
                    $updateUserSettings->db = $db;
                    $updateUserSettings->id = $feed->page_data['by'];
                    $userSettings = $updateUserSettings->getSettings();

                    if(!empty($_POST)) {
                        if(isset($user['user_group']) && $user['user_group'] == 1 && $userSettings['user_group'] == 1) {
                            unset($_POST);
                        }
                        $message = $feed->createPage($_POST ?? [], 1);
                        $feed->page_data = $feed->pageData($_GET['c']);

                        // If there's an error during page validation
                        if($message[0]) {
                            $TMPL['message'] = notificationBox('error', $message[1]);
                        } else {
                            if($message[1]) {
                                $TMPL['message'] = notificationBox('success', $LNG['settings_saved']);
                            } else {
                                $TMPL['message'] = notificationBox('info', $LNG['nothing_changed']);
                            }
                        }
                    }
                    if(!empty($feed->page_data)) {
                        // The disabled attribute for inputs
                        $TMPL['disabled'] = ' disabled';
                        $TMPL['id'] = $feed->page_data['id'];
                        $TMPL['current_name'] = $feed->page_data['name'];
                        $TMPL['current_title'] = $feed->page_data['title'];
                        $TMPL['current_desc'] = $feed->page_data['description'];
                        $TMPL['current_website'] = $feed->page_data['website'];
                        $TMPL['current_phone'] = $feed->page_data['phone'];
                        $TMPL['current_address'] = $feed->page_data['address'];
                        $TMPL['page_' . (isset($_POST['page_category']) ? $_POST['page_category'] : $feed->page_data['category'])] = ' selected="selected"';
                        if($feed->page_data['verified']) {
                            $TMPL['on_v'] = ' selected="selected"';
                        } else {
                            $TMPL['off_v'] = ' selected="selected"';
                        }

                        // Get the page author
                        $author = $feed->profileData(null, $feed->page_data['by']);
                        $TMPL['author'] = $author['username'];

                        $TMPL['page'] = '<div class="message-avatar"><a href="' . $CONF['url'] . '/index.php?a=page&name=' . $feed->page_data['name'] . '" rel="loadpage"><img src="' . $CONF['url'] . '/image.php?src=' . $feed->page_data['image'] . '&t=a&w=48&h=48"></a></div><div class="message-top"><div class="message-author"><a href="' . $CONF['url'] . '/index.php?a=page&name=' . $feed->page_data['name'] . '" rel="loadpage">' . $feed->page_data['name'] . '</a></div><div class="message-time">' . $feed->page_data['likes'] . ' ' . $LNG['likes'] . '</div></div>';
                    } else {
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=manage_pages&m=page_not_exists");
                    }
                } else {
                    $skin = new skin('admin/manage_pages');
                    $page = '';

                    // Remove a page
                    if(isset($_GET['delete']) && !empty($_GET['delete'])) {
                        $page_info = $feed->pageData(null, $_GET['delete']);
                        $feed->id = $page_info['by'];

                        $updateUserSettings = new updateUserSettings();
                        $updateUserSettings->db = $db;
                        $updateUserSettings->id = $page_info['by'];
                        $userSettings = $updateUserSettings->getSettings();

                        if(isset($user['user_group']) && $user['user_group'] == 1 && $userSettings['user_group'] == 0 || isset($user['user_group']) == false) {
                            $TMPL['message'] = $feed->deletePage($_GET['delete'], null, 1);
                        }
                    }

                    $feed->per_page = $settings['uperpage'];
                    $TMPL['pages'] = $feed->getPages(0, 0);

                    if(isset($_GET['m']) && $_GET['m'] == 'page_not_exists') {
                        $TMPL['message'] = notificationBox('error', $LNG['page_not_exists']);
                    }
                }
            } elseif($_GET['b'] == 'manage_groups') {
                $feed = new feed();
                $feed->db = $db;
                $feed->url = $CONF['url'];

                if(isset($_GET['deleted'])) {
                    $TMPL['message'] = notificationBox('success', sprintf($LNG['group_deleted'], htmlspecialchars($_GET['deleted'], ENT_QUOTES, 'UTF-8')));
                }

                if(!empty($_GET['c'])) {
                    $skin = new skin('admin/edit_group');
                    $page = '';
                    $feed->group_data = $feed->groupData($_GET['c']);

                    $group = $feed->groupOwner($feed->group_data['id']);

                    $updateUserSettings = new updateUserSettings();
                    $updateUserSettings->db = $db;
                    $updateUserSettings->id = $group['user'];
                    $userSettings = $updateUserSettings->getSettings();

                    if(!empty($_POST)) {
                        if(isset($user['user_group']) && $user['user_group'] == 1 && $userSettings['user_group'] == 1) {
                            unset($_POST);
                        }

                        $message = $feed->createGroup($_POST ?? [], 1);
                        $feed->group_data = $feed->groupData($_GET['c']);

                        // If there's an error during group validation
                        if($message[0]) {
                            $TMPL['message'] = notificationBox('error', $message[1]);
                        } else {
                            if($message[1]) {
                                $TMPL['message'] = notificationBox('success', $LNG['settings_saved']);
                            } else {
                                $TMPL['message'] = notificationBox('info', $LNG['nothing_changed']);
                            }
                        }
                    }
                    if(!empty($feed->group_data)) {
                        // The disabled attribute for inputs
                        $TMPL['disabled'] = ' disabled';
                        $TMPL['id'] = $feed->group_data['id'];
                        $TMPL['current_name'] = $feed->group_data['name'];
                        $TMPL['current_title'] = $feed->group_data['title'];
                        $TMPL['current_desc'] = $feed->group_data['description'];
                        if($feed->group_data['privacy'] == 1) {
                            $TMPL['privacy_private'] = ' selected="selected"';
                        } else {
                            $TMPL['privacy_public'] = ' selected="selected"';
                        }
                        if($feed->group_data['posts'] == 1) {
                            $TMPL['posts_admins'] = ' selected="selected"';
                        } else {
                            $TMPL['posts_members'] = ' selected="selected"';
                        }
                        $TMPL['group'] = '<div class="message-avatar"><a href="' . $CONF['url'] . '/index.php?a=group&name=' . $feed->group_data['name'] . '" rel="loadpage"><img src="' . $CONF['url'] . '/image.php?src=' . $feed->group_data['cover'] . '&t=c&w=48&h=48"></a></div><div class="message-top"><div class="message-author"><a href="' . $CONF['url'] . '/index.php?a=group&name=' . $feed->group_data['name'] . '" rel="loadpage">' . $feed->group_data['name'] . '</a></div><div class="message-time">' . sprintf($LNG['x_members'], $feed->group_data['members']) . '</div></div>';
                    } else {
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=manage_groups&m=group_not_exists");
                    }
                } else {
                    $skin = new skin('admin/manage_groups');
                    $page = '';

                    // Remove a group
                    if(!empty($_GET['delete'])) {
                        $group = $feed->groupOwner($_GET['delete']);
                        $feed->id = $group['user'];

                        $updateUserSettings = new updateUserSettings();
                        $updateUserSettings->db = $db;
                        $updateUserSettings->id = $group['user'];
                        $userSettings = $updateUserSettings->getSettings();

                        // Prevent moderators from editing/deleting other moderators groups.
                        if(isset($user['user_group']) && $user['user_group'] == 1 && $userSettings['user_group'] == 0 || isset($user['user_group']) == false) {
                            $TMPL['message'] = $feed->deleteGroup($_GET['delete'], null, 1);
                        }
                    }

                    $feed->per_page = $settings['uperpage'];
                    $TMPL['groups'] = $feed->getGroups(0, 0);

                    if(isset($_GET['m']) && $_GET['m'] == 'group_not_exists') {
                        $TMPL['message'] = notificationBox('error', $LNG['group_not_exists']);
                    }
                }
            } elseif($_GET['b'] == 'users') {
               
                if(isset($_GET['filter']) && array_key_exists($_GET['filter'], $admin_mmen_pages) == false) {
                    header("Location: ".$CONF['url']."/index.php?a=global_admin");
                    exit();
                }

                $manageUsers = new manageUsers();
                $manageUsers->db = $db;
                $manageUsers->url = $CONF['url'];

               $per_page = $manageUsers->per_page = $settings['record_per_page'];

                if(!isset($_GET['e'])) {
                    $skin = new skin('admin/manage_users');
                    $page = '';

                    // Save the array returned into a list
                    if(isset($_GET['filter']) && $_GET['filter'] == 'pending') {
                        $TMPL['users'] = $manageUsers->getmmen(0, 1,$per_page);
                    } elseif(isset($_GET['filter']) && $_GET['filter'] == 'active') {
                        $TMPL['users'] = $manageUsers->getmmen(0, 2,$per_page);
                    } elseif(isset($_GET['filter']) && $_GET['filter'] == 'reject') {
                        $TMPL['users'] = $manageUsers->getmmen(0, 3,$per_page);
                    } elseif(isset($_GET['filter']) && $_GET['filter'] == 're-submit') {
                        $TMPL['users'] = $manageUsers->getmmen(0, 4,$per_page);
                    } elseif(isset($_GET['filter']) && $_GET['filter'] == 'subs_expired') {
                        $TMPL['users'] = $manageUsers->getmmen(0, 5,$per_page);
                    }elseif(isset($_GET['filter']) && $_GET['filter'] == 'temp_blocked') {
                        $TMPL['users'] = $manageUsers->getmmen(0, 6,$per_page);
                    }else {
                        $TMPL['users'] = $manageUsers->getmmen(0, 0,$per_page);
                    }
                } else {
                    $skin = new skin('admin/edit_user');
                    $page = $TMPL['message'] = '';
                    $getUser = $manageUsers->getUser($_GET['e'] ?? null, $_GET['ef'] ?? null);
                    if(!$getUser) {
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=users&m=profile_not_exists");
                    }
                    // // Create the class instance
                    // $updateUserSettings = new updateUserSettings();
                    // $updateUserSettings->db = $db;
                    // $updateUserSettings->id = $getUser['idu'];
                    // $updateUserSettings->title = $settings['title'];
                    // $updateUserSettings->email = $CONF['email'];

                    // $feed = new feed();
                    // $feed->db = $db;
                    // $feed->id = $updateUserSettings->id;
                   
                    // $userSettings =  getUser($getUser['id']);

                    if(!empty($_POST)) {
                        $TMPL['message'] .= $updateUserSettings->query_array('users', array_map("strip_tags_array", $_POST ?? []));

                        // Re-update the information
                        $userSettings = $updateUserSettings->getSettings();
                    }

                    // $TMPL['countries'] = countries(1, $userSettings['country']);

                    if(isset($getUser['dob'])) {
                        $date = explode('-', $getUser['dob']);
                    } else {
                        $date = [0, 0, 0];
                    }


                    $TMPL['years'] = generateDateForm(0, $date[0]);
                    $TMPL['months'] = generateDateForm(1, $date[1]);
                    $TMPL['days'] = generateDateForm(2, $date[2]);

                    $TMPL['profile_pic'] = "<img src='".permalink($CONF['url'].'/image.php?t=mm&w=100&h=100&src='.$getUser['profile_pic'].'')."'>";

                    $TMPL['username'] = $getUser['uname'];
                    $TMPL['id'] = $getUser['id'];
                    $TMPL['currentFirstName'] = $getUser['fname'];
                    $TMPL['currentLastName'] = $getUser['last_name'];
                    $TMPL['current_p_num'] = $getUser['p_number'];
                    $TMPL['currentLocality'] = $getUser['locality'];
                    
                    $TMPL['currentDairy_name'] = $getUser['dairy_name'];

                    $TMPL['currentstate_name'] = $getUser['state_name'];
                    $TMPL['currentstate_id'] = $getUser['state'];

                    $TMPL['currentdistrict_name'] = $getUser['district_name'];
                    $TMPL['currentdistrict_id'] = $getUser['district'];

                    $TMPL['currentsub_district_name'] = $getUser['sub_district_name'];
                    $TMPL['currentsub_district_id'] = $getUser['sub_district'];

                    $TMPL['join_date'] = $getUser['joined'];
                    $TMPL['state'] = load_state($getUser['state']);
                    $cities = new load_cites();
                    $cities->db = $db;
                    $TMPL['district'] = $cities->load_district($getUser['state'],$getUser['district']);
                    $TMPL['sub_district'] = $cities->load_sub_district($getUser['district'],$getUser['sub_district']);
                    $TMPL['pincode'] = $getUser['pincode'];

                    if($getUser['gender'] == '0') {
                        $TMPL['ngender'] = 'selected="selected"';
                    } elseif($getUser['gender'] == '1') {
                        $TMPL['mgender'] = 'selected="selected"';
                    } else {
                        $TMPL['fgender'] = 'selected="selected"';
                    }

                    if($getUser['area']== '1'){
                        $TMPL['rural_area'] = 'selected="selected"';
                    }elseif($getUser['area']== '2'){
                        $TMPL['urban_area'] = 'selected="selected"';
                    }else{
                        $TMPL['no_area'] = 'selected="selected"';
                    }

                    if($getUser['distribute_type']== '1'){
                        $TMPL['d2d_service'] = 'selected="selected"';
                    }elseif($getUser['distribute_type']== '2'){
                        $TMPL['dairy_located'] = 'selected="selected"';
                    }elseif($getUser['distribute_type']== '3'){
                        $TMPL['customer_choice'] = 'selected="selected"';
                    }else{
                        $TMPL['no_distribute'] = 'selected="selected"';
                    }

                    if($getUser['acc_status'] == '0') {
                        $TMPL['pending'] = 'selected="selected"';
                    } elseif($getUser['acc_status'] == '1') {
                        $TMPL['active'] = 'selected="selected"';
                    } elseif($getUser['acc_status'] == '2') {
                        $TMPL['reject'] = 'selected="selected"';
                    } elseif($getUser['acc_status'] == '3') {
                        $TMPL['re-submit'] = 'selected="selected"';
                    } elseif($getUser['acc_status'] == '4') {
                        $TMPL['subs_expired'] = 'selected="selected"';
                    }  elseif($getUser['acc_status'] == '5') {
                        $TMPL['temp_block'] = 'selected="selected"';
                    }

                    if($getUser['subs_type'] == '0') {
                        $TMPL['not_subscribe'] = 'selected="selected"';
                    } elseif($getUser['subs_type'] == '1') {
                        $TMPL['subscribe'] = 'selected="selected"';
                    } elseif($getUser['subs_type'] == '2') {
                        $TMPL['subs_free_trial'] = 'selected="selected"';
                    }elseif($getUser['subs_type'] == '3') {
                        $TMPL['subs_free'] = 'selected="selected"';
                    }

                   $TMPL['subs_date'] = date('d-m-Y h:i:s A', strtotime($getUser['subs_time']));


                    // $TMPL['user'] = '<div class="message-avatar" id="avatar' . $getUser['id'] . '"><a href="' . $CONF['url'] . '/index.php?a=profile&u=' . $getUser['uname'] . '" rel="loadpage"><img src="' . $CONF['url'] . '/image.php?src=' . $getUser['profile_pic'] . '&t=mm&w=100&h=100"></a></div><div class="message-top"><div class="message-author"><a href="' . $CONF['url'] . '/index.php?a=profile&u=' . $getUser['uname'] . '" rel="loadpage">' . $getUser['uname'] . '</a></div><div class="message-time">' . $getUser['p_number'] . '</div></div>';
                    $TMPL['user'] = '<div class="message-author"><a href="' . $CONF['url'] . '/index.php?a=profile&u=' . $getUser['uname'] . '" rel="loadpage">' . $getUser['uname'] . '</a></div><div class="message-time">' . $getUser['p_number'] . '</div>';

                    $TMPL['message'] .= ($userSettings['suspended'] ? notificationBox('error', $LNG['account_suspended']) : '');
                }

                // If GET delete is set, delete the user
                if(isset($_GET['delete']) && $_GET['token_id'] == $_SESSION['token_id']) {
                    // Create the class instance
                    $updateUserSettings = new updateUserSettings();
                    $updateUserSettings->db = $db;
                    $updateUserSettings->id = $_GET['delete'];
                    $userSettings = $updateUserSettings->getSettings();

                    // Prevent moderators from deleting other moderators
                    if((isset($user['user_group']) && $user['user_group'] == 1 && $userSettings['user_group'] == 0) || isset($user['user_group']) == false) {
                        // Delete the profile images
                        deleteImages(array($userSettings['image']), 1);
                        deleteImages(array($userSettings['cover']), 0);

                        $manageUsers->deleteUser($_GET['delete']);
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=users&m=" . $_GET['deleted']);
                    } else {
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=users");
                    }
                }

                if(isset($_GET['m']) && $_GET['m'] == 'profile_not_exists') {
                    $TMPL['message'] = notificationBox('error', $LNG['profile_not_exists']);
                } elseif(isset($_GET['m']) && !empty($_GET['m'])) {
                    $TMPL['message'] = notificationBox('success', sprintf($LNG['user_has_been_deleted'], htmlspecialchars($_GET['m'], ENT_QUOTES, 'UTF-8')));
                }
            } elseif($_GET['b'] == 'manage_ads' && isset($user['user_group']) == false) {
                $skin = new skin('admin/manage_ads');
                $page = '';

                $TMPL['ad1'] = $settings['ad1'];
                $TMPL['ad2'] = $settings['ad2'];
                $TMPL['ad3'] = $settings['ad3'];
                $TMPL['ad4'] = $settings['ad4'];
                $TMPL['ad5'] = $settings['ad5'];
                $TMPL['ad6'] = $settings['ad6'];
                $TMPL['ad7'] = $settings['ad7'];
                if(!empty($_POST)) {
                    // Unset the submit array element
                    $updateSettings = new updateSettings();
                    $updateSettings->db = $db;
                    $updated = $updateSettings->query_array('settings', $_POST);
                    if($updated == 1) {
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=manage_ads&m=s");
                    } else {
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=manage_ads&m=i");
                    }
                }
                if(isset($_GET['m']) && $_GET['m'] == 's') {
                    $TMPL['message'] = notificationBox('success', $LNG['settings_saved']);
                } elseif(isset($_GET['m']) && $_GET['m'] == 'i') {
                    $TMPL['message'] = notificationBox('info', $LNG['nothing_saved']);
                }
            } elseif($_GET['b'] == 'site_settings' && isset($user['user_group']) == false) {
                $skin = new skin('admin/site_settings');
                $page = '';

                $TMPL['current_title'] = $settings['title'];
                $TMPL['language'] = $settings['language'];
                $TMPL['captcha'] = $settings['captcha'];
                $TMPL['name'] = $settings['name'];
                $TMPL['uname'] = $settings['uname'];
                $TMPL['pincode'] = $settings['pincode'];
                $TMPL['locality'] = $settings['locality'];
                $TMPL['aperip'] = $settings['aperip'];
                $TMPL['time_zone'] = $settings['time_zone'];
               
                $TMPL['smtp_host'] = $settings['smtp_host'];
                $TMPL['smtp_port'] = $settings['smtp_port'];
                $TMPL['smtp_username'] = $settings['smtp_username'];
                $TMPL['smtp_password'] = $settings['smtp_password'];
                $TMPL['email_provider'] = $settings['email_provider'];
                
                $TMPL['d_rate_minimum_setting'] = $settings['d_rate_minimum'];
                $TMPL['d_rate_maximum_setting'] = $settings['d_rate_maximum'];

                $TMPL['f_rate_minimum_setting'] = $settings['f_rate_minimum'];
                $TMPL['f_rate_maximum_setting'] = $settings['f_rate_maximum'];

                $TMPL['mf_minimum_setting'] = $settings['mf_minimum'];
                $TMPL['mf_maximum_setting'] = $settings['mf_maximum'];

                $TMPL['weight_minimum_setting'] = $settings['weight_minimum'];
                $TMPL['weight_maximum_setting'] = $settings['weight_maximum'];

                $TMPL['record_per_page'] = $settings['record_per_page'];
               

                if($settings['captcha'] == '1') {
                    $TMPL['on'] = 'selected="selected"';
                } else {
                    $TMPL['off'] = 'selected="selected"';
                }

                if($settings['permalinks'] == '1') {
                    $TMPL['permaon'] = 'selected="selected"';
                } else {
                    $TMPL['permaoff'] = 'selected="selected"';
                }

                if($settings['mail'] == '1') {
                    $TMPL['mailon'] = 'selected="selected"';
                } else {
                    $TMPL['mailoff'] = 'selected="selected"';
                }


                if($settings['smtp_email'] == '1') {
                    $TMPL['smtpon'] = 'selected="selected"';
                } else {
                    $TMPL['smtpoff'] = 'selected="selected"';
                }

                if($settings['smtp_auth'] == '1') {
                    $TMPL['smtpaon'] = 'selected="selected"';
                } else {
                    $TMPL['smtpaoff'] = 'selected="selected"';
                }

                if(empty($settings['smtp_secure'])) {
                    $TMPL['ssoff'] = 'selected="selected"';
                } elseif($settings['smtp_secure'] == 'tls') {
                    $TMPL['sstls'] = 'selected="selected"';
                } elseif($settings['smtp_secure'] == 'ssl') {
                    $TMPL['ssssl'] = 'selected="selected"';
                }
				
				$TMPL['timezone_list'] = generateTimezoneForm($settings['timezone']);

                if(isset($_POST['submit'])) {
                    // Unset the submit array element
                    unset($_POST['submit']);
                    $updateSettings = new updateSettings();
                    $updateSettings->db = $db;

                    // Transform the user's value in the appropriate format
                    $_POST['intervalm'] = $_POST['intervalm'] * 1000;
                    $_POST['intervaln'] = $_POST['intervaln'] * 1000;
                    $_POST['size'] = ($_POST['size'] * 1024) * 1024;
                    $_POST['sizemsg'] = ($_POST['sizemsg'] * 1024) * 1024;

                    $updated = $updateSettings->query_array('settings', $_POST);
                    if($updated == 1) {
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=site_settings&m=s");
                    } else {
                        header("Location: " . $CONF['url'] . "/index.php?a=global_admin&b=site_settings&m=i");
                    }
                }

                $TMPL['message'] = '';
                if(isset($_GET['m']) && $_GET['m'] == 's') {
                    $TMPL['message'] .= notificationBox('success', $LNG['settings_saved']);
                } elseif(isset($_GET['m']) && $_GET['m'] == 'i') {
                    $TMPL['message'] .= notificationBox('info', $LNG['nothing_changed']);
                }

                if(!extension_loaded('openssl') && ($settings['fbapp'] || $settings['smtp_email'])) {
                    $TMPL['message'] .= notificationBox('error', $LNG['openssl_error']);
                }
                if(!function_exists('curl_exec')) {
                    $TMPL['message'] .= notificationBox('info', $LNG['curl_error']);
                }
            }
        } elseif(isset($_GET['b']) && in_array($_GET['b'], $admin_pages) == false) {
            header("Location: ".$CONF['url']."/index.php?a=global_admin");
            exit();
        } else {
			$skin = new skin('admin/dashboard'); $page = '';

			// Get the Today's Activity
			list(
			$TMPL['mmen_today'], $TMPL['mmen_yesterday'], $TMPL['mmen_two_days'], $TMPL['mmen_three_days'], $TMPL['mmen_four_days'], $TMPL['mmen_five_days'], $TMPL['mmen_six_days'],
			$TMPL['m_customers_today'], $TMPL['m_customers_yesterday'], $TMPL['m_customers_two_days'], $TMPL['m_customers_three_days'], $TMPL['m_customers_four_days'], $TMPL['m_customers_five_days'], $TMPL['m_customers_six_days'],
			$TMPL['bought_milk_today'], $TMPL['bought_milk_yesterday'], $TMPL['bought_milk_two_days'], $TMPL['bought_milk_three_days'], $TMPL['bought_milk_four_days'], $TMPL['bought_milk_five_days'], $TMPL['bought_milk_six_days'],
			$TMPL['sold_milk_today'], $TMPL['sold_milk_yesterday'], $TMPL['sold_milk_two_days'], $TMPL['sold_milk_three_days'], $TMPL['sold_milk_four_days'], $TMPL['sold_milk_five_days'], $TMPL['sold_milk_six_days'],
			$TMPL['update_milk_today'], $TMPL['update_milk_yesterday'], $TMPL['update_milk_two_days'], $TMPL['update_milk_three_days'], $TMPL['update_milk_four_days'], $TMPL['update_milk_five_days'], $TMPL['update_milk_six_days'],
			$TMPL['cleared_today'], $TMPL['cleared_yesterday'], $TMPL['cleared_two_days'], $TMPL['cleared_three_days'], $TMPL['cleared_four_days'], $TMPL['cleared_five_days'], $TMPL['cleared_six_days'],
			$TMPL['bought_milk_mf_today'], $TMPL['bought_milk_mf_yesterday'], $TMPL['bought_milk_mf_two_days'], $TMPL['bought_milk_mf_three_days'], $TMPL['bought_milk_mf_four_days'], $TMPL['bought_milk_mf_five_days'], $TMPL['bought_milk_mf_six_days'],
			$TMPL['bought_milk_dm_today'], $TMPL['bought_milk_dm_yesterday'], $TMPL['bought_milk_dm_two_days'], $TMPL['bought_milk_dm_three_days'], $TMPL['bought_milk_dm_four_days'], $TMPL['bought_milk_dm_five_days'], $TMPL['bought_milk_dm_six_days'],
			$TMPL['sold_milk_mf_today'], $TMPL['sold_milk_mf_yesterday'], $TMPL['sold_milk_mf_two_days'], $TMPL['sold_milk_mf_three_days'], $TMPL['sold_milk_mf_four_days'], $TMPL['sold_milk_mf_five_days'], $TMPL['sold_milk_mf_six_days'],
			$TMPL['sold_milk_dm_today'], $TMPL['sold_milk_dm_yesterday'], $TMPL['sold_milk_dm_two_days'], $TMPL['sold_milk_dm_three_days'], $TMPL['sold_milk_dm_four_days'], $TMPL['sold_milk_dm_five_days'], $TMPL['sold_milk_dm_six_days'],
			$TMPL['online_users']) = admin_stats($db, 1, array('conline' => $settings['conline']));
			
			// Stats to generate the graphs for
			$stats = array('mmen', 'm_customers', 'bought_milk', 'sold_milk', 'update_milk', 'total_milk', 'bought_mf','bought_dm','sold_mf','sold_dm');

			foreach($stats as $val) {
			    $TMPL[$val.'_stats'] = '';

				// Get the stats values
				$stats_days = array($TMPL[$val.'_today'], $TMPL[$val.'_yesterday'], $TMPL[$val.'_two_days'], $TMPL[$val.'_three_days'], $TMPL[$val.'_four_days'], $TMPL[$val.'_five_days'], $TMPL[$val.'_six_days']);
				
				// Get the maximum value in a day
				$val_max = max($stats_days);
				
				$i = 0;
				foreach($stats_days as $value) {
					// Get the dates
					$date = date('Y-m-d', strtotime("-$i days", strtotime(date('Y-m-d'))));
					$date = explode('-', $date);
					
					$month = intval($date[1]);
					
					// Calculate the percentage
                    if($val_max > 0) {
                        $percentage = ($value * 100) / $val_max;
                    } else {
                        $percentage = 0;
                    }

					$TMPL[$val.'_stats'] .= '<li title="'.$LNG["month_$month"].' '.$date[2].': '.$value.' '.$LNG[$val].'"><span style="height:'.$percentage.'%"></span></li>';
					$i++;
				}
			}
			
			$TMPL['mmen_percentage'] = percentage($TMPL['mmen_today'], $TMPL['mmen_yesterday']);
			$TMPL['m_customers_percentage'] = percentage($TMPL['m_customers_today'], $TMPL['m_customers_yesterday']);
			$TMPL['bought_milk_percentage'] = percentage($TMPL['bought_milk_today'], $TMPL['bought_milk_yesterday']);
			$TMPL['sold_milk_percentage'] = percentage($TMPL['sold_milk_today'], $TMPL['sold_milk_yesterday']);
			$TMPL['update_milk_percentage'] = percentage($TMPL['update_milk_today'], $TMPL['update_milk_yesterday']);
			$TMPL['cleared_percentage'] = percentage($TMPL['cleared_today'], $TMPL['cleared_yesterday']);
			$TMPL['bought_milk_mf_percentage'] = percentage($TMPL['bought_milk_mf_today'], $TMPL['bought_milk_mf_yesterday']);
			$TMPL['bought_milk_dm_percentage'] = percentage($TMPL['bought_milk_dm_today'], $TMPL['bought_milk_dm_yesterday']);
			$TMPL['sold_milk_mf_percentage'] = percentage($TMPL['sold_milk_mf_today'], $TMPL['sold_milk_mf_yesterday']);
			$TMPL['sold_milk_dm_percentage'] = percentage($TMPL['sold_milk_dm_today'], $TMPL['sold_milk_dm_yesterday']);
			
			// Count the enabled plugins
			// $countPlugins = $db->query("SELECT * FROM `plugins`");
			
			// Get the current theme's info
			include(__DIR__ .'/../'.$CONF['theme_path'].'/'.$CONF['theme_name'].'/info.php');
			$TMPL['site_loaded'] = sprintf($LNG['site_loaded'], $CONF['url'].'/index.php?a=global_admin'.(isset($user['user_group']) && $user['user_group'] ? '' : '&b=themes'), $name, $CONF['url'].'/index.php?a=global_admin'.(isset($user['user_group']) && $user['user_group'] ? '' : '&b=plugins'), $countPlugins->num_rows);
			
			// Get the software's info
			include(__DIR__ .'/../info.php');
			$TMPL['site_version'] = sprintf($LNG['site_version'], $url, $name, $version);

            if (isset($_SESSION['message'])) {
                $TMPL['message'] = $_SESSION['message'];
                unset($_SESSION['message']);
            }

			$TMPL['soft_url'] = $url;
		}
		
		$page .= $skin->make();
		$TMPL = $TMPL_old; unset($TMPL_old);
		$TMPL['settings'] = $page;
		
		if(isset($_GET['logout'])) {
			$admin->logOut();

            // If the user is a moderator
            if(isset($user['user_group']) && $user['user_group'] == 1) {
                $logout = new User;
                $logout->db = $db;
                $logout->uname = $user['uname'];
                $logout->logOut(true);
            }
			header("Location: ".$CONF['url']."/index.php?a=global_admin");
		}
	} else {
		// Set the content to false, change the $skin to log-in.
		$content = false;
	}
	
	// Bold the current link
	if(isset($_GET['b']) && in_array($_GET['b'], $admin_pages)) {
		$LNG["admin_menu_{$_GET['b']}"] = $LNG["admin_menu_{$_GET['b']}"];
		$TMPL['welcome'] = $LNG["admin_ttl_{$_GET['b']}"];
	} else {
		$LNG["admin_menu_dashboard"] = $LNG["admin_menu_dashboard"];
		$TMPL['welcome'] = $LNG["admin_ttl_dashboard"];
	}

	$menu = array(	''											=> array('admin_menu_dashboard', '', 'dashboard'),
					'&b=site_settings'							=> array('admin_menu_site_settings', '', 'settings'),
					// '&b=themes' 								=> array('admin_menu_themes', '', 'themes'),
					// '&b=plugins'								=> array('admin_menu_plugins', '', 'plugins'),
					// '&b=languages'								=> array('admin_menu_languages', '', 'languages'),
					'&b=stats'									=> array('admin_menu_stats', '', 'stats'),
					'&b=users'									=> array('admin_menu_users', $admin_mmen_pages, 'users'),
					// '&b=manage_pages'							=> array('admin_menu_manage_pages', '', 'pages'),
					// '&b=manage_groups'							=> array('admin_menu_manage_groups', '', 'groups'),
					//'&b=manage_reports'							=> array('admin_menu_manage_reports', adminMenuCounts($db, 0), 'reports'),
					// '&b=manage_ads'								=> array('admin_menu_manage_ads', '', 'ads'),
					// '&b=info_pages'								=> array('admin_menu_info_pages', '', 'info'),
					// '&b=security'								=> array('admin_menu_security', '', 'security'),
					'&logout'                                  	=> array('admin_menu_logout', '', 'logout'));

	// If the logged-in user is a Moderator, remove menu elements
	if(isset($user['user_group']) && $user['user_group']) {
		unset($menu['&b=site_settings'], $menu['&b=users_settings'], $menu['&b=social'], $menu['&b=themes'], $menu['&b=plugins'], $menu['&b=languages'], $menu['&b=manage_ads'], $menu['&b=info_pages'], $menu['&b=security']);
	}

	$i = 1;
	$TMPL['menu'] = '';
	foreach($menu as $link => $title) {
        
		$class = $collapsed = '';
        if(isset($_GET['b']) && $link == '&b='.$_GET['b'] && in_array($_GET['b'], $admin_pages)) {
            $class = ' sidebar-link-active';
            $ttl = $LNG[$title[0]];
        } elseif(empty($link) && empty($_GET['b'])) {
            $class = ' sidebar-link-active';
            $ttl = $LNG[$title[0]];
        }
		
		$is_menu = (is_array($title[1]) ? 1 : 0);
        
        // if(isset($_GET['filter']) && in_array($_GET['filter'], $admin_mmen_pages)) {
             
        //     $collapsed = ($title[1][$_GET['filter']] ? ' sidebar-link-sub-active' : '');
        // }

        if(!isset($title[3])) {
            $TMPL['menu'] .= '<div class="sidebar-link'.$class.($is_menu ? ' sidebar-link-sub'.$collapsed.'" id="sub-menu'.$i.'"' : '"').'><a '.($is_menu ? 'onclick="adminSubMenu('.$i.')"' : 'href="'.$CONF['url'].'/index.php?a=global_admin'.$link.'"').' '.(($title[0] !== 'admin_menu_logout' && !$is_menu) ? 'rel="loadpage"' : '').'><img src="'.$CONF['url'].'/'.$CONF['theme_url'].'/images/icons/settings/'.$title[2].'.svg">'.$LNG[$title[0]].' '.($title[1] && !$is_menu ? '<span class="admin-notifications-number">'.$title[1].'</span>' : '').'</a></div>';

            // Start the menu's container
            if($is_menu) {
                $TMPL['menu'] .= '<div id="sub-menu-content'.$i.'" class="sub-menu"'.((isset($_GET['filter']) && $title[1][$_GET['filter']]) ? '' : ' style="display: none;"').'>';
                foreach($title[1] as $sub_url => $sub_title) {
                    $class = '';
                    if(isset($_GET['filter']) && $sub_url == $_GET['filter']) {
                        $class = ' sidebar-link-active';
                        $ttl .= ' - '.$LNG['list_'.$_GET['filter']];
                    }
                    $TMPL['menu'] .= '<div class="sidebar-link'.$class.'"><a href="'.$CONF['url'].'/index.php?a=global_admin'.$link.'&filter='.$sub_url.'" rel="loadpage">'.$sub_title.'</a></div>';
                }
                $TMPL['menu'] .= '</div>';
            }

            $i++;
        }
	}

	$TMPL['url'] = $CONF['url'];
	$TMPL['localurl'] = $CONF['url'];
	$TMPL['title'] = $LNG['title_admin'].' - '.(isset($_SESSION['is_admin']) ? $ttl : $LNG['login']).' - '.$settings['title'];
	if($content) {
		$skin = new skin('admin/content');
	} else {
		$skin = new skin('admin/login');
	}
    
	return $skin->make();
}
?>