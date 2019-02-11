<?php
class session {
	private $driver = null;
	private static $connection = null;
	private static $accessToken = null;
	private static $domainPath = '/';
	
	private static $wechat = null;
	
	private $disabledField = array(
		'accessToken'
	);
	
	private static $lifeTime = 0;
	
	public function __construct(){
		
		if(empty(self::$connection)){
			//会话
			$filename = __ROOT__.'/config/session.xml';
			if(!is_file($filename)){
				throw new exception('未定义会话配置文件');
			}
			$config = simplexml_load_file($filename);
			if(!$config){
				$config = simplexml_load_string(file_get_contents($filename));
			}
			
			$drive = current($config->driver);
			$setting = $config->connections->$drive;
			$setting->sessionid = $this->_session_id();
			
			self::$lifeTime = intval((string)$setting->lifetime);
			$driver = 'session_drive_'.$drive;
			
			self::$connection = new $driver($setting);
			self::$connection->init();
			$path = '/';
			if(isset($setting->path)){
				$path = (string)$setting->path;
			}
			self::$domainPath = $path;
		}
		$this->driver = self::$connection;
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
	
	private function getSessionFile(){
		$folder =  __CACHE__.'/command';
		if(!is_dir($folder)){
			mkdir($folder,0777,1);
		}
		$sessionKey = $this->getSessionKey();
		return ($folder.'/'.md5($sessionKey.__URL__).'.sess.php');
	}
	private function _session_id()
	{
		$host = $this->getHost();
		
		
		$sessionid = '';
		$sessionKey = $this->getSessionKey();
		if(defined('IN_COMMAND') && IN_COMMAND){
			
			$file = $this->getSessionFile();
			if(is_file($file)){
				$sessionid = file_get_contents($file);
			}
		}else{
			if(array_key_exists('accessToken',$_GET)){
				$sessionid = $_GET['accessToken'];
			}else{
				$sessionid = $_COOKIE[$sessionKey];
			}
		}
		if(!$sessionid)
		{
			$sessionid = $this->getSessionId();
			$host = '.'.$host;
			if(defined('IN_COMMAND') && IN_COMMAND){
				$file = $this->getSessionFile();
				$result = file_put_contents($file,$sessionid);
			}else{
				$result = setcookie($sessionKey,$sessionid,0,$this->getPath(),$host,0,1);
			}
			if(!$result){
				throw new exception('COOKIE写失败',101);
			}
			$client = array(
				__HOST__,
				__DOMAIN__,
				__AGENT__,
				__REFERER__,
				__CLIENTIP__,
				__URL__
			);
			$folder = __LOG__.'/session/'.date('Y-m/d');
			if(!is_dir($folder)){
				mkdir($folder,0777,1);
			}
			
			$prevFolder = __LOG__.'/session/'.date('Y-m/d',strtotime('-1 day'));
			if(is_dir($prevFolder)){
				$handle = opendir($prevFolder);
				while($filename = readdir($handle))
				{
					if(in_array($filename,array('.','..')))
					{
						continue;
					}
					$filename = $prevFolder.'/'.$filename;
					unlink($filename);
				}
				rmdir($prevFolder);
			}
			if(!IN_COMMAND){
				file_put_contents($folder.'/session_init_'.(strtolower(__REQUEST_METHOD__)).'_'.date('YmdH').'txt',date('Y-m-d H:i').' '.(implode(',',$client).'cookie>>'.json_encode($_COOKIE,1))."\r\n",FILE_APPEND);
			}
		}
		self::$accessToken = $sessionid;
		return $sessionid;
	}
	
	/**
	 * 返回当前会话ＩＤ
	 * @return accessToken
	 */
	public function getAccessToken(){
		return self::$accessToken;
	}
	private function getSessionId(){
		$charid = strtolower(md5(uniqid(mt_rand(1000,microtime() * 10000), true).mt_rand(1000,microtime() * 10000).$this->get_client_ip().$_SERVER['HTTP_USER_AGENT']));
		$charid = md5($charid);
       	self::$accessToken =  $charid;
		return $charid;
	}
	
	
	protected function get_client_ip()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] as $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip;
	}
	public function set($field,$value)
	{
		if(!in_array($field,$this->disabledField)){
			$this->driver->set($field,$value);
		}
	}
	
	public function getSessionKey(){
		$key = substr($this->getPath(),1);
		return 'PHPAUTHID'.(strlen($key) > 0?'_'.strtoupper($key):'');
	}
	
	public function get($field = '')
	{
		if(!in_array($field,$this->disabledField)){
			return $this->driver->get($field);
		}else{
			$field = ucfirst($field);
			$action = 'get'.$field;	
			return $this->$action();
		}
	}
	public function rm($field = '')
	{
		$this->driver->clear($field);
	}
	public function write()
	{
		$this->driver->clear($field);
	}
	public function getPath(){
		return (defined('__SUB_HOST__') && __SUB_HOST__ != ''?'/'.__SUB_HOST__:"/");
	}
	public function killToken($token){
		$this->driver->kill($token);
	}
	
	public function destroy($expireTime = 0)
	{
		$host = $this->getHost();
		
		$this->driver->destroy();
		$expireTime = intval($expireTime);
		if(!$expireTime){
			$expireTime = self::$lifeTime;
		}
		$expireTime = time()+$expireTime;
		
		$sessionKey = $this->getSessionKey();
		
		if(defined('IN_COMMAND') && IN_COMMAND){
			$sessionFile = $this->getSessionFile();
			file_put_contents($sessionFile,$this->getSessionId());
		}else{
			setcookie($sessionKey,$this->getSessionId(),$expireTime,$this->getPath(),$host,0,1);		
			if($expireTime){
				setcookie($sessionKey.'_EXPIRE',$expireTime,$expireTime,$this->getPath(),$host,0,1);
			}else{
				setcookie($sessionKey.'_EXPIRE',$expireTime,-1,$this->getPath(),$host,0,1);
			}
		}
		$this->driver->setSessionId(self::$accessToken);
	}
	public function __destruct()
	{
		
	}
	
	public function __call($method,$args){
		
		$object = '';
		//日志
		if(in_array($method,array('log','error','info','waring'))){
			throw new Exception(implode(' ',$args),20001);
		}
		
		switch($method){
			case 'model':
				$method = ucfirst($method);
				$methodName = '';
				list($methodName) = $args;
				if(strpos($methodName,'#') !== false){
					list($methodName,$sub_method) = explode('#',$methodName);
				}
				if($methodName){
					$method = $methodName.$method;
					$object = new $method;
				}
				break;
			case 'cookie':
				$field = '';
				$expire = 0;
				switch(count($args)){
					case 1:
						list($field) = $args;
						break;
					case 2:
						list($field,$value) = $args;
						break;
					case 3:
						list($field,$value,$expire) = $args;
						break;
				}
				$object = new helper_cookie();
				
				if(isset($value)){
					$object = $object->set($field,$value,$expire);
				}else{
					$object = $object->get($field);
				}
				break;
			case 'service':
				$method = ucfirst($method);
				$methodName = '';
				list($methodName) = $args;
				if(strpos($methodName,'#') !== false){
					list($methodName,$sub_method) = explode('#',$methodName);
				}
				$method = $methodName.$method;
				
				$object = new $method;
				if(method_exists($object,'init')){
					$object->init();
				}
				break;
			case 'extend':
				//加载扩展
				list($extendName) = $args;
				$object = new $extendName;
				break;
			case 'session':
				$object = new $method;
				if(!empty($args)){
					if(count($args) > 1){
						list($field,$value) = $args;
						$object = $object->set($field,$value);
					}else{
						list($field) = $args;
						$object = $object->get($field);
					}
				}
				break;
			case 'memory':
			case 'cache':
				$methodDriver = 'memory';
				$object = new $methodDriver(array(),$method);
				if(!empty($args)){
					switch(count($args)){
						case 3:
							list($field,$value,$expireTime) = $args;
							$object = $object->set($field,$value,$expireTime);
							break;
						case 2:
							list($field,$value) = $args;
							$object = $object->set($field,$value);
							break;
						case 1:
							list($field) = $args;
							$object = $object->get($field);
							break;
					}
				}
				break;
				default:
					list($methodName) = $args;
					$method = $method.'_'.$methodName;
					$object = new $method;
				break;
		}
		return $object;	
	}
}
?>