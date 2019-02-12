<?php
class helper_mms
{
	
	private $ch;
	
	private $api = 'http://218.206.27.231:8085';
	private $username = '700286';
	private $password = 'O264KN8u';
	private $from = '106575632252';
	
	private $passport = '';
	
	private $mobile = '';
	private $message = '';
	
	public function __construct()
	{
		$this->ch = curl_init();
		
		$header = array();
		$header[] = 'Content-type: text/xml';  
		curl_setopt($this->ch, CURLOPT_URL, $this->api);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
		$this->auth();
	}
	public function init($mobile,$message,$_from = 0)
	{
		$this->mobile = $mobile;
		$this->message = $message;
		$_from = intval($_from);
		if($_from)
		{
			$this->from = $this->from.$_from;
		}
		return $this;
	}
	
	public function getMessage()
	{
		return $this->message;
	}
	
	/**
	 * 认证
	 */
	private function auth()
	{
		$auth_xml_body = '
		<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
		<soapenv:Body>
			<ns1:auth soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="http://webservice.mas.sxit.com">
					<header href="#id0"/>
				<spid xsi:type="xsd:string">'.$this->username.'</spid>
				<password xsi:type="xsd:string">'.$this->password.'</password>
			</ns1:auth>
			<multiRef id="id0" soapenc:root="0" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="ns2:InfoHeader" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns2="urn:model.webservice.mas.sxit.com">
				<code xsi:type="xsd:string">auth</code>
				<sid xsi:type="xsd:string" xsi:nil="true"/>
				<sourceCode xsi:type="xsd:string" xsi:nil="true"/>
				<timeStamp xsi:type="xsd:string">0</timeStamp>
			</multiRef>
		</soapenv:Body>
		</soapenv:Envelope>';


		$result = $this->sendCurl($auth_xml_body);
	
		if($result['authReturn']['respCode'] != 0)
		{
			return false;
			
		}
		$this->passport = $result['authReturn']['respMessage'];
	}
	/**
	 * 发短信
	 */
	public function send()
	{
		if(!$this->passport)
		{
			return false;
		}
		
		$mobile = $this->mobile;
		if(!is_array($mobile))
		{
			$mobile = array($mobile);
		}
		$mobileCount = count($mobile);
		
		$sequence_id = time();
		$mobileXml = '';
		foreach($mobile as $value){
			$mobileXml .= '<receiverList xsi:type="xsd:string">'.$value.'</receiverList>';
		}
		$senmms_xml_body = '
		<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
		<soapenv:Body>
			<ns1:sendSms soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="http://webservice.mas.sxit.com">
				<header href="#id0"/>
				<passport xsi:type="xsd:string">'.$this->passport.'</passport>
				<sequence href="#id1"/>
				<srcid xsi:type="xsd:string">'.$this->from.'</srcid>
				<receiverList soapenc:arrayType="xsd:string['.$mobileCount.']" xsi:type="soapenc:Array" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">
				'.$mobileXml.'
				</receiverList>
				<content xsi:type="xsd:string">'.$this->message.'【老宗医】</content>
				<reportFlag href="#id2"/>
			</ns1:sendSms>
			<multiRef id="id2" soapenc:root="0" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" 
			xsi:type="xsd:boolean" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">true</multiRef>
			<multiRef id="id0" soapenc:root="0" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="ns2:InfoHeader" xmlns:ns2="urn:model.webservice.mas.sxit.com" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">
				<code xsi:type="xsd:string">sendSms</code>
				<sid xsi:type="xsd:string" xsi:nil="true"/>
				<sourceCode xsi:type="xsd:string" xsi:nil="true"/>
					<timeStamp xsi:type="xsd:string">0</timeStamp>
			</multiRef>
			<multiRef id="id1" soapenc:root="0" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="xsd:int" 
			xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$sequence_id.'</multiRef>
		</soapenv:Body>
		</soapenv:Envelope>';
		$result = $this->sendCurl($senmms_xml_body);
		if(isset($result['sendSmsReturn']['respCode']) && $result['sendSmsReturn']['respCode'] != 0)
		{
			//发送失败
			if(defined('__DEBUG__') && __DEBUG__ == 'DEVELOP')
			{
				return true;
			}
			return false;
		}
		
		return true;
	}
	/**  短信应答 */
	protected function response()
	{
		$xmlbody = '
		<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
			<soapenv:Body>
				<ns1:getSendResp soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="http://webservice.mas.sxit.com">
					<header href="#id0"/>
					<passport xsi:type="xsd:string">'.$this->passport.'</passport>
				</ns1:getSendResp>
				<multiRef id="id0" soapenc:root="0" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="ns2:InfoHeader" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns2="urn:model.webservice.mas.sxit.com">
					<code xsi:type="xsd:string">getSendResp</code>
					<sid xsi:type="xsd:string" xsi:nil="true"/>
					<sourceCode xsi:type="xsd:string" xsi:nil="true"/>
					<timeStamp xsi:type="xsd:string">0</timeStamp>
				</multiRef>
			</soapenv:Body>
		</soapenv:Envelope>';
		$result = $this->sendCurl($xmlbody);
	}
	/**  */
	//状态报告
	protected function report()
	{
		$xmlbody = '<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
		<soapenv:Body>
			<ns1:getDeliver soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="http://webservice.mas.sxit.com">
				<header href="#id0"/>
				<passport xsi:type="xsd:string">通行证</passport>
			</ns1:getDeliver>
			<multiRef id="id0" soapenc:root="0" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="ns2:InfoHeader" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns2="urn:model.webservice.mas.sxit.com">
				<code xsi:type="xsd:string">getDeliver</code>
				<sid xsi:type="xsd:string" xsi:nil="true"/>
				<sourceCode xsi:type="xsd:string" xsi:nil="true"/>
				<timeStamp xsi:type="xsd:string">0</timeStamp>
			</multiRef>
		</soapenv:Body>
		</soapenv:Envelope>';
		$result = $this->sendCurl($xmlbody);
	}
	/**  */
	/** 注销 */
	public function logout()
	{
		$logout_xml_body = '
		<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
		<soapenv:Body>
			<ns1:terminate soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="http://webservice.mas.sxit.com">
			<header href="#id0"/>
				<passport xsi:type="xsd:string">'.$this->passport.'</passport>
			</ns1:terminate>
			<multiRef id="id0" soapenc:root="0" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="ns2:InfoHeader" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns2="urn:model.webservice.mas.sxit.com">
				<code xsi:type="xsd:string">terminate</code>
				<sid xsi:type="xsd:string" xsi:nil="true"/>
				<sourceCode xsi:type="xsd:string" xsi:nil="true"/>
				<timeStamp xsi:type="xsd:string">0</timeStamp>
			</multiRef>
		</soapenv:Body>
		</soapenv:Envelope>';
		$result = $this->sendCurl($logout_xml_body);
		$this->passport = '';
	}
	public function __destruct()
	{
		if($this->passport)
		{
			$this->logout();
		}
		curl_close($this->ch);
	}
	
	
	protected function sendCurl($fields)
	{
		
		$result = '';
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $fields);
		$response = curl_exec($this->ch);
		
