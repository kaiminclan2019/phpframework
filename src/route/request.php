<?php
/**
 * 路径
 */
class route_request {
	
	private $application = 'controller';
	private $block = '';
	private $action = '';
	// 参数
	private $seoData = array();
	
	private $permission = '';
	
	private $view = '';
	
	private $path;
	
	private $param;
	
	private $domain = '';
	
	private $filename;
	
	/**
	 * 路由关键字
	 * 一些特定的字符，在系统里有特别的所指
	 * m/touch 手机终端
	 * admin 登录请求
	 */
	private $disabledAction = array(
		'm','touch','admin'
	);
	
	/**
	 * 设备类型
	 *
	 */
	private $agent;
	
	public function __construct($request){
		$this->init($request);
	}
	public function init($requestUri){
		if($requestUri){
			$request_data = parse_url($requestUri);
			if(isset($request_data['path'])){
				if($request_data['path'] != '/' && strpos(substr($request_data['path'],1),'/') === false && strpos($request_data['path'],'.html') === false){
					if(__REQUEST_METHOD__ != 'CONSOLE'){
						throw new Exception('路由不存在',404);
					}
				}
				$this->path = $this->parse_path($request_data['path']);
			}
			
			
			if(isset($request_data['query'])){
				$param = $this->parse_query($request_data['query']);
				if(!empty($param)){
					foreach($param as $field=>$data){
						if(is_array($data) && empty($this->param[$field])){
							$this->param[$field] = $data;
						}else{
							if(strlen($data) > 0 && empty($this->param[$field])){
								$this->param[$field] = $data;
							}
						}
					}
				}
				//$this->param = $this->param?array_merge($param,$this->param):$param;
			}
		}
		if(!empty($_POST)){
			foreach($_POST as $field=>$data){
				$field = str_replace('\'','',$field);
				$field = str_replace('"','',$field);
				$this->param[$field] = $data;
			}
			application::$indicent['post'] = $this->param;
			$_POST = array();
		}
		application::$indicent['get'] = $_GET;
		$_GET = array();
		if(count($this->param) > 0){
			foreach($this->param as $field=>$data){
				$_GET[$field] = $data;
			}
		}
	}
	
	
	private function parse_path($path){
		if(defined('__APP_HOST__')){
			$hostList = explode(',',__APP_HOST__);
			foreach($hostList as $key=>$host){
				$subLen = strlen($host);
				if(strpos('/'.$host,$path) === 0){
					$path = substr($path,$subLen+1);
					break;
				}
				if(strpos($path,'/'.$host.'/') === false){
					continue;
				}
				$subHost = substr($path,1,$subLen);
				if(in_array($subHost,$hostList)){
					$path = substr($path,$subLen+1);
					break;
				}
			}
		}
		//关键字判断
		if(strpos($path,'/returnUrl') !== false){
			list($path,$returnUrl) = explode('/returnUrl',$path);
			$returnUrl = urldecode(substr($returnUrl,1));
			$value = str_replace('//','/',urldecode($value));
			$value = str_replace(':/','://',$value);
			$this->param['returnUrl'] = $returnUrl;
		}
		$param_data = array();
		if(strpos($path,'.html') !== false || strpos($path,'_') !== false){
			$rewrite = new route_rewrite($path);
			$path = $rewrite->getPath();
			if(!$path){
				throw new Exception('页面路由未定义',404);
			}
			$agent = $rewrite->getAgent();
			if($agent){
				$this->agent = $agent;
			}
			$view = $rewrite->getView();
			if($view){
				$this->view = $view;
			}
			$this->seoData = $rewrite->getSeoData();
			$this->permission = $rewrite->getPermission();
			$this->param = $rewrite->getParam();
			$this->domain = $rewrite->getDomain();
		}
		
		$pathArray = explode('/',$path);
		foreach($pathArray as $key=>$path){
			if(strlen($path) < 1 || $path == NULL){
				unset($pathArray[$key]);
			}
		}
		
		
		$path_data = array_values($pathArray);
		if($path_data){
			list($firstAction) = $path_data;
			if(in_array($firstAction,$this->disabledAction)){
				
				$this->agent = $firstAction;
				
				$path_data = array_slice($path_data,1);
				
			}
			foreach($path_data as $key=>$path){
				if(strpos($path,'.') !== false){
					list($action) = explode('.',$path);
					if(strpos($action,'_') !== false){
						$action_data = explode('_',$action);
						list($action) = $action_data;
					}
					$path_data[$key] = $action;
				}
			}
			$length = count($path_data);
			if(defined('__REQUEST_METHOD__') && __REQUEST_METHOD__ == 'CONSOLE'){
				list($this->block) = $path_data;
				$param_data = array_slice($path_data,1);
			}else{
				switch($length){
					case 1:
						list($this->block) = $path_data;
						break;
					case 2:
						list($this->block,$this->action) = $path_data;
						break;
					case 3:
					default:
						list($this->block,$this->action) = $path_data;
						$param_data = array_slice($path_data,2);
					break;
				}
			}
		}
		if($param_data){
			$length = count($param_data);
			foreach($param_data as $key=>$param){
				if(defined('__REQUEST_METHOD__') && __REQUEST_METHOD__ == 'CONSOLE'){
						$this->param[] = $param_data[$key];
				}else{
					if($key %2 == 1){
						$this->param[$param_data[$key-1]] = $param_data[$key];
					}
				}
			}
		}
		return $path_data;
	}
	
