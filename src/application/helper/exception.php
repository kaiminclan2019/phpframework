<?php
/***
 * 异常处理
 */
class application_helper_exception {
	public function __construct(){
		
	}
	
	public static function debug_backtrace() {
		
		$data = array();
		$debug_backtrace = debug_backtrace();
		krsort($debug_backtrace);
		//echo '<pre>';		var_dump($debug_backtrace); die();
		foreach ($debug_backtrace as $k => $error) {
			$file = str_replace(__ROOT__, '', $error['file']);
			
			$func = isset($error['class']) ? $error['class'] : '';
			$func .= isset($error['type']) ? $error['type'] : '';
			$func .= isset($error['function']) ? $error['function'] : '';
			
			$error[line] = sprintf('%04d', $error['line']);

			$show = "<li>[Line: $error[line]]".$file."($func)</li>";
			$log .= !empty($log) ? ' -> ' : '';$file.':'.$error['line'];
			$log .= $file.':'.$error['line'];
			$data[] = array('line'=>sprintf('%04d', $error['line']),'file'=>$file,'func'=>$func);
			$otherList = (array)$error['args'][0];
			if($otherList){
				foreach($otherList['Exceptiontrace'] as $cnt=>$track){
					$data[] = array('line'=>sprintf('%04d', $track['line']),'file'=>$track['file'],'func'=>$track['function']);
				}
			}
		}
		return $data;
	}
	
	public static function callException($exception){
		$code = $exception->getCode();
		$msg = $exception->getMessage();
		
		//如果是字母下划线结合,提取语言包
		
		if(preg_match('^[a-zA-Z\_]*$',$msg)){
			
		}
		
		$line = $exception->getLine();
		$file = $exception->getFile();
		$file = str_replace(__ROOT__,'',str_replace('\\','/',$file));
		
		switch($code){
			case 404:
				ob_start();
				ob_end_clean();
				echo '<pre>';
				header('HTTP/1.1 404 Not Found'); 
				echo '<html>';
				echo '<head>';
				echo '<title>404 Not Found</title>';
				echo '</head>';
				echo '<body>';
				echo '<h1>404 Not Found</h1>';
				echo '<hr />';
				echo '<p>'.$msg.'</p>';
				echo '</body>';
				echo '</html>';
			break;
			case 302:
				ob_start();
				ob_end_clean();
				header('location:'.$msg);
			break;
			default:
				$len = strlen($code);
				if(!defined('__ACCEPT__')){
					
				}else{
					switch(__ACCEPT__){
							case 'text/html':
							self::template($code,$msg,$exception->getTrace());
						break;
							case 'application/xml':
							echo '<?xml version="1.0" encoding="utf-8"?>';
							echo '<error>'.$code.'</error>';
							echo '<msg>'.$msg.'</msg>';
						break;
							case 'application/json':
							case 'text/plain':
							default:
							self::send(json_encode(array('status'=>$code,'msg'=>$msg),JSON_UNESCAPED_UNICODE));
						break;
					}
				}
			break;
		}
		exit();
	}
	
	private static function send($data){
		if(__REQUEST_METHOD__ == 'CONSOLE'){
			switch($_SERVER['OS']){
				case 'WINNT':
				case 'Windows_NT':
					echo mb_convert_encoding($data,'gbk','utf8')."\r\n";
				break;
				default:
					echo $data;
					break;
			}
		}else{
			echo $data;
		}
		exit();
	}
	