		if(curl_errno($this->ch)){
			$result = curl_error($this->ch);
		}else{
			
			$result = $this->xml_to_array($response);
			if(isset($result['sendSmsReturn']['respCode']) && $result['sendSmsReturn']['respCode'] != 0)
			{
				$this->message = 'RespCode：'.$result['authReturn']['respCode'].'>>';'respMessage：'.$result['authReturn']['respMessage'];
			}
		}
		
		
		return $result;
	}
	
	protected function xml_to_array( $xml )
	{
	    $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
	    if(preg_match_all($reg, $xml, $matches))
	    {
	        $count = count($matches[0]);
	        $arr = array();
	        for($i = 0; $i < $count; $i++)
	        {
	            $key= $matches[1][$i];
	            $val = $this->xml_to_array( $matches[2][$i] );  // 递归
	            if(array_key_exists($key, $arr))
	            {
	                if(is_array($arr[$key]))
	                {
	                    if(!array_key_exists(0,$arr[$key]))
	                    {
	                        $arr[$key] = array($arr[$key]);
	                    }
	                }else{
	                    $arr[$key] = array($arr[$key]);
	                }
	                $arr[$key][] = $val;
	            }else{
	                $arr[$key] = $val;
	            }
	        }
	        return $arr;
	    }else{
	        return $xml;
	    }
	}
}
?>