	public function getPermission(){
		return $this->permission;
	}

	
	public function getSeoData(){
		return $this->seoData;
	}
	public function getView(){
		return strlen($this->view) < 1?$this->action:$this->view;
	}
	
	public function getAgent(){
		return $this->agent;
	}
	
	public function parse_query($query){
		$param_data = array();	
		if(strpos($query,'&') === false && strpos($query,'=') === false){
			//不存在连接
			throw new Exception('页面不存在2',404);
		}
		
		//by 20180918 取消转义 针对登录接口调整 jqm 
		//$query = urldecode($query);
		
		$query_data = explode('&',$query);
		//echo '<pre>';		var_dump($query_data); die();
		foreach($query_data as $key=>$query){
			list($field,$value) = explode('=',$query);
			if($field == 'returnUrl'){
				$value = str_replace('//','/',urldecode($value));
				$value = str_replace(':/','://',$value);
				
			}
			
			$field = str_replace('\'','',$field);
			$field = str_replace('"','',$field);
			
			if(strpos($field,'[]') !== false){
				$field = substr($field,0,-2);
				$param_data[$field][] = $value;			
			}
			elseif(strpos($field,'[') !== false){
				$field = str_replace(']','',$field);
				$fieldData = explode('[',$field);
				switch(count($fieldData)){
					case 2:
						list($a,$b) = $fieldData;
						$param_data[$a][$b] = $value;	
					break;
					case 3:
						list($a,$b,$c) = $fieldData;
						$param_data[$a][$b][$c] = $value;	
					break;
					case 4:
						list($a,$b,$c,$d) = $fieldData;
						$param_data[$a][$b][$c][$d] = $value;	
					break;
				}		
			}
			else{
				$param_data[$field] = $value;
			}
		}
		return $param_data;
	}
	/**
	 * 应用
	 */
	public function getApplication(){
		return defined('__REQUEST_METHOD__') && __REQUEST_METHOD__ == 'CONSOLE'?__REQUEST_METHOD__:$this->application;
	}
	/**
	 * 模块
	 */
	public function getBlock(){
		return $this->block;
	}
	/**
	 * 操作
	 */
	public function getAction(){
		return $this->action;
	}
	
	/**
	 * 参数
	 */
	public function getDomain(){
		return $this->domain;
	}
	
	/**
	 * 参数
	 */
	public function getParam(){
		
		return $this->param;
	}
	
	public function getFile(){
		return $this->filename;
	}
}
?>