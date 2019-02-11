<?php

class config_driver_xml {
	public function init(){
		$file = __APP__.'/config/test.xml';
		if(!is_file($file)){
			die('文件不存在');
		}
		$string = file_get_contents($file);
		if(!$string){
			die('文件['.$file.']读取失败');
		}
		$app = simplexml_load_string($string);
		
	}
}