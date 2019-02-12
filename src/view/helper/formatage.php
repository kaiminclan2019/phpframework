<?php
/**
 * 年龄计算
 */
class view_helper_formatage
{
	private $before = '';
	private $after = '';
	public function __construct()
	{
	}
	public function init($before = 0, $after = 0)
	{
		$this->before = $before;
		if($after < 1)
		{
			$after = date('Y-m-d');
		}
		$this->after = $after;
		return $this;
	}
	/*
	 * @description    取得两个时间戳相差的年龄
	 * @before         较小的时间戳
	 * @after          较大的时间戳
	 * @return str     返回相差年龄y岁m月d天
	**/
	private function datediffage($before, $after) 
	{
		if ($before>$after) {
			$b = getdate($after);
			$a = getdate($before);
		} else {
			$b = getdate($before);
			$a = getdate($after);
		}
		$n = array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
		$y=$m=$d=0;
		if ($a['mday']>=$b['mday']) { //天相减为正
		
			if ($a['mon']>=$b['mon']) {//月相减为正
				$y=$a['year']-$b['year'];$m=$a['mon']-$b['mon'];
			}else { //月相减为负，借年
				$y=$a['year']-$b['year']-1;$m=$a['mon']-$b['mon']+12;
			}
			$d=$a['mday']-$b['mday'];
		} else {  //天相减为负，借月
			if ($a['mon']==1) { //1月，借年
				$y=$a['year']-$b['year']-1;$m=$a['mon']-$b['mon']+12;$d=$a['mday']-$b['mday']+$n[12];
			} else {
				if ($a['mon']==3) { //3月，判断闰年取得2月天数
					$d=$a['mday']-$b['mday']+($a['year']%4==0?29:28);
				}
				else {
					$d=$a['mday']-$b['mday']+$n[$a['mon']-1];
				}
				if ($a['mon']>=$b['mon']+1) { //借月后，月相减为正
					$y=$a['year']-$b['year'];$m=$a['mon']-$b['mon']-1;
				}
				else { //借月后，月相减为负，借年
					$y=$a['year']-$b['year']-1;$m=$a['mon']-$b['mon']+12-1;
				}
			}
		}
		return ($y==0?'':$y.'岁').($m==0?'':$m.'个月').($d==0?'':$d.'天');
	}
	
	public function get()
	{
		return $this->datediffage($this->before,$this->after);
	}
}