<?php
class helper_random    
{    
    public static function nextLong()    
    {    
        $tmp = rand(0,1)?'-':'';    
        return $tmp.rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(100, 999).rand(100, 999);    
    }
	
	public static function getString($len){
		$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		return self::parse($str,$len);
	}
	public static function getDigital($len){
		$str = '0123456';
		return self::parse($str,$len);
	}
	public static function getIntString($len){
		$str = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
		$str .= '123456789';
		return self::parse($str,$len);
	}
	
	private static function parse($str,$len)
	{
		$result = array();
		$length = strlen($str);
		for($i=0;$i<$len;$i++)
		{
			$result[] = substr($str,mt_rand(0,$length-1),1);
		}
		
		return implode('',$result);
	}
	/**
	 * 全概率计算
	 *
	 * @param array $p array('a'=>0.5,'b'=>0.2,'c'=>0.4)
	 * @return string 返回上面数组的key
	 */
	public static function lucky($ps){
		static $arr = array();
		$key = md5(serialize($ps));
	
		if (!isset($arr[$key])) {
			$max = array_sum($ps);
			foreach ($ps as $k=>$v) {
				if(!is_numeric($v)) continue;
				$v = $v / $max * 10000;
				for ($i=0; $i<$v; $i++) $arr[$key][] = $k;
			}
		}
		return $arr[$key][mt_rand(0,count($arr[$key])-1)];
	}
	
}  