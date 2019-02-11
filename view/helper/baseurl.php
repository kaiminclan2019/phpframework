<?php
class view_helper_baseurl
{
	private $data = '';
	public function __construct()
	{
	}
	public function init($url)
	{
		$this->data = $url;
		return $this;
	}
	
	private function parseHost($domain){
		$tmp = str_replace(__SITE_URL__,'',$domain);
		if(!$tmp){
			$domain = 'www.'.$domain;
		}
		list($host) = explode('.',$domain);
		
		if(defined('IN_MOBILE') && IN_MOBILE > 0){
			if(!in_array($host,array('m'))){
				$domain = str_replace($host,'m',$domain);
				if($host != 'www'){
					$domain .= '/'.$host;
				}
			}
		}
		
		if(defined('__APP_DEUBG_RELEASE__') && __APP_DEUBG_RELEASE__ == true){
			$mask = '.test.';
			if($_SERVER['SERVER_ADDR'] == '127.0.0.1' && strpos($domain,$mask) === false){
				$domain = preg_replace('/\./',$mask,$domain,1);
			}
		}
		
		return $domain;
	}
	
	public function get()
	{
		$return_url = array();
		$url = $this->data;
		$host = $_SERVER['HTTP_HOST'];

		$url_data = parse_url($url);
		if(!isset($url_data['host'])){
			$return_url[] = 'http://';
			$return_url[] = $this->parseHost($host);
		}else{
			if(isset($url_data['scheme'])){
				$return_url[] = $url_data['scheme'].'://';
			}
			$return_url[] = $this->parseHost($url_data['host']);
		}
		
		if(isset($url_data['path'])){
			if(substr($url_data['path'],0,1) != '/'){
				$url_data['path'] = '/'.$url_data['path'];
			}
			$return_url[] = $url_data['path'];
		}
		
		
		return str_replace(' ','',implode('',$return_url));
	}
}