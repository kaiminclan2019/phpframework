<?php
// +----------------------------------------------------------------------
// | Framework
// +----------------------------------------------------------------------
// | This is NOT a freeware, use is subject to license terms
// +----------------------------------------------------------------------
// | Author: jianqimin <kaimin.clan@gmail.com>
if(!defined('__BAMBOO__')) {
	exit('Access Denied');
}
class view_plug_form
{
	public static function _checkbox($id,$data,$selected)
	{
		$output =array();
		if(is_array($data))
		{
			foreach($data as $key=>$val)
			{
				$selected = in_array($val['id'],$selected)?'checked="checked"':'';
				$output[] = '<input type="checkbox" name="" value="'.$val['id'].'" '.$selected.' />'.$val['title'];
			}
		}else{
			
			$selected = in_array($val['id'],$selected)?'checked="checked"':'';
			$output[] = '<input type="checkbox" name="" value="'.$val['id'].'" '.$selected.' />'.$val['title'];
		}
		
		return implode('',$output);
	}
	
	
	public static function _radio()
	{
		$output =array();
		if(is_array($data))
		{
			foreach($data as $key=>$val)
			{
				$selected = in_array($val['id'],$selected)?'checked="checked"':'';
				$output[] = '<input type="radio" name="" value="'.$val['id'].'" '.$selected.' />'.$val['title'];
			}
		}else{
			
			$selected = in_array($val['id'],$selected)?'checked="checked"':'';
			$output[] = '<input type="radio" name="" value="'.$val['id'].'" '.$selected.' />'.$val['title'];
		}
		
		return implode('',$output);
	}
	public static function _select($id,$data,$selected)
	{
		$option = array();
		if(!is_array($data))
		{
			$data = array($data);
		}
		
		foreach($data as $key=>$val)
		{
			$selected = in_array($val['id'],$selected)?'selected="selected"':'';
			$option[] = '<option value="'.$val['id'].'" '.$selected.'>'.$val['title'].'</option>';
		}
		return '<select id="" name="">'.implode(' ',$option).'</select>';
	}
	public static function _text($element,$selected,$type='text')
	{
		if(!in_array($type,array('text','hidden')))
		{
			return '';
		}
		return '<input type="'.$type.'" id="'.$element.'" name="'.$element.'" value="'.$selected.'">';
	}
	public static function _textarea($element,$selected)
	{
		return '<textarea id="'.$element.'" name="'.$element.'">'.$selected.'</textarea>';
	}
	
	
}
?>