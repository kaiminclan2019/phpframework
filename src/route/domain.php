<?php
/**
 * 主机
 */
class domain {
	/** 主机 */
	private $host;
	
	/** 域名类型 */
	private $type = array(
        'com','top','tech','net','org','biz','cn',
        'com.cn','top','org','mil','cc','tv','ac',
        'edu','arpa','biz','info','pro','name','coop',
        'us','travel','xxx','idv','aero','museum','mobi',
        'asia','tel','int'
    );
	//域名
	private $domain = 'example.com';
	
	private $current_host = array();
	
	private $type = array(
        'com','top','tech','net','org','biz','cn',
        'com.cn','top','org','mil','cc','tv','ac',
        'edu','arpa','biz','info','pro','name','coop',
        'us','travel','xxx','idv','aero','museum','mobi',
        'asia','tel','int'
    );
	
	public function init(){
		
		$this->host = $_SERVER['HTTP_HOST'];
		
		//兼容IP地址访问
		if(!is_numeric($this->host)){
			
			if(!strpos($this->host,$this->domain)){
				$this->error('DOMAIN IS NOT EXISTS');
			}
			
			$this->host = str_replace($this->domain,'',$this->host);
			$this->current_host = array_filter(explode('.',$this->host));
		}
	}
	
	/**
	 * 错误消息
	 */
	private function error($local_code,$status = 100){
		throw new Exception($local_code,$status);
	}
}

?>