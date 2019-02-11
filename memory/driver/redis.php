<?php
class memory_driver_redis
{
	
	const MEMORY_REDIS_READ = 0;
	const MEMORY_REDIS_WRITE = 1;
	public function __construct($config = array())
	{
		$this->config = $config;
	}
	
	protected function connect($mode = 0){
		
		if(!class_exists('Redis')){
			return -1;
		}
		if(empty($this->config)){
			return -2;
		}
		
		$pconnect = 0;
		if($this->config->xpath('pconnect')){
			$pconnect = (int)$this->config->pconnect;
		}
		$requirepass = 0;
		if($this->config->xpath('requirepass')){
			$requirepass = (int)$this->config->requirepass;
		}
		$serializer = 0;
		if($this->config->xpath('serializer')){
			$serializer = (int)$this->config->serializer;
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
		
		try {
			$this->obj = new Redis;
				
			$start = microtime(true);
			if($pconnect) {
				$connect = @$this->obj->pconnect($host, $port);
			} else {
				$connect = @$this->obj->connect($host, $port);
			}
			
			$time = microtime(true)-$start;
			$this->indicent('connect '.$config['host'].' '.$time);
		} catch (RedisException $e) {
			
		}
		if($connect) {
			if($requirepass) {
				$this->obj->auth($requirepass);
			}
			@$this->obj->setOption(Redis::OPT_SERIALIZER, $serializer);
		}
		return 0;
	}
	
	

	public function get($key) {
		
		$conn = $this->connect(self::MEMORY_REDIS_READ);
		if($conn === 0){
			if(is_array($key)) {
				return $this->getMulti($key);
			}
			return $this->obj->get($key);
		}
	}

	public function getMulti($keys) {
		
		$conn = $this->connect(self::MEMORY_REDIS_READ);
		if($conn === 0){
			$result = $this->obj->getMultiple($keys);
			$newresult = array();
			$index = 0;
			foreach($keys as $key) {
				if($result[$index] !== false) {
					$newresult[$key] = $result[$index];
				}
				$index++;
			}
			unset($result);
		}
		return $newresult;
	}

	public function select($db=0) {
		
		$conn = $this->connect(self::MEMORY_REDIS_READ);
		if($conn === 0){
			return $this->obj->select($db);
		}
	}

	public function set($key, $value, $ttl = 0) {
		
		$conn = $this->connect(self::MEMORY_REDIS_WRITE);
		if($conn === 0){
			if($ttl) {
				return $this->obj->setex($key, $ttl, $value);
			} else {
				return $this->obj->set($key, $value);
			}
		}
	}

	public function rm($key) {
		
		$conn = $this->connect(self::MEMORY_REDIS_WRITE);
		if($conn === 0){
			return $this->obj->delete($key);
		}
	}

	public function setMulti($arr, $ttl=0) {
		
		$conn = $this->connect(self::MEMORY_REDIS_WRITE);
		if($conn === 0){
			if(!is_array($arr)) {
				return FALSE;
			}
			foreach($arr as $key => $v) {
				$this->set($key, $v, $ttl);
			}
		}
		return TRUE;
	}

	public function inc($key, $step = 1) {
		
		$conn = $this->connect(self::MEMORY_REDIS_WRITE);
		if($conn === 0){
			return $this->obj->incr($key, $step);
		}
	}

	public function dec($key, $step = 1) {
		
		$conn = $this->connect(self::MEMORY_REDIS_WRITE);
		if($conn === 0){
			return $this->obj->decr($key, $step);
		}
	}

	public function getSet($key, $value) {
		
		$conn = $this->connect(self::MEMORY_REDIS_WRITE);
		if($conn === 0){
			return $this->obj->getSet($key, $value);
		}
	}

	public function sADD($key, $value) {
		
		$conn = $this->connect(self::MEMORY_REDIS_WRITE);
		if($conn === 0){
			return $this->obj->sADD($key, $value);
		}
	}

	public function sRemove($key, $value) {
		
		$conn = $this->connect(self::MEMORY_REDIS_WRITE);
		if($conn === 0){
			return $this->obj->sRemove($key, $value);
		}
	}

	public function sMembers($key) {
		
		$conn = $this->connect(self::MEMORY_REDIS_WRITE);
		if($conn === 0){
			return $this->obj->sMembers($key);
		}
	}

	public function sIsMember($key, $member) {
		return $this->obj->sismember($key, $member);
	}

	public function keys($key) {
		return $this->obj->keys($key);
	}

	public function expire($key, $second){
		return $this->obj->expire($key, $second);
	}

	public function sCard($key) {
		return $this->obj->sCard($key);
	}

	public function hSet($key, $field, $value) {
		return $this->obj->hSet($key, $field, $value);
	}

	public function hDel($key, $field) {
		return $this->obj->hDel($key, $field);
	}

	public function hLen($key) {
		return $this->obj->hLen($key);
	}

	public function hVals($key) {
		return $this->obj->hVals($key);
	}

	public function hIncrBy($key, $field, $incr){
		return $this->obj->hIncrBy($key, $field, $incr);
	}

	public function hGetAll($key) {
		return $this->obj->hGetAll($key);
	}

	public function sort($key, $opt) {
		return $this->obj->sort($key, $opt);
	}

	public function exists($key) {
		return $this->obj->exists($key);
	}

	public function clear() {
		return $this->obj->flushAll();
	}
}