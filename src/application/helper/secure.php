<?php
/**
 *
 * 安全控制
 
 访问方式
	GET HEAD POST

访问来源
	IP,代理
	COOKIE
	站内
堵截机制
	禁止访问
		永久
		临时
	限制访问
		IP黑名单，时间段内禁止
		验证码
 *
 * 设备安全，浏览器代理；特别授权代理
 *
 * 访问安全，用户发起访问，记录IP地址；回写COOKIE;连接请求间隔；APPID授权白名单；
 *
 * 数据安全，用户推送到后台数据，不允许有敏感词;不允许替代第三方链接,文件头、类型检测
 *
 * 来源安全,CSRF攻击，针对所有表单请求。增加CSRF验证；增加来源检测
 *
 * 处罚措施
 
 * 访问请求，输入验证码，图片验证码，短信/邮件验证码，安全链接；封禁IP;
 
 
 */
class application_helper_secure {
	
	/** 代理检测 */
	private function isAgent(){
		if(!defined('__AGENT__')){
			throw new Exception('未定义代理',-1);
		}
		
		if(__AGENT__ == ''){
			throw new Exception('未定义代理',-2);
		}
	}
	
	/** 访问安全 */
	private function isRequest(){
		
	}
	/** 数据安全*/
	private function isAllowedData(){
		
	}
	/** 来源安全*/
	private function isAllowedReferer(){
		$url = $_SERVER['REQUEST_URI'];
		$referer = $_SERVER['HTTP_REFERER'];
		if(strpos($referer,__SITE_URL__) === false){
			throw new Exception('非法访问',-1);
		}
	}
}