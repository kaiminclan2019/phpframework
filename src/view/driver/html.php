<?php
/**
 * 模板编译
 * 
 * 标签识别
 * 
 */
class view_driver_html
{
	
	private static $plugin = array();
	private $theme = '';
	private $sub_theme = '';
	private $layout = '';
	private $block = '';
	private $action = '';
	
	private $viewType = '.htm';
	
	private $indexTemplate = 'main';
	
	private $fileList = array();
	
	private static $parseFileList = array();
	
	/** 错误消息 */
	private $errors = '';
	
	private $hashCode = '';
	
	/**
	 * 错误消息
	 */
	private function error($local_code,$status = 1010){
		throw new Exception($local_code,$status);
	}
	
	public function __construct($theme,$permission,$layout,$block,$action)
	{
		if(defined('IN_MOBILE') && IN_MOBILE == 1){
			$theme = $theme.'/touch';
		}
		$this->theme = $theme;
		$this->sub_theme = $permission;
		//子主题说明
		//admin 
		//public
		//member
		//guest
		
		$this->layout = $layout?$layout:$this->indexTemplate;
		$this->block = strtolower($block);
		$this->action = strtolower($action);
		
		$this->hashCode = security_helper_csrf::getToken();
	}
	
	public function location_get($field,$default){
		$output = '';
		if(isset($_GET[$field])){
			$output = $_GET[$field];
			if(!is_numeric($output)){
				$output = '"'.$output.'"';
			}
		}else{
			$output = $default;
		}
		
		return $output;
	}
	
	private function preg_match_block_attr($html){
	
		//取出所有block标签文本  
		$c1 = preg_match_all('/<block\s.*?>/', $html, $m1);  
		
		//对所有的block标签进行取属性  
		for($i=0; $i<$c1; $i++) {    
			//匹配出所有的属性  
			$c2 = preg_match_all('/(\w+)\s*=\s*(?:(?:(["\'])(.*?)(?=\2))|([^\/\s]*))/', $m1[0][$i], $m2);  
			//将匹配完的结果进行结构重组  	
			for($j=0; $j<$c2; $j++) {    
				$list[$i][$m2[1][$j]] = !empty($m2[4][$j]) ? $m2[4][$j] : $m2[3][$j];  
			}  
		}  
		return $list;
	}
	
	private function get_block_where($html){
		$list = array();
		$c2 = preg_match_all('/(\w+)\s*=\s*(?:(?:(["\'])(.*?)(?=\2))|([^\/\s]*))/', $html, $m2);  
		//将匹配完的结果进行结构重组  	
		for($j=0; $j<$c2; $j++) {    
			$list[$m2[1][$j]] = !empty($m2[4][$j]) ? $m2[4][$j] : $m2[3][$j];  
		}
		
		//如果BLOCK参数在GET发现有相同的键存在，数据已GET理的为准
		foreach($list as $field=>$data){
			
			if(isset($_GET[$field])){
				$list[$field] = htmlspecialchars($_GET[$field]);
			}
			
			application::$indicent['get'][$field] = $data;
		}
		
		
		
		return $list;
	}
	public function getDebug(){
		
		return Debug::get();
	}
	public function getHash(){
		
		$hash = $this->hashCode;
		$this->cookie('SECURITY_CSRF',$hash);
		
		return $this->hashCode;
	}
	
