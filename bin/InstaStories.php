<?php
    /*
        InstaStories Program
        Copyright Ivan_Alone, 2018
        GNU General Public License 3
    */
    
    $save_path = replaceCycle($argv[1] != null && file_exists($argv[1]) ? $argv[1] . '/' : './', '/', 2);

    echo 'Loading...' . PHP_EOL;
    
    // Activates Fiddler 2 Proxy - useful for debugging
    // define  ('__curl_proxy', '127.0.0.1:8888'); 
    
    define  ('__instagram', $save_path.'Instagram'); 
    define  ('__temp', $save_path.'temp'); 
    define  ('__cookie_path', __temp.'/curl_cookies.lcf');
    
    $cookies = @extractCookies(@file_get_contents(__cookie_path));
    if (@$cookies['www.instagram.com']['csrftoken'] == null) {
        urlQuery('https://www.instagram.com');
    }
    $cookies = @extractCookies(@file_get_contents(__cookie_path));
    define('__csrftoken', $cookies['www.instagram.com']['csrftoken']['value']);
    
    include ('ConsoleGraph.class.php');
    
    $loading_sprite = file_get_contents($save_path.'bin/instaload.png.conpic');
    $developer_sprite = file_get_contents($save_path.'bin/myLogo.png.conpic');
    
    echo 'Loading done!' . PHP_EOL;
    
    
    //$console = new ConsoleGraph('__do_not_configure_window');
    $console = new ConsoleGraph();
    
    $console->graphTitle('InstaStories Saver');
    
    $console->graphClear();
    $console->graphColor(0xF, 0x5);
    echo $loading_sprite;
    sleep(2);
    
    $console->graphClear();
    $console->graphColor(0x0, 0xC);
    echo $developer_sprite;
    sleep(1);
    
    
    
    $console->graphClear();
    $console->graphColor(0xF, 0x0);
    
    
    $console->graphStartingLine();
    $console->graphEmptyLine();
    $console->graphWriteToCenterLine('Instagram Stories Saver [Ivan_Alone]');
    $console->graphEmptyLine();
    $console->graphDottedLine();
    
    $user = null;    
    
    if (@$cookies['www.instagram.com']['ds_user_id'] != null) {
    
        $user = $cookies['www.instagram.com']['ds_user_id']['value'];
        
    } else {
        $console->graphEmptyLine();
        $console->graphWriteToLine('Enter your login & password from Instagram: ');
        $console->graphEmptyLine();
        $console->graphWriteToLine('Login: ');
        $login = $console->graphReadLn();
        $console->graphEmptyLine();
        $console->graphWriteToLine('Password: ');
        $pass = $console->graphReadPassword();
        $console->graphEmptyLine();
        $console->graphDottedLine();
        $console->graphEmptyLine();
        
        if ($login != null && $pass != null) {
            $authstate = urlQuery('https://www.instagram.com/accounts/login/ajax/', array(
                'username' => $login,
                'password' => $pass
            ));
            $cookies = @extractCookies(@file_get_contents(__cookie_path));
            $auth_json = json_decode($authstate, true);
            
            if ($auth_json['authenticated']) {
                $user = $cookies['www.instagram.com']['ds_user_id']['value'];
                $console->graphWriteToLine('Logged in in '.date('H:i d.m.Y'));
            } else {
                $console->graphWriteToLine('Login or password is incorrect, exiting!');
                @unlink(__cookie_path);
            }
        } else {
            $console->graphWriteToLine('Login or password is empty, exiting!');
            @unlink(__cookie_path);
        }
    }
    
    $console->graphEmptyLine();
    
    if ($user != null) {
        $feed = @json_decode(urlGetQuery('https://www.instagram.com/?__a=1'), 1);
        $console->graphWriteToLine('Grabbing subscribes from '.$feed['graphql']['user']['username'].'\'s feed...');
        $console->graphEmptyLine();
        
        $stories = @json_decode(urlGetQuery('https://www.instagram.com/graphql/query/?query_id=17890626976041463&variables={}'), 1)['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'];
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
                
                $user_stories = json_decode(urlGetQuery('https://www.instagram.com/graphql/query/?query_id=17873473675158481&variables={"reel_ids":["'.$id.'"],"precomposed_overlay":false}'), 1);
                @mkdir(__instagram);
                foreach($user_stories['data']['reels_media'] as $reels_media) {
                    $directory = __instagram.'/'.$reels_media['user']['username'];
                    @mkdir($directory);
                    foreach ($reels_media['items'] as $story) {
                        $time_public = $story['taken_at_timestamp'];
                        switch ($story['__typename']) {
                            case 'GraphStoryImage':
                            case 'GraphStoryVideo':
                                $images = $story[$story['__typename'] == 'GraphStoryVideo' ? 'video_resources' : 'display_resources'];
                                $images_count = count($images);
                                $image_maxres = $images[$images_count-1];
                                
                                $filename = $directory.'/'.$reels_media['user']['username'].' at '.date('Y.m.d - H.i.s',$time_public).($story['__typename'] == 'GraphStoryVideo' ? '.mp4' : '.jpg');
                                
                                if (!file_exists($filename))
                                    file_put_contents($filename, urlGetQuery($image_maxres['src'], 0));;
                            break;
                        }
                    }
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
    

    function urlGetQuery($url, $tryDecode = true) {
        @mkdir(__temp);
        $header = array('Accept:', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0', 'Accept: */*', 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3', 'Accept-Encoding: gzip', 'Referer: https://www.instagram.com/', 'X-CSRFToken: '.@__csrftoken, 'X-Instagram-AJAX: 1', 'Content-Type: application/x-www-form-urlencoded', 'X-Requested-With: XMLHttpRequest', 'Connection: keep-alive');
        $curl = curl_init();
        if (@__curl_proxy != null && @__curl_proxy != '__curl_proxy') {
            curl_setopt($curl, CURLOPT_PROXY, __curl_proxy);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLINFO_HEADER_OUT,1);
        curl_setopt($curl,CURLOPT_HEADER, 1); 
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl,CURLOPT_COOKIEJAR, __cookie_path); 
        curl_setopt($curl,CURLOPT_COOKIEFILE, __cookie_path); 
        $m=curl_exec($curl);
        
        return $tryDecode ? @gzdecode(explode("\r\n\r\n", $m)[@__curl_proxy != null && __curl_proxy != '__curl_proxy' ? 2 : 1]) : @(explode("\r\n\r\n", $m)[@__curl_proxy != null && __curl_proxy != '__curl_proxy' ? 2 : 1]);
    }
    
    function urlQuery($url, $par_array = array(), $tryDecode = false) {
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
        curl_setopt($curl,CURLOPT_HEADER, 1); 
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl,CURLOPT_COOKIEJAR, __cookie_path); 
        curl_setopt($curl,CURLOPT_COOKIEFILE, __cookie_path); 
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $m=curl_exec($curl);
        return $tryDecode ? @gzdecode(explode("\r\n\r\n", $m)[@__curl_proxy != null && __curl_proxy != '__curl_proxy' ? 2 : 1]) : @(explode("\r\n\r\n", $m)[@__curl_proxy != null && __curl_proxy != '__curl_proxy' ? 2 : 1]);
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
    