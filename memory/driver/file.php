<?php
class memory_driver_file
{
	private $max = 1000;
	private $cnt = 0;
	
	public function __construct($config = array())
	{
		$this->init($config);
	}
	public function init($config)
	{
	}
	
	private function getFile($key){
		if(strpos($key,'#') !== false){
			$key = str_replace('#','_',$key);
		}
		$filename = $key.'.cache.php';
		
		$filename = str_replace('_','/',$filename);
				
		return __CACHE__.'/'.$filename;
	}

	public function get($key) {
		$data = array();
		$file = $this->getFile($key);
		$currTime = time();
		if(is_file($file)){
			$cacheData = file_get_contents($file);
			if($cacheData){
				$cacheData = json_decode($cacheData,true);
				if($cacheData){
					if($currTime > $cacheData['expire_time']){
						unlink($file);
					}
					$data = $cacheData['data'];
				}
			}
		}
		return $data;
	}

	public function getMulti($keys) {
	}
	public function set($key, $value, $ttl = 0) {
		$file = $this->getFile($key);
		$folder = dirname($file);
		if(!is_dir($folder)){
			mkdir($folder,0777,1);
		}
		
		$currTime = time();
		$html = json_encode(array('dateline'=>$currTime,'expire_time'=>$currTime+60*60,'data'=>$value));
		$result = file_put_contents($file,$html);
		if(!$result){
		}
	}

	public function delete($key) {
		$file = $this->getFile($key);
		if(is_file($file)){
			unlink($file);
		}
		if(is_dir($file)){
			$this->removeFolder(__DATA__,$file);
			$this->cnt = 0;
		}
	}
	
	private function removeFolder($rootFolder,$folder){
		$jump = array('.','..','index.htm');
		$handle = opendir($folder);
		if($handle){
			while(($filename = readdir($handle)) !== false){
				if(in_array($filename,$jump)){
					continue;
				}
				$subFolder = $folder.'/'.$filename;
				if(is_dir($subFolder)){
					$this->removeFolder($rootFolder,$subFolder);
				}
				if(is_file($subFolder)){
					@unlink($subFolder);
					$this->cnt++;
				}
			}
		}
		closedir($handle);
		if($folder != $rootFolder){
			rmdir($folder);
			$this->cnt++;
		}
		
		if($this->cnt > $this->max){
			return '';
		}
	}

	public function clear() {
	}

	public function inc($key, $step = 1) {
	}

	public function dec($key, $step = 1) {
	}
	public function flush(){
	}
}