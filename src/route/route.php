<?php
/**
 * 路由
 主机
 路径
 参数
 *
 */
class route {
	/** 主机 */
	private $host = '';
	private $method = '';
	private $request = '';
	private $seoData = array();
	
	private $permission = '';
	
	/** 设备 */
	private $agent = '';
	
	/** 应用 */
	private $application = '';
	
	/** 模块 */
	private $block = '';
	
	/** 操作 */
	private $action = '';
	private $domain = '';
	/** 参数 */
	private $param = array();
	
	/**
	 * 初始化
	 **/
	public function __construct($host,$request,$method){
		$this->init($host,$request,$method);
	}
	/**
	 * 初始化
	 * @param $setting 配置信息
	 */
	public function init($host,$request,$method){
		//主机
		$this->host = $host;
		$this->method = in_array($method,array('get','post'))?$method:'';
		$request = new route_request($request);
		
		$this->agent = $request->getAgent();
		//应用
		$this->application = $request->getApplication();
		
		$this->block = $request->getBlock();
		
		$this->action = $request->getAction();
		$this->template = $request->getView();
		$this->seoData = $request->getSeoData();
		$this->permission = $request->getPermission();
		$this->domain = $request->getDomain();
		
		//参数
		$this->param = $request->getParam();
		if($this->param){
			foreach($this->param as $field=>$value){
				if(is_array($value)){
					continue;
				}
				$this->param[$field] = urldecode($value);
			}
		}
		
	}
	
	/**
	 * 参数
	 */
	public function getDomain(){
		return $this->domain;
	}
	
	
	
	public function getPermission(){
		return $this->permission;
	}
	/**
	 * 设备
	 */
	public function getSeoData(){
		return $this->seoData;
	}
	/**
	 * 设备
	 */
	public function getAgent(){
		return $this->agent;
	}
	/**
	 * 应用
	 */
	public function getApplication(){
		return $this->application;
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
	public function getTemplate(){
		return $this->template;
	}
	/**
	 * 参数
	 */
	public function getParam(){
		return $this->param;
	}
	
	/**
	 * 应用ID
	 */
	public function getAppId(){
		return 0;
	}
	
	/**
	 * 操作ID
	 */
	public function getActionId(){
		return 0;
	}
	
	/**
	 * 完整地址
	 */
	public function getFullUrl(){
		return $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * 操作默认
	 */	
	public function __call($method,$args){
	}
	/**
	 * 资源释放
	 */
	public function __destruct(){
		
	}
}