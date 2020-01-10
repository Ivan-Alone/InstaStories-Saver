<?php
    /*
        InstaStories Program
        Copyright Ivan_Alone, 2018
        GNU General Public License 3
    */
    
    /*  Algorhythm  ---  Start  */
    
	function fillArrayByLines(&$array, $file) {
		$data = file_get_contents($file);
		foreach (explode("\n", $data) as $line) {
			$line = trim($line);

			if (strlen($line) > 0 && strlen($line) <= 30) {
				array_push($array, $line);
			}
		}
	}
	
	function PostQuery($url, $par_array = [], $header_plus = [], $noDecodeJSON = false) {
		global $net;
		return $net->Request([
			CURLOPT_URL => $url,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($par_array)
		], $header_plus, $noDecodeJSON);
	}
	
    echo 'Loading...' . PHP_EOL;
    
    $config = @json_decode(@file_get_contents('config.json'), true);
    
    $save_path = replaceCycle($argv[1] != null && file_exists($argv[1]) ? $argv[1] . '/' : './', '/', 2);
    
    include ('ConsoleGraph.class.php');
    include ('Network.class.php');
    
    define  ('__instagram', $save_path.$config['stories_folder']); 
    define  ('__temp', $save_path.$config['temp_folder']); 
    define  ('__cookie_path', __temp.'/'.$config['cookies_storage']);
	
	@mkdir(__temp);
	
    // Uncomment for activate Fiddler Proxy - useful for debugging
	$net = new Network(__cookie_path/*/, '127.0.0.1:8888'/**/);
	
    $cookies = new CurlCookies(__cookie_path);
    if ($cookies->getValidValue('csrftoken') == null) {
        PostQuery('https://www.instagram.com', array(), getInstagramHeaders());
        $cookies->reload();
    }
    
    $__csrftoken = $cookies->getValidValue('csrftoken');
    
    $loading_sprite = @file_get_contents($save_path.$config['loading_sprite_1']);
    $developer_sprite = @file_get_contents($save_path.$config['loading_sprite_2']);
    
	
	$wl = "whitelist.txt";
	$bl = "blacklist.txt";

	$whitelist = [];
	$blacklist = [];

	if (file_exists($wl)) {
		fillArrayByLines($whitelist, $wl);
	} else {
		if (file_exists($bl)) {
			fillArrayByLines($blacklist, $bl);
		}
	}
	
	
    echo 'Loading done!' . PHP_EOL;
    
  //$console = new ConsoleGraph('__do_not_configure_window');
    $console = new ConsoleGraph();
    
    $console->graphTitle('InstaStories Saver');
    
    $console->graphColor(0xF, 0x0, true);
    
    if (!in_array('--no-bootsprites', $argv)) {
        $console->graphDrawPic(0xF, 0xD, $loading_sprite, 2);
        $console->graphDrawPic(0x0, 0xC, $developer_sprite, 1);
    }
    
    $console->graphStartingLine();
    $console->graphEmptyLine();
    $console->graphWriteToCenterLine('Instagram Stories Saver [Ivan_Alone]');
    $console->graphEmptyLine();
    $console->graphDottedLine();
    
    $user = null;    
    
    if ($cookies->getValidValue('ds_user_id') != null) {
    
        $user = $cookies->getValidValue('ds_user_id');
        
    } else {
        while (true) {
            $console->graphEmptyLine();
            $console->graphWriteToLine('Enter your login & password from Instagram: ');
            $console->graphEmptyLine();
            $console->graphWriteToLine('Login: ');
            $login = $console->graphReadLn();
            $console->graphEmptyLine();
            $console->graphWriteToLine('Password: ');
            $pass = $console->graphReadPassword();
            $console->graphEmptyLine();
            
            if ($login != null && $pass != null) {
                $auth_json = PostQuery('https://www.instagram.com/accounts/login/ajax/', array(
                    'username' => $login,
                    'password' => $pass
                ), getInstagramHeaders());
				
                $cookies->reload();
                
                if (@$auth_json['authenticated']) {
                    $user = $cookies->getValidValue('ds_user_id');
                    $console->graphWriteToLine('Logged in in '.date('H:i d.m.Y'));
                    
                    $console->graphEmptyLine();
                    $console->graphDottedLine();
                    
                    break;
                } elseif (@$auth_json['message'] == 'checkpoint_required') {
                    $checkpoint_url = $auth_json['checkpoint_url'];
                    $checkout_data_dcd = PostQuery('https://www.instagram.com'.$checkpoint_url, array(
                        'choice' => 1
                    ), getInstagramHeaders());
                    
                    $console->graphWriteToLine('Attention: security code was sent to '.$checkout_data_dcd['fields']['contact_point']);
                    $sequrity_data = array();
                    $EC_TEST = 0;
                    
                    while (@$sequrity_data['status'] != 'ok') {
                        if ($EC_TEST != 0) {
                            $console->graphWriteToLine('Error: wrong security code, repeat it!');
                            $console->graphEmptyLine();
                        }
                        $console->graphWriteToLine('Enter your code: ');
                        
                        $sequrity_data = PostQuery('https://www.instagram.com'.$checkpoint_url, array(
                            'security_code' => $console->graphReadLn()
                        ), getInstagramHeaders());
                        
                        $EC_TEST++;
                        $console->graphEmptyLine();
                    }
                    
                    $cookies->reload();
                    $user = $cookies->getValidValue('ds_user_id');
                    $console->graphWriteToLine('Logged in in '.date('H:i d.m.Y'));
                    
                    $console->graphEmptyLine();
                    $console->graphDottedLine();
                    
                    break;
                } else {
                    $console->graphWriteToLine('Login or password is incorrect, repeat input please!');
                }
            } else {
                $console->graphWriteToLine('Login or password is empty, repeat input please!');
            }
            $console->graphEmptyLine();
            $console->graphDottedLine();
        }
        $__csrftoken = $cookies->getValidValue('csrftoken');
    }
    
    $console->graphEmptyLine();
    
    if ($user != null) {
        if (@$config['incognito']) {
            $console->graphWriteToCenterLine('!!! Incognito mode is activated !!!');
            $console->graphEmptyLine();
            $console->graphEmptyLine();
        }
       
        $feed = $net->Request(array(CURLOPT_URL => 'https://www.instagram.com/graphql/query/?query_hash=01b3ccff4136c4adf5e67e1dd7eab68d&variables={}'), getInstagramHeaders(), true);
		
		$feed = @json_decode( $feed, true);
		
        $console->graphWriteToLine('Grabbing subscribes from '.@$feed['data']['user']['username'].'\'s feed...');
        $console->graphEmptyLine();
        
        $stories = $net->GetQuery('https://www.instagram.com/graphql/query/?query_id=17890626976041463&variables={}', getInstagramHeaders());
        $stories = @$stories['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'];
        $console->graphWriteToLine('Subscribes grabbed, going to downloading...');
        $console->graphEmptyLine();
        $console->graphDottedLine();
        $console->graphEmptyLine();
		
		$flag1 = false;
			
        if (!is_array($stories) || count($stories) < 1) {
            $console->graphWriteToLine('Nothing to download, no stories in your feed!');
            $console->graphEmptyLine();
        } else {
            foreach($stories as $__user) {
                $user       = $__user['node'];
                $id         = $user['id'];
                $user_info  = $user['user'];
                
				$owner = $user_info['username'];
				$flag = false;
				
				if (count($whitelist) > 0) {
					if (!in_array($owner, $whitelist)) {
						$flag = true;
					}
				} else {
					if (count($blacklist) > 0) {
						if (in_array($owner, $blacklist)) {
							$flag = true;
						}
					}
				}
				
				if (!$flag) {
					$flag1 = true;
					$console->graphWriteToLine('Reading & downloading Stories by '.$user_info['username'].'...');
					$console->graphEmptyLine();
					
					$user_stories = $net->GetQuery('https://www.instagram.com/graphql/query/?query_id=17873473675158481&variables={"reel_ids":["'.$id.'"],"precomposed_overlay":false}', getInstagramHeaders());
					@mkdir(__instagram);
					
					$directory = __instagram.'/'.$user_info['username'];
					@mkdir($directory);
					downloadStoriesByLink($console, $directory, $user_info['username'], $user_stories['data']['reels_media'][0]['items']);
					$console->graphEmptyLine();
					
					$console->graphWriteToLine('Trying to find & download Pinned Stories by '.$user_info['username'].'...');
					$console->graphEmptyLine();
					$user_highlight = $net->GetQuery('https://www.instagram.com/graphql/query/?query_hash=9ca88e465c3f866a76f7adee3871bdd8&variables={"user_id":"'.$user['id'].'","include_highlight_reels":true}', getInstagramHeaders());
					if (is_array(@$user_highlight['data']['user']['edge_highlight_reels']['edges']) && count($user_highlight['data']['user']['edge_highlight_reels']['edges']) != 0) {
						$stories_spack = array();
						foreach ($user_highlight['data']['user']['edge_highlight_reels']['edges'] as $sdd => $stories_pack) {
							$stories_pack = $stories_pack['node'];
							if ($stories_pack['__typename'] == 'GraphHighlightReel' && $stories_pack['cover_media'] != null) {
								$stories_spack[] = $stories_pack['id'];
							}
						}
						
						$packs_array = $net->GetQuery('https://www.instagram.com/graphql/query/?query_hash=45246d3fe16ccc6577e0bd297a5db1ab&variables={"highlight_reel_ids":["'.implode('","', $stories_spack).'"],"precomposed_overlay":false}', getInstagramHeaders());
						$items = array();
						foreach ($packs_array['data']['reels_media'] as $st_block) {
							foreach ($st_block['items'] as $story) {
								$items[] = $story;
							}
						}
						downloadStoriesByLink($console, $directory, $user_info['username'], $items);
						
					} else {
						$console->graphWriteToLine('Pinned are empty!');
					}
					$console->graphEmptyLine();
					$console->graphEmptyLine();
				}
            }
        }

		if (!$flag1) {
			$console->graphWriteToLine("Nothing to download, no stories in your feed!");
			$console->graphEmptyLine();
		}
		
        $console->graphDottedLine();
        $console->graphEmptyLine();
        $console->graphWriteToLine('Downloading of Stories done!');
    }
    
    $console->graphEmptyLine();
    $console->graphEndingLine();
    
    if (!in_array('--no-exit-pause', $argv)) {
        $console->graphFinish();
    } else {
        $console = null;
        exit;
    }
    
    /*  Algorhythm  ---  End  */
    
    
    
    /*  Functions  ---  Start  */
    
	function downloadStoriesByLink($console, $directory, $username, $link) {
		global $config;
		global $net;
		
		$console->graphProgressBarCreate();
		$console->graphProgressBarUpdate(0, count($link));
		foreach ($link as $id => $story) {
			$time_public = $story['taken_at_timestamp'];
			switch ($story['__typename']) {
				case 'GraphStoryImage':
				case 'GraphStoryVideo':
					$images = $story[$story['__typename'] == 'GraphStoryVideo' ? 'video_resources' : 'display_resources'];
					$images_count = count($images);
					$image_maxres = $images[$images_count-1];
					
					$filename = $directory.'/'.$username.' at '.date('Y.m.d - H.i.s',$time_public).($story['__typename'] == 'GraphStoryVideo' ? '.mp4' : '.jpg');
					
					if (!file_exists($filename)) {
						if (!@$config['incognito']) {
							$timestamp = time();
							$status = PostQuery('https://www.instagram.com/stories/reel/seen', array(
								'reelMediaId' => $story['id'], 
								'reelMediaOwnerId' => $story['owner']['id'], 
								'reelId' => $story['owner']['id'], 
								'reelMediaTakenAt' => $timestamp, 
								'viewSeenAt' => $timestamp
							), getInstagramHeaders(array(
								'Referer' => 'https://www.instagram.com/stories/'.$username.'/'
							)));
						}
						
						if (@$config['incognito'] ? true : @$status['status'] == 'ok') {
							file_put_contents($filename, $net->Request(array(CURLOPT_URL =>$image_maxres['src']), getInstagramHeaders(), true));
						}
					}
				break;
			}
			$console->graphProgressBarUpdate($id+1, count($link));
		}
		$console->graphProgressBarClose();
	}
	
	function getInstagramHeaders($plus = array()) {
		global $__csrftoken;
		$elite = array(
			'Referer' => 'https://www.instagram.com/', 
			'X-CSRFToken' => @$__csrftoken, 
			'X-Instagram-AJAX' => '1', 
			'Content-Type' => 'application/x-www-form-urlencoded', 
			'X-Requested-With' => 'XMLHttpRequest'
		);
		foreach ($plus as $name => $value) {
			$elite[$name] = $value;
		}
		return $elite;
	}
	
    function replaceCycle($string, $replace, $cycle_lenght) {
        if ($cycle_lenght < 2) return $string;
        $find = '';
        for ($i = 0; $i < $cycle_lenght; $i++) {
            $find .= $replace;
        }
        while (strpos($string, $find) !== false) {
            $string = str_replace($find, $replace, $string);
        }
        return $string;
    }
    
    /*  Functions  ---  End  */