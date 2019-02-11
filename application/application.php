<?php
/**
 * 应用
 *
 环境(系统引擎（php），系统配置，空间（硬盘，内存）)
 组件(消息，日志，安全，数据,插件)
 设备(PC/WAB/APP,ip,agent,url)
 路由(主机，模式)
 用户(身份，权限，信息)
 视图(js/css/html/tag) 
 */
class application {
	public static $indicent = array();
	
	private $app = null;
	
	private $title;
	private	$keyword;
	private	$description;	
	
	private $domain = '';
	
	//认证地址
	private $auth = array();
	//权限
	private $permission;
	
	private $security;
	/**
     * 初始化	
	 */
	public function __construct(){		
		libxml_disable_entity_loader(true);
		//自动加载
		require_once __ROOT__.'/vendor/PHPBamboo/application/helper/loader.php';
		spl_autoload_register( array('Loader','load'));
		
		//错误托管
		//错误处理
		set_error_handler(array('application_helper_error','callError'));
		//异常
		set_exception_handler(array('application_helper_exception','callException'));
		//中断接管
		register_shutdown_function(array('application_helper_shutdown','callShutDown'));
		
		//版本限定
		$php_version = substr(str_replace('.','',PHP_VERSION),0,2);
		if($php_version < 55){
			die('The PHP version must be more than 5.5');
		}
		$diskFreeSpace = disk_free_space(__ROOT__);
		if($diskFreeSpace < 1024){
			die('Disk unavailable space');
		}
		//磁盘空间小于100MB，报警
		if($diskFreeSpace < 104857600){
			
		}
		//扩展
		$loaded_extension  = get_loaded_extensions();
		
		libxml_disable_entity_loader(TRUE);
		
		$this->init_setting();
		
		//初始化架构
		new application_helper_framework();
		
		
	}
	
	/**
	 *
     * 调试
	 *
	 */
	private function deubug($data){
		
	}
	
	private function init_user(){
		$this->session = new session();
	}
	
	private function checkmobile() {
		$mobile = array();
		$touchbrowser_list =array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
					'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
					'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
					'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
					'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
					'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
					'benq', 'haier', '^lct', '320x320', '240x320', '176x220', 'windows phone');
		$wmlbrowser_list = array('cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom',
				'pantech', 'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',
				'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte');

		$pad_list = array('ipad');
		
		$isMobile = 0;
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
		foreach($touchbrowser_list as $key=>$agent){
			if(strpos($useragent,$agent) !== false){
				$isMobile = 1;
				break;
			}
		}
		if(!$isMobile) {
			foreach($wmlbrowser_list as $key=>$agent){
				if(strpos($useragent,$agent) !== false){
					$isMobile = 1;
					break;
				}
			}
		}
		if(!$isMobile) {
			foreach($pad_list as $key=>$agent){
				if(strpos($useragent,$agent) !== false){
					$isMobile = 1;
					break;
				}
			}
		}
		
