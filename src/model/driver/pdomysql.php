<?php
class model_driver_pdomysql extends model_driver_db {
	protected $debug_data = array();
	
	/**
	 * 链接数据库服务器
	 * @param $mode 模式,0读,1写
	 */
	protected function connect($mode){
		
		$start = microtime(true);
		
		if(!empty($this->linkPool) && isset($this->linkPool[$mode]) && mysql_ping($this->linkPool[$mode])){
			$conn = $this->linkPool[$mode];
		}else{
			list($host,$port,$username,$password) = $this->getConfig($mode);
			$host = $host.':'.$port;
			$db = $this->database;
			
			try{
				$conn = new PDO("mysql:dbname=$db;host=$host", $username, $password);			
			}catch(PDOException $e){
				$this->error = $e->getMessage();
				$this->status = $e->getCode();
			}
			if($this->hasError()){		
				throw new Exception($msg,3009);	
			}
			$this->linkPool[$mode] = $conn;
		}
		$this->record[] = array(
			'sql'=>'mysql_connect',
			'timer'=>microtime(true)-$start
		);
			
		$this->curlink = $conn;
		
		$this->exec_sql('set names '.$this->charset,$mode);
		$this->exec_sql('SET sql_mode=`NO_UNSIGNED_SUBTRACTION`',$mode);
	}
	/**
	 * 表格拆分表
	 */
	public function getSplitTable($indexTable,$subTable,$field){
		
		$result = $this->exec_sql('SELECT count(*) as total FROM information_schema.TABLES WHERE table_name ="'.$this->prefix.'_'.$subTable.'";');		
		$tableData = mysql_fetch_array($result);
		if($tableData['total'] < 1){
			$result = $this->exec_sql('SELECT * FROM information_schema.TABLES WHERE table_name ="'.$this->prefix.'_'.$indexTable.'";');		
			$tableData = mysql_fetch_array($result);
			mysql_select_db($tableData['TABLE_SCHEMA'],$this->curlink);
			$result = $this->exec_sql('show create table `'.$this->prefix.'_'.$indexTable.'`');
			$tableInfo = '';
			while($row = mysql_fetch_array($result)){
				$tableInfo = $row[1];
			}
			
			$tableInfo = str_replace($this->prefix.'_'.$indexTable,$this->prefix.'_'.$subTable,$tableInfo);
			
			$tableInfo = str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$tableInfo);
			
			$this->exec_sql($tableInfo);
			
			$this->exec_sql('ALTER TABLE `'.$this->prefix.'_'.$subTable.'` AUTO_INCREMENT=1;');
			mysql_select_db($this->database,$this->curlink);
		}
		return $subTable;
	}
	/**
	 * 表信息
	 */
	public function getTableInfo($indexTable){
		$result = $this->exec_sql('show create table `'.$this->prefix.'_'.$indexTable.'`');
		$tableInfo = array();
		while($row = mysql_fetch_array($result)){
			$tableInfo = $row[1];
		}
		
		return $tableInfo;
	}
	
	
	public function exec_sql($sql,$mode){
		
		
		if(empty($this->curlink)){
			$this->connect($mode);
		}
		$result = '';
		if(!$this->hasError()){
			$this->setSql($sql);
			
			$start = microtime(true);
			try{
				$result = $this->curlink->query($sql);		
			}catch(PDOException $e){
				$this->error = $e->getMessage();
				$this->status = $e->getCode();
			}
			$stopTime = round(microtime(true)-$start,6);
			application::$indicent['sql'][] = self::$record[] = array(
				'sql'=>$sql,
				'timer'=>$stopTime
			);
			
			if($stopTime > 0.03){
				$filename = __LOG__.'/mysql_slow'.date('Ymd').'.log';
				file_put_contents($filename,"\r\n".$sql.">>".$stopTime,FILE_APPEND);
			}
			
			if($this->error){
				if($_SERVER["SERVER_ADDR"] == '127.0.0.1'){
					define('ERROR_SQL', $this->error);
				}
				$debugData = '';
				if(defined('__LOG__')){
					$filename = __LOG__.'/mysql_error.log';
					if(filesize($filename) > (2*1024*1024)){
						unlink($filename);
					}
					$debugData = json_encode($this->debug_data,JSON_UNESCAPED_UNICODE);
					$sql .= "\r\n".$debugData;
					file_put_contents($filename,"\r\n".date('H:i m/d/Y')."\r\n".(defined('__URL__')?__URL__:$_SERVER['REQUEST_URI'])."\r\n".$sql."\r\n".$this->error,FILE_APPEND);
				}
				$msg = '联系开发者，致命错误.'.$debugData;
				if(defined('__APP_DEBUG__') && __APP_DEBUG__ == true){
					$msg = $sql.$this->error;
				}
				throw new Exception($msg,3010);
			}
		}
		
		return $result;
	}
	public function lockRead($options){
		$this->parse_options($options);	
		$this->exec_sql('LOCK TABLES '.$this->table_name.' READ');
	}
	
	public function lockWrite($options){
		$this->parse_options($options);	
		$this->exec_sql('LOCK TABLES '.$this->table_name.' WRITE');
	}
	
	public function unLock($options){	
		$this->parse_options($options);		
		$this->exec_sql('UNLOCK TABLES ');
	}
	
	public function insert($options,$multi = 0){
		$this->parse_options($options);
		$result = 0;
		$insertCnt = 1;
		$sql = ($multi == 2?'REPLACE':'INSERT').' INTO '.$this->table_name.' (`'.implode('`,`',$this->field).'`) VALUES ';
		if($multi){
			$temp = array();
			$insertCnt = 0;
			foreach($this->value as $cnt=>$subVal){
				$tempData = '';
				foreach($subVal as $k=>$v){
					$tempData .= (strlen($v) < 1?'NULL':$v).',';
				}
				$tempData = substr($tempData,0,-1);
				
				$temp[] = '('.$tempData.')';
				$insertCnt ++;
			}
			$sql .= implode(',',$temp); 
		}else{
			$sql .= '('.implode(',',$this->value).')';
		}
		
		if($_SERVER["SERVER_ADDR"] == '127.0.0.1'){
			define('INSERT_SQL', $sql);
		}
		$this->saveSql($sql);
		if($multi == 1){
			$result = $this->exec_sql($sql,self::MODEL_DATABASE_WRITE);
			$result = $insertCnt;
			//$sql = 'SELECT identity FROM '.$this->table_name.' ORDER BY identity DESC LIMIT '.$insertCnt;
		}else{
			$result = $this->exec_sql($sql);
			$result = mysql_insert_id($this->curlink);
		}
		
		
			
			$stmt = $pdo->prepare("insert into user(name,gender,age)values(?,?,?)");
			$stmt->bindValue(1, 'test');
			$stmt->bindValue(2, 2);
			$stmt->bindValue(3, 23);
			$stmt->execute();

			if(!$this->hasError()){
				$result = $stmt->rowCount();//受影响行数
			}
		
		
		return $result;
	}
	
	public function newSchema($schema){
		$sql = 'CREATE DATABASE IF NOT EXISTS '.$schema.' '.$this->charset;
	}
	
	public function removeSchema($schema){
		$sql = 'DROP DATABASE '.$schema;
	}
	public function update($options){
		
		$this->parse_options($options);
		
		$result = 0;
		if($this->where){
			$data = array();
			foreach($this->field as $key=>$field){
				$data[] = '`'.$field.'` = '.$this->value[$key];
			}
			
			$sql = 'UPDATE '.$this->table_name.' SET '.implode(',',$data).' WHERE '.$this->where;
			if($_SERVER["SERVER_ADDR"] == '127.0.0.1'){
				define('UPDATE_SQL', $sql);
			}
			$this->saveSql($sql);
			
			$stmt = $pdo->prepare("insert into user(name,gender,age)values(?,?,?)");
			$stmt->bindValue(1, 'test');
			$stmt->bindValue(2, 2);
			$stmt->bindValue(3, 23);
			$stmt->execute();

			if(!$this->hasError()){
				$result = $stmt->rowCount();//受影响行数
			}
		}
		return $result;
	}
	public function delete($options){
		$this->parse_options($options);
		$result = 0;
		if($this->where){
			$sql = 'DELETE FROM '.$this->table_name.' WHERE '.$this->where;	
			$this->saveSql($sql);	
			$result = $this->exec_sql($sql,self::MODEL_DATABASE_WRITE);
			if($result){
				if(!$this->hasError()){
					$result = mysql_affected_rows($this->curlink);
				}
			}
		}
		return $result;
	}
	public function fetch_index($options){
		$indexData = array();
		$this->parse_options($options);
		$len = strlen($this->prefix);
		if(substr($this->table_name,0,$len) != $this->prefix) {
			$this->table_name = $this->prefix.'_'.$this->table_name;
		}
		$sql = 'show index from '.$this->table_name;
		$this->saveSql($sql);
		$result = $this->exec_sql($sql,self::MODEL_DATABASE_READ);
		
		return $indexData;
	}
	
	protected function parse_options($options){
		if(isset($options['_debug_data_'])){
			$this->debug_data = $options['_debug_data_'];
			unset($options['_debug_data_']);
		}
		if(isset($options['database'])){
		
			if(empty($this->curlink)){
				$this->connect();
			}
			$result = mysql_select_db($options['database'],$this->curlink);
		}else{
			$result = mysql_select_db($this->database,$this->curlink);
		}
		$this->table_name = $this->prefix.'_'.$options['table'];
		$this->where = '';
		if(isset($options['where'])){
			$this->where = $options['where'];
		}
		$this->field = '';
		if(isset($options['field'])){
			$this->field = $options['field'];
		}
		$this->value = '';
		if(isset($options['value'])){
			$this->value = $options['value'];
		}
		$this->group = '';
		if(isset($options['groupby'])){
			$this->group = ' GROUP BY '.$options['groupby'];
		}
		$this->order = '';
		if(isset($options['orderby'])){
			$this->order = ' ORDER BY '.$options['orderby'];
		}
		$this->limit = '';
		if(isset($options['limit'])){
			$this->limit = ' LIMIT '.$options['limit'];
		}
	}
	
	public function parse_field($fields){
		$field = '*';
		if(isset($fields['field'])){
			$field = $fields['field'];
			$field = implode(',',$field);
		}
		return $field;
	}
	
	public function select($options){
		$this->parse_options($options);
		$output = array();
		$sql = 'SELECT SQL_NO_CACHE '.$this->parse_field($options).' FROM '.$this->table_name;
		if($this->where){
			$sql .= ' WHERE '.$this->where;
		}
		if($this->group){
			$sql .= $this->group;
		}
		if($this->order){
			$sql .= $this->order;
		}
		if($this->limit){
			$sql .= $this->limit;
		}

		$result = $this->exec_sql($sql,self::MODEL_DATABASE_READ);
		
		if(!$this->hasError()){
			if($result){
				$output = $this->curlink->fetchAll();
			}
			
			if(!empty($output) && $this->limit == ' LIMIT 1'){
				$output = current($output);
			}
		}
		
		return $output;
	}
	
	public function parse($value,$field){
		if(empty($this->curlink)){
			$this->connect(self::MODEL_DATABASE_READ);
		}
		if(!$this->hasError()){
			if(!is_numeric($value)){
				$isParse = 0;
				$_subField = '';
				if(strpos($value,'+') !== false ){
					list($_subField) = explode('+',$value);
				}
				if(strpos($value,'-') !== false ){
					list($_subField) = explode('-',$value);
				}
				if($_subField && $_subField == $field){
					$isParse = 1;
				}
				if(!$isParse){
					$value = '"'.mysql_real_escape_string($value,$this->curlink).'"';
				}
			}else{
				$value = '\''.$value.'\'';
			}
		}
		return $value;
	}
	public function start(){
		$sql = 'BEGIN';
		$this->exec_sql($sql,self::MODEL_DATABASE_WRITE);
	}
	public function rollback(){
		$sql = 'ROLLBACK';
		$this->exec_sql($sql,self::MODEL_DATABASE_WRITE);
	}
	public function commit(){
		$sql = 'COMMIT';
		$this->exec_sql($sql,self::MODEL_DATABASE_WRITE);
	}
}