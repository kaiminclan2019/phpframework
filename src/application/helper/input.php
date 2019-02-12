<?php
/**
 * 输入
 * 对输入数据的处理
 * 输入流
 * get
 * post
 */
class application_helper_input {
	
	/** POST 数据 */
	private $_post = array();
	
	/** COOKIE 数据 */
	private $_cookie = array();
	
	/** GET 数据 */
	private $_get = array();
	
	/** 输入流*/
	private $_input = array();
	
	private function __cookie(){
	}
	
	private function __get(){
	}
	
	private function __post(){
	}
	private function __input(){
		$raw_post_data = file_get_contents('php://input', 'r'); 
	}
	
	public function __construct(){
	
	}
	public function __destruct(){
		
	}
}
?>