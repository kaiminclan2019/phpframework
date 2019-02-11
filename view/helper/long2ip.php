<?php
class view_helper_long2ip
{
	private $clientip = '';
	public function __construct()
	{
	}
	public function init($clientip)
	{
		$this->clientip = $clientip;
		return $this;
	}
	
	public function get()
	{
		return long2ip($this->clientip);
	}
}