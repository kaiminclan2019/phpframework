<?php
// +----------------------------------------------------------------------
// | Framework
// +----------------------------------------------------------------------
// | This is NOT a freeware, use is subject to license terms
// +----------------------------------------------------------------------
// | Author: jianqimin <kaimin.clan@gmail.com>
class view_plug_tag
{
	private static $mask = '[a-zA-Z0-9_\'\"\[\]]+';
	
	public static function _empty($template)
	{
		$template = preg_replace('/([\n\r\t]*)\<empty\sname="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php if(empty(\\2[\\3])) { ?>\\4')", $template);
		$template = preg_replace('/([\n\r\t]*)\<empty\sname="(\S+)">([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php if(empty(\\2)) { ?>\\3')", $template);
		$template = preg_replace('/([\n\r\t]*)\<empty\sname=\'\.(\S+)\.\'>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php if(empty(\\2)) { ?>\\3')", $template);
		$template = preg_replace('/([\n\r\t]*)\<empty\sname=\'.([\$\.a-zA-Z0-9\_\[\]\'\"]+).\'>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php if(empty(\\2)) { ?>\\3')", $template);
		$template = preg_replace("/([\n\r\t]*)\<else\s\/>/i", "<?php } else { ?>", $template);
		$template = preg_replace("/([\n\r\t]*)\<\/empty\>/i", "<?php
 } ?>", $template);
		
		return $template;
	}

