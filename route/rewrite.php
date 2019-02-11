<?php
/**
 * 重写
 *
 * /view.html
 * /view_1.html
 * /news/12234.html
 * /news/12234_1.html
 * /news/12234-2-3-4-5.html
 * /news/afssdfdsf.html
 * /new/asdfdsfsdfsdf_1.html
 *
 */
class route_rewrite {
	private $path = '';
	
	private $application = '';
	private $block = '';
	private $action = '';
	// 参数
	private $param = array();
	private $seoData = array();
	
	private $permission = '';
	private $domain = '';
	
	private $view = '';
	
	private $setting = array();
	
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
	
	public function __construct($path){
		$this->init($path);
	}
	public function getSeoData(){
		return $this->seoData;
	}
	
	public function getAgent(){
		return $this->agent;
	}
	
	
	public function getParam(){
		return $this->param;
	}
	
	public function getDomain(){
		return $this->domain;
	}
	
	
	public function getView(){
		return $this->view;
	}
	
	public function getPermission(){
		return $this->permission;
	}

	public function init($path){
	    $routeFile = '';
	    $hostRouteFile = __ROOT__.'/config/'.__DOMAIN__.'.xml';
	    if(is_file($hostRouteFile)){
	        $routeFile = $hostRouteFile;
        }else{
            $routeFile = __ROOT__.'/config/route.xml';
        }
		$settingList = array();
		if(is_file($routeFile)){
			$xml = simplexml_load_string(file_get_contents($routeFile));
			foreach($xml->route as $key=>$route){
				$seo = $route->xpath('seo')?$route->seo:'';
				$urlName = (string)$route->url;
				$field = $fieldConfig = array();
				if($route->xpath('param')){
					foreach($route->param as $key=>$param){
						$tField = (string)$param->code;
						$field[] = $tField;
						$config = array(
							'type'=>(string)$param->type,
							'tooltip'=>(string)$param->name
						);
						
						if($param->xpath('value')){
							$config['value'] = (string)$param->value;
						}
						
						$fieldConfig[$tField] = $config;
						
					}
				}
				$settingList[$urlName] = array(
					'blockclass'=>(string)$route->folder,
					//'script'=>'',
					'setting'=>implode('|',$field),
					'filter'=>$fieldConfig,
					'layout'=> $route->xpath('layout')?(string)$route->layout:'',
					'domain'=> $route->xpath('domain')?(string)$route->domain:'',
					'permission'=>(string)$route->permission,
					'primaltplname'=>empty($route->template)?$urlName:(string)$route->template,
					'seotitle'=>empty($seo)?'':(string)$seo->title,
					'seokeyowrd'=>empty($seo)?'':(string)$seo->keyword,
					'seodescription'=>empty($seo)?'':(string)$seo->description,
				);
			}
		}
		$settingFile = __CACHE__.'/menu.php';
		if(is_file($settingFile)){
			$this->setting = json_decode(file_get_contents($settingFile),true);
		}
		//数据组装
		if($this->setting){
			$this->setting = $settingList;
		}else{
			$this->setting = array_merge($this->setting,$settingList);
		}
		
		$path = urldecode($path);
		
		if(strpos($path,'.') !== false){
			list($path) = explode('.',$path);
		}
		
		//终端识别
		
		if(strpos($path,'/m/') !== false){
			$path = str_replace('/m/','/',$path);
		}
		$path = substr($path,1);
		//参数识别
		if(strpos($path,'_') !== false){
			$temp = explode('_',$path);
			list($path) = $temp;
			$paramArray = array_values(array_slice($temp,1));
		}
				
		if(strpos($path,'/') !== false){
			$path = str_replace('/','-',$path);
		}
		
		$pathArray = explode('/',$path);
		foreach($pathArray as $key=>$path){
			if(strlen($path) < 1 || $path == NULL){
				unset($pathArray[$key]);
			}
		}
		$pathArray = array_values($pathArray);
		
		//识别关键字
		list($firstAction) = $pathArray;
		if(in_array($firstAction,$this->disabledAction)){
			
			$this->agent = $firstAction;
			
			$pathArray = array_values(array_slice($pathArray,1));
			
		}
		
		$pathData = array();
		list($folder) = $pathArray;
		if(is_numeric($folder)){
			$pathArray[0] = 'DATE';
		}
		$argLen = count($pathArray);
		if($argLen > 1){
			$firstFolder = '';
			$argList = array();
			if($isSubFolder){
				$firstFolder = $pathArray[0].'/';
			}else{
				$argList[] = $pathArray[0];
			}
			$folder = $firstFolder.implode('_',$argList);
		}
		if($paramArray){
			$pathArray = array_merge($pathArray,$paramArray);
		}
		//提取路由信息
		if(isset($this->setting[$folder])){
			$menuData = $this->setting[$folder];
			$this->seoData = array(
				(empty($menuData['seotitle'])?$menuData['title']:$menuData['seotitle']),
				$menuData['seokeyword'],
				$menuData['seodescription'],
			);
			
			
			//权限
			if(isset($menuData['permission'])){
				$this->permission = strtolower($menuData['permission']);
			}
			//域名
			if(isset($menuData['domain'])){
				$this->domain = strtolower($menuData['domain']);
			}
			
			//模块
			if(!empty($menuData['blockclass'])){
				$pathData[] = $menuData['blockclass'];
			}
			
			//控制器或模板
			if(!empty($menuData['script'])){
				$pathData[] = $menuData['script'];
			}else{
				//没有定义控制器，那么此处的参数作为视图使用
				if(isset($menuData['primaltplname']) && strlen($menuData['primaltplname']) > 0){
					$this->view = $menuData['primaltplname'];
				}else{
					if(strpos($folder,'_{') !== false){
						list($this->view) = explode('_',$folder);
					}else{
						$this->view = $folder;
					}
				}
			}
			$setting = array_filter(explode('|',$menuData['setting']));
			if($setting){
				$pathArray = array_slice($pathArray,1);
				$settingLen =  count($setting);
				$filter = $menuData['filter'];
				foreach($setting as $cnt=>$field){
					
					if(isset($pathArray[$cnt])){
						$value = $pathArray[$cnt];
						unset($pathArray[$cnt]);
					}
					elseif(isset($filter[$field]['value'])){
						$value = $filter[$field]['value'];
					}else{
						continue;
					}
					$this->param[$field] = $value;
				}
				$len = count($this->param);
				
				if(strcmp($len, $settingLen) !== 0){
					throw new Exception('页面不存在1',404);
				}
				
				foreach($setting as $key=>$field){
					$value = 0;
				}
			}else{
				//未定义参数
				//重置参数数组
				$pathArray = array();
			}
			
			$pathData = array_merge($pathData,$pathArray);
			$this->path = $pathData;
		}
	}
	/*
	 *
	 */
	public function getPath(){
		
		return implode('/',$this->path);
	}
}
?>