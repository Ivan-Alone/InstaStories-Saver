<?php
    /*
        InstaStories Program
        Copyright Ivan_Alone, 2018
        GNU General Public License 3
    */
        
    echo 'Loading...' . PHP_EOL;
    if ($argv[1] == null) $argv[1] = $_GET['addr'];
    $save_path = replaceCycle($argv[1] != null && file_exists($argv[1]) ? $argv[1] . '/' : './', '/', 2);
    
    // Activates Fiddler 2 Proxy - useful for debugging
    // define  ('__curl_proxy', '127.0.0.1:8888'); 
    
    include ('ConsoleGraph.class.php');
    
    define  ('__instagram', $save_path.'Instagram'); 
    define  ('__temp', $save_path.'temp'); 
    define  ('__cookie_path', __temp.'/curl_cookies.lcf');
    
    $cookies = @extractCookies(@file_get_contents(__cookie_path));
    if (@$cookies['www.instagram.com']['csrftoken'] == null) {
        urlQuery('https://www.instagram.com');
        $cookies = @extractCookies(@file_get_contents(__cookie_path));
    }
    
    define('__csrftoken', $cookies['www.instagram.com']['csrftoken']['value']);
    
    $loading_sprite = @file_get_contents($save_path.'bin/instaload.png.conpic2');
    $developer_sprite = @file_get_contents($save_path.'bin/myLogo.png.conpic2');
    
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
    
    if (@$cookies['www.instagram.com']['ds_user_id'] != null) {
    
        $user = $cookies['www.instagram.com']['ds_user_id']['value'];
        
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
                $auth_json = urlQuery('https://www.instagram.com/accounts/login/ajax/', array(
                    'username' => $login,
                    'password' => $pass
                ));
                
                $cookies = @extractCookies(@file_get_contents(__cookie_path));
                
                if (@$auth_json['authenticated']) {
                    $user = $cookies['www.instagram.com']['ds_user_id']['value'];
                    $console->graphWriteToLine('Logged in in '.date('H:i d.m.Y'));
                    
                    $console->graphEmptyLine();
                    $console->graphDottedLine();
                    
                    break;
                } elseif (@$auth_json['message'] == 'checkpoint_required') {
                    $checkpoint_url = $auth_json['checkpoint_url'];
                    $checkout_data_dcd = urlQuery('https://www.instagram.com'.$checkpoint_url, array(
                        'choice' => 1
                    ));
                    
                    $console->graphWriteToLine('Attention: security code was sent to '.$checkout_data_dcd['fields']['contact_point']);
                    $sequrity_data = array();
                    $EC_TEST = 0;
                    
                    while (@$sequrity_data['status'] != 'ok') {
                        if ($EC_TEST != 0) {
                            $console->graphWriteToLine('Error: wrong security code, repeat it!');
                            $console->graphEmptyLine();
                        }
                        $console->graphWriteToLine('Enter your code: ');
                        
                        $sequrity_data = urlQuery('https://www.instagram.com'.$checkpoint_url, array(
                            'security_code' => $console->graphReadLn()
                        ));
                        
                        $EC_TEST++;
                        $console->graphEmptyLine();
                    }
                    
                    $cookies = @extractCookies(@file_get_contents(__cookie_path));
                    $user = $cookies['www.instagram.com']['ds_user_id']['value'];
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
    }
    
    $console->graphEmptyLine();
    
    if ($user != null) {
        $feed = urlGetQuery('https://www.instagram.com/?__a=1');
        $console->graphWriteToLine('Grabbing subscribes from '.($feed['graphql']['user']['username'] == null ? 'your' : ($feed['graphql']['user']['username'].'\'s')).' feed...');
        $console->graphEmptyLine();
        
        $stories = urlGetQuery('https://www.instagram.com/graphql/query/?query_id=17890626976041463&variables={}')['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'];
        $console->graphWriteToLine('Subscribes grabed, going to downloading...');
        $console->graphEmptyLine();
        $console->graphDottedLine();
        $console->graphEmptyLine();
        
        if (!is_array($stories)) {
            $console->graphWriteToLine('Nothing to download, no stories in your feed!');
            $console->graphEmptyLine();
        } else {
            foreach($stories as $__user) {
                $user       = $__user['node'];
                $id         = $user['id'];
                $user_info  = $user['user'];
                
                $console->graphWriteToLine('Reading & downloading Stories by '.$user_info['username'].'...');
                $console->graphEmptyLine();
                
                $user_stories = urlGetQuery('https://www.instagram.com/graphql/query/?query_id=17873473675158481&variables={"reel_ids":["'.$id.'"],"precomposed_overlay":false}');
                @mkdir(__instagram);
                
                foreach($user_stories['data']['reels_media'] as $reels_media) {
                    $directory = __instagram.'/'.$reels_media['user']['username'];
                    @mkdir($directory);
                    
                    $console->graphProgressBarCreate();
                    $console->graphProgressBarUpdate(0, count($reels_media['items']));
                    foreach ($reels_media['items'] as $id => $story) {
                        $time_public = $story['taken_at_timestamp'];
                        switch ($story['__typename']) {
                            case 'GraphStoryImage':
                            case 'GraphStoryVideo':
                                $images = $story[$story['__typename'] == 'GraphStoryVideo' ? 'video_resources' : 'display_resources'];
                                $images_count = count($images);
                                $image_maxres = $images[$images_count-1];
                                
                                $filename = $directory.'/'.$reels_media['user']['username'].' at '.date('Y.m.d - H.i.s',$time_public).($story['__typename'] == 'GraphStoryVideo' ? '.mp4' : '.jpg');
                                
                                if (!file_exists($filename))
                                    file_put_contents($filename, file_get_contents($image_maxres['src']));
                            break;
                        }
                        $console->graphProgressBarUpdate($id+1, count($reels_media['items']));
                    }
                    $console->graphProgressBarClose();
                    $console->graphEmptyLine();
                    $console->graphEmptyLine();
                }
                
            }
        }
        $console->graphDottedLine();
        $console->graphEmptyLine();
        $console->graphWriteToLine('Downloading of Stories done!');
    }
    
    $console->graphEmptyLine();
    $console->graphEndingLine();
    $console->graphFinish();
    

    function urlGetQuery($url) {
        @mkdir(__temp);
        $header = array('Accept:', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0', 'Accept: */*', 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3', 'Accept-Encoding: gzip', 'Referer: https://www.instagram.com/', 'X-CSRFToken: '.@__csrftoken, 'X-Instagram-AJAX: 1', 'Content-Type: application/x-www-form-urlencoded', 'X-Requested-With: XMLHttpRequest', 'Connection: keep-alive');
        $curl = curl_init();
        if (@__curl_proxy != null && @__curl_proxy != '__curl_proxy') {
            curl_setopt($curl, CURLOPT_PROXY, __curl_proxy);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLINFO_HEADER_OUT,1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl,CURLOPT_COOKIEJAR, __cookie_path); 
        curl_setopt($curl,CURLOPT_COOKIEFILE, __cookie_path); 
        $m=curl_exec($curl);
        
        $json_1 = json_decode($m, true);
        $json_2 = json_decode(@gzdecode($m), true);
        
        if (is_array($json_2)) return $json_2;
        if (is_array($json_1)) return $json_1;
        
        return false;
   }
    
    function urlQuery($url, $par_array = array()) {
        @mkdir(__temp);
        $post = substr(toGetQuery($par_array), 0, -1);
        $header = array('Accept:', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0', 'Accept: */*', 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3', 'Accept-Encoding: gzip', 'Referer: https://www.instagram.com/', 'X-CSRFToken: '.@__csrftoken, 'X-Instagram-AJAX: 1', 'Content-Type: application/x-www-form-urlencoded', 'X-Requested-With: XMLHttpRequest', 'Connection: keep-alive');
        $curl = curl_init();
        if (@__curl_proxy != null && @__curl_proxy != '__curl_proxy') {
            curl_setopt($curl, CURLOPT_PROXY, __curl_proxy);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLINFO_HEADER_OUT,1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl,CURLOPT_COOKIEJAR, __cookie_path); 
        curl_setopt($curl,CURLOPT_COOKIEFILE, __cookie_path); 
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $m=curl_exec($curl);
        
        $json_1 = json_decode($m, true);
        $json_2 = json_decode(@gzdecode($m), true);
        
        if (is_array($json_2)) return $json_2;
        if (is_array($json_1)) return $json_1;
        
        return false;
    }
    
    function toGetQuery($array) {
        $get = null;
        if (is_array($array))
            foreach ($array as $k => $v) {
                $get .= urlencode($k) . '=' . urlencode($v) . '&';
            }
        return $get == null ? null : $get;
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
    
    function extractCookies($string) {
        $cookies = array();
        $lines = explode("\n", $string);
        foreach ($lines as $line) {
            if (isset($line[0]) && substr_count($line, "\t") == 6) {
                $tokens = explode("\t", $line);
                $tokens = array_map('trim', $tokens);
                $cookie = array();
                $cookie['flag'] = $tokens[1];
                $cookie['path'] = $tokens[2];
                $cookie['secure'] = $tokens[3];
                $cookie['expiration'] = $tokens[4];
                $cookie['value'] = $tokens[6];
                $cookies[$tokens[0]][$tokens[5]] = $cookie;
            }
        }
        return $cookies;
    }
    