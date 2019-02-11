<?php
class view_driver_xml
{
	
	private $data = array();
	public function dispather($data)
	{
		if($data['status'] == 200)
		{
			echo self::arrtoxml(array('status'=>$data['status'],'msg'=>$data['msg'],'data'=>$data['data'])); 
		}else{
			$redirect_uri = $data['redirect_uri'];
			if($redirect_uri)
			{
				echo self::arrtoxml(array('status'=>$data['status'],'msg'=>$data['msg'],'redirect_uri'=>$redirect_uri)); 
			}else{
				echo self::arrtoxml(array('status'=>$data['status'],'msg'=>$data['msg'])); 
			}
		}
		exit();
	}
	
	public static function arrtoxml($arr,$dom=0,$item=0){
    if (!$dom){
        $dom = new DOMDocument("1.0");
    }
    if(!$item){
        $item = $dom->createElement("root"); 
        $dom->appendChild($item);
    }
    foreach ($arr as $key=>$val){
        $itemx = $dom->createElement(is_string($key)?$key:"item");
        $item->appendChild($itemx);
        if (!is_array($val)){
            $text = $dom->createTextNode($val);
            $itemx->appendChild($text);
             
        }else {
            self::arrtoxml($val,$dom,$itemx);
        }
    }
    return $dom->saveXML();
}
	public static function xml_to_array( $xml )
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