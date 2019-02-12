<?php
/**
 *
 * 浏览器-CDN资源服务器-HTML/JSON/XML/TEXT-PHP文件-MEMORY-DATABASE
 *
 */
class Memory
{	
	private static $cache;
	
	private static $allowed_cache = false;
	
	public function __construct($config = array(),$driver = ''){
		if($driver == 'cache'){
			$driver = 'file';
			$prefix = 0;
			$charset = 0;
			$host = 0;
			$port = 0;
		}else{
			$driver = '';
			if(empty(self::$cache)){
				//数据库
				$filename = __ROOT__.'/config/cache.xml';
				if(is_file($filename)){
					$config = simplexml_load_string(file_get_contents($filename));
					if(!$config){
						throw new exception('缓存配置文件加载失败',1100);
					}
					$driver = current($config->driver);
					if($driver != 'file'){
						$setting = $config->connections->$driver;
					}
				}
			}
		}
		
		if($driver){
				
			$driver = 'memory_driver_'.$driver;		
			self::$cache = new $driver($setting);		
			self::$allowed_cache = true;	
		}
	}
	public function get($key) 
	{
		if(!self::$allowed_cache || empty(self::$cache)){
			return array();
		}
		return self::$cache->get($key);
	}
	public function set($key, $value, $ttl = 0) {
		if(empty(self::$cache)){
			return -1;
		}
		if(!self::$allowed_cache){
			return -2;
		}
		return self::$cache->set($key, $value,$ttl);
	}

	public function rm($key) {
		if(!self::$allowed_cache){
			return -1;
		}
		return self::$cache->delete($key);
	}

	public function clear() {
		if(empty(self::$cache)){
			new Memory();
		}
		if(self::$allowed_cache){
			return -1;
		}
		return self::$cache->flush();
	}
	
	public function __destruct(){
	}
	
	public function __call($method,$args){
	}
}