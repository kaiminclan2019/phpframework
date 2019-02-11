<?php
/**
 *
 * 自动加载
 *
 */
class Loader 
{
	public static $folder = array();
	private static $namespace = array(
	
		'app'=>array(
			'block','console','controller','model','service','task'
		),
		
		'vendor'=>array(
			'application','console','debug','controller','helper','service','extend','incident','model','memory','route','security','block','view','session'
		)
		
	);
	
	public static function load($class){
		
		$_class = $class;
		/*
		特殊字符 
		# app
		_ vendor
		*/
		//空间确定
		$path = array();
		if(strpos($class,'_') !== false){
			$path[] = __ROOT__.'/vendor/PHPBamboo';
		}
		//基础目录查找
		$namespaceList = self::$namespace;
		
		foreach($namespaceList as $key=>$folders){
			
			foreach($folders as $cnt=>$folder){
				if($key == 'vendor'){
					$_class = strtolower($class);
					if($folder == $_class){
						$path = array();
						$class = $_class;
						if($_class == 'service' || $_class == 'block' || $_class == 'console'){
							$folder = 'controller';
						}
						$path[] = __ROOT__.'/vendor/PHPBamboo/'.$folder;
						break;
					}
				}
				if($key == 'app'){
					$folder = ucfirst($folder);
					if(substr($class,-(strlen($folder))) == $folder){
						
						$path[] = __ROOT__.'/app/'.$folder.(isset(self::$folder[$class])?'/'.self::$folder[$class]:'');
						break;
					}
				}
			}
		}
		if($path){
			//路径重置
			$resetClassFile = $class;
			if(strpos($resetClassFile,'_') !== false){
				$resetClassFile = str_replace('_','/',$resetClassFile);
			}
			
			$classFile = implode('/',$path).'/'.$resetClassFile.'.php';
			$classFile = self::parseFile($classFile);
			
			if(!is_file($classFile)){
				throw new Exception('文件'.$classFile.'不存在',102);
			}
			if(!class_exists($class)){
				application::$indicent['file'][] = $classFile;
				require_once $classFile;
			}
		}
	}
	
	private static function parseFile($classFile){
		if(strpos($classFile,'/vendor/') !== false){
			return $classFile;
		}
		if(strpos($classFile,'/Controller/') !== false){
			return $classFile;
		}
		
		$classFileList = explode('/',str_replace('\\','/',$classFile));
		$arrLen = count($classFileList)-1;
		$method = substr($classFileList[$arrLen],0,-4);		
		
		if(!in_array($method,array('block','service','model'))){	
			$includeFolder = '';
			$alias = __ROOT__.'/app/alias.php';
			if(is_file($alias)){
				$aliasData = array();
				$aliasList = require_once $alias;
				if(strpos($method,'Block') !== false && isset($aliasList['block'])){
					$aliasData = $aliasList['block'];
				}
				elseif(strpos($method,'Service') !== false && isset($aliasList['service'])){
					$aliasData = $aliasList['service'];
				}
				elseif(strpos($method,'Model') !== false && isset($aliasList['model'])){
					$aliasData = $aliasList['model'];
				}
				if(!empty($aliasData)){
					foreach($aliasData as $folder=>$fileBox){
						if(in_array($method,$fileBox)){
							$includeFolder = ucfirst($folder);
							break;
						}
					}
				}
			}
			if(empty($includeFolder)){		
				$len = strlen($method);
				$mathes = array(ucfirst(substr($method,0,1)));
				for($i=1;$i<$len;$i++){
					$letter = substr($method,$i,1);
					if(preg_match('/[A-Z]/',$letter)){
						break;
					}
					$mathes[] = $letter;
				}
				$includeFolder = implode('',$mathes);
			}
			$classFile = str_replace($method, $includeFolder.'/'.$method,$classFile);
		}
		return str_replace('//','/',$classFile);
	}
}
?>