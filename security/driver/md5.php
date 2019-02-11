<?php
class security_driver_md5
{
	private $salt;
	private $config;
	
	public function init($config)
	{
		$this->config = $config;
		return $this;
	}
	
	public function encrypt(){
		
		return md5(md5(md5($this->config['salt'].$this->config['data'])));
		
	}
	
	public function decrypt(){
		return $this->data;
	}
}