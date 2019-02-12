<?php
class view_helper_formatnumeric
{
	private $num = '';
	private $mode = '';
	private $sim = '';
	public function __construct()
	{
	}
	public function init($num,$mode = true,$sim = true)
	{
		$this->num = $num;
		$this->mode = $mode;
		$this->sim = $sim;
		return $this;
	}
	
	public function get()
	{
		$num = $this->num;
		$sim = $this->sim;
		$mode = $this->mode;
	    if(!is_numeric($num)) return 0;
	    $char    = $sim ? array('零','一','二','三','四','五','六','七','八','九')
	    : array('零','壹','贰','叁','肆','伍','陆','柒','捌','玖');
		$char    = $sim == 2?array(0,1,2,3,4,5,6,7,8,9):$char;
	    $unit    = $sim ? array('','十','百','千','','万','亿','兆')
	    : array('','拾','佰','仟','','萬','億','兆');
		$unit    = $sim == 2?'':$unit;
	    $retval  = $mode ? '元':'点';
		$retval = $mode == 2?'':$retval;
	    //小数部分
	    if(strpos($num, '.'))
		{
	        list($num,$dec) = explode('.', $num);
	        $dec = strval(round($dec,2));
	        if($mode)
			{
	            $retval .= "{$char[$dec['0']]}角{$char[$dec['1']]}分";
	        }else
			{
	            for($i = 0,$c = strlen($dec);$i < $c;$i++) 
				{
	                $retval .= $char[$dec[$i]];
	            }
	        }
	    }
	    //整数部分
	    $str = $mode ? strrev(intval($num)) : strrev($num);
	    for($i = 0,$c = strlen($str);$i < $c;$i++) 
		{
	        $out[$i] = $char[$str[$i]];
	        if($mode)
			{
	            $out[$i] .= $str[$i] != '0'? $unit[$i%4] : '';
				if($i>1 and $str[$i]+$str[$i-1] == 0)
				{
					$out[$i] = '';
				}
				if($i%4 == 0)
				{
					$out[$i] .= $unit[4+floor($i/4)];
				}
	        }
	    }
	    $retval = join('',array_reverse($out)) . $retval;
	    return $retval;
	}
}