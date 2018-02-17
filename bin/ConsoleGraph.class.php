<?php
    /*
        ConsoleGraph PHP Class
        Copyright Ivan_Alone, 2018
        GNU General Public License 3
    */
    
    class ConsoleGraph {
        private $useStarsAsWinBuilders = false;
        
        private $colors = array(0x0, 0x7);
        private $slide = 0;
        
        public function __construct($useStarsAsWinBuilders = false) {
            if (PHP_OS != 'WINNT') self::linuxColorInit();
            
            if (is_string($useStarsAsWinBuilders)) {
                if ($useStarsAsWinBuilders == '__do_not_configure_window') {
                    $this->useStarsAsWinBuilders = (PHP_OS == 'WINNT' && $this->getWinNT_Version() != '10.0') ? true : false;
                    return;
                }
            }
            $this->useStarsAsWinBuilders = (PHP_OS == 'WINNT' && $this->getWinNT_Version() != '10.0') ? true : $useStarsAsWinBuilders;
            self::configureWindowSize();
            self::graphClear();
        }
    
        public function graphSetSlide($slide) {
            $this->slide = $slide;
        }
        
        public function graphClear() {
            self::exeW(PHP_OS == 'WINNT' ? 'cls' : 'reset');
        }
        
        public function graphTitle($title) {
            $title = $title==null?'Console Graph Class App':trim($title);
            if (PHP_OS == 'WINNT') {
                self::exe('title '.$title);
            } else {
                echo "\033]2;{$title}\007";
            }
        }
        
        public function graphColor($bg, $txt, $store = false) {
            $bg = max(0, min(15, $bg));
            $txt = max(0, min(15, $txt));
            if ($store) $this->colors = array($bg, $txt);
            if (PHP_OS == 'WINNT') {
                self::exeW('color '.dechex($bg).dechex($txt));
                return true;
            } else {
                self::linuxColorInit();
                self::exeW('setterm --background '.self::colorCut($bg).' --foreground '.self::colorCut($txt));
                return true;
            }
        }
        
        public function graphColorReset() {
            return self::graphColor($this->colors[0], $this->colors[1]);
        }
        
        public function graphReadLn($text = null) {
            self::graphLine();
            echo ($text==null?null:$text.' ').self::getSlideSpaces().'> ';
            $try = self::read();
            echo chr(0x0D);
            return $try;
        }
        
        public function graphReadPassword($text = null) {
            self::graphLine();
            echo ($text==null?null:$text.' ').self::getSlideSpaces().'> ';
            $try = self::read();
            $mask = '';
            if (PHP_OS == 'WINNT') {
                if ($this->getWinNT_Version() == '10.0') {
                    for ($i = 0; $i < 117-$this->slide-2-($text==null?0:strlen($text)+1); $i++) {
                        echo chr(0x08);
                        if ($i < strlen($try))
                        $mask .= '▒';
                    }
                }
            } else {
                echo "\033[1A";
                $ccc = 3 + strlen($text.'') + 2 + $this->slide;
                echo "\033[{$ccc}C";
                for ($i = 0; $i < strlen($try); $i++) {
                    $mask .= '▒';
                }
            }
            if (PHP_OS != 'WINNT' || $this->getWinNT_Version() == '10.0') {
                echo $mask;
                echo chr(0x0A);
                echo chr(0x0D);
            }
            return $try;
        }
        
        public function graphDottedLine() {
            $gen = '';
            for ($i = 0; $i < 114; $i++) {
                $gen .= ($i%2) ? ' ' : ($this->useStarsAsWinBuilders?'*':'=');
            }
            self::graphLine();
            self::graphWrite($gen, true);
        }
        
        public function graphFilledLine() {
            self::graphLine(true);
        }
        
        public function graphEmptyLine() {
            self::graphLine(false, true);
        }
        
        public function graphStartingLine() {
            self::graphLine(true, false, 0);
        }
        
        public function graphEndingLine() {
            self::graphLine(true, false, 1);
        }
        
        public function graphWriteToCenterLine($text) {
            self::graphLine();
            $text = trim($text);
            $str = '';
            for ($i = 0; $i < min(114, strlen($text)); $i++) {
                $str .= $text[$i];
            } 
            $d = (strlen($str)/2.0);
            $r = round($d);
            $d = $r < $d ? $r+1 : $r;
            for ($i = 0; $i < 57-$d; $i++) {
                $str = ' '.$str;
            }
            self::graphWrite($str, true);
        }
    
        public function graphDrawPicFile($bg_color, $fg_color, $filename, $show_timer = 0, $args = array()) {
            return $this->graphDrawPic($bg_color, $fg_color, file_get_contents($filename), $show_timer, array());
        }
        
        public function graphDrawPic($bg_color, $fg_color, $data, $show_timer = 0, $args = array()) {
            if (substr($data, 0, 7) != 'ACONPIC') {
                return false;
            }
            
            $restore = @$args['restore']===null?true:$args['restore'];
            $clear = @$args['clear']===null?true:$args['clear'];
            
            $this->graphClear();
            $this->graphColor($bg_color, $fg_color);
            
            $isWin32 = PHP_OS == 'WINNT';
            $isWin10 = $this->getWinNT_Version() == '10.0';
            
            if ((!$isWin32 || $isWin10) && true) {
                $modes = array(' ', '▄', '▀', '█');
            } else {
                $modes = array(' ', ',', '\'', '#');
            }
            
            $x_size = $this->convert2bytes($data[7], $data[8]);
            $y_size = $this->convert2bytes($data[9], $data[10]);
            
            $ox_test = $x_size/4;
            $ox_test_f = (int)($ox_test);
            $x_bytes = $ox_test_f + ($ox_test_f < $ox_test ? 1 : 0);
            
            if ($x_bytes*$y_size != strlen($data)-11) {
                return false;
            }
            
            $start = 11;
            $image = '';
            for ($i = 0; $i < $y_size; $i++) {
                $line = substr($data, $start, $x_bytes);
                $x = 0;
                for ($s = 0; $s < strlen($line); $s++) {
                    $chain = $this->normalize(decbin(ord($line[$s])));
                    for ($px = 0; $px < 4; $px++) {
                        if ($x >= $x_size) break 2;
                        $image .= $modes[bindec(substr($chain, $px*2, 2))];
                        $x++;
                    }
                }
                $image .= !$isWin32 || $isWin10 ? PHP_EOL : null;
                $start += $x_bytes;
            }
            
            echo (substr($image, 0, !$isWin32 ? -2 : ($isWin10 ? -3 : -1)));
            
            if ($show_timer <= 0) {
                $this->graphPause();
            } else {
                sleep ($show_timer);
            }
            
            if ($clear) $this->graphClear();
            if ($restore) $this->graphColorReset();
            
            return true;
        }
        
        public function graphProgressBarCreate() {
            self::graphLine();
        }
        
        public function graphProgressBarUpdate($current, $count) {
            $sym = $this->useStarsAsWinBuilders ? '#' : '▓';            
            if ($current > $count) {
                $cur_txt = $count;
            } else {
                $cur_txt = $current;
                for ($i = 0; $i < strlen(''.$count)-strlen(''.$current); $i++) {
                    $cur_txt = ' '.$cur_txt;
                }
            }
            
            $counter = $cur_txt.' / '.$count.'  ';
            
            $len = strlen($counter);
            $_100_perc = 114-$len;
            
            $blocks = round(($current/$count)*$_100_perc);
            
            for ($i = 0; $i < $blocks; $i++) {
                $counter .= $sym;
            }
            
            echo $counter;
            
            for ($i = 0; $i < strlen($counter)-($this->useStarsAsWinBuilders?0:2*$blocks); $i++) {
                
                echo chr(0x08);
            }
            
        }
        
        public function graphProgressBarClose() {
            echo chr(0x0A);
            echo chr(0x0D);
        }
        
        public function graphWriteToLine ($text) {
            self::graphLine();
            self::graphWrite($text);
        }
        
        public function graphFinish() {
            self::graphPause();
            exit;
        }
        
        public function graphPause() {
            shell_exec(PHP_OS == 'WINNT' ? 'pause' : "read a");
        }
        
        private function read() {
            return trim(fgets(STDIN));
        }
        
        private function exeR($aim) {
            self::exe($aim);
        }
        
        private function exeW($aim) {
            @pclose(@popen($aim,'w'));
        }
        
        private function exe($aim) {
            @pclose(@popen($aim,'r'));
        }
        
        private function getSlideSpaces() {
            $str = '';
            for ($i = 0; $i < $this->slide; $i++) $str .= ' ';
            return $str;
        }
        
        private function getWinNT_Version() {
            if (PHP_OS != 'WINNT') {
                return false;
            }
            return php_uname('r');
        }
        
        private function configureWindowSize() {
            if (PHP_OS == 'WINNT')
                self::exeW('mode con:cols=120 lines=30');
            else 
                echo "\e[8;30;120t";
        }
        
        private function colorCut($color) {            
            $c = decbin($color);
            $l = strlen($c);
            if ($l > 3) {
                return bindec(substr($c, $l-3, 3));
            } else {
                return $color;
            }
        }
        
        private function linuxColorInit() {
            $color_reloc = array(
                array(0x00, 0x00, 0x00),
                array(0x00, 0x00, 0x80),
                array(0x00, 0x80, 0x00),
                array(0x00, 0x80, 0x80),
                array(0x80, 0x00, 0x00),
                array(0x80, 0x00, 0x80),
                array(0x80, 0x80, 0x00),
                array(0xFF, 0xFF, 0xFF)
            );
            
            foreach ($color_reloc as $id => $color) {
                self::exeW('tput initc '.$id.' '.round($color[0]*(1000/255)).' '.round($color[1]*(1000/255)).' '.round($color[2]*(1000/255)));
            }
        }
        
        private function graphLine($isFull = false, $isEmpty = false, $start_marker = -1) {
            if (PHP_OS == 'WINNT') {
                echo ' ';
            } else {
                echo chr(0x1B).'[1C';
            }
            echo $this->useStarsAsWinBuilders?'*':($start_marker == -1 ? ($isFull?'╠':'║') : ($start_marker == 0 ? '╔' : '╚')); 
            for($i=0;$i<116;$i++)
                echo $isFull?($this->useStarsAsWinBuilders?'*':'═'):' ';
            echo $this->useStarsAsWinBuilders?'*':($start_marker == -1 ? ($isFull?'╣':'║') : ($start_marker == 0 ? '╗' : '╝')); 
            if ($isFull) {
                echo chr(0x0A);
                echo chr(0x0D);
                return;
            }
            if (!$isEmpty) {
                for($i=0;$i<116;$i++) 
                    echo chr(0x08);
            } else {
                echo chr(0x0A);
                echo chr(0x0D);
            }
        }
        
        private function graphWrite($text, $isCenter=false) {
            $text = ($isCenter ? null:self::getSlideSpaces()).$text;
            $str = '';
            for ($i = 0; $i < min(114, strlen($text)); $i++) {
                $str .= $text[$i];
            } 
            echo $str;
            echo chr(0x0A);
            echo chr(0x0D);
        }
    
        private function normalize($input, $mod = 8, $block = '0') {
            while(strlen($input) < $mod) {
                $input = $block.$input;
            }
            return $input;
        }
    
        private function convert2bytes($byte1, $byte2) {
            return bindec($this->normalize(decbin(ord($byte1))).$this->normalize(decbin(ord($byte2))));
        }
    }
?>