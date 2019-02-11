<?php
/**
 * 应用
 *
 * 输出
 *
 */
class application_helper_output {
	
	
	/** 要输出的数据　*/
	private $data = '';
	
	/** 客户支持的压缩类型　*/
	private $encoding = '';
	
	
	public function __construct($encoding,$data){
				
		$this->data = $encoding;
		
		$this->encoding = $encoding;
	}
	
	public function data($data){
		$this->data = $data;
	}
	/**
	 * 定义输出头部
	 */
	public function _header($header){
		
	}
	/**
	 *
	 * 清除输出
	 *
	 */
	private function clean(){
		ob_end_clean();
	}
	
	/**
	 * 
	 * 开始发送
	 * 
	 * 
	 */
	public function send(){
		
		if(is_array($msg)){
			$msg = json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		
		switch($_SERVER['OS']){
			case 'WINNT':
			case 'Windows_NT':
				echo mb_convert_encoding($this->data,'gbk','utf8')."\r\n";
			break;
			default:
				echo $this->data."\r\n";
				break;
		}
		
		/*
		ob_start();  
		if (extension_loaded('zlib')) {
			//页面没有输出且浏览器可以接受GZIP的页面    
			if (!headers_sent() && strpos($this->encoding, 'gzip') !== FALSE){ 
				ob_start('ob_gzhandler');  
			}  
		} 
		$content = ob_get_contents();
		ob_end_clean();
		
		echo $this->data;
		*/
	}
	
	public function __destruct(){
		ob_end_flush(); 
	}
}
?>