<?php
abstract class model {
	/** 表名 */
	protected $_name = '';
	/** 主键 */
	protected $_primary;
	
	/** 驱动 */
	protected $_driver = 'db';
	
	/** 数据库 */
	protected $_database;
	/** 字段 */
	protected $_fields = array();
	
	private $db;
	private static $connection;
	
	private $options = array();
	
	private $error;
	
	
	/** 缓存 */
	/** 键 */
	protected $_pre_cache_key = '';
	/** 有效期 */
	protected $_pre_cache_ttl = 60;
	/** 开关 */
	protected $_allowmem = true;
	
	public function __construct(){
		
		if(empty(self::$connection[$this->_driver])){
		
			//数据库
			$filename = __ROOT__.'/config/database.xml';
			if(!is_file($filename)){
				throw new exception('未定义数据库配置文件',3000);
			}
			$config = simplexml_load_string(file_get_contents($filename));
			if(!empty($this->_driver) && $this->_driver != 'db'){
				$driver = $this->_driver;
			}
			elseif(!$config->xpath('driver') || empty($config->driver)){
				throw new exception('未定义数据库配置文件',3000);
			}
			else{
				$driver = (string)$config->driver;
			}
			
			$setting = '';
			if($config->xpath('connections') && $config->connections->xpath($driver)){
				$setting = $config->connections->$driver;
			}
			
			if(empty($setting)){
				throw new exception('数据驱动【'.$driver.'】未定义',3001);
			}		
			
			$driver = 'model_driver_'.$driver;
			self::$connection[$this->_driver] = new $driver($setting);
		}
		$this->db = self::$connection[$this->_driver];
		$this->options = array();
		if($this->_database){
			$this->options['database'] = $this->_database;
		}
		$this->table($this->_name);
	}
	public function lockRead(){		
		$this->db->lockRead($this->options);
	}
	
	public function lockWrite(){			
		$this->db->lockWrite($this->options);
	}
	
	public function unLock(){				
		$this->db->unLock($this->options);
	}
	/**
	 * 表
	 * 
	 */
	public function table($table){
		if(strpos($table,'.') !== false){
			list($database,$table) = explode('.',$table);
			$this->options['database'] = $database;
		}
		if(strpos($table,'#') !== false){
			$table = str_replace('#','_',$table);
		}
		
		$this->options['table'] = $table;
		return $this;
	}
	/**
	 * 数据库
	 * 
	 */
	public function schema($schema){
		$this->options['schema'] = $schema;
	}
	/**
	 * 子表
	 * 
	 */
	public function subTable($table,$field = ''){
		$tableName = $this->options['table'];		
		$subTableName = $tableName.'_'.$table;
		$this->options['table'] = $this->db->getSplitTable($tableName,$subTableName,$field);
		
		return $this;
	}
	
	/**
	 * 
	 * 
	 */
	private function parseWhereArray($whereVal){
		if(is_array($whereVal)){
			foreach($whereVal as $key=>$val){
				if(strcmp($val,0) === 0){
					continue;
				}
				if($val == ''){
					unset($whereVal[$key]);
				}
			}
			$whereVal = array_unique($whereVal);
		}
		return $whereVal;
	}
	
	public function where($where){
		$_where = array();
		
		$whereTagList = array(
			'eq'=>'=',
			'neq'=>'<>',
			'gt'=>'>',
			'egt'=>'>=',
			'lt'=>'<',
			'elt'=>'<=',
		);
		
		$fieldsData = array();
		foreach($where as $field=>$value){
			if(in_array($field,array('or'))){
				$orWhere = array();
				foreach($value as $_field=>$_value){
					
					if(is_array($_value)){
						$_value = $this->parseWhereArray(array_map('intval',$_value));
												
						$orWhere[] = $_field.' IN ('.implode(',',$_value).')';
					}else{
						if(is_numeric($_value)){
							$orWhere[] = $_field.' = '.($_value);
						}else{
							$orWhere[] = $_field.' = "'.($_value).'"';
						}
					}
					$fieldsData[] = $_field;
				}
				$_where[] = '('.implode(' OR ',$orWhere).')';
				continue;
			}
			if(is_array($value) && count($value) > 0){
				$tag = '';
				list($tag,$_value) = $value;
				$tag = strtoupper($tag);
				switch($tag){
					case 'LIKE': 
						$_where[] = $field.' '.$tag.' "'.$_value.'"'; 
						break;
					case 'BETWEEN': 
						list($start,$end) = $_value;
						$_where[] = $field.' '.$tag.' '.$start.' AND '.$end; 
						break;
					default:
						$tag = strtolower($tag);
						if(in_array($tag,array('>','<','<>','>=','<='))){
							if($tag == '<>' && is_array($_value)){
								$_where[] = $field .' NOT IN ('.implode(',',$this->parseWhereArray($_value)).')';
							}else{
								$_where[] = $field.' '.$tag.(is_numeric($_value)?$_value:'"'.$_value.'"');
							}
						}
						elseif(isset($whereTagList[$tag])){
							$_where[] = $field.' '.$whereTagList[$tag].$_value;
						}
						else{
							foreach($value as $wKey=>$wVal){
								if(!is_numeric($wVal) && !empty($wVal)){
									$value[$wKey] = '"'.$wVal.'"';
								}
							}							
							$value = implode(',',$this->parseWhereArray($value));
							if($value){
								$_where[] = $field.' IN ('.$value.')';
							}
						}
					break;
				}
			}else{
				$_where[] = $field.' = '.$this->db->parse($value,$field);
				$fieldsData[] = $field;
			}
		}
		
		//索引检测
		//@jqm 2018 1107 增加对条件的索引判断
		/*
		$indexList = $this->db->fetch_index($this->options);		
		foreach($fieldsData as $key=>$field){
			if(!in_array($field,$indexList)){
				$this->info('Index '.$field.' not defined');
			}
		}
		*/
		$this->options['where'] = implode(' AND ',$_where);
		return $this;
	}
	
