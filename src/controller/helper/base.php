<?php

class controller_helper_base {
	/** 页面数据 */
	protected $data = array();
	
	
	private static $filter;
	private static $setting = array();
	
	public function _get_data()
	{
		return $this->data;
	}
	/***
	 *
	 * 设置数据
	 * @param $field  字段
	 * @param $value  数据
	 *
	 * @return mixed;
	 */
	protected function assign($field,$value){
		$this->data[$field] = $value;
	}
	
	/***
	 *
	 * IP地址
	 *
	 * @return long;
	 */
	protected function getClientIp(){
		return ip2long(__CLIENTIP__);
	}
	
	
	/***
	 *
	 * 设备码
	 *
	 * @return string;
	 */
	protected function getDeviceCode(){
		return md5(__CLIENTIP__.__AGENT__);
	}
	
	protected function config($field){
		if(!$field){
			return '';
		}
		
		$type = 'app';
		if(strpos($field,'#') !== false){
			list($type,$field) = explode('#',$field);
		}
		
		if(strpos($field,'.') !== false){
			$field = explode('.',$field);
		}
		
		$configFile = __ROOT__.'/config/'.$type.'.xml';
		if(is_file($configFile)){
			$xml = simplexml_load_file($configFile);
			if(!$xml){
				$xml = simplexml_load_string(file_get_contents($configFile));
			}
			$subField = '';
			if(is_array($field)){
				list($field,$subField) = $field;
			}
			$isExists = $xml->xpath($field);
			if($isExists){
				if(strlen($subField)){
					$data = (array)$xml->$field->$subField;
				}else{
					$data = (array)$xml->$field;
				}
				if(count($data) < 2){
					$data = current($data);
				}
			}
			return $data;
		}
		
		return '';
		
	}
	
	/***
	 *
	 * 获取信息
	 * 
	 */
	protected function argument($field){
		
		$setting = $this->setting();
		if(empty(self::$setting)){
			self::$setting = $setting;
		}else{
			foreach($setting as $key=>$config){
				if(isset(self::$setting[$key])){
					continue;
				}
				self::$setting[$key] = $config;
			}
		}
		/**
		 * post
		   get
			cookie
			session
			header
 		 */
		
		if(empty(self::$filter)){
			include_once __ROOT__.'/vendor/PHPFilter/PHPFilter.php';
			self::$filter = new PHPFilter();
		}
		
		$value = self::$filter->init($field,self::$setting)->_toData();
		return $value;
	}
	
	final protected function array_remove_field($array,$fields = array()){
		if(is_array($array)){
			
			foreach($array as $field=>$data){
				if(in_array($field,$fields)){
					unset($array[$field]);
				}
			}
		}
		return $array;
	}	
	/***
	 *
	 * 消息
	 *
	 */
	protected function info($msg,$status){
		$pattern = '/[a-zA-Z0-9_]/';
		if(preg_match($pattern,$msg)){
			$package = 'common';
			if(defined('__LANG__') && defined('__LANG_CODE__')){
				$filename = __LANG__.'/'.__LANG_CODE__.'/lang_'.$package.'.php';
				if(is_file($filename)){
					$langData = include_once($filename);
					if(isset($langData[$msg])){
						$msg = $langData[$msg];
					}
				}
			}
		}
		throw new Exception($msg,$status);
	}
	/**
	 *
	 *
	 */
	protected function debug($msg){
		if(is_array($msg)){
			echo '<pre>';
		}
		var_dump($msg);
	}
	
	
	/***
	 *
	 * 用户信息
	 *
	 */
	protected function getUID(){
		return intval($this->session('uid'));
	}
	
	/***
	 *
	 * 用户时间
	 *
	 */
	protected function getTime(){
		return time();
	}
	
	/***
	 *
	 * 序列号
	 *
	 */
	protected function get_sn(){
		return date('YmdHis');
	}
	
	private function parseFile($method){
		$len = strlen($method);
		$mathes = array(ucfirst(substr($method,0,1)));
		for($i=1;$i<$len;$i++){
			$letter = substr($method,$i,1);
			if(preg_match('/[A-Z]/',$letter)){
				break;
			}
			$mathes[] = $letter;
		}
		$folder = implode('',$mathes);
		$method = $folder.'_'.$method;
		return $method;
	}
	/***
	 *
	 * 默认方法
	 *
	 */
	public function __call($method,$args){
		
		$object = '';
		//日志

		if(in_array($method,array('log','notice','warning','error','crit','alert','emerg','panic'))){			
			array_unshift($args,$method);
			$method = 'incident';
		}
		
		switch($method){
			case 'model':
				$method = ucfirst($method);
				$methodName = '';
				list($methodName) = $args;
				if(strpos($methodName,'#') !== false){
					list($methodName,$sub_method) = explode('#',$methodName);
				}
				if($methodName){
					$method = $methodName.$method;
					$object = new $method;
				}
				break;
			case 'cookie':
				$field = '';
				$expire = 0;
				switch(count($args)){
					case 1:
						list($field) = $args;
						break;
					case 2:
						list($field,$value) = $args;
						break;
					case 3:
						list($field,$value,$expire) = $args;
						break;
				}
				$object = new helper_cookie();
				
				if(isset($value)){
					$object = $object->set($field,$value,$expire);
				}else{
					$object = $object->get($field);
				}
				break;
			case 'service':
				$method = ucfirst($method);
				$methodName = '';
				list($methodName) = $args;
				if(strpos($methodName,'#') !== false){
					list($methodName,$sub_method) = explode('#',$methodName);
				}
				$method = $methodName.$method;
				
				$object = new $method;
				if(method_exists($object,'init')){
					$object->init();
				} 
				break;
			case 'extend':
				//加载扩展
				list($extendName) = $args;
				$object = new $extendName;
				break;
			case 'session':
				$object = new $method;
				if(!empty($args)){
					if(count($args) > 1){
						list($field,$value) = $args;
						$object = $object->set($field,$value);
					}else{
						list($field) = $args;
						$object = $object->get($field);
					}
				}
				break;
			case 'incident':
				$status = 0;
				switch(count($args)){
					case 2:
						list($method,$msg) = $args;
						break;
					case 3:
						list($method,$msg,$status) = $args;
						break;
				}
				$methodDriver = 'Incident';
				$object = new $methodDriver($method,$msg,$status);
				break;
			case 'memory':
			case 'cache':
				$methodDriver = 'memory';
				$object = new $methodDriver(array(),$method);
				if(!empty($args)){
					switch(count($args)){
						case 3:
							list($field,$value,$expireTime) = $args;
							$object = $object->set($field,$value,$expireTime);
							break;
						case 2:
							list($field,$value) = $args;
							$object = $object->set($field,$value);
							break;
						case 1:
							list($field) = $args;
							$object = $object->get($field);
							break;
					}
				}
				break;
				default:
					list($methodName) = $args;
					$method = $method.'_'.$methodName;
					$object = new $method;
				break;
		}
		return $object;	
	}
}