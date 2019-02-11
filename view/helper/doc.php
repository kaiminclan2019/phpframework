<?php
class view_helper_doc
{
	private $data = '';
	private $foramt = '';
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
		$output = $this->data;
		
		return preg_replace('/<br\\s*?\/??>/i','',$output);;
	}
}