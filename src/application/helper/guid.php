<?php
class application_helper_guid{
    private $valueBeforeMD5;    
    private $valueAfterMD5;  
	
	private $guid = '';
	public function __construct(){
		$this->parse();
	}
	public function parse(){
		$address = NetAddress::getLocalHost();    
        $this->valueBeforeMD5 = $address->toString().':'.System::currentTimeMillis().':'.Random::nextLong();    
        $this->valueAfterMD5 = md5($this->valueBeforeMD5);    
	}
	public function newGuid(){
		return new Guid();
	}
	public function toString()    
    {    
        $raw = strtoupper($this->valueAfterMD5);    
        return substr($raw,0,8).'-'.substr($raw,8,4).'-'.substr($raw,12,4).'-'.substr($raw,16,4).'-'.substr($raw,20);    
    }    
}
class System    
{    
    function currentTimeMillis()    
    {    
        list($usec, $sec) = explode(" ",microtime());    
        return $sec.substr($usec, 2, 3);    
    }    
}   

class NetAddress    
{    
    private $Name = 'localhost';    
    private $IP = '127.0.0.1';    
    public static function getLocalHost() // static    
    {    
        $address = new NetAddress();    
        $address->Name = $_ENV["COMPUTERNAME"];    
        $address->IP = $_SERVER["SERVER_ADDR"];    
		
        return $address;    
    }    
    function toString()    
    {    
        return strtolower($this->Name.'/'.$this->IP);    
    }    
}    
?>