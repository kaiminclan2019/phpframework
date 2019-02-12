<?php
class memory_driver_memcache
{
	private $config = array();
	
	const MEMORY_MEMCACHE_READ = 0;
	const MEMORY_MEMCACHE_WRITE = 1;
	
	
	public function __construct($config)
	{
		$this->config = $config;
	}
	private function indicent($log)
	{		
		$folder = __LOG__.'/memcache/'.date('Ym/d');
		if(!is_dir($folder))
		{
			mkdir($folder,0777,1);
		}
		
		$log = date('Y/m/d H:i:s').' '.$log;
		file_put_contents($folder.'/'.date('YmdH').'.log',$log.";\r\n",FILE_APPEND);
	}
	
	protected function connect($mode = 0){
		
		if(!class_exists('Memcache')){
			return -1;
		}
		if(empty($this->config)){
			return -2;
		}
		$pconnect = 0;
		if($this->config->xpath('pconnect')){
			$pconnect = (int)$this->config->pconnect;
		}
		
		if($this->config->xpath('default')){
			$config = $this->config->default;
		}
		switch($mode){
			case self::MEMORY_MEMCACHE_READ:
				if($this->config->xpath('read')){
					$config = $this->config->read;
				}
				break;
			case self::MEMORY_MEMCACHE_WRITE:
				if($this->config->xpath('write')){
					$config = $this->config->write;
				}
				break;
		}
		if($config->xpath('item')){
			$setting = array();
			foreach($config->item as $key=>$obj){
				$setting[] = array((int)$obj->weight,(string)$obj->host,(int)$obj->port);
			}
			
			$index = mt_rand(0,count($setting)-1);
			list(,$host,$port) = $setting[$index];
			
		}else{
			$host = (string)$config->host;
			$port = (int)$config->port;
		}
		
		$this->obj = new Memcache;
			
		$start = microtime(true);
		if($pconnect) {
			$connect = @$this->obj->pconnect($host, $port);
		} else {
			$connect = @$this->obj->connect($host, $port);
		}
		
		$time = microtime(true)-$start;
		$this->indicent('connect '.$config['host'].' '.$time);
		return 0;
	}

	public function get($key) {
		
		$conn = $this->connect(self::MEMORY_MEMCACHE_READ);
		if($conn === 0){
			$start = microtime(true);
			$result = $this->obj->get($key);
			$time = microtime(true)-$start;
			$this->indicent('get '.$key.' '.$time);
		}
		return $result;
	}

	public function getMulti($keys) {
		$conn = $this->connect(self::MEMORY_MEMCACHE_READ);
		if($conn === 0){
			$start = microtime(true);
			$data = $this->obj->get($keys);
			$time = microtime(true)-$start;
			$this->indicent('getmulti '.$key.' '.$time);
		}
		return $data;
	}
	public function set($key, $value, $ttl = 0) {
		
		$conn = $this->connect(self::MEMORY_MEMCACHE_WRITE);
		if($conn === 0){
			$start = microtime(true);
			$result = $this->obj->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
			$time = microtime(true)-$start;
			$this->indicent('set '.$key.' '.$time);
		}
		return $result;
	}

	public function rm($key) {
		$this->connect(self::MEMORY_MEMCACHE_WRITE);
		return $this->obj->delete($key);
	}

	public function clear() {
		$this->connect(self::MEMORY_MEMCACHE_WRITE);
		return $this->obj->flush();
	}

	public function inc($key, $step = 1) {
		$this->connect(self::MEMORY_MEMCACHE_WRITE);
		return $this->obj->increment($key, $step);
	}

	public function dec($key, $step = 1) {
		$this->connect(self::MEMORY_MEMCACHE_WRITE);
		return $this->obj->decrement($key, $step);
	}
}