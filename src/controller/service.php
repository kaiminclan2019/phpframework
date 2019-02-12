<?php
/**
 * 控制器基类
 * 
 */
abstract class service extends controller_helper_base {
	
	protected function getInt($ids){
		if(is_array($ids)){
			return array_map('intval',$ids);
		}
		return intval($ids);
	}
}