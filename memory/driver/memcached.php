<?php
class memory_driver_memcached
{
	public function __construct($config = array())
	{
		$this->init($config);
	}
	public function init($config)
	{
		if(!class_exists('Memcached')){
			die('Memcached 未开启');
		}
		
		$this->obj = new Memcached;
		if($config['pconnect']) {
			$connect = @$this->obj->pconnect($config['host'], $config['port']);
		} else {
			$connect = @$this->obj->connect($config['host'], $config['port']);
		}
		$this->enable = $connect ? true : false;
	}

	public function get($key) {
		return $this->obj->get($key);
	}

	public function getMulti($keys) {
		return $this->obj->get($keys);
	}
	public function set($key, $value, $ttl = 0) {
		return $this->obj->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
	}

	public function rm($key) {
		return $this->obj->delete($key);
	}

	public function clear() {
		return $this->obj->flush();
	}

	public function inc($key, $step = 1) {
		return $this->obj->increment($key, $step);
	}

	public function dec($key, $step = 1) {
		return $this->obj->decrement($key, $step);
	}
}