<?php
class application_helper_client {
	
	public function isSSL(){
		$method = '';
		//响应类型
		$method = $_SERVER['REQUEST_METHOD'];
		return $method;
	}
	/** 
		方式
	*/
	public function getRequetUri(){
		$host = $this->getHost();
		$requeturi = '';
		if(isset($_SERVER['REQUEST_URI'])){
			$requeturi = $_SERVER['REQUEST_URI'];
		}else{
			$requeturi = '/'.implode('/',array_slice($_SERVER['argv'],1));
		}
		return $requeturi;
	}
	/** 
		方式
	*/
	public function getMethod(){
		$method = '';
		//响应类型
		if(isset($_SERVER['REQUEST_METHOD'])){
			$method = $_SERVER['REQUEST_METHOD'];
		}else{
			$method = 'CONSOLE';
		}
		return $method;
	}
	
	/** 
		主机
	*/
	public function getHost(){
		$host = '';
		//响应类型
		$hostData = explode('.',$_SERVER['HTTP_HOST']);
		if(count($hostData) > 2){
			list($host) = $hostData;
		}
		return $host;
	}
	
	/** 
		主机
	*/
	public function getFullDomain(){
		return 'http://'.$_SERVER['HTTP_HOST'];
	}
	
	/** 
		主机
	*/
	public function getDomain(){
		//响应类型
		$hostList = explode('.',$_SERVER['HTTP_HOST']);
		unset($hostList[0]);
		if(in_array($hostList[1],array('test','demo'))){
			unset($hostList[1]);
		}
		return implode('.',$hostList);
	}
	/** 
		ACCEPT 类型
		string object json xml ,html
	*/
	public function getAccept(){
		$accept = '';
		//响应类型
		if(isset($_SERVER['REQUEST_METHOD'])){
			list($accept) = explode(',',$_SERVER['HTTP_ACCEPT']);
		}else{
			$accept = 'text/plain';
		}
		return $accept;
	}
	/** 来源  */
	public function getReferer(){
		
		$referer = $_SERVER['HTTP_REFERER'];
		
		if(strpos($referer,__SITE_URL__) == FALSE){
			$referer = '/';
		}
		
		return $referer;
		
	}
	
	/** 代理 */
	public function getAgent(){
		$agent = $_SERVER['HTTP_USER_AGENT'];
		return $agent;
		
	}
	/** IP地址 */
	public function getClientIp(){
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip;
		
	}
	
	//客户端支持的压缩类型
	public function getEncoding(){
		$encoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		return $encoding;
		
	}
	//客户端语言
	public function getLanguage(){
		list($lang) = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		return strtolower($lang);
		
	}
}
?>