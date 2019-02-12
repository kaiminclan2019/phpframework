<?php
class session_drive_file {
	private $sessionId = '';
	private $database = '';
	private $prefix = '';
	private $lifetime = 0;
	private $sessionfile = '';
	private $session_data = '';
	public function __construct($config){
		$this->database = __STORAGE__.(string)$config->database;
		$this->prefix = (string)$config->prefix;
		$this->lifetime = (string)$config->lifetime;
		$this->sessionId = $config->sessionid;
	}
	
	public function init(){
		
		if(!is_dir($this->database))
		{
			mkdir($this->database,0777,1);
		}
		
		$this->lifetime = intval($this->lifetime) > 0 ? $this->lifetime:1440;
		$this->sessionfile = $this->database.'/sess_'.$this->sessionId;
		
		if(is_file($this->sessionfile))
		{
			$this->session_data = json_decode(file_get_contents($this->sessionfile),true);
		}
	}
	
	public function setSessionId($sessionId)
	{
		$this->sessionId = $sessionId;
		$this->sessionfile = $this->database.'/sess_'.$this->sessionId;
		
	}
	
	public function kill($token){
		$sessionFile = $this->database.'/sess_'.$token;
		if(is_file($sessionFile)){
			unlink($sessionFile);
		}
	}
	
	public function set($field,$value, $expire=600)
	{
		if(is_file($this->sessionfile))
		{
			$this->session_data = json_decode(file_get_contents($this->sessionfile),true);
		}
		$this->session_data[$field] = $value;
		$this->session_data['__lifetime__'] = $this->lifetime;
		$this->session_data['visit_time'] = time();
		$result = 0;
		$result =file_put_contents($this->sessionfile,json_encode($this->session_data));
		$this->clearfile();
		//file_put_contents(__LOG__.'/session_write_'.date('Ymd').'.log',$this->sessionfile."\r\n",FILE_APPEND);
		
	}
	
	protected function clearfile()
	{
		$lifeTime = $this->lifetime;
		$dirname = dirname($this->sessionfile);
		$handle = opendir($dirname);
		$cookieExpireTime = intval($_COOKIE['PHPAUTHID_EXPIRE']);
		$cookieExpireTime = 0;
		if($cookieExpireTime){
			$lifeTime = $cookieExpireTime;
		}
		while($filename = readdir($handle))
		{
			if(in_array($filename,array('.','..')))
			{
				continue;
			}
			$filename = $dirname.'/'.$filename;
			clearstatcache();
			$visitTime = fileatime($filename);
			if(time() > ($visitTime+$lifeTime))
			{
				$msg = date('Y/m/d H:i:s').'-visitTime:'.$visitTime.' cookieExpireTime:'.$cookieExpireTime.'lifeTime:'.$lifeTime;
				file_put_contents(__LOG__.'/clean_session_'.date('Ym').'.txt',$msg."\r\n",FILE_APPEND);
				unlink($filename);
			}
		}
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
					file_put_contents($this->sessionfile,json_encode($this->session_data));
				}
			}
		}else{
			$output = $this->session_data;
		}
		
		$this->clearfile();
		return $output;
	}
	 /**
     * 清除session
     * @param  String  $name  session name
     */
    public function clear($name)
	{
		$output = true;
			if(array_key_exists($name,$this->session_data))
			{
        		unset($this->session_data[$name]);
			}
		return $output;
    }
	
	public function destroy()
	{
		unlink($this->sessionfile);
		if(is_file($this->sessionfile)){
        }
		//file_put_contents(__LOG__.'/session_error_'.date('Ymd').'.log',"deleted ".$this->sessionfile.' '.($isUnLinks?1:0),FILE_APPEND);
	}
}