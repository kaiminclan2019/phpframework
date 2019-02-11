<?php
/**
 * 调试窗口
 *
 */
class application_helper_debug{
	
	public function dispather(){
		ob_start();
		$e = ob_get_contents();
	}
}
?>