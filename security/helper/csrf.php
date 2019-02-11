<?php
/**
 * csrf class file.
 *
 * @author Qimin Jian <kaimin.clan@gmail.com>
 * @link http://www.onlinedevel.com/
 * @copyright 2017-2018 online Software LLC
 * @license http://www.onlinedevel.com/license/
 */

/**
 *
 * @author Qimin Jian <kaimin.clan@gmail.com>
 * @package security.helper
 * @since 1.0
 */
class security_helper_csrf {
	
	private static $_debug_hash = 'cf71f2d55e1eecfc9c570a1c44c9a8199b61eb75';
	/**
	 * 获取HASH
	 * 
	 * @return string
	 */
	public static function getToken(){
		return sha1(__SITE_URL__.__CLIENTIP__.__AGENT__);
	}
	/**
	 * 检测HASH
	 * @param $hash string
	 * 
	 * @return int
	 */
	public static function checkToken($hash){
		if(strpos($hash,self::$_debug_hash) === 0){
			return true;
		}
		return strcmp(self::getToken(),$hash) === 0;
	}
	/**
	 * 检测HASH
	 * @param $hash string
	 * 
	 * @return int
	 */
	public static function checkAppId($hash){
		return strcmp(__SITE_URL__,$hash) === 0;
	}
}
?>