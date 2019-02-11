<?php
/**
 * 控制器基类
 * 
 */
abstract class controller extends controller_helper_base {



	/**  
	 *
	 *  权限
	 *
	 *  public 公共  任何用户皆可访问
	 *
	 *  admin  系统  管理用户可访问
	 *
	 *  user   用户  登录用户可访问
	 *
	 *  guest  游客  非登录用户可访问
	 *
	 *  
	 */
	protected $permission = 'public';
	/**
	 * 
	 * 战备等级
	 * 5 任何人都可以访问，不做限制
	 * 4 需要验证码才能访问  来源检测
	 * 3 授权用户才能访问
	 * 2 管理员才能访问
	 * 1 开发人员才能访问
	 */
		
	protected $readiness = 5;
	
	/** 访问方式 */
	protected $method = 'get';
	/** 响应方式 */
	protected $accept;
	
	/** 标题 */
	protected $navtitle = '';
	/** 关键字 */
	protected $keywords = '';
	/** 描述 */
	protected $description = '';
	
	/** 面包屑*/
	protected $breadcrumb = array();
	
	/** 传输参数 */
	private $request_data = array();
	
	
	/** 布局 */
	protected $layout = '';
	protected $template = '';
	
	
	abstract protected function setting();
	abstract public function fire();
	
	/**
	 * 验证码
	 */
	public function captcha()
	{
	}
	
	
	protected function setTitle($title)
	{
		$this->navtitle = $title;
	}
	protected function setKeyword($keyword)
	{
		$this->keywords = $keyword;
	}
	protected function setDescription($description)
	{
		$this->description = $description;
	}
	protected function setBreadcrumb($title,$url = '')
	{
		$this->breadcrumb[] = array('title'=>$title,'url'=>$url);
	}
	/**
     * 战备等级
	 */
	final public function _get_readiness()
	{
		return $this->readiness;
	}
	/**
     * 面包屑
	 */
	final public function _get_breadcrumb()
	{
		return $this->breadcrumb;
	}
	
	/**
     * 访问控制	
	 */
	final public function getPerimission()
	{
		return $this->permission;
	}
	
	/**
     * 访问方式	
	 */
	final public function getMethod()
	{
		return $this->method;
	}
	
	
	final protected function addBreadcrumb($title,$href = '')
	{
		$this->breadcrumb[] = array('title'=>$title,'url'=>$href);
	}
	
	final public function _get_breadcrumb_data()
	{
		return $this->breadcrumb;
	}
	public function _set_request_data($request_data)
	{
		return $this->request_data = $request_data;
	}
	public function _get_layout()
	{
		return $this->layout;
	}
	public function _get_template()
	{
		return $this->template;
	}
	public function _get_accept()
	{
		return $this->accept;
	}
	
	
	public function _get_seo_data()
	{
		return array($this->navtitle,$this->keywords,$this->description);
	}
	
	public function display($template = ''){
		$this->template = $template;
	}
	
	protected function loadExcelData($attach){
		
		$fileTypeData = explode('.',$attach);
		$fileType = $fileTypeData[count($fileTypeData)-1];
		$filename = __ROOT__.$attach.'.source.'.$fileType;
		
		if(!is_file($filename)){
			$this->info('文件不存在'.$filename,400012);
		}
		
		
		require_once __ROOT__.'/vendor/PHPExcel/Classes/PHPExcel.php';
		
		$reader = PHPExcel_IOFactory::createReader('Excel5');
		$PHPExcel = $reader->load($filename); // 载入excel文件
		
		return $PHPExcel->getSheet(0)->toArray();
	}
}