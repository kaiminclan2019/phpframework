<?php
/**
 * 输出处理
 * 数字，标签，文本格式化处理
 */
class view_helper_output
{
	private $data = '';
	public function init($tag)
	{
		$this->data = $tag;
		return $this;
	}
	
	public function get()
	{
		return $return;
	}
}