	/**
	 * 模板编译
	 */
	protected function parse_template($template,$mtime)
	{
		//头部
		$text = array();
		if(!empty($this->fileList)){
			foreach($this->fileList as $key=>$file){
				$text[] = '$this->refresh("'.$file[0].'","'.$file[1].'");';
				
				application::$indicent['view'][] = $file[0];
			}
		}
		$template = '<?php '.implode(" ?>\n<?php ",$text)." ?>\n".$template;
		
		$template = "<?php if(!defined('__BAMBOO__')) //exit('Access Denied');?>\n$template";
		
		$mtime = intval($mtime);
		if($mtime > strtotime('2018-07-08')){
			//过滤样式
        	$template  =   preg_replace('/style=.+?[\'|\"]/is','',$template);		
			//过滤JS代码
        	$template  =   preg_replace('/<script[\s\S]((?!src).)*?<\/script>/is','',$template);        
		}
		//过滤注释
        $template  =   preg_replace('#<!--[^\!\[]*?-->#','',$template);
		//页面DNA
        $template  =   preg_replace('/{__DEBUG__}/is','<?php echo $this->getDebug();?>',$template);
		
        $template  =   preg_replace('/{__HASH__}/is','<?php echo $this->getHash() ?>',$template);
		//页面DNA
        $template  =   preg_replace('/{__HASHCODE__}/is','TEST',$template);
		//版本处理
        $template  =   preg_replace('/{__VERSION__}/is','?v='.date('YmdHis'),$template);
		//常量处理
        $template  =   preg_replace('/{__([A-Z_]+)__}/is','<?php echo (defined(\'\\1\')?\\1:\'\') ?>',$template);
		//链接处理
        $template  =   preg_replace('/{\$([\'\[\]a-z0-9_\/.:]+)\|base_url}/is','<?php echo $this->base_url($\\1) ?>',$template);
        
		$template  =   preg_replace('/{([\'\[\]$a-z0-9_\/.:]+)\|base_url}/is','<?php echo $this->base_url(\'\\1\') ?>',$template);
		
        $template  =   preg_replace('/{([\'\[\]$a-z0-9_\/.:]+)\|base_url}/is','<?php echo $this->base_url(\'\\1\') ?>',$template);
		//系统函数
        $template  =   preg_replace('/\$(location)\.session\.('.$rules.')/is','$this->session(\'\\2\')',$template);
        $template  =   preg_replace('/\$(location)\.cookie\.('.$rules.')/is','$this->cookie(\'\\2\')',$template);
        $template  =   preg_replace('/\$(location)\.cache\.('.$rules.')/is','$this->cache(\'\\2\')',$template);
		
		
		//插件处理
		$rule = '/([\n\r\t]*)\<helper\sid="(\S+)"\sname="(\S+)"\s([-$_\[\]{}a-zA-Z0-9\.\/\'\"\=\s]+)\s\/>([\n\r\t]*)/is';
		$template = preg_replace($rule,'<?php  $\\2 = $this->\\3($this->get_block_where(\'\\4\')); ?>', $template);
		
		//全局数据
		$rules = '[\_a-zA-Z]+';
        $template  =   preg_replace('/{\$(location)\.('.$rules.')}/is','<?php echo htmlspecialchars_decode($_GET[\'\\2\']) ?>',$template);
        $template  =   preg_replace('/{\$(location)\.('.$rules.')\.('.$rules.')\.('.$rules.')}/is','<?php echo htmlspecialchars_decode($_GET[\'\\2\'][\'\\3\'][\'\\4\']) ?>',$template);
        $template  =   preg_replace('/{\$(location)\.('.$rules.')\.('.$rules.')}/is','<?php echo htmlspecialchars_decode($_GET[\'\\2\'][\'\\3\']) ?>',$template);
        $template  =   preg_replace('/\$(location)\.('.$rules.')/is','$_GET[\'\\2\']',$template);
		
		//参数处理
		$rule = '/([\n\r\t]*)\<url\sname="(\S+)"\sdefault="(\S+)"\s\/>([\n\r\t]*)/is';
		$template =    preg_replace($rule,'<?php  echo $this->location_get(\'\\2\',\\3) ?>', $template);
		
		//模块处理		
		
		//模块
		//来源
		//接口，条件，数量，排序
		//缓存，有效期，权重
		$rule = '/([\n\r\t]*)\<block\sid="(\S+)"\sname="(\S+)"\sparam="\$(\S+)"\s\/>([\n\r\t]*)/is';
		$template =    preg_replace($rule,'<?php  $\\2 = view_plug_block::block(\'\\3\',$\\4) ?>', $template);
		
		$rule = '/([\n\r\t]*)\<block\sid="(\S+)"\sname="(\S+)"\sparam="(\S+)"\s\/>([\n\r\t]*)/is';
		$template =    preg_replace($rule,'<?php  $\\2 = view_plug_block::block(\'\\3\',\'\\4\') ?>', $template);
		
		$rule = '/([\n\r\t]*)\<block\sid="(\S+)"\sname="(\S+)"\s\/>([\n\r\t]*)/is';
		$template =    preg_replace($rule,'<?php  $\\2 = view_plug_block::block(\'\\3\') ?>', $template);
		
		//高级版
		//提取BLOCK标签里的属性为条件传递
		$rule = '/([\n\r\t]*)\<block\sid="(\S+)"\sname="(\S+)"\s([-$_\[\]{}a-zA-Z0-9\'\"\=\s]+)\s\/>([\n\r\t]*)/is';
		$template = preg_replace($rule,'<?php  $\\2 = view_plug_block::block(\'\\3\',$this->get_block_where(\'\\4\')) ?>', $template);
		
		
        $template  =   preg_replace('/\"\$(\w+)"/is','\'.$\\1.\'',$template);
		
        $template  =   preg_replace('/\"\$([_a-zA-Z\[\]\'\"]+)"/is','\'.$\\1.\'',$template);
		
		//变量
		//默认数据
        $template  =   preg_replace('/{\$(\w+)\|default=\'(\S+)\'}/is','<?php echo $this->setVals($\\2,\'\\3\') ?>',$template);
        $template  =   preg_replace('/{\$(\w+)\.(\w+)\|default=\'(\S+)\'}/is','<?php echo $this->setVals($\\1[\'\\2\'],\'\\3\') ?>',$template);
		
		//语言包
        $template  =   preg_replace('/{\$(\w+)\.(\w+)\|lang\s*}/is','<?php echo $this->lang(\\2,$\\1["\\2"]) ?>',$template);
		
        $template  =   preg_replace('/{\$([_a-zA-Z0-9\.\[\]\'\"]+)\|(\S+)\s*}/is','<?php echo $this->\\2($\\1) ?>',$template);
        
		$template  =   preg_replace('/{\$(\w+)\.(\w+)\.(\w+)\.(\w+)\s*}/is','<?php echo ($\\1["\\2"]["\\3"]["\\4"]) ?>',$template);
		
        $template  =   preg_replace('/{\$(\w+)\.(\w+)\.(\w+)\s*}/is','<?php echo ($\\1["\\2"]["\\3"]) ?>',$template);
        $template  =   preg_replace('/{\$(\w+)\.(\w+)\s*}/is','<?php echo ($\\1["\\2"]) ?>',$template);
		
        $template  =   preg_replace('/{\$(\w+)\s*}/is','<?php echo ($\\1) ?>',$template);
        $template  =   preg_replace('/{\$(\S+)\s*}/is','<?php echo ($\\1) ?>',$template);

		$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\.}/s", "<?php echo (\\1)?>", $template);
		
		
		if(strpos($template,'</form>') !== false)
		{
			
			$template = str_replace('</form>','<input type="hidden" name="__hash__" value="<?php echo $this->getHash() ?>" />'."\r\n".'</form>',$template);
		}
		
