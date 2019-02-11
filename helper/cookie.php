<?php
class helper_cookie {
	
	private static $setting = null;
	private static $domainPath = '/';
	
	private $sectet = '';
	
	public function __construct(){
		
		if(empty(self::$setting)){
			//会话
			$filename = __ROOT__.'/config/app.xml';
			if(!is_file($filename)){
				throw new exception('未定义COOKIE配置文件');
			}
			$config = simplexml_load_string(file_get_contents($filename));
			if(!$config){
				throw new exception('配置文件加载失败');
			}
			self::$setting = $config;
		}
		$this->secret = self::$setting->secret;
	}
	// 加密
	private function encrypt($plain_text) { 
		$key = $this->secret;
		if(!function_exists('mcrypt_get_iv_size')){
			return $plain_text;
		}
		$plain_text = trim($plain_text);  
		$iv = substr(md5($key), 0,mcrypt_get_iv_size (MCRYPT_CAST_256,MCRYPT_MODE_CFB));  
		$c_t = mcrypt_cfb (MCRYPT_CAST_256, $key, $plain_text, MCRYPT_ENCRYPT, $iv);  
		return trim(chop(base64_encode($c_t)));  
	}
	
	//解密
	private function decrypt($c_t) {  
		$key = $this->secret;
		if(!function_exists('mcrypt_get_iv_size')){
			return $c_t;
		}
		$c_t = trim(chop(base64_decode($c_t)));  
		$iv = substr(md5($key), 0,mcrypt_get_iv_size (MCRYPT_CAST_256,MCRYPT_MODE_CFB));  
		$p_t = mcrypt_cfb (MCRYPT_CAST_256, $key, $c_t, MCRYPT_DECRYPT, $iv);  
		return trim(chop($p_t));  
	}
	
	private function getHost(){
		$host = explode('.',$_SERVER['HTTP_HOST']);
		if(self::$domainPath == './'){
			switch(count($host)){
				case 4:
					$host = array_slice($host,2);
					break;
				case 3:
					$host = array_slice($host,1);
					break;
			}
		}
		return implode('.',$host);
	}
	
	public function get($field){
		$output = '';
		if(isset($_COOKIE[$field])){
			$output = $_COOKIE[$field];
			$output = $this->decrypt($output);
			$res = json_decode($output, true);
   		 	$error = json_last_error();
			if (empty($error)) {
				$output = $res;
			}
		}
		return $output;
	}
	
	
	public function set($field,$value,$expire = 0){
		
		$host = $this->getHost();
		if(!empty($value)){
			if($expire){
				$expire = time()+$expire;
			}
			if(is_array($value)){
				$value = json_encode($value);
			}
			$value = $this->encrypt($value);
		}else{
			$expire = time()-3600;
		}
		setcookie($field,$value,$expire,'/',$host,0,1);
		return ;
	}
}