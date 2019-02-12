<?php
/**
 * 　视图
 
 *　配置信息
 *  编译文件
 *  数据绑定
 *  信息返回
 */
class view {
	
	/** 业务驱动　 */
	private $driver = '';
	private $accept;
	/**
	 * 主题
	 */
	private $theme;
	/** 业务（用户，后台） */
	private $business;
	/** 布局 */
	private $layout;
	/** 模块 */
	private $block;
	/** 操作 */
	private $action;
	/** 编码 */
	private $charset;
	/** 数据 */
	private $data;
	
	/**
	 * 初始化
	 **/
	public function __construct($accept,$theme,$business,$layout,$charset,$block,$action,$data){
		$this->init($accept,$theme,$business,$layout,$charset,$block,$action,$data);
	}
	/**
	 * 初始化
	 */
	public function init($accept,$theme,$business,$layout,$charset,$block,$action,$data){
		$this->accept = $accept;
		switch($this->accept){
			case 'application/xml':
				$this->driver =  new view_driver_xml();
			break;
			case 'application/json':
				$this->driver =  new view_driver_json();
			break;
			case 'text/plain':
				$this->driver =  new view_driver_plain();
			break;
			case 'text/html':
			default:
				$this->driver =  new view_driver_html($theme,$business,$layout,$block,$action);
			break;
		}
		
		
		$this->theme = $theme;
		$this->business = $business == 'admin'?'backend':'';
		$this->layout = $layout;
		$this->charset = $charset;
		$this->block = $block;
		$this->action = $action;
		$this->data = $data;
	}
	
	/**
	 * 编译模板
	 */
	private function compile(){
		$compile_dir = './';
		if(defined('__COMPILE__')){
			$compile_dir = __COMPILE__;
		}
		
		$this->driver->compile();
	}
	/**
	 * 缓存文件
	 */
	private function cache(){
		$cache_dir = './';
		if(defined('__CACHE__')){
			$cache_dir = __CACHE__;
		}
		$cache_dir = $cache_dir.'/page';
	}
	/**
	 * 静态文件
	 */
	private function makehtml(){
		$html_dir = './';
		if(defined('__HTML__')){
			$html_dir = __HTML__;
		}
	}
	
	/**
	 * 业务输出
	 */
	public function dispather(){
		if(!isset($this->data['status'])){
			$this->data['status'] = 200;
		}
		if($this->driver){
			
			ob_end_clean();
			ob_start();
			$this->driver->dispather($this->data);
			
			$e = ob_get_contents();
			$this->writeCache($e);
			
			exit();
		}
	}
	
	/**
	 *
	 * 静态资源写盘
	 *
	 */
	private function writeCache($data){
		if(strtoupper(__APP_PERMISSION__) != 'PUBLIC'){
			return ;
		}
		
				
		if(!$data){
			return '';
		}
		if(!defined('__APP_HTML__')){
			return '';
		}
		
		if(__APP_HTML__ === false){
			return ;
		}
		
		
		$disabledMask = array(':','"','}');		
		foreach($disabledMask as $key=>$mask){			
			if(strpos(urldecode(__URL__),$mask) !== false){
				return '';
			}
		}
		
		$filename = 'index.html';
					
		
		list($urlPath) = explode('?',__URL__);
		
		
		$urlPathList = array_values(array_filter(explode('/',$urlPath)));
		
		if(strpos(__URL__,'.') !== false){
			$len = count($urlPathList);
			$filename = $urlPathList[$len-1];
			$urlPathList = array_slice($urlPathList,0,$len-1);
		}
		if(strpos($filename,'.html') !== false){
			$folder = '';
			$fileType = '';
			
			switch($this->accept){
				case 'application/xml':
					$folder= 'xml';
					$fileType = '.xml';
				break;
				case 'application/json':
					$folder= 'json';
					$fileType = '.json';
				break;
				case 'text/plain':
					$folder= 'plain';
					$fileType = '.txt';
				break;
				case 'text/html':
				default:
					$folder= 'html';
					$fileType = '.html';
				break;
			}
			
			$folder = __DATA__.'/'.$folder.'/'.__HOST__.'/'.implode('/',$urlPathList);
			if(!is_dir($folder)){
				$result = mkdir($folder,0777,1);
				if(!$result){
					
				}
			}
			$filename = $folder.'/'.$filename.$filetype;
			$result = file_put_contents($filename,$data);
		}
	}
	/**
	 * 操作默认
	 */
	public function __call($method,$args){
	
		if(strpos($method,'_') !== false)
		{
			$method = str_replace('_','',$method);
		}
		
		if(strpos($method,'=') !== false)
		{
			list($method,$argsStr) = explode('=',$method);
			if(strpos($argsStr,',') !== false){
				$args = array_merge($args,explode(',',$argsStr));
			}
			elseif(is_array($argsStr)){
				$argsStr = array($argsStr);
			}
			
			$args = array_merge($args,$argsStr);
			
		}
		
		$plugin_name = $object = 'view_helper_'.$method;
		
		if(empty(self::$plugin) || !array_key_exists($plugin_name,self::$plugin))
		{
			$object = new $object;
			self::$plugin[$plugin_name] = $object;
		}
		call_user_func_array(array(self::$plugin[$plugin_name], 'init'), $args);
			
		return self::$plugin[$plugin_name]->get();
	}
	/**
	 * 资源释放
	 */
	public function __destruct(){
	}
}