		return $isMobile;

	}
	
	/**
	 *
     * 初始化配置
	 *
	 */
	private function init_setting(){
		
		//应用
		$filename = __ROOT__.'/config/app.xml';
		if(!is_file($filename)){
			throw new exception('未定义应用配置文件');
		}
		$app = simplexml_load_string(file_get_contents($filename));
		if(!$app){
			throw new exception('配置文件加载失败');
		}
				
		//时区
		date_default_timezone_set(current($app->timezone));
		//根地址
		define('__SITE_URL__',current($app->domain));
		//页面模式
		$debug = current($app->debug);
		if(is_numeric($debug)){
			$debug = intval($debug);
			$debug = $debug  < 1?false:true;
		}else{
			$debug = strtolower($debug);
			$debug = $debug == 'false'?false:true;
		}
		$this->auth = (array)$app->auth;
		
		$security = (array)$app->security;
		$csrf = false;
		$release = false;
		if(isset($security['csrf'])){
			$csrf = $security['csrf'];
			$csrf = $csrf == 'false'?false:true;
			$release = $release == 'release'?true:false;
		}
		define('__APP_SECURITY_CSRF__',$csrf);
		define('__APP_DEBUG__',$debug);
		define('__APP_DEUBG_RELEASE__',$release);
		//底层版本
		define('__APP_VERSION__','0.1.1');
		
		$html = current($app->html);
		if(is_numeric($html)){
			$html = intval($html);
			$html = $html  < 1?false:true;
		}else{
			if($html === 'true'){
				$html = true;
			}
			if($html === 'false'){
				$html = false;
			}
		}
		define('__APP_HTML__',$html);
		if($app->xpath('seo')){
			$this->title = (string)$app->seo->title;
			$this->keyword = (string)$app->seo->keyword;
			$this->description = (string)$app->seo->description;
		}
		
		$this->app = $app;
		
		error_reporting(0);
		
		//扩展
		$filename = __ROOT__.'/config/extensions.xml';
		if(is_file($filename)){
			$extensions = simplexml_load_string(file_get_contents($filename));
		}
	}
	
	/**
     * 空间	
	 */
	public function run(){
		//系统配置
		$settingFile = __STORAGE__.'/'.current(explode('.',__SITE_URL__)).'.php';
		if(is_file($settingFile)){
			$settingData = file_get_contents($settingFile);
			$settingData = json_decode($settingData,true);
			
			$this->title = $settingData['sitename'];
			$this->title = $settingData['title'];
			$this->keyword = $settingData['keyword'];
			$this->description = $settingData['description'];
		}
		
		//客户端构建
		$client = new application_helper_client();
		
		//请求空间
			
		define('__HOST__',$client->getHost());		
		//请求空间
		define('__DOMAIN__',$client->getDomain());
		
		//请求设备
		define('__AGENT__',$client->getAgent());
		define('__ACCEPT__',$client->getAccept());
		
		//来源地址
		define('__REFERER__',$client->getReferer());
		//客户ＩＰ
		define('__CLIENTIP__',$client->getClientIp());
		//请求方式
		define('__REQUEST_METHOD__',strtoupper($client->getMethod()));
		define('IN_COMMAND',!in_array(__REQUEST_METHOD__,array('GET','POST')));
		define('__URL__',$client->getRequetUri());
		define('__FULL_URL__',$client->getFullDomain().__URL__);
		//语言类型
		define('__LANG_CODE__',$client->getLanguage());
		
		
		//检测主机
		if($this->app && strcmp(__HOST__,'m') === 0){
			$hostList = (string)$this->app->host;
			define('__APP_HOST__',$hostList);
			$hostList = explode(',',$hostList);
			
			if(!in_array(__HOST__,$hostList)){
				throw new exception(__HOST__.'未定义');
			}
			$subHost = '';
			foreach($hostList as $key=>$host){
				if(strpos('/'.$host,__URL__) === 0){
					$subHost = $host;
					break;
				}
				
				$mask = '/'.$host.'/';
				if(strpos(__URL__,$mask) === false){
					continue;
				}
				$subLen = strlen($host);
				$subHost = substr(__URL__,1,$subLen);
				if(in_array($subHost,$hostList)){
					break;
				}
				$subHost = 'www';
			}
			if($subHost != 'www' && in_array($subHost,$hostList)){
				define('__SUB_HOST__',$subHost);		
			}
		}
		
		
		//检测域名
		if(!IN_COMMAND){
			if(strpos(__SITE_URL__,',') !== 0){
				$domainData = explode(',',__SITE_URL__);
				if(!in_array(__DOMAIN__,$domainData)){
					$this->info('no defined domain'.__DOMAIN__);
				}
			}else{
				if(strpos(__SITE_URL__,__DOMAIN__) !== 0){
					$this->redirect('http://'.__HOST__.'.'.__SITE_URL__.__URL__);
				}
			}
		}
		
		$isMobile = (__HOST__ == 'm')?1:0;
		
		//路由识别
		$setting = array();
		$route = new route($client->getHost(),$client->getRequetUri(),$client->getMethod());
		if($isMobile < 1){
			if(in_array($route->getAgent(),array('m','touch'))){
				$isMobile = 1;
			}
		}
		if(!$isMobile){
			$isMobile = $this->checkmobile();
		}
		if($this->app->xpath('mobile') && intval($this->app->mobile->auto_jump) == 1 && $isMobile && __HOST__ != 'm'){
			$subHost = __HOST__;
			if($subHost == 'www'){
				$subHost = '';
			}else{
				$subHost = '/'.$subHost;
			}
			$newUrl = 'http://m.'.($_SERVER['REMOTE_ADDR'] == '127.0.0.1'?'test.':'').__DOMAIN__.$subHost.__URL__;
			$this->redirect($newUrl);
		}
		$domain = $route->getDomain();
		if(strlen($domain) > 0 && !in_array($domain,array('m','www'))){
			$settingDomain = array();
			if(strpos($domain,',') !== false){
				$settingDomain = explode(',',$domain);
			}else{
				$settingDomain[] = $domain;
			}
			$settingDomain[] = 'm';
			$settingDomain = array_unique($settingDomain);
			if(!in_array(__HOST__,$settingDomain)){
				
				$this->info('不存在的页面',404);
			}
			foreach($settingDomain as $key=>$domain){
				if(in_array($domain,array('m','www'))){
					unset($settingDomain[$key]);
				}
			}
			if(in_array(__HOST__,array('m')) && !defined('__SUB_HOST__')){
				list($subHost) = array_values($settingDomain);	
				$subHost = '/'.$subHost;			
				$newUrl = 'http://m.'.($_SERVER['REMOTE_ADDR'] == '127.0.0.1'?'test.':'').__DOMAIN__.$subHost.__URL__;
				if($isMobile){
					$this->redirect($newUrl);
				}
			}
			
		}
		$this->init_user();
		define('IN_MOBILE',$isMobile);
		
		$currentMethod = $client->getMethod();

		//业务执行
		$data = array();
		$dispather = new application_helper_dispather($route->getApplication(),$route->getBlock(),$route->getAction(),$route->getParam());
		
		$dispatherAccept = $dispather->getAccept();
		
		if(empty($dispatherAccept)){
			$dispatherAccept = $client->getAccept();
		}
		//请求类型
		define('__ACCEPT__',$dispatherAccept);
		
		//访问请求拦截
		$dispatherMethod = $dispather->getMethod();
		
		if($dispatherMethod == 'POST'){
		
			//URL,同一个IP,同一个代理，同一个来源，同一个访问方式
			$accessHash = md5(__FULL_URL__.__CLIENTIP__.__AGENT__.__REFERER__.__REQUEST_METHOD__);
			
			$allowAccessHash = $this->session->get('allowAccessHash');
			
			if($allowAccessHash && strcmp($allowAccessHash,$accessHash) !== 0){
				//$this->info('稍后，正在处理..',1004);
			}
			$this->session->set('allowAccessHash',$accessHash);
			
			if(defined('__APP_SECURITY_CSRF__') && __APP_SECURITY_CSRF__ == true){
				$routeData = $route->getParam();
				if(strcmp(__REQUEST_METHOD__,$dispatherMethod) !== 0){
					$this->info('无权限操作',1001);
				}
				
				
				if(!isset($routeData['__hash__']) && !$routeData['__HASH__']){
					$this->info('未定义的访问来源',1002);
				}
				
				$hashCode = isset($routeData['__hash__'])?$routeData['__hash__']:'';
				if(empty($hashCode)){
					$hashCode = isset($routeData['__HASH__'])?$routeData['__HASH__']:'';
				}
				
				if(!security_helper_csrf::checkToken($hashCode)){				
					$this->info('此操作未授权',1003);
				}
			}
		}
		
		//战备等级
		//1016验证码
		$readiness = $dispather->getReadiness();
		switch($readiness){
			case 4:
				//验证码
				
			break;
		}
		//权限
		$permission = '';
		$routePermission = $route->getPermission();
		$dispatherPermission = $dispather->getPermission();
		if(strlen($routePermission) > 0){
			$permission = $routePermission;
		}else{
			$permission = $dispatherPermission;
		}
		$this->permission = $permission;
		//权限
		define('__APP_PERMISSION__',$this->permission);
		
		$uid = intval($this->session->get('uid'));
		$roleType = intval($this->session->get('roleType'));
		$allowAction = $this->session->get('allowAction');
		if(is_array($allowAction)){
			$allowAction = array_map('intval',$allowAction);
		}
		$actionId =  $route->getActionId();
		
		if(in_array($permission,array('admin','user','supplier','client')) && $uid < 1){
			$this->info('还没有登录',1010);
		}
		elseif(!in_array($actionId,$allowAction)){
			//$this->info('没有权限',1013);
		}
		switch($permission){
			case 'guest':
				if($uid >0){
					$this->info('欢迎回来',1011);
				}
				break;
			case 'admin':
				if(!in_array($roleType,array(1,2))){
					$this->info('没有权限',1013);
				}
				break;
		}
		
		$startTime = time();
		$dispather->run();	
		$endTime = time();
		
		if($endTime > $startTime && ($endTime-$startTime) > 3){
			$folder = __LOG__.'/'.date('Ym/d');
			if(!is_dir($folder)){
				mkdir($folder,0777,1);
			}
			file_put_contents($folder.'/'.date('YmdH').'.txt',"\r\n".__CLIENTIP__.' '.date('H:i:s y-m-d').' '.__FULL_URL__.' '.$endTime-$startTime.' '.__AGENT__."\r\n");
		}
		//执行日志
		//执行人，IP地址，消耗掉时间
		//完成地址
		define('__URL__','/'.$route->getBlock().'/'.$route->getAction());
		
		
		$dispatherData = $dispather->getData();
		$routeSeoData = $route->getSeoData();
		if($routeSeoData){
			if(empty($dispatherData['seotitle'])){
				$dispatherData['seotitle'] = $routeSeoData[0];
			}
			if(empty($dispatherData['seokeyword'])){
				$dispatherData['seokeyword'] = $routeSeoData[1];
			}
			if(empty($dispatherData['seodescription'])){
				$dispatherData['seodescription'] = $routeSeoData[2];
			}
		}else{
			$dispatherData['seotitle'] = $this->title;
		}
		//$dispatherData['seotitle'] .= (!empty($dispatherData['seotitle']) ?'-':'').$this->title;
		if(empty($dispatherData['seokeyword'])){
			$dispatherData['seokeyword'] = $this->keyword;
		}
		if(empty($dispatherData['seodescription'])){
			$dispatherData['seodescription'] = $this->description;	
		}
		
		$dispatherData['breadcrumb'] = $dispather->getBreadcrumb();
		
		//模版主题
		$theme = (string)$this->app->style;
		if(!($primaltplname =$dispather->getTemplate())){
			$primaltplname = $route->getAction();
			if(!$primaltplname){
				$primaltplname = $route->getTemplate();
			}
		}
		//布局
		$layout = $dispather->getLayout();
		$layout = empty($layout)?(string)$this->app->layout:$layout;
		//编码
		$charset = (string)$this->app->charset;
		
		$view = new view($dispatherAccept,$theme,$permission,$layout,$charset,$route->getBlock(),$primaltplname,$dispatherData);
		$view_content = $view->dispather();
		
		//数据输出
		$output = new application_helper_output($view_content,$client->getEncoding());
		$output->send();		
	}
	
	protected function getLoginUrl(){
		$loginUrl = '/';
		if(isset($this->auth[$this->permission])){
			$loginUrl = $this->auth[$this->permission];
		}
		$currentUrl = $_SERVER['REQUEST_URI'];
		if($currentUrl){
			if(substr($currentUrl,0,8) == '/doctor/'){
				$loginUrl = '/doctor/doctorlogin.html';
			}
		}
		
		$fromUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if(strlen($this->fromUrl) > 0){
			$fromUrl = $this->fromUrl;
		}
		elseif(strpos($fromUrl,'.html') === false){
			$fromUrl = $_SERVER['HTTP_REFERER'];
			if(empty($fromUrl)){
				$fromUrl = '/';
			}
		}
		
		return $loginUrl.'?fromUrl='.$fromUrl;
	}
	
	protected function getHomeUrl(){
		$loginUrl = '/';
		$currentUrl = $_SERVER['REQUEST_URI'];
		if($currentUrl){
			if(substr($currentUrl,0,8) == '/doctor/'){
				$loginUrl = '/doctor/doctorhome.html';
			}
		}
		return $loginUrl;
	}
	
	protected function info($msg,$status = 200){
		ob_start();
		ob_end_clean();
		
		if(in_array($status,array(1010,1011,1013))){
			$refererUrl = __URL__;
			if(strpos($refererUrl,'.html') === false){
				$refererUrl = $_SERVER['HTTP_REFERER'];
			}
			if(strpos($refererUrl,'.html') !== false){
				$this->cookie('refererUrl',$refererUrl);
			}
		}
		switch(__ACCEPT__){
			case 'text/html':
				if($status == 1010)
				{
					$this->redirect($this->getLoginUrl());
				}elseif($status == 1011){
					$this->redirect($this->getHomeUrl());
				}
				break;
			case 'application/json':
				$loginMsg = array(
					'status'=>$status,
					'msg'=>$msg
				);	
				if($status == 1010){
					$loginMsg['redirect_uri'] = $this->getLoginUrl();
				}		
				if($status == 1011){
					$loginMsg['redirect_uri'] = $this->getHomeUrl();
				}
				echo json_encode($loginMsg);exit();
			break;
		}
		$this->error($msg,$status);
	}
	
	protected function redirect($url){
		if(defined('__REQUEST_METHOD__') && __REQUEST_METHOD__ != 'CONSOLE'){
			ob_start();
			ob_end_clean();
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Cache-Control: no-cache");
			header("Pragma: no-cache");
			header('location:'.$url);
			exit();
		}
	}
	
	/**
	 * 操作默认
	 */
	public function __call($method,$args){
		
		switch($method){
			case 'cookie':
				$field = '';
				$value = '';
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
				
				if($value){
					$object = $object->set($field,$value,$expire);
				}else{
					$object = $object->get($field);
				}
				break;
		}
		return $object;
	}
	
	protected function error($msg,$status = 1000){
		throw new Exception($msg,$status);
	}
	
	/**
     * 资源释放	
	 */
	public function __destruct(){
		$this->session->set('allowAccessHash','');
		$debug = new application_helper_debug();
		$debug->dispather();
		
		
	}
}