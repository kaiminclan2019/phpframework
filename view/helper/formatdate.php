<?php
class view_helper_formatdate
{
	private $data = '';
	private $foramt = '';
	public function __construct()
	{
	}
	public function init($date,$foramt = '')
	{
		$this->data = $date;
		$this->format = $foramt;
		return $this;
	}
	
	public function get()
	{
		$show_time = $this->data;
		$foramt = $this->format;
		$output = '';
		if($show_time)
		{
		
		
			if($foramt == 'date')
			{
				$output = date('Y-m-d',$show_time);
			}else{
				$now_time = time();
				$dur = $now_time - $show_time;
			
			if($dur < 0){
				return  date("Y-m-d H:i:s",$show_time);
			}else{
				if($dur < 60)
				{
					$output = $dur.'秒前';
				}else{
					if($dur < 3600)
					{
						$output = floor($dur/60).'分钟前';
					}else{
						if($dur < 86400)
						{
							$output = floor($dur/3600).'小时前';
						}else{
							if($dur < 259200)
							{//3天内
								$output = floor($dur/86400).'天前';
							}else{
								$output = date("Y-m-d H:i",$show_time);
							}
						}
					}
				}
			}
			}
		}
		return $output;
	}
}