	public static function _notempty($template)
	{
		$template = preg_replace('/([\n\r\t]*)\<notempty\sname="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php if(!empty($\\2[\\3])) { ?>\\4')", $template);
		$template = preg_replace('/([\n\r\t]*)\<notempty\sname="(\$\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php if(!empty(\\2)) { ?>\\3')", $template);
		$template = preg_replace('/([\n\r\t]*)\<notempty\sname="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php if(!empty($\\2)) { ?>\\3')", $template);
		$template = preg_replace('/([\n\r\t]*)\<notempty\sname=\'.([\$\.a-zA-Z0-9\_\[\]\'\"]+).\'>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php if(!empty(\\2)) { ?>\\3')", $template);
		$template = preg_replace("/\<\/notempty\>/i", "<?php } ?>", $template);
		
		return $template;
	}
	public static function _eq()
	{
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="(\w+)"\svalue="(\w+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php if($\\2==\"\\3\") { ?>\\4')", $template);
		$template = preg_replace("/([\n\r\t]*)\<else\s\/>/i", "<?php } else { ?>", $template);
		$template = preg_replace("/\<\/eq\>/i", "<?php } ?>", $template);
	}
	public static function _neq($template)
	{
		$template = preg_replace('/([\n\r\t]*)\<neq\sname="(\S+)\.(\S+)"\svalue="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]!=\\4[\\5]) { ?>\\6')", $template);
		$template = preg_replace('/([\n\r\t]*)\<neq\sname="(\S+)\.(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]!=\\4) { ?>\\5')", $template);
		$template = preg_replace('/([\n\r\t]*)\<neq\sname="('.self::$mask.')"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if($\\2!=\\3) { ?>\\4')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<neq\sname=\'.(\S+).\'\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2 != \\3) { ?>\\4')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<neq\sname="(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2!=\\3) { ?>\\4')", $template);
		$template = preg_replace("/\<else\s\/>/i", "<?php
 } else { ?>", $template);
		$template = preg_replace("/\<\/neq\>/i", "<?php
 } ?>", $template);
		return $template;
	}
	public static function _gt($template)
	{
		$template = preg_replace('/([\n\r\t]*)\<gt\sname="(\S+)\.(\S+)"\svalue="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]>\\4[\\5]) { ?>\\6')", $template);
		$template = preg_replace('/([\n\r\t]*)\<gt\sname="(\S+)\.(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]>\\4) { ?>\\5')", $template);
		$template = preg_replace('/([\n\r\t]*)\<gt\sname="(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2>\\3) { ?>\\4')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<gt\sname=\'.(\S+).\'\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2 > \\3) { ?>\\4')", $template);
		$template = preg_replace("/\<else\s\/>/i", "<?php
 } else { ?>", $template);
		$template = preg_replace("/\<\/gt\>/i", "<?php
 } ?>", $template);
		return $template;
	}
	public static function _egt($template)
	{
		$template = preg_replace('/([\n\r\t]*)\<egt\sname="(\S+)\.(\S+)"\svalue="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]>=\\4[\\5]) { ?>\\6')", $template);
		$template = preg_replace('/([\n\r\t]*)\<egt\sname="(\S+)\.(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]>=\\4) { ?>\\5')", $template);
		$template = preg_replace('/([\n\r\t]*)\<egt\sname="(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2>=\\3) { ?>\\4')", $template);
		$template = preg_replace("/\<else\s\/>/i", "<?php
 } else { ?>", $template);
		$template = preg_replace("/\<\/egt\>/i", "<?php
 } ?>", $template);
		return $template;
	}
	public static function _php($template)
	{
		$template = preg_replace('/([\n\r\t]*)\<php\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php \\6')", $template);
		$template = preg_replace("/\<\/php\>/i", "?>", $template);
		return $template;
	}
	
	public static function _lt($template)
	{
		$template = preg_replace('/([\n\r\t]*)\<lt\sname="(\S+)\.(\S+)"\svalue="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]<\\4[\\5]) { ?>\\6')", $template);
		$template = preg_replace('/([\n\r\t]*)\<lt\sname="(\S+)\.(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]<\\4) { ?>\\5')", $template);
		$template = preg_replace('/([\n\r\t]*)\<lt\sname="(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2<\\3) { ?>\\4')", $template);
 
 
 
		$template = preg_replace('/([\n\r\t]*)\<lt\sname=\'.(\S+).\'\svalue=\'.(\S+).\'\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2 <\\3) { ?>\\4')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<lt\sname=\'.(\S+).\'\svalue="([A-Za-z]+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2 <\'\\3\') { ?>\\4')", $template);
		$template = preg_replace('/([\n\r\t]*)\<lt\sname=\'.(\S+).\'\svalue="([-0-9]+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2 <\\3) { ?>\\4')", $template);
 
		$template = preg_replace("/\<else\s\/>/i", "<?php
 } else { ?>", $template);
		$template = preg_replace("/\<\/lt\>/i", "<?php
 } ?>", $template);
		return $template;
	}
	
	public static function _elt($template)
	{
		$template = preg_replace('/([\n\r\t]*)\<elt\sname="(\S+)\.(\S+)"\svalue="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]<=\\4[\\5]) { ?>\\6')", $template);
		$template = preg_replace('/([\n\r\t]*)\<elt\sname="(\S+)\.(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2[\\3]<=\\4) { ?>\\5')", $template);
		$template = preg_replace('/([\n\r\t]*)\<elt\sname="(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(\\2<=\\3) { ?>\\4')", $template);
		$template = preg_replace("/\<else\s\/>/i", "<?php
 } else { ?>", $template);
		$template = preg_replace("/\<\/elt\>/i", "<?php
 } ?>", $template);
		return $template;
	}
	
	public static function _volist($template)
	{
		$template = preg_replace("/[\n\r\t]*\<volist\s+id=\"(\S+)\"\s+key=\"(\S+)\"\s+name=\"(\S+)\"\>[\n\r\t]*/ies", "view_plug_tag::stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2 => \\3) { ?>')", $template);
		$template = preg_replace('/[\n\r\t]*\<volist\s+id="([a-zA-Z0-9]+)"\s+name="([\$a-zA-Z0-0\.]+)"\>[\n\r\t]*/ies', "view_plug_tag::stripvtags('<? if(is_array($\\2)) foreach($\\2 as \$key=>$\\1) { ?>')", $template);
		$template = preg_replace("/\<\/volist\>/i", "<?php
 } ?>", $template);
	}
	
	public static function _escape()
	{
        $template  =   preg_replace('/{\$(\w+)\.(\w+)\s*}/is','<?php
 echo ($\\1["\\2"]) ?> ',$template);
		
        $template  =   preg_replace('/{\$(\w+)\s*}/is','<?php
 echo ($\\1) ?> ',$template);
	}
	
	public static function _select($template)
	{
		$template = preg_replace('/[\n\r\t]*\<select\s+id="(\S+)"\s+name="(\S+)"\s+value="(\S+)">[\n\r\t]*/ies', "view_plug_tag::stripvtags('<?php
 select($\\1,$\\2,$\\3)?>')", $template);
		return $template;
	}
	public static function _range($template)
	{
		$template = preg_replace('/[\n\r\t]*\<range\s+name="(\S+)\.(\S+)"\s+value="(\S+)\.(\S+)"\>[\n\r\t]*/ies', "view_plug_tag::stripvtags('<?php
 if(in_array(\\1[\\2],\\3[\\4])) { ?>')", $template);
		$template = preg_replace('/[\n\r\t]*\<range\s+name="(\S+)\.(\S+)"\s+value="(\S+)"\>[\n\r\t]*/ies', "view_plug_tag::stripvtags('<?php
 if(in_array(\\1[\\2],\\3)) { ?>')", $template);
		$template = preg_replace('/([\n\r\t]*)\<range\s+name=\'.(\S+).\'\s+value=\'.(\S+).\'\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(in_array(\\2,\\3)) { ?>')", $template);
 
		$template = preg_replace('/[\n\r\t]*\<range\s+name="([a-zA-Z0-9]+)"\s+value="(\S+)"\>[\n\r\t]*/ies', "view_plug_tag::stripvtags('<?php
 if(in_array('\\1',\\2)) { ?>')", $template);

 
		$template = preg_replace('/[\n\r\t]*\<range\s+name="(\S+)"\s+value="(\S+)"\>[\n\r\t]*/ies', "view_plug_tag::stripvtags('<?php
 if(in_array($\\1,view_plug_tag::formatData(\'\\2\'))) { ?>')", $template);

		$template = preg_replace('/([\n\r\t]*)\<range\s+name=\'.(\S+).\'\s+value="([$a-zA-Z0-9\[\]\']+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(in_array(\\2,\\3)) { ?>')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<range\s+name=\'.(\S+).\'\s+value="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(in_array(\\2,view_plug_tag::formatData(\'\\3\'))) { ?>')", $template);
 
		$template = preg_replace("/\<\/range\>/i", "<?php
 } ?>", $template);
		
		return $template;
	}
	
	public static function formatData($string){
		return explode(',',$string);
	}
	
	
	public static function _if($template)
	{
		
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="(\S+)\.(\S+)\.(\S+)"\svalue="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2[\'\\3\'][\'\\4\']) && \\2[\'\\3\'][\'\\4\']==\\5[\'\\6\']) { ?>\\7')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="(\S+)\.(\S+)"\svalue="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2[\'\\3\']) && \\2[\'\\3\']==\\4[\'\\5\']) { ?>\\6')", $template);
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="(\S+)\.(\S+)"\svalue="(\S+)\.(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2[\'\\3\']) && \\2[\'\\3\']==\\4[\'\\5\']) { ?>\\6')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="(\S+)\.(\S+)\.(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2[\'\\3\'][\'\\4\']) && \\2[\'\\3\'][\'\\4\']==\'\\5\') { ?>\\6')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="(\S+)\.(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2[\'\\3\']) && \\2[\'\\3\']==\\4) { ?>\\5')", $template);
		
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="\$(\S+)"\svalue="([a-zA-Z]+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset($\\2) && $\\2==$\\3) { ?>\\4')", $template);
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="\$(\S+)"\svalue="([0-9]+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset($\\2) && $\\2==\\3) { ?>\\4')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="('.self::$mask.')"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset($\\2) && $\\2==\\3) { ?>\\4')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<eq\sname=\'.(\S+).\'\svalue=\'.(\S+).\'\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2) && \\2==\\3) { ?>\\4')", $template);
 
 
		$template = preg_replace('/([\n\r\t]*)\<eq\sname=\'.(\S+).\'\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2) && \\2==\\3) { ?>\\4')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<eq\sname=\'.(\S+).\'\svalue="([A-Za-z]+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2) && \\2==\'\\3\') { ?>\\4')", $template);
		$template = preg_replace('/([\n\r\t]*)\<eq\sname=\'.(\S+).\'\svalue="([-0-9]+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2) && \\2==\\3) { ?>\\4')", $template);
 
		$template = preg_replace('/([\n\r\t]*)\<eq\sname="(\S+)"\svalue="(\S+)"\>([\n\r\t]*)/ies', "view_plug_tag::stripvtags('\\1<?php
 if(isset(\\2) && \\2==\\3) { ?>\\4')", $template);
		$template = preg_replace("/\<else\s\/>/i", "<?php
 } else { ?>", $template);
		$template = preg_replace("/\<\/eq\>/i", "<?php
 } ?>", $template);
		
		return $template;
	}
	
	
	
	public static function _radio($template)
	{
		$template = preg_replace('/[\n\r\t]*\<radio\s+id="(\S+)"\s+name="(\S+)"\s+value="(\S+)">[\n\r\t]*/ies', "view_plug_tag::stripvtags('<?php
 radio($\\1,$\\2,$\\3)?>')", $template);
		return $template;
	}
	
	
	public static function _checkbox($template)
	{
		$template = preg_replace('/[\n\r\t]*\<checkbox\s+id="(\S+)"\s+name="(\S+)"\s+value="(\S+)">[\n\r\t]*/ies', "view_plug_tag::stripvtags('<?php
 checkbox($\\1,$\\2,$\\3)?>')", $template);
		
		return $template;
	}
	
	public static function _vlist($template)
	{
		$template = preg_replace("/[\n\r\t]*\<volist\s+id=\"(\S+)\"\s+key=\"(\S+)\"\s+name=\"(\S+)\"\>[\n\r\t]*/ies", "view_plug_tag::stripvtags('<?php if(is_array($\\3)) { foreach($\\3 as $\\2 => $\\1) { ?>')", $template);
		$template = preg_replace('/[\n\r\t]*\<volist\s+id="(\S+)"\s+name="(\w+)\.(\w+)"\>[\n\r\t]*/ies', "view_plug_tag::stripvtags('<?php
 if(is_array($\\2[\"\\3\"])) { foreach($\\2[\"\\3\"] as \$key=>$\\1) { ?>')", $template);
		$template = preg_replace('/[\n\r\t]*\<volist\s+id="(\S+)"\s+name="(\S+)"\>[\n\r\t]*/ies', "view_plug_tag::stripvtags('<?php
 if(is_array($\\2)) { foreach($\\2 as \$key=>$\\1) { ?>')", $template);
		$template = preg_replace("/[\n\r\t]*\<volist\s+(\S+)\s+(\S+)\s+(\S+)\>[\n\r\t]*/ies", "view_plug_tag::stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>')", $template);
		$template = preg_replace("/\<\/volist\>/i", "<?php
 } } ?>", $template);
		
		return $template;
	}
	
	public static function _block($template)
	{

		/*PHP正则提取图片img标记中的任意属性*/
		$circle = '<circle id="ap_test" cx="200" cy="2000" r="2" stroke="black" fill="red"/>';
 
		preg_match_all('/(\w+)\s*=\s*(?:(?:(["\'])(.*?)(?=\2))|([^\/\s]*))/', $circle, $match);

		$template = preg_replace('/[\n\r\t]*\<block\s+id="(\S+)"\s+name="(\S+)"\>[\n\r\t]*/ies', 
		"view_plug_tag::stripvtags('<?php ($\\2)) { foreach($\\2 as \$key=>$\\1) ?>')", $template);
		
		return $template;
	}
	
	public static function _image($template)
	{
		//附件
		$template = preg_replace('/<image\s+title="(\S+)"\s+src="(\S+)"\s+href="(\S+)">/ies', "view_plug_tag::stripvtags('<?php
 attachment($\\1,$\\2,$\\3)?>')", $template);
		return $template;
	}
	
	public static function stripvtags($expr, $statement = '') {
		$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
		$statement = str_replace("\\\"", "\"", $statement);
		return $expr.$statement;
	}
}