<?php
class model_driver_mysqli  extends model_driver_db {
	
	protected function connect(){
		
		$conn = mysqli_connect($this->host.':'.$this->port,$this->username,$this->password,$this->database);
		mysqli_query($conn,'set names '.$this->databsae);
		$this->error = mysqli_error($conn);
		$this->status = mysqli_errno($conn);
		
		$this->curlink = $conn;
	}
	
	public function insert($options){
		$result = 0;
		$sql = 'INSERT INTO '.$this->table_name.' (`'.implode('`,`',$this->field).'`) VALUES ('.implode(',',$this->value).')';
		
		$result = $this->exec_sql($sql);
		if($result){
			$result = mysqli_insert_id($this->curlink);
		}
		return $result;
	}
	public function update($options){
		$result = 0;
		if($this->where){
			$data = array();
			foreach($this->field as $key=>$field){
				$data[] = $field.'= '.$this->value[$key];
			}
			
			$sql = 'UPDATE '.$this->table_name.' SET '.implode(',',$data).' WHERE '.$this->where;
			$result = $this->exec_sql($sql);
			if($result){
				$result = mysqli_affected_rows($this->curlink);
			}
		}
		return $result;
	}
	public function delete($options){
		$result = 0;
		if($this->where){
			$sql = 'DELETE FROM '.$this->table_name.' WHERE '.$this->where;		
			$result = $this->exec_sql($sql);
			if($result){
				$result = mysqli_affected_rows($this->curlink);
			}
		}
		return $result;
	}
	public function select($options){
		$result = array();
		if($this->where){
			$sql = 'SELECT * FROM '.$this->table_name.' WHERE '.$this->where.'ORDER BY identity DESC LIMIT 1';
			$result = $this->exec_sql($sql);
			if($result){
				$result = mysqli_affected_rows($this->curlink);
				while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)){
					$result = $row;
				}
			}
		}
		
		return $result;
	}
	
	public function parse($value){
		if(empty($this->curlink)){
			$this->connect();
		}
		if(!is_numeric($value)){
			$value = '"'.mysqli_real_escape_string($this->curlink,$value).'"';
		}
		return $value;
	}
	
	public function exec_sql($sql){
		
		if(empty($this->curlink)){
			$this->connect();
		}
		
		$result = mysqli_query($this->curlink,$sql);
		$this->error = mysqli_error($this->curlink);
		$this->status = mysqli_errno($this->curlink);
		
		return $result;
	}
	
	
}