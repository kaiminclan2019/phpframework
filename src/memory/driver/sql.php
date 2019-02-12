<?php
class memory_driver_sql
{
	public function __construct($config = array())
	{
		$this->init($config);
	}
	public function init($config)
	{
		
	}

	public function get($key) {
	}

	public function getMulti($keys) {
	}
	public function set($key, $value, $ttl = 0) {
	}

	public function rm($key) {
	}

	public function clear() {
	}

	public function inc($key, $step = 1) {
	}

	public function dec($key, $step = 1) {
	}
}