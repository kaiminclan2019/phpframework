<?php

//页面模块

class view_plug_block
{	
	private static $block = array();
	
	private static $cacheTime = 0;
	
	private static $cacheWeight = 0;
	
	public static function block($script,$param= '')
	{
		$blockData = array();
		
		if(array_key_exists('cacheTime',$param) && $param['cacheTime'] > 0) {
			self::$cacheTime = intval($param['cacheTime']);
			unset($param['cacheTime']);
		}
		
		if(array_key_exists('cacheWeight',$param)) {
			self::$cacheTime = intval($param['cacheWeight']);
			unset($param['cacheWeight']);
		}
		
		if(self::$cacheTime){
			$cacheKey = '';
			if(!empty($param)){
				$field = implode('_',array_keys($param));
				$values = implode('_',array_values($param));
				$cacheKey = md5($script.$field.$values);
			}
			
			list($url) = explode('.',__URL__);
			$folder = __STORAGE__.'/block'.'/'.__HOST__.(defined('__SUB_HOST__')?'/'.__SUB_HOST__:'');
			if(self::$cacheWeight){
				$urlData = explode('/',$url);				
				$urlData = array_slice($urlData,0,self::$cacheWeight);
				$url = implode('/',$urlData);
			}
			$folder = $folder.''.$url;
			
			if(!is_dir($folder)){
				mkdir($folder,0777,1);
			}
			
			
			$curTime = time();
			$cacheFile = $folder.'/'.$cacheKey.'.block.php';
			if(is_file($cacheFile)){
				$cacheData = file_get_contents($cacheFile);
				if($cacheData){
					$cacheData = json_decode($cacheData,true);
					if($curTime < $cacheData['expire_time']){
						$blockData = $cacheData['data']; 
					}
				}
			}
		}
		
		if(!$blockData){
			$script = ucfirst($script).'Block';
			if(!isset(self::$block[$script])){
				self::$block[$script] = new $script();
			}
			
			if(method_exists(self::$block[$script],'init')){
				self::$block[$script]->init($param,$limit,$start);
			}
			$blockData = self::$block[$script]->getdata($param);
			if(self::$cacheTime){
				if(array_key_exists('total',$blockData) && $blockData['total'] > 0){
					file_put_contents($cacheFile,json_encode(array('data'=>$blockData,'expire_time'=>$curTime+self::$cacheTime),JSON_UNESCAPED_UNICODE));
				}
			}
		}
		
		return $blockData;	
	}
	
	private function getCache($key){
		
	}
	
	private function writeCache($file,$data,$expireTime){
		
	}
}