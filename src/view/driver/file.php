<?php
// +----------------------------------------------------------------------
// | Framework
// +----------------------------------------------------------------------
// | This is NOT a freeware, use is subject to license terms
// +----------------------------------------------------------------------
// | Author: jianqimin <kaimin.clan@gmail.com>
if(!defined('__BAMBOO__')) {
	exit('Access Denied');
}
class view_driver_file
{
	
	private $data = array();
	public function set_data($data)
	{
		$this->data = $data;
	}
	private $start = 0;
	public function init()
	{
		if (isset($_SERVER['HTTP_RANGE']) && ($_SERVER['HTTP_RANGE'] != "") && preg_match("/^bytes=([0-9]+)-$/i", $_SERVER['HTTP_RANGE'], $match) && ($match[1] < $fsize)) {
    		$this->start = $match[1]; 
		}
	}
	public function dispather()
	{
		if ($this->star--> 0) {  
    		fseek($fp, $start);  
    		header("HTTP/1.1 206 Partial Content");  
    		header("Content-Length: " . ($fsize - $start));  
    		header("Content-Ranges: bytes" . $start . "-" . ($fsize - 1) . "/" . $fsize);  
		} else {  
    		header("Content-Length: $fsize");  
    		header("Accept-Ranges: bytes");  
		}  
		
		@header("Cache-control: public"); 
		@header("Pragma: public"); 
		
		@header("Content-Type: application/octet-stream");  
		@header("Content-Disposition: attachment;filename=20130906excel022.xsl");  
		fpassthru($fp);
	}
}
?>