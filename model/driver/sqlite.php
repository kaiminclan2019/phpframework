<?php
class model_driver_sqlite extends model_driver_db {
	
	protected function connect(){
		$conn =  new SQLite3($this->database.'.db');
		$this->error = $conn->lastErrorMsg($conn);
		$this->status = $conn->lastErrorCode($conn);
		if(!$this->hasError()){
			$this->curlink = $conn;
			//$this->exec_sql('"set" names '.$this->charset);
		}
	}
	
	
	public function exec_sql($sql){
		
		
		if(empty($this->curlink)){
			$this->connect();
		}
		
		$result = '';
		if(!$this->hasError()){
			$this->setSql($sql);
			$method = substr(strtolower($sql),0,6);
			if($method == 'select'){
				$result = $this->curlink->query($sql);
			}else{
				$result = $this->curlink->exec($sql);
			}
			$this->error = $this->curlink->lastErrorMsg();
			$this->status = $this->curlink->lastErrorCode();
			if($this->status){
				if(defined('__LOG__')){
					file_put_contents(__LOG__.'/sqlite_error_'.date('Ymd').'.log',"\r\n".(defined('__URL__')?__URL__:$_SERVER['REQUEST_URI'])."\r\n".$sql."\r\n".$this->error.':'.$this->status,FILE_APPEND);
				}
				throw new Exception('联系开发者，致命错误.',3010);
			}
			if(in_array($method,array('update','insert','delete')) && defined('__BACKUP__')){
				file_put_contents(__BACKUP__.'/'.date('Ymd').'.sql',$sql.";\r\n",FILE_APPEND);
			}
		}
		
		return $result;
	}
	
	public function insert($options){
		$this->parse_options($options);
		$result = 0;
		$sql = 'INSERT INTO '.$this->table_name.' (`'.implode('`,`',$this->field).'`) VALUES ('.implode(',',$this->value).')';
		
		$result = $this->exec_sql($sql);
		if($result){
			$result = $this->curlink->lastInsertRowID  ();
		}
		
		
		return $result;
	}
	public function update($options){
		
		$this->parse_options($options);
		
		$result = 0;
		if($this->where){
			$data = array();
			foreach($this->field as $key=>$field){
				$data[] = $field.'= '.$this->value[$key];
			}
			
			$sql = 'UPDATE '.$this->table_name.' SET '.implode(',',$data).' WHERE '.$this->where;
			$result = $this->exec_sql($sql);
			if($result){
				if(!$this->hasError()){
					$result = $this->curlink->changes ();
				}
			}
		}
		return $result;
	}
	public function delete($options){
		$this->parse_options($options);
		$result = 0;
		if($this->where){
			$sql = 'DELETE FROM '.$this->table_name.' WHERE '.$this->where;		
			$result = $this->exec_sql($sql);
			if($result){
				if(!$this->hasError()){
					$result = $this->curlink->changes ();
				}
			}
		}
		return $result;
	}
	
	protected function parse_options($options){
		
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
		$sql = 'SELECT '.$this->parse_field($options).' FROM '.$this->table_name;
		if($this->where){
			$sql .= ' WHERE '.$this->where;
		}
		if($this->order){
			$sql .= $this->order;
		}
		if($this->group){
			$sql .= $this->group;
		}
		if($this->limit){
			$sql .= $this->limit;
		}
		$result = $this->exec_sql($sql);
		
		if(!$this->hasError()){
			if($result){
				while($row = $result->fetchArray(SQLITE_ASSOC)){
					$output[] = $row;
				}
			}
			
			if(!empty($output) && $this->limit == ' LIMIT 1'){
				$output = current($output);
			}
		}
		
		return $output;
	}
	
	/**
	 *
	 * SQL编辑
	 *
	 */
	public function parse($value){
		if(empty($this->curlink)){
			$this->connect();
		}
		if(!is_numeric($value)){
			$value = '"'.$value.'"';
		}
		return $this->curlink->escapeString($value);
	}
	/**
	 *
	 * 开启事务
	 *
	 */
	public function start(){
		$sql = 'BEGIN';
		$this->exec_sql($sql);
	}
	/**
	 *
	 * 事务回滚
	 *
	 */
	public function rollback(){
		$sql = 'ROLL BACK';
		$this->exec_sql($sql);
	}
	/**
	 *
	 * 事务提交
	 *
	 */
	public function commit(){
		$sql = 'COMMIT';
		$this->exec_sql($sql);
	}
	/**
	 *
	 * 关闭连接
	 *
	 *
	 */
	public function close(){
		$this->curlink->close();
	}
}