<?php
/**
 *
 * 调试信息
 *
 *
 */
class Debug {
	private static $html = '
		<style type="text/css">
			html,body { padding-bottom:200px; }
			ul,ol { list-style:none; }
			.frame-debug-box { border:#ededed 1px solid; background:#fff; position:fixed; bottom:0; left:0; right:0; height:50px;  overflow:auto }
			.frame-debug-box ul { border-bottom:#000 1px solid; }
			.frame-debug-box ul:after { content: "020";   display: block;   height: 0;  clear: both;  visibility: hidden;  }
			.frame-debug-box ul li { float:left; padding:5px 15px; cursor:pointer; }
			.frame-debug-box table { border-collapse:collapse; width:100%; }
			.frame-debug-box table tr th,.frame-debug-box table tr td { border:#ededed 1px solid; padding:10px 25px; }
			.debug-block { display:none; }
			#debug_box table { width:100%; }
		</style>
		<script type="text/javascript">
				function showBlock(id){
					document.getElementById("debug_box").style.height = "500px";
					$(".debug-block").css("display","none");
					document.getElementById("debug_"+id).style.display="block";
				}
				function onDebugClosed(){
					$(this).hide();
					$(".debug-block").hide();
					document.getElementById("debug_box").style.height = "50px";
				}
		window.onload=function(){
			if (typeof jQuery == "undefined") { 
				document.getElementById("debug_box").style.display="none";
			}
		}
		</script>
			<div id="debug_box" class="frame-debug-box">
				<ul>
					<li onClick="showBlock(\'post\')">POST</li>
					<li onClick="showBlock(\'get\')">GET</li>
					<li onClick="showBlock(\'sql\')">SQL</li>
					<li onClick="showBlock(\'template\')">模板</li>
					<li onClick="showBlock(\'file\')">文件</li>
					<li onClick="onDebugClosed()" data-hide="0">关闭</li>
				</ul>
				{__text__}
			</div>
			
		';
		public static function getGet(){
			$html = '<table id="debug_get" class="debug-block">';
			$html .= '
				<thead>
					<th>编号</th>
					<th>标题</th>
					<th>数据</th>
				</thead>
			';
			$fileList = application::$indicent['get'];
			$cnt = 1;
			foreach($fileList as $field=>$data){
				$html .= '<tr><td>'.($cnt).'</td><td>'.$field.'</td><td>'.json_encode($data,1).'</td></tr>';
				$cnt++;
			}
			
			$html .= '</table>';
			return $html;
		}
		public static function getPost(){
			$html = '<table id="debug_post" class="debug-block">';
			$html .= '
				<thead>
					<th>编号</th>
					<th>标题</th>
					<th>数据</th>
				</thead>
			';
			$fileList = application::$indicent['post'];
			$cnt = 1;
			foreach($fileList as $field=>$data){
				$html .= '<tr><td>'.($cnt).'</td><td>'.$field.'</td><td>'.json_encode($data,1).'</td></tr>';
				$cnt++;
			}
			
			$html .= '</table>';
			return $html;
		}
		public static function getTempFile(){
			$html = '<table id="debug_template" class="debug-block">';
			$html .= '
				<thead>
					<th>编号</th>
					<th>标题</th>
				</thead>
			';
			$fileList = application::$indicent['view'];
			foreach($fileList as $key=>$file){
				$html .= '<tr><td>'.($key+1).'</td><td>'.$file.'</td></tr>';
			}
			
			$html .= '</table>';
			return $html;
		}
		public static function getLoadFile(){
			$html = '<table id="debug_file" class="debug-block">';
			$html .= '
				<thead>
					<th>编号</th>
					<th>标题</th>
				</thead>
			';
			$fileList = application::$indicent['file'];
			foreach($fileList as $key=>$file){
				$html .= '<tr><td>'.($key+1).'</td><td>'.$file.'</td></tr>';
			}
			
			$html .= '</table>';
			return $html;
		}
		public static function getSql(){
			$html = '<table id="debug_sql" class="debug-block">';
			$html .= '
				<thead>
					<th>编号</th>
					<th>标题</th>
					<th>时间</th>
				</thead>
			';
			$sqlList = application::$indicent['sql'];
			foreach($sqlList as $key=>$sql){
				$html .= '<tr><td>'.($key+1).'</td><td>'.$sql['sql'].'</td><td>'.$sql['timer'].'</td></tr>';
			}
			
			$html .= '</table>';
			return $html;
		}
		public static function get(){
			if(defined('__APP_DEBUG__') && __APP_DEBUG__ == true){
				return str_replace('{__text__}',self::getPost().self::getSql().self::getGet().self::getTempFile().self::getLoadFile(),self::$html);
			}
		}
}
?>