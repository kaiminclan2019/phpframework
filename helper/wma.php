<?php
$power = new wma();

echo $power->get(12.5,5);

	
/**
 *
 函数： WMA(X,N) 
参数： X为数组，N为计算周期
返回： 返回数组 
说明： 求X的加权移动平均。  
算法: 若Y=WMA(X,N)， 则 Y=(N*X0+(N-1)*X1+(N-2)*X2)+...+1*XN)/(N+(N-1)+(N-2)+...+1),X0表示本周期值，X1表示上一周期值... 

示例： WMA(CLOSE,5);
可以这样替换 (5*C+4*REF(C,1)+3*REF(C,2)+2*REF(C,3)+1*REF(C,4))/(5+4+3+2+1);

 */
 
class wma {
	
	const OPEN = 'open';
	
	const LOW = 'low';
	
	const HIGH = 'high';
	
	const CLOSE = 'close';
	
	function get($close,$day){
		$day = intval($day);
		if($day < 1){
			return 0;
		}
		
		$start = $close*$day;
		$end = 0;
		for($i=2;$i<=$day;$i++){
			$start += $this->ref(self::CLOSE,$i);
			$end +=$i;
		}
		
		if($start == 0 || $end == 0){
			return 0;
		}
		
		$lastVal = round($start/$end,4);
		
		return $lastVal;
		
	}

	function ref($type,$offset){
		
		$data = array(
			array('open'=>12.25,'low'=>10.25,'high'=>13.25,'close'=>14),
			array('open'=>12.25,'low'=>10.25,'high'=>13.25,'close'=>14),
			array('open'=>12.25,'low'=>10.25,'high'=>13.25,'close'=>14),
			array('open'=>12.25,'low'=>10.25,'high'=>13.25,'close'=>14),
			array('open'=>12.25,'low'=>10.25,'high'=>13.25,'close'=>14),
		);
		
		$length = count($data);
		if($length < $offset){
			return 0;
		}
		return $data[$offset-1][$type];
	}
}
?>