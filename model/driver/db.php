<?php
/**
 * 数据库驱动
 */
class model_driver_db {
	protected $table_name;
	protected $where;
	protected $field;
	protected $value;
	protected $limit;
	protected $group;
	protected $order;
	
	//当前链接
	protected $curlink;
	//链接池
	protected $linkPool;
	
	protected $username;
	protected $password;
	protected $host;
	protected $port;
	protected $database;
	protected $prefix;
	protected $charset;
	
	protected $audit;
	
	protected $status;
	protected $error;
	
	protected static $record = array();
	private $sql;
	
	
	const MODEL_DATABASE_READ = 0;
	const MODEL_DATABASE_WRITE = 1;
	
	
	
	public function __construct($config){
		$this->config = $config;
		
		if($this->config->xpath('database')){
			$this->database = (string)$this->config->database;
		}
			
		if($this->config->xpath('audit')){
			$this->audit = intval($this->config->audit);
		}
		if($this->config->xpath('charset')){
			$this->charset = (string)$this->config->charset;
		}
		if($this->config->xpath('prefix')){
			$this->prefix = current((array)$this->config->prefix);
		}
	}
	
	protected function getConfig($mode){
		
		if(empty($this->config)){
			throw new exception('未定义配置信息',3003);
		}	
		if($this->config->xpath('default')){
			$config = $this->config->default;
		}
		switch($mode){
			case self::MODEL_DATABASE_READ:
				if($this->config->xpath('read')){
					$config = $this->config->read;
				}
				break;
			case self::MODEL_DATABASE_WRITE:
				if($this->config->xpath('write')){
					$config = $this->config->write;
				}
				break;
		}
		if($config->xpath('item')){
			$setting = array();
			$randData = array();
			foreach($config->item as $key=>$obj){
				$weight = (int)$obj->weight;
				$setting[$weight] = array((string)$obj->host,(int)$obj->port,(string)$obj->username,(string)$obj->password);
				$randData[$weight] = $weight*0.1; 
			}
			
			$index = helper_random::lucky($randData);
			list($host,$port,$username,$password) = $setting[$index];
			
		}else{
			$host = (string)$config->host;
			$port = (int)$config->port;
			$username = (string)$config->username;
			$password = (string)$config->password;
		}
		
		return array($host,$port,$username,$password);
	}
	
	protected function setSql($sql){
		$this->sql = $sql;
	}
	
	public function get_last_sql(){
		return $this->sql;
	}
	
	protected function saveSql($sql){
		if(!defined('__BACKUP__')){
			return 0;
		}
		$method = substr(strtolower($sql),0,6);
		$folder = __BACKUP__.'/'.date('Ym/d');
		if(!is_dir($folder)){
			mkdir($folder,0777,1);
		}
		$filename = $folder.'/'.date('YmdH').'.sql';
		$result = file_put_contents($filename,$sql.";\r\n",FILE_APPEND|LOCK_EX);
		if(!$result){
			file_put_contents(__LOG__.'/write_sql_back_error_'.date('YmdH').'.txt',($sql."出错了【".$result."】;\r\n"),FILE_APPEND);
		}
		if($method == 'delete'){
			$filename = __BACKUP__.'/'.date('YmdH').'_delete.sql';
			
			file_put_contents($filename,"\r\n".date('H:i m/d/Y')."\r\n".__CLIENTIP__.'-'.(defined('__URL__')?__URL__:$_SERVER['REQUEST_URI'])."\r\n".$sql."\r\n",FILE_APPEND);
		}
	}
	
	public function get_debug(){
		return $this->record;
	}
	protected function hasError(){
		if(strlen($this->error) > 0)
		{
			throw new Exception($this->error,3005);
		}
		return strlen($this->error) > 0?1:0;
	}
	protected function error($msg){
		$this->output($msg);
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
	public function parse($value){
		
	}
	protected function connect(){
		
	}
	public function update($options){
		
	}
	public function insert($options){
		
	}
	public function delete($options){
		
	}
	public function select($options){
		
	}
	public function version(){
		
	}
	public function start(){
		
	}
	public function rollback(){
		
	}
	public function commit(){
		
	}
	
	public function options(){
		
	}
	
	public function lockRead(){
	}
	public function lockWrite(){
	}
	public function unLock(){
	}
	
	public function __destruct(){
		$timer = 0;
		$cnt = 0;
		foreach(self::$record as $key=>$data){
			$timer += $data['timer'];
			$cnt+=1;
		}
		$html = '<div class="">累计查询:'.$cnt.',累计时间：'.number_format($timer,10,'.','').'秒</div>';
		
		if(defined('__APP_DEBUG__') && __APP_DEBUG__ == true){
			//echo $html;
		}
		if($this->audit){
			$folder = __BACKUP__.'/audit/'.date('Ym/d');
			if(!is_dir($folder)){
				mkdir($folder,0777,1);
			}
			$filename = $folder.'/'.date('YmdH').'.sql';
			file_put_contents($filename,__FULL_URL__.";\r\n",FILE_APPEND);
			
			foreach(self::$record as $key=>$data){
				$result = file_put_contents($filename,$data['sql']."  timer:".($data['timer']).";\r\n",FILE_APPEND|LOCK_EX);
				if(!$result){
					file_put_contents(__LOG__.'/write_sql_audict_error_'.date('YmdH').'.txt',($data['sql']."出错了【".$result."】;\r\n"),FILE_APPEND);
				}
			}
		}
	}
	
}