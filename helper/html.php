<?php

class helper_Html {
	
	public static function removeHref($content){
		
		return preg_replace("#<a[^>]*>(.*?)</a>#is", "$1", $content);
	}
	public static function removeFont($content){
		
		return preg_replace("#<font[^>]*>(.*?)</font>#is", "$1", $content);
	}
	public static function removeSpan($content){
		
		return preg_replace("#<span[^>]*>(.*?)</span>#is", "$1", $content);
	}
	
	public function removeEmptyTag($html){
		return preg_replace("/<[^\/>]*>([\s]?)*<\/[^>]*>/s", '', $html); 
	}
	
	
	public static function removeHtmlTags($tags, $str,$content = true)
	{
		if($content){
			return preg_replace("#<".$tags."[^>]*>(.*?)</".$tags.">#is", "$1", $str);
		}else{
			return preg_replace("#<".$tags."[^>]*>(.*?)</".$tags.">#is", "", $str);
		}
	}
	
	/**
	 * 提取链接
	 */
	public static function fetchLinks($html) {    

		preg_match_all("'<\s*a\s.*?href\s*=\s*([\"\'])?(?(1)(.*?)\\1|([^\s\>]+))[^>]*>?(.*?)</a>'isx",$html,$links);  
		return $links;
	}
	/**
	 * 提取标签内数据
	 */
	public static function fetchTagData($html,$tag = '*') {    

		$pattern = '/<.'.$tag.'?>(.*?)<\/.'.$tag.'?>/is';
		preg_match_all($pattern,$html,$match); 
		return $match;
	}
	/**
	 * 提取图片
	 */
	public static function fetchImage($html)
	{
		$match = array();
		if($html)
		{
			$pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.jpeg|\.pjpeg|\.wmb]))[\'|\"].*?[\/]?>/";
			preg_match_all($pattern,$html,$match);
		} 
		
		return $match;
	}
	/**
	 * 移除JS代码
	 */
	public static function removeJavascript($html)
	{
		if(!$html)
		{
			return $html;
		}
		if(strpos($html,'</script>') !== false)
		{
			$html = preg_replace("'<script[^>]*?>.*?</script>'si", '', $html); 
		}
		
		return $html;
	}

	/**
	 * 移除样式代码
	 */
	public static function removeStyle($html)
	{
		if(!$html)
		{
			return $html;
		}
		if(strpos($html,'</style>') !== false)
		{
			$html = preg_replace("'<style[^>]*?>.*?</style>'si", '', $html); 
		}
		
		return $html;
	}

	/**
	 * 移除注释代码
	 */
	public static function removeNotes($html)
	{
		if(!$html)
		{
			return $html;
		}
		if(strpos($html,'<!--') !== false)
		{
			$html = preg_replace("'<!--[/!]*?[^<>]*?>'si", '', $html); 
		}
		return $html;
	}
}