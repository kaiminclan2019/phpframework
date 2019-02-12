<?php
/**
 * 控制台
 */
class Console {
	
	private function get_sn(){
		
		return date('YmdHis'); 
	}
	private function getTime(){ return time();}
	
	protected function error($msg){
		$this->output($msg);
		exit();
	}
	protected function info($msg){
		$this->output($msg);
	}
	
	protected function output($msg){
		
		if(is_array($msg)){
			$msg = json_encode($msg,JSON_UNESCAPED_UNICODE);
		}
		
		switch($_SERVER['OS']){
			case 'WINNT':
			case 'Windows_NT':
				echo mb_convert_encoding($msg,'gbk','utf8')."\r\n";
			break;
			default:
				echo $msg."\r\n";
				break;
		}
	}
	
	protected function getText($content,$start,$end){
		
		$result = '';
		if(strpos($content,$start) !== false){
			list(,$content) = explode($start,$content);
			$result = $content;
		}
		if(strpos($content,$end) !== false){
			list($content) = explode($end,$content);
			$result = $content;
		}
		
		return $result;
	}
	/**
	 * 浏览器代理
	 */
	protected function get_rand_agent()
	{
		$agent = array(
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.110 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
			'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 Firefox/3.0'
		);
		return $agent[mt_rand(0,count($agent)-1)];
	}
	/**
	 * 随机IP
	 */
	protected function get_rand_ip(){
		$ip_long = array(
			  array('607649792', '608174079'), //36.56.0.0-36.63.255.255
			  array('975044608', '977272831'), //58.30.0.0-58.63.255.255
			  array('999751680', '999784447'), //59.151.0.0-59.151.127.255
			  array('1019346944', '1019478015'), //60.194.0.0-60.195.255.255
			  array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
			  array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
			  array('1947009024', '1947074559'), //116.13.0.0-116.13.255.255
			  array('1987051520', '1988034559'), //118.112.0.0-118.126.255.255
			  array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
			  array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
			  array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
			  array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
			  array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
			  array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
			  array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
	  );
	  $rand_key = mt_rand(0, 14);
	  $huoduan_ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
	  return $huoduan_ip;
	}
	
	/*
	 * 保存远程图片
	 */
	function getPic($url)
	{
		$imageFile = '';
		if(function_exists('curl_init'))
		{
			$ch=curl_init();
			$timeout=5;
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
			$img=curl_exec($ch);
			curl_close($ch);
	  }else{
			ob_start();
			readfile($url);
			$img=ob_get_contents();
			ob_end_clean();
	  }
	  
	  //文件大小
	  $fp2=@fopen($save_dir.$filename,'w');
	  fwrite($fp2,$img);
	  fclose($fp2);
	  
	  return $imageFile;
	}
	
	protected function save_file($url, $file='', $timeout=60){
		$filename = '';
		if(function_exists('curl_init')){
			$curl = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$temp = curl_exec($ch);
			if(curl_error($ch)){
				//下载失败
			}
			$result = file_put_contents($file, $temp);
			if(!$result){
				//保存失败
			}
		}else{
			$setting = array(
				'http'=>array(
					'method'=>'GET',
					'header'=>'',
					'timeout'=>$timeout
				)
			);
			$context = stream_context_create($opts);
			$result = copy($url, $file, $context);
			if(!$result){
				//保存失败
			}
		}
	}
}