		if(strpos($template,'<eq ') !== false)
		{
			$template = view_plug_tag::_if($template);
		}
		if(strpos($template,'<neq ') !== false)
		{
			$template = view_plug_tag::_neq($template);
		}
		
		if(strpos($template,'<gt ') !== false)
		{
			$template = view_plug_tag::_gt($template);
		}
		
		if(strpos($template,'<egt ') !== false)
		{
			$template = view_plug_tag::_egt($template);
		}
		
		if(strpos($template,'<lt ') !== false)
		{
			$template = view_plug_tag::_lt($template);
		}
		if(strpos($template,'<elt ') !== false)
		{
			$template = view_plug_tag::_elt($template);
		}
		if(strpos($template,'<empty ') !== false)
		{
			$template  = view_plug_tag::_empty($template);
		}
		if(strpos($template,'<notempty ') !== false)
		{
			$template  = view_plug_tag::_notempty($template);
		}
		if(strpos($template,'<volist ') !== false)
		{
			$template = view_plug_tag::_vlist($template);
		}
		
		if(strpos($template,'<php>') !== false)
		{
			$template = view_plug_tag::_php($template);
		}
		if(strpos($template,'<range ') !== false)
		{
			$template = view_plug_tag::_range($template);
		}
		if(strpos($template,'<block ') !== false)
		{
			$template = view_plug_tag::_block($template);
		}
		return $template;
	}
	/**
	 * 操作默认
	 */
	public function __call($method,$args){
		
		if(in_array($method,array('cookie'))){
			
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
		if(strpos($method,'_') !== false)
		{
			$method = str_replace('_','',$method);
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
	
	public function refresh($templateFile,$compileFile){
		
		$sourceFileTime = filemtime($templateFile);
		$compileFileTime = filemtime($compileFile);
		if($sourceFileTime > $compileFileTime){
			$this->parse(1);
		}
	}
	
	public function parse($jump = 0){
		if(!defined('__COMPILE__')){
			$this->error('为定义编译目录');
		}
		
		$themeList = array($this->theme);
		$viewFolder = __APP__.'/View';
		$handle = opendir($viewFolder);
		while($folder = readdir($handle)){
			if(in_array($folder,array('.','..'))){
				continue;
			}
			
			$subFolder = $viewFolder.'/'.$folder;
			if(is_dir($subFolder)){
				$themeList[] = $folder;
			}
		}
		$themeList = array_unique($themeList);
		
		
		
		
		$rootTheme = $this->sub_theme.'/'.$this->theme;
		$filename = __VIEW__.'/'.$rootTheme;
		if(defined('IN_MOBILE') && IN_MOBILE == 1 && !is_dir($filename)){
			$this->theme = str_replace('/touch','',$this->theme);
			$filename = __VIEW__.'/'.$this->theme;
			
		}
		
		$layout = $filename.'/layout/'.$this->layout.$this->viewType;
		
		if(!$this->block && !$this->action){
			$template = $filename.'/main'.$this->viewType;
			$compile = __COMPILE__.'/'.$rootTheme.'/'.$this->indexTemplate.'.tpl.php';
		}else{
			if(!$this->action){
				$template = $filename.'/'.$this->block.'/home'.$this->viewType;
				$compile = __COMPILE__.'/'.$rootTheme.'/'.$this->block.'_home.tpl.php';
			}else{
				$template = $filename.'/'.$this->block.'/'.$this->action.$this->viewType;
				$compile = __COMPILE__.'/'.$rootTheme.'/'.$this->block.'_'.$this->action.'.tpl.php';
			}
		}
		$this->fileList[] = array($layout,$compile);
		if(!is_file($template)){
			$template = str_replace(__APP__,'',$template);
			$this->error('模板文件 '.$template.'不存在');
		}
		$this->fileList[] = array($template,$compile);
		//模板编译处理
		$isCompiled = true;
		$jump = intval($jump);
		if($jump < 1){
			if(is_file($compile)){
				
				//编译时间
				$compile_filemtime = filemtime($compile);
				//布局文件时间
				$layout_filemtime = filemtime($layout);
				//模板文件时间
				$template_filemtime = filemtime($template);
				
				if($template_filemtime < $compile_filemtime && $layout_filemtime < $compile_filemtime){
					$isCompiled = false;
				}
			}
		}
		
		if($isCompiled || (defined('__APP_DEBUG__') && __APP_DEBUG__ == true)){
						
			//布局识别
			$template = file_get_contents($template);
			
			if(strpos($template,'<template') !== false){
				preg_match_all ('/<template\sfile="([_a-zA-Z\/]+)"\s\/>/', $template, $matches);
				if(isset($matches[1])){
					foreach($matches[1] as $key=>$block){
						$file = '';
						$start = substr($block,0,1);
						if($start == '/'){
							$file = $filename.$block.$this->viewType;
						}else{
							$file = $filename.'/'.$this->block.'/'.$block.$this->viewType;
						}
						if(!is_file($file)){
							$this->error('模板文件 '.$file.'不存在');
						}
						$this->fileList[] = array($file,$compile);
						$sub_template_text = file_get_contents($file);
						$template = str_replace('<template file="'.$block.'" />',$sub_template_text,$template);
					}
				}
			}
			if(strpos($template,'<nolayout') !== false){
				$template = str_replace('<nolayout />','',$template);
			}else{
				//布局识别
				$layout_text = '';
				if(strpos($template,'<layout') !== false){
					if(is_file($layout)){
						$layout_text = file_get_contents($layout);
					}
				}else{
					if(is_file($layout)){
						$layout_text = file_get_contents($layout);
					}
				}
				
				if($layout_text){
					$template = str_replace('{__CONTENT__}',$template,$layout_text);
				}
				
			}
			
			$checkFileList = $this->fileList;
			$lastModifyTime = 0 ;
			foreach($checkFileList as $sFile=>$cFile){
				$htmFile = $cFile[0];
					if($lastModifyTime < 1){
						$lastModifyTime = filemtime($htmFile);
					}else{
						$currModifyTime = filemtime($htmFile);
						if($currModifyTime > $lastModifyTime){
							
							$lastModifyTime = $currModifyTime;
						}
					}
			}
			
			
			//调试控制台
			$template = $template;
				
			//标签转换
			$template = $this->parse_template($template,$lastModifyTime);
			
			//数据保存
			$folder = dirname($compile);
			if(!is_dir($folder)){
				$result = mkdir($folder,0700,1);
				if(!$result){
					die('folder '.$folder.' create failed');
				}
			}

			$fp = fopen($compile,'w');
			flock($fp, LOCK_EX);
			if(!fwrite($fp, $template))
			{
				file_put_contents($compile,$template);
			}
			flock($fp,LOCK_UN);
			fclose($fp);
		}
		
		return $compile;
	}
	
	public function dispather($data){
		extract($data);
		include_once $this->parse();
	}
	
	public function __destruct()
	{
	}
}