	/**
	 *
	 * 数据转换
	 * 数组转换为字符，谨支持一维数组；二维数组会自动提取值，去掉键
	 * @param $data  要转换的数据
	 *
	 * @return string
	 *
	 */
	private function arrayToString($data)
	{
		$result = array();
		if(is_array($data)){
			$data = array_values($data);
			foreach($data as $key=>$val){
				if($val == NULL) continue;
				
				$result[] = $val;
			}
			$data = implode(',',$data);
		}
		return $data;
	}
	
	
	public function field($fields){
		
		if(!is_array($fields)){
			$fields = explode(',',$fields);
		}
		foreach($fields as $key=>$field){
			$field = $this->checkField($field);
			if(empty($field)){
				$this->info($field.' not Undefined');
			}
		}
		if(empty($this->options['field'])){
			$this->options['field'] = $fields;
		}else{
			$this->options['field'] = array_merge($this->options['field'],$fields);
		}		
		return $this;
	}
	
	/**
	 * 字段检测
	 */
	private function checkField($field){
		$result = array();
		if(isset($this->fields[$field])){
			$result = $this->fields[$field];
		}
		return $result;
	}
	
	private function filter($setting){
		return $setting;
	}
	
	public function data($data){
		$this->options['_debug_data_'] = $data;
		$fields = array_keys($data);
		$values = array_values($data);
		
		foreach($values as $key=>$value){
			if(in_array($fields[$key],array('__hash__'))){
				unset($fields[$key]);
				unset($values[$key]);
				continue;
			}
			if(is_array($value)){
				//触发多条写入
				$len = count($value);
				$subLen = count($fields);
				
				$temp = array();
				for($i=0;$i<$len;$i++){
					for($j=0;$j<$subLen;$j++){
						if(isset($values[$j][$i])){
							$tempVal = $values[$j][$i];
							$subTempLen = strlen($tempVal);
							$insertNewVal = ($subTempLen < 1?NULL:$tempVal);
							if(!is_numeric($insertNewVal)){
								$insertNewVal = $this->db->parse($insertNewVal);
							}
							$temp[$i][$j] = $insertNewVal;
						}
					}
				}
				$values = $temp;
				break;
			}else{
				$values[$key] = $this->db->parse($this->filter($value,$this->checkField($fields[$key])),$fields[$key]);
			}
		}
		
		$this->options['field'] = $fields;
		$this->options['value'] = $values;
		return $this;
	}
	
	public function orderby($orderby){
		if(!empty($orderby)){
			if(!is_array($orderby)){
				$orderby = array($orderby);
			}
			
			$this->options['orderby'] = implode(',',$orderby);
		}
		return $this;
	}
	
	public function order($order){
		return $this->orderby($order);
	}
	
	public function group($groupby){
		if(!empty($groupby)){
			if(!is_array($groupby)){
				$groupby = array($groupby);
			}
			
			$this->options['groupby'] = implode(',',$groupby);
		}
		return $this;
	}
	
	public function limit($offset,$length = 0,$count = 0){
		if($length){
			if($offset > $count){
				$offset = ceil($count/$length);
				$offset += 1;
			}
			
			$start = (($offset-1)*$length);
			$this->options['limit'] = ($start < 0?0:$start).','.$length;
		}else{
			$this->options['limit'] = $offset;
		}
		return $this;
	}
	public function select(){
		$cacheKey = md5(implode('_',$this->options));
		$result = $this->fetchCache($cacheKey);	
		if(!$result){
			$options = $this->options;
			$result = $this->db->select($options);
			if($result){
				$listdata = array();
				foreach($result as $key=>$data){
					if(isset($data[$this->_primary])){
						$listdata[$data[$this->_primary]] = $data;
					}else{
						$listdata[] = $data;
					}
				}
				$result = $listdata;
				$this->writeCache($cacheKey,$result);
			}
		}
		
		return $result;
	}
	
