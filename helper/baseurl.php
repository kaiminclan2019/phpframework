<?php
class helper_baseurl
{
	public function init()
	{
		$ismobile = 0;
		$host = $_SERVER['HTTP_HOST'];
		if(strpos($host,'m.') !== false)
		{
			$ismobile = 1;
		}
		$host = $_SERVER['HTTP_HOST'];
		$url_data = parse_url($url);
		if(!isset($url_data['host']))
		{
			$url_data['host'] = $host;
		}
		
		$env = $_SERVER['SERVER_ADDR'] == '192.168.0.202'?'demo':'test';
		
		if(strpos($url_data['host'],$env) === false)
		{
			list($column,$domain,$type) = explode('.',$url_data['host']);
			$url_data['host'] = $column.'.'.$env.'.'.$domain.'.'.$type;
		}
		$return_url = ($url_data['scheme'].'://'.$url_data['host'].$url_data['path']);
		$url_data = parse_url($return_url);
		list($host) = explode('.',$url_data['host']);
		if($ismobile == 1 && !in_array($host,array('m')))
		{
			$sub_host = '/'.$host;
			if($host == 'www')
			{
				$sub_host = '';
			}
			$return_url = $url_data['scheme'].'://'.str_replace($host,'m',$url_data['host']).$sub_host.$url_data['path'];
		}
		
		return $return_url;
	}
}