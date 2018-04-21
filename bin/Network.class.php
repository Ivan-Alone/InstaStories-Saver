<?php

	class Network {
		private $cookie_path;
		private $proxy_data;
		
		public function __construct($cookie_path, $proxy_data = null) {
			$this->cookie_path = $cookie_path;
			$this->proxy_data = $proxy_data;
		}
	
		public function GetQuery($url, $header_plus = array()) {
			return $this->Request(array(
				CURLOPT_URL => $url
			), $header_plus);
		}
		
		public function PostQuery($url, $par_array = array(), $header_plus = array()) {
			return $this->Request(array(
				CURLOPT_URL => $url,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($par_array)
			), $header_plus);
		}
		
		public function Request($curl_opt_array, $header_plus = array(), $noDecodeJSON = false) {
			$curl = curl_init();
			if ($this->proxy_data != null) {
				curl_setopt($curl, CURLOPT_PROXY, $this->proxy_data);
			}
			
			foreach ($curl_opt_array as $id => $value) {
				curl_setopt($curl, $id, $value);
			}
		
			$header = array(
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0', 
				'Accept' => '*/*', 
				'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3', 
				'Connection' => 'keep-alive'
			);
			foreach ($header_plus as $name => $value) {
				$header[$name] = $value;
			}
			
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0); 
			curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $this->compileHeader($header, array('Accept')));
			curl_setopt($curl,CURLOPT_COOKIEJAR, $this->cookie_path); 
			curl_setopt($curl,CURLOPT_COOKIEFILE, $this->cookie_path); 
			
			$data = curl_exec($curl);
			
			if ($noDecodeJSON) return $data;
			
			$json = json_decode($data, true);
			
			if (is_array($json)) return $json;
			
			return false;
		}
		
		private function compileHeader($header_array, $remove_array) {
			$header = array();
			foreach($remove_array as $val) $header[] = $val.':';
			foreach($header_array as $key => $val) $header[] = $key . ': ' . $val;
			return $header;
		}
	}
	
	class CurlCookies {
		private $cookies;
		private $cookies_file;
		
		public function __construct($filename) {
			$this->cookies_file = $filename;
			$this->reload();
		}
		
		public function getValidValue($key) {
			foreach ($this->cookies as $domain) {
				foreach ($domain as $name => $cookie) {
					if ($key == $name && $cookie['value'] != null && $cookie['value'] != '""') {
						return $cookie['value'];
					}
				}
			}
			return null;
		}
		
		public function reload() {
			$this->cookies = $this->extractCookies(@file_get_contents($this->cookies_file));
		}
    
		public function extractCookies($string) {
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
	}