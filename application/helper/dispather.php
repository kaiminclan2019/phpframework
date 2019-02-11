<?php
/**
 *
 * 业务执行
 *
 */
class application_helper_dispather
{
	
	private $controller = null;
	private $instance = null;
	
	private $application = '';
	private $block = '';
	private $action = '';
	
	/** 布局 */
	private $layout = '';
	/** 模板 */
	private $template = '';
	
	/** 业务数据 */
	private $data = array();
	
	/** 默认权限　*/
	private $permission = 'public';
	/** 输出方式　*/
	private $accept;
	/** 战备等级 */
	private $readiness;
	/** 路径 */
	private $breadcrumb;
	/** 权限空间　*/
	private $allowed_list = array('guest','user','admin');
	
	private $request_data = array();
	
	/**
	 * 初始化
	 */
	public function __construct($application,$block,$action,$data){
		$this->init($application,$block,$action,$data);
	}
	public function init($application,$block,$action,$data){
		$this->application = empty($application)?'':ucfirst($application);
		$this->block = empty($block)?'':ucfirst($block);
		$this->action = empty($action)?'':ucfirst($action);
		$_GET = $data;
		
		//访问控制
		$permission = $this->permission;
		$allowed = $this->allowed_list;
		$application = ucfirst(strtolower($this->application));
		
		$filename = '';		
		if(__REQUEST_METHOD__ == 'CONSOLE'){
			$filename = (defined('__APP__')?__APP__:'').'/'.$application.'/'.ucfirst($this->block).ucfirst($this->action).$application.'.php';
			$ac = ucfirst($this->block).$application;			
		}else{
			if($this->block && $this->action){
				$filename = (defined('__APP__')?__APP__:'').'/'.$application.'/'.ucfirst($this->block).'/'.ucfirst($this->action).$application.'.php';
				$ac = ucfirst($this->action).$application;
				Loader::$folder[$ac] = $this->block;
			}
		}
		
		if($filename){
			try{
				$class = new ReflectionClass($ac);
			}
			catch(Exception $e){
				var_dump($e->getMessage()); die();
			}
			
			$this->controller = $class;
			
			$this->instance = $instance  = $class->newInstanceArgs();
			
			if(__REQUEST_METHOD__ != 'CONSOLE'){
				//传入数据
				//访问权限
				$method = $class->getMethod('getPerimission'); 
				$this->permission = $method->invoke($instance);
				//访问类型
				$method = $class->getMethod('_get_accept'); 
				$this->accept = $method->invoke($instance);
				
				$method = $class->getMethod('getMethod'); 
				$this->method = $method->invoke($instance);
				//访问类型
				$method = $class->getMethod('_get_breadcrumb'); 
				$this->breadcrumb = $method->invoke($instance);
				
				//战备等级
				$readiness = $class->getMethod('_get_readiness');
				$this->readiness = $method->invoke($instance);
				
			}
		}
		
	}
	/**
	 * 战备等级
	 */
	public function getReadiness(){
		return $this->readiness;
	}
	/**
	 * 路径
	 */
	public function getBreadcrumb(){
		return $this->breadcrumb;
	}
	/**
	 * 请求方式
	 */
	public function getAccept(){
		return $this->accept;
	}
	
	/**
	 * 布局
	 */
	public function getLayout(){
		return $this->layout;
	}
	
	/**
	 * 布局
	 */
	public function getTemplate(){
		return $this->template;
	}
	
	/**
	 * 页面数据
	 */
	public function getData(){
		if($this->controller != null && $this->controller->hasMethod('_get_seo_data'))
		{
			//获取数据
			$method = $this->controller->getMethod('_get_seo_data'); 
			list($navtitle_list[],$seokeyword,$seodescription) = $method->invoke($this->instance); 
			
		}
		$this->data['seotitle'] = implode('_',array_filter($navtitle_list));
		$this->data['seokeyword'] = $seokeyword;
		$this->data['seodescription'] = $seodescription;	
		
		return $this->data;
	}
	/**
	 * 页面权限
	 */
	public function getPermission(){
		return $this->permission;
	}
	/**
	 * 请求类型 GET POST
	 */
	public function getMethod(){
		return strtoupper($this->method);
	}
	/**
	 * 页面执行
	 */
	public function run(){
		
		$class = $this->controller;
		$instance = $this->instance;
		if($class){
			if($class->hasMethod('fire'))
			{
				$method = $class->getMethod('fire'); 
				$method->invoke($instance); 
				
			}
			if($class->hasMethod('_get_data'))
			{
				//获取数据
				$method = $class->getMethod('_get_data'); 
				$this->data = $method->invoke($instance); 
			}
			if($class->hasMethod('_get_layout'))
			{
				//获取布局
				$method = $class->getMethod('_get_layout'); 
				$this->layout = $method->invoke($instance); 
			}
			if($class->hasMethod('_get_template'))
			{
				//获取数据
				$method = $class->getMethod('_get_template'); 
				$this->template = $method->invoke($instance); 
			}
			if($class->hasMethod('_get_breadcrumb'))
			{
				//获取数据
				$method = $class->getMethod('_get_breadcrumb'); 
				$this->breadcrumb = $method->invoke($instance); 
			}
		}
	}
}
?>