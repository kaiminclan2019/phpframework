<?php
class view_helper_formatbyte
{
	private $data = '';
	public function __construct()
	{
	}
	public function init($date)
	{
		$this->data = $date;
		return $this;
	}
	
	public function get()
	{
		$size = $this->data;
		$units = array(' B', ' KB', ' MB', ' GB', ' TB'); 
		for ($i = 0; $size >= 1024 && $i < 4; $i++) 
		{
			$size /= 1024; 
		}
		return round($size, 2).$units[$i]; 
	}
}