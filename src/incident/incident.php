<?php
/*
消息等级

七个主要的等级，依序是由不重要排列到重要讯息等级：

info：仅是一些基本的讯息说明而已；

notice：比 info 还需要被注意到的一些信息内容；

warning：警示讯息，可能有问题，但是还不至于影响到某个 daemon 运作。

error ：一些重大的错误讯息，这就要去找原因了。

crit：比 error 还要严重的错误信息，crit 是临界点 (critical) 的缩写，已经很严重了！

alert：警告警告，已经很有问题的等级，比 crit 还要严重！

emerg 或 panic：疼痛等级，意指系统已经几乎要当机的状态！ 很严重的错误信息了。
//开发者
//管理员
//运营员
//供应商
//客户
*/
class Incident {
	
	//事件级别
	//消息级
	const INCIDENT_LEVEL_INFO = 1;
	//警告级
	const INCIDENT_LEVEL_NOTICE = 2;
	//致命级
	const INCIDENT_LEVEL_WARNING = 3;
	//致命级
	const INCIDENT_LEVEL_ERROR = 4;
	//致命级
	const INCIDENT_LEVEL_CRIT = 5;
	//致命级
	const INCIDENT_LEVEL_ALERT = 6;
	//致命
	const INCIDENT_LEVEL_EMERG = 7;
	//致命
	const INCIDENT_LEVEL_PANIC = 8;
	
	/** 事件信息 */
	public static $data = array();
	
	//初始化
	public function __construct($method,$msg,$status){
		self::$method($msg,$status);
		
	}
	/** 
	 * 消息
	 * @param $msg 事件消息
	 * @param $level 事件级别 通知用户/	通知管理员/	通知开发者/	安全审计
	 * 
	 */
	public static function log($msg,$level = INCIDENT::INCIDENT_LEVEL_TOOLTIP){
		self::$data[] = $msg;
	}
	
	/**
	 * 比 info 还需要被注意到的一些信息内容；
	 */
	public static function info($msg){
		
	}
	/**
	 * 比 info 还需要被注意到的一些信息内容；
	 */
	public static function notice($msg){
		
	}
	/**
	 * 警示讯息，可能有问题，但是还不至于影响到某个 daemon 运作。
	 */
	public static function warn($msg){
	}
	/**
	 * 一些重大的错误讯息，这就要去找原因了。
	 */
	public static function error($msg){
	}
	/**
	 * ：比 error 还要严重的错误信息，crit 是临界点 (critical) 的缩写，已经很严重了！
	 */
	public static function crit($msg){
	}
	/**
	 * 警告，已经很有问题的等级，比 crit 还要严重！
	 */
	public static function alert($msg){
	}
	/**
	 * emerg 或 panic：疼痛等级，意指系统已经几乎要当机的状态！ 很严重的错误信息了。
	 */
	public static function emerg($msg){
	}
	/**
	 * emerg 或 panic：疼痛等级，意指系统已经几乎要当机的状态！ 很严重的错误信息了。
	 */
	public static function panic($msg){
	}
	
	
	/** 
	 * 保存日志
	 *
	 */
	public static function save(){
		self::$obj->data(self::$data)->save();
		
		if(defined('__LOG__')){
			$folder = __LOG__;
		}else{
			$folder = realpath(dirname(__FILE__));
		}
		$folder = $folder.'/'.date('Ym/d');
		if(!is_dir($folder)){
			mkdir($folder,0777,1);
		}
		
		$filename = $folder.'/'.date('YmdH').'.log';
		foreach(self::$data as $key=>$val){
			file_put_contents($filename,"\r\n".$val."\r\n",FILE_APPEND);
		}
	} 
	
	
	public function __destruct(){
	}
	private function clearFolder($folder){
		
		if(is_dir($folder)){
			return 0;
		}
		$handle = opendir($folder);
		while($filename = readdir($handle))
		{
			if(in_array($filename,array('.','..')))
			{
				continue;
			}
			$filename = $folder.'/'.$filename;
			unlink($filename);
		}
		rmdir($folder);
	}
}
?>