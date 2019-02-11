<?php
class session_drive_memcache {
	private $sessionId = '';
	
	private $lifetime = 0;
	private $session_data = '';	
	
	private $obj;
	
	public function __construct($config){
		$this->obj = new memory_driver_memcache($config);
		$this->lifetime = (string)$config->lifetime;
		$this->setSessionId($config->sessionid);
	}
	
	public function init(){
		$this->session_data = $this->obj->get($this->sessionId);
	}
	
	public function setSessionId($sessionId)
	{
		$this->sessionId = 'sess_'.$sessionId;
	}
	
	public function set($field,$value, $expire=600)
	{
		$this->session_data[$field] = $value;
		$this->session_data['visit_time'] = time();
		
		$this->obj->set($this->sessionId,$this->session_data,$expire);
	}
	
	public function get($field = '')
	{
		$output = '';
		if($field)
		{
			if(is_array($this->session_data)){
				if(array_key_exists($field,$this->session_data))
				{
					$output = $this->session_data[$field];
					$this->session_data['visit_time'] = time();
					$this->obj->set($this->sessionId,$this->session_data,$this->lifetime);
				}
			}
		}else{
			$output = $this->session_data;
		}
		return $output;
	}
	
	public function destroy()
	{
		$this->obj->rm($this->sessionId);
	}
}