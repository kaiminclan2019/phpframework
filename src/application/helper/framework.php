<?php
/**
 * 项目架构
 */
class application_helper_framework {
	/** 业务（命令） */
	private $app = 'app';
	/** 配置 */
	private $config = 'config';

	
	
	/** 日志（访问日志？运行日志？操作日志？） */
	private $log = 'log';
	/** 网页 */
	private $site = 'site';
	/** 数据库 */
	private $sql = 'sql';
	
	/** 编译/临时 */
	private $storage = 'storage';
	/** 缓存目录 */
	private $backup_data = 'storage/backup';
	/** 备份目录 */
	private $storage_data = 'storage/data';
	/** 模板编译目录 */
	private $storage_view = 'storage/view';
	/** 运行日志 */
	private $storage_log = 'storage/log';



	/** 单元测试 */
	private $test = 'tests';
	
	public function __construct(){
		if(!defined('__ROOT__')){
			$filename = dirname(__FILE__);
			define('__ROOT__',str_replace('\\','/',substr($filename,0,(stripos($filename,'vendor')-1))));
		}
		
		$setting = array(
			//app			命令
			'app'=>'app',
			'block'=>'app/Block',
			'console'=>'app/Console',
			'controller'=>'app/Controller',
            'events'=>'app/Events',
            'listeners'=>'app/Listeners',
			'lang'=>'app/Lang',
			'model'=>'app/Model',
			'service'=>'app/Service',
			'task'=>'app/Task',
			'tests'=>'app/Test',
            'tests_unit'=>'app/Test/Unit',
            'tests_feature'=>'app/Test/Feature',
			//config		配置
			'config'=>'config',
			//data		数据
			'data'=>'data',
			'attach'=>'data/attachment',
			'html'=>'data/html',
			'json'=>'data/json',
			'xml'=>'data/xml',
			//sql			数据库
			'sql'=>'sql',
			//storage		编译/临时
			'storage'=>'storage',
			//|--backup		备份目录
			'backup'=>'storage/backup',
			//|--data		缓存目录
			'cache'=>'storage/data',
			//|--view		模板编译目录
			'compile'=>'storage/view',
			//|--log		运行日志
			'log'=>'storage/log',
			//视图		模版
            'resources'=>'resources',
            'site'=>'resources/site',
			'view'=>'resources/template',
			'view_admin'=>'resources/template/admin',
			'view_guest'=>'resources/template/guest',
			'view_user'=>'resources/template/user',
			'view_public'=>'resources/template/public',
			//vendor		库
			'vendor'=>'vendor',
		);
		foreach($setting as $app=>$folder){
			$folder = __ROOT__.'/'.$folder;
			if(!is_dir($folder)){
				if(!mkdir($folder,0777)){
					die('no permission');
				}
				file_put_contents($folder.'/index.html',date('Ymdhis'));
			}
			define('__'.strtoupper($app).'__',$folder);
		}
	}
	public function __destruct(){
		
	}
}
?>