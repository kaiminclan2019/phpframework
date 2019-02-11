<?php
/**
 * 控制器基类
 * 
 */
abstract class console extends controller_helper_base {
	
	protected function loadUrlData($url){
		include_once __ROOT__.'/vendor/PHPSnoopy/Snoopy.class.php';	
		
		$snoopy = new Snoopy();				
		$snoopy->agent = $this->agentList[mt_rand(0,count($this->agentList)-1)];
		
		$snoopy->cookies["SessionID"] = md5(time());
		$snoopy->rawheaders["Pragma"] = "no-cache";
		$ip = $this->getIp();
		$snoopy->rawheaders["X_FORWARDED_FOR"] = $ip; //伪装ip
		$snoopy->rawheaders["CLIENT-IP"] = $ip; //伪装ip
		$snoopy->fetch($url); 
		return $snoopy->results;
	}
	
	protected function getHtmlVal($data){
		return  trim(strip_tags($data));	
	}
	
	private function getIp(){
		$ip=mt_rand(11, 191).".".mt_rand(0, 240).".".mt_rand(1, 240).".".mt_rand(1, 240);   //随机ip  
		return $ip;
	}
	
	/***
	 *
	 * 消息
	 *
	 */
	protected function info($msg,$tag = "\r\n"){
		if(is_array($msg)){
			$msg = json_encode($msg,JSON_UNESCAPED_UNICODE);
		}
		echo mb_convert_encoding($msg,'gbk','utf8').$tag;
	}
	
	private function getCode(){
		return __CACHE__.'/'.md5(get_called_class().implode('',$_SERVER['argv']).'dispather').'.txt';
	}
	
	private function getLockedData(){
		$output = array();
		$filename = $this->getCode();
		if(is_file($filename)){
			$data = file_get_contents($filename);
			if($data){
				$output = json_decode($data,true);
			}
		}
		return $output;
	}
	//任务偏移
	protected function adjustOffset($cnt = 0){
		
		$cnt = intval($cnt);
		$processData = $this->getLockedData();
		if(!isset($processData['offset'])){
			$processData['offset'] = 1;
		}
		$processData['offset'] += 1;
		file_put_contents($this->getCode(),json_encode($processData));
		
	}
	
	public function getStart(){
		$processData = $this->getLockedData();
		return $processData['page'];
	}
	
	public function getOffset(){
		$processData = $this->getLockedData();
		return $processData['offset'];
	}
	
	//任务状态
	protected function isLocked(){
		
		$processData = $this->getLockedData();
		if(isset($processData['status']) && $processData['status'] == 'begin'){
			return 1;
		}
		
		return 0;
	}
	
	//锁定
	public function locked($cnt){
		$processData = $this->getLockedData();
		$processData['page'] = $cnt;
		$processData['status'] = 'begin';
		file_put_contents($this->getCode(),json_encode($processData));
	}
	//解锁
	protected function unlock(){
		$processData = $this->getLockedData();
		$processData['status'] = 'end';
		$processData['offset'] = 1;
		
		file_put_contents($this->getCode(),json_encode($processData));
	}
}