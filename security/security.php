<?php
/**
 * 
 安全
 数据操作
 	函数/命令
	SQL语句
	身份 
 数据层面
	敏感词/字
 	数字/整数/浮点数/字符/纯文本字符/HTML字符/类型/
 	电话/手机号码/电子邮件/URL/文件格式 
 接口
	来源(CSRF)
	频率(同源二次) 
 */
class security
{
	/**
	 *
	 * 访问控制
	 *
	 * 操作/请求/浏览
	 *
	 */
	public function alloweAccess(){
		
	}
	/**
	 *
	 * 串
	 *
	 * @param $len 串长度 
	 * @return string 串
	 */
	public static function getSalt($type,$len = 8){
		$salt = '';
		
		$salt = helper_random::getIntString($len);
		
		return $salt;
	}
	/**
	 *
	 * 加密
	 *
	 * @param $type 类型 
	 * @param $data 数据 
	 *
	 * @return string 串
	 */
	public static function encrypt($type,$data){
		$str = $data;
		
		return $salt;
	}
	/**
	 *
	 * 解密
	 *
	 * @param $type 类型 
	 * @param $data 数据 
	 *
	 * @return string 串
	 */
	public static function decrypt($type,$data){
		$str = $data;
		
		return $salt;
	}
	
	public function __call($method,$args){
	}
}