	public function find(){
		
		$cacheKey = md5(implode('_',$this->options));
		$result = $this->fetchCache($cacheKey);	
		if(!$result){
			$options = $this->options;
			$options['limit'] = 1;
			$result = $this->db->select($options);
			if($result){
				$this->writeCache($cacheKey,$result);
			}
		}
		return $result;
	}
	
	/**
	 * 事务开启
	 */
	public function start(){
		$this->db->start();
	}
	/**
	 * 回滚
	 */
	public function rollback(){
		$this->db->rollback();
	}
	
	/**
	 * 提交
	 */
	public function commit(){
		$this->db->commit();
	}
	
	public function fetchRow(){
		
		$cacheKey = md5(implode('_',$this->where));
		$result = $this->fetchCache($cacheKey);
		
		if(!$result){
			$options = $this->options;
			$result = $this->db->select($options);
			if($result){
				$this->writeCache($cacheKey,$result);
			}
		}
		return $result;
	}
	
	/**
	 * 删除
	 */
	public function delete(){
		$result = $this->db->delete($this->options);
		$this->removeCache();
		return $result;
	}
	/**
	 * 保存修改
	 */
	public function save(){
		$result = $this->db->update($this->options);
		$this->removeCache();
		return $result;
	}
	
	/**
	 * 新增
	 */
	
	public function add(){
		$result = $this->db->insert($this->options);
		$this->removeCache();
		return $result;
	}
	
	public function addMulti(){
		
		$lastInsertIds = array();
		
		$this->lockWrite();
		$step = 0;
		$insertCount = $this->db->insert($this->options,1);
		if($insertCount < 2){
			$step = 1;
			$insertCount = 2;
		}
		$this->options['field'] = '';
		$listdata = $this->field($this->_primary.' AS lastId')->order($this->_primary.' DESC')->limit($insertCount)->select();
		if($listdata){
			foreach($listdata as $key=>$data){
				$lastInsertIds[] = $data['lastId'];
				if($step > 0 && $key < 1){
					break;
				}
			}
		}
		//var_dump($lastInsertIds,$listdata,$this->get_last_sql()); die();
		$this->unLock();
		$this->removeCache();
		return $lastInsertIds;
	}
	
	public function replace(){
		$result = $this->db->insert($this->options,2);
		$this->removeCache();
		return $result;
	}
	
	/**
	 * 获取调试信息
	 */
	public function getDebug(){
		return $this->db->get_debug();
	}
	
	public function get_last_sql(){
		return $this->db->get_last_sql();
	}
	/**
	 * 字段＋１
	 */
	public function setInc($field,$num){
		$result = $this->data(array($field=>$field.'+'.intval($num)))->save();
		$this->removeCache();
		return $result;
	}
	/**
	 * 字段减1
	 */
	public function setDec($field,$num){
		$result = $this->data(array($field=>$field.'-'.intval($num)))->save();
		$this->removeCache();
		return $result;
	}
	
	public function __call($method,$args){
		switch($method){
			case 'count':
				$this->field('count(*) as total');
				$data = $this->fetchRow();
				if(count($data) < 2){
					$data = current($data);
					return $data['total'];
				}else{
					return $data;
				}
				break;
			case 'avg':
			case 'sum':
				list($args) = $args;
				if(strpos($args,',') !== false){
					$args = explode(',',$args);
				}
				if(!is_array($args)){
					$args = array($args);
				}
				$fields = array();
				$method = strtoupper($method);
				foreach($args as $arg){
					$fields[] = $method.'('.$arg.') as '.$arg;
				}
				
				$this->field(implode(',',$fields));
				$data = $this->find();
				if(count($data) < 2){
					return current(array_values($data));
				}
				return $data;
				break;
		}
	}
	
	/** 获取缓存 */
	public function fetchCache($key){
		$data = array();
		if($this->_allowmem == false){
			return $data;
		}
		
		$key = $this->getCacheKey($key);
		
		//$data = Memory::get($key);
		
		return $data;
	}
	/** 设置缓存 */
	public function writeCache($key,$data){
		if($this->_allowmem == false){
			return $data;
		}
		$key = $this->getCacheKey($key);
		
		//$data = Memory::set($key,$data,$this->_cache_ttl);
	}
	/** 删除缓存 */
	public function removeCache($key = ''){
		if($this->_allowmem == false){
			return -1;
		}
		
		$key = $this->getCacheKey($key);
		
		//Memory::rm($key);
	}
	
	private function getCacheKey($key = ''){
		return (strlen($this->_database) > 0?$this->_database.'_':'').(str_replace('_','-',$this->_name)).$this->_pre_cache_key.($key?('#'.$key):'');
	}
}