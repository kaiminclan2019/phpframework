<?php
class helper_agent
{
	private static $touchbrowser_list = array(
		'iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
		'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
		'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
		'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
		'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
		'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
		'benq', 'haier', '^lct', '320x320', '240x320', '176x220', 'windows phone'
	);
	private static $wmlbrowser_list = array(
		'cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom',
		'pantech', 'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',
		'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte'
	);

	private static $pad_list = array('ipad');
	
	public static function init($agent)
	{
		//主机识别
		$host = $_SERVER['HTTP_HOST'];
		
		//设备识别
		$agent = $_SERVER['HTTP_USER_AGENT'];
		list($device) = explode('.',$host);
		$ismobile = 0;
		if($device == 'm')
		{
			$ismobile = 1;
		}else{
			$useragent = strtolower($agent);
			//touch
			foreach(self::$touchbrowser_list as $key=>$mask)
			{
				if(strpos($useragent, $mask) !== false)
				{
					$ismobile = 1;
					break;
				}
			}
			
			if($ismobile < 1)
			{
				//wml版
				foreach(self::$wmlbrowser_list as $key=>$mask)
				{
					if(strpos($useragent, $mask) !== false)
					{
						$ismobile = 1;
						break;
					}
				}
			}
		}
		return $ismobile;
	}
}