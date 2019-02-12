<?php
class view_helper_removestyle
{
	private $data = '';
	public function __construct()
	{
	}
	public function init($tag)
	{
		$this->data = $tag;
		return $this;
	}
	
	public function get()
	{	
		$return = preg_replace("/style=.+?['|\"]/i",'',$this->data);    
		return $return;
	}
}