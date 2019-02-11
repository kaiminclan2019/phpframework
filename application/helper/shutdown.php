<?php
class application_helper_shutdown {
	
	public function __construct(){
		
	}
	
	public static function callShutDown(){
		 if($e = error_get_last()) {
			   
			//$e['type']对应php_error常量  
			$message = '';
			$included_files = get_included_files();  
			foreach ($included_files as $filename) {
    			$message .= "$filename\n\n";
			}
			$message .= "出错信息:\t".$e['message']."\n\n";  
			$message .= "出错文件:\t".$e['file']."\n\n";  
			$message .= "出错行数:\t".$e['line']."\n\n";  
			$message .= "\t\t请工程师检查出现程序".$e['file']."出现错误的原因\n";  
			$message .= "\t\t希望能您早点解决故障出现的原因<br/>";  
			if(defined('__APP_DEBUG__') && __APP_DEBUG__ == true){
				if($e['line']){
					echo $message.'<br />'; 
				}else{
					
				}
			}else{
				$folder = '';
				if(defined('__LOG__') && __LOG__ != ''){
					$folder = __LOG__;
				}else{
					$folder = './';
				}
				file_put_contents($folder.'/error_500_'.date('Ymd').'.txt',$message,FILE_APPEND);
			}
			//sendemail to  
		}  
	}
	
	public function registerShutdownEvent() {
        $callback = func_get_args();
        
        if (empty($callback)) {
            trigger_error('No callback passed to '.__FUNCTION__.' method', E_USER_ERROR);
            return false;
        }
        if (!is_callable($callback[0])) {
            trigger_error('Invalid callback passed to the '.__FUNCTION__.' method', E_USER_ERROR);
            return false;
        }
        $this->callbacks[] = $callback;
        return true;
    }
    public function callRegisteredShutdown() {
        foreach ($this->callbacks as $arguments) {
            $callback = array_shift($arguments);
            call_user_func_array($callback, $arguments);
        }
    }
    public function dynamicTest() {
        echo '_REQUEST array is '.count($_REQUEST).' elements long.<br />';
    }
    public static function staticTest() {
        echo '_SERVER array is '.count($_SERVER).' elements long.<br />';
    }
}
?>