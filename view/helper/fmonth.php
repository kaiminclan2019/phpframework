<?php
class view_helper_fmonth
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
		$show_time = $this->data;
		$output = '';
		if($show_time)
		{
				$output = date('Y-m',$show_time);
		}
		return $output;
	}
}