	public static function template($status,$errorMsg,$trackList = array()){
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title>系统错误</title>
				</head>
				
				<body>
				<div id="container">
					<h1>Error</h1>
					<div class="info">'.$errorMsg;
					
						if($status == 1013){
							$html .= '<span id="timer">5</span>秒后返回';
							$html .= '<script type="text/javascript">
								var cnt = 6;
								window.onload=function(){ timerLock();}; 
								function timerLock(){ 
									cnt--;
									document.getElementById("timer").innerHTML=cnt;
									if(cnt < 1){
										window.location.href="'.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'/').'";
									}
									setTimeout(timerLock,1000);
								}
							</script>';
						}
					$html .= '</div>';
		if($status != 1013 && $trackList){
			$html .= '
			<div class="info">
				<p>
					<strong>PHP Debug</strong>
				</p>
				<table cellpadding="5" cellspacing="1" width="100%" class="table">
					<tr class="bg2">
						<td>No.</td>
						<td>File</td>
						<td>Line</td>
						<td>Code</td>
					</tr>';
					foreach($trackList as $key=>$track){
						if(!$track['file']) continue;
						///if(strpos($track['file'],'\\vendor\\') !== false) continue;
						//if(strpos($track['file'],'\\storage\\') !== false) continue;
						if($_SERVER['REMOTE_ADDR'] != '127.0.0.1'){
							$track['file'] = str_replace(__ROOT__,'',$track['file']);
						}
						$html .= '<tr class="bg1">
							<td>'.($key+1).'</td>
							<td>'.$track['file'].'</td>
							<td>'.$track['line'].'</td>
							<td>'.$track['function'].'</td>
						</tr>';
					}
					$html .= '
				</table>
			</div>';
		}
		$html .= '
			<div class="help"></div>
		</div>
		<style type="text/css">
		<!--
		body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
		#container { width: 90%; padding:5%; }
		#message   { width: 1024px; color: black; }
		
		.red  {color: red;}
		a:link     { font: 9pt/11pt verdana, arial, sans-serif; color: red; }
		a:visited  { font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
		h1 { color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;}
		.bg1{ background-color: #FFFFCC; border-bottom:#ccc 1px solid; padding:5px; }
		.bg2{ background-color: #EEEEEE;}
		.table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
		.info {
			background: none repeat scroll 0 0 #F3F3F3;
			border: 0px solid #aaaaaa;
			border-radius: 10px 10px 10px 10px;
			color: #000000;
			font-size: 11pt;
			line-height: 160%;
			margin-bottom: 1em;
			padding: 1em;
		}
		
		.help {
			background: #F3F3F3;
			border-radius: 10px 10px 10px 10px;
			font: 12px verdana, arial, sans-serif;
			text-align: center;
			line-height: 160%;
			padding: 1em;
		}
		
		.sql {
			background: none repeat scroll 0 0 #FFFFCC;
			border: 1px solid #aaaaaa;
			color: #000000;
			font: arial, sans-serif;
			font-size: 9pt;
			line-height: 160%;
			margin-top: 1em;
			padding: 4px;
		}
		-->
		</style>
		</body>
		</html>
		';
		if(defined('__APP_DEBUG__') && __APP_DEBUG__ == false){
			$html = '
<!DOCTYPE html>
<html>
<head>
<title>出错了!</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
html,body
{
	width:100%;
	height:100%;
	margin:0;
	padding:0;
}
.text
{
	line-height:28px;
	font-family: microsoft yahei,microsoft jhenghei,verdana,tahoma;
	font-size:14px;
	color:#62261c;
	float:right;
	padding-top:100px;
}
.text a,.text a:link
{
	color:#66261c;
	text-decoration:underline;
	font-weight:bold;
}
.text a:hover
{
	text-decoration:none;
}
img {
	border: none;
	margin: 0;
	padding:0;
	display: block;
}
</style>
</head>
<body>
	<div style="width:100%; height:100%;">
		<div style="width:200px; height:240px;  margin:0 auto;">
		        <div class="text">莫有办法,出错了<br />你可以回到 <a href="/">网站首页</a><br />或者刷新 <a href="javascript:window.location.reload();">试试</a></div>
		   
		</div>
	</div>
</body>
</html>';
		echo $html;
		}else{
			echo $html;
		}
	}
	
}
?>