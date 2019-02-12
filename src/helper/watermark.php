<?php
class helper_watermark
{
	
	//原始图像
	private $local = '';
	//水印图像
	private $watemark = '';
	
	private $imageType = 0;
	
	//透明度
	private $transparent = 50;
	
	//上边距
	private $type;
	//图像中顶部
	const POSITION_MIDDLE_TOP = 1;
	//图像正中心
	const POSITION_MIDDLE_CENTER = 2;
	//图像中底部
	const POSITION_MIDDLE_BOTTOM = 3;
	//图像左上角
	const POSITION_LEFT_TOP = 4;
	//图像左中角
	const POSITION_LEFT_CENTER = 5;
	//图像左下角
	const POSITION_LEFT_BOTTOM = 6;
	//图像右上角
	const POSITION_RIGHT_TOP = 7;
	//图像右中角
	const POSITION_RIGHT_CENTER = 8;
	//图像右下角
	const POSITION_RIGHT_BOTTOM = 9;
	
	private $right = 25;
	private $bottom = 25;
	
	
	private $local_w = 0;
	private $local_h = 0;
	private $target_w = 0;
	private $target_h = 0;
	
	public function init($options = array()){
		
		if(empty($options)){
			
			$options = array(
				'local'=>__ROOT__.'\data\attachment/timg.jpg',
				'type'=>0, //0文字，1图片
				'watemark'=>'tyjk',
				'position'=>10,
				'right'=>10,
				'bottom'=>25,
				'transparent'=>50	//透明度
			);
		}
		
		$this->local = $options['local'];
		$this->watemark = $options['watemark'];
		$this->right = $options['right'];
		$this->bottom = $options['bottom'];
		$this->transparent = $options['transparent'];
		$this->type = $options['type'];
		
		$this->parase();
		
		return $this;
	}
	
	/**
	 *
	 * 水印位置
	 *
	 * 支持远程文件
	 */
	private function getPositionDimision($position){
		$output = array(
			'top'=>0,
			'left'=>0
		);
		switch($position){
			case self::POSITION_LEFT_TOP:
				$output['top'] = 0;
				$output['left'] = 0;
				break;
			case self::POSITION_LEFT_CENTER:				
				$output['top'] = ($this->local_h-$this->target_h)/2;//水印高度与图片高度的中间值
				$output['left'] = 0;
				break;
			case self::POSITION_LEFT_BOTTOM:
				
				$output['top'] = $this->local_h-$this->target_h;//水印高度
				$output['left'] = 0;
				break;
				
			case self::POSITION_MIDDLE_TOP:
				$output['top'] = 0;
				$output['left'] = ($this->local_w-$this->target_w)/2; //水印宽度
				break;
			case self::POSITION_MIDDLE_CENTER:
				$output['top'] = ($this->local_h-$this->target_h)/2;  //水印高度
				$output['left'] = ($this->local_w-$this->target_w)/2; //水印宽度
				break;
			case self::POSITION_MIDDLE_BOTTOM:
				$output['top'] = ($this->local_h-$this->target_h);  //水印高度
				$output['left'] = ($this->local_w-$this->target_w)/2; //水印宽度
				break;
			case self::POSITION_RIGHT_TOP:
				$output['top'] = 0;
				$output['left'] = $this->local_w-$this->target_w; //水印宽度
				break;
			case self::POSITION_RIGHT_CENTER:
				$output['top'] = ($this->local_h-$this->target_h)/2;	 //水印宽度
				$output['left'] = $this->local_w-$this->target_w; //水印宽度
				break;
			case self::POSITION_RIGHT_BOTTOM:
				$output['top'] = $this->local_h-$this->target_h;  //水印宽度
				$output['left'] = $this->local_w-$this->target_w; //水印宽度
				break;
		}
		
		return $output;
	}
	/**
	 *
	 * 判断文件是否存在
	 *
	 * 支持远程文件
	 */
	private function fileExists($file){
		$result = false;
		if(strpos($file,'http://') !== false || strpos($file,'https://') !== false){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $file);
			curl_setopt($ch, CURLOPT_HEADER, 0); //不取得返回头信息
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$result = $httpCode == 404?false:true;
		}else{
			$result = is_file($file);
		}
		
		return $result;
	}
	public function parase(){
		// 加载要加水印的图像
		
		$this->imageType = 2;
		if(!$this->fileExists($this->local)){
			die($this->local.' does not exist.');
		}
		$this->source = imagecreatefromjpeg($this->local);
		
		$this->local_w = imagesx($this->source);
		$this->local_h = imagesy($this->source);

		//文字
		if($this->type < 1){
			//背景色
			$red = imagecolorallocate($this->source, 0xFF, 0x00, 0x00);
			imagefilledrectangle($this->source, 0, 0, 300, 100, $red);
			
			//字体颜色
			$black = imagecolorallocate($this->source, 0x00, 0x00, 0x00);
			
			$font_file = __ROOT__.'\site\font/t1.ttf';
			$this->watemark = mb_convert_encoding($this->watemark,'utf8');
			imagefttext($this->source, 13, 0, 500, 350, $black, $font_file, $this->watemark);
			
		}else{
			if(!$this->fileExists($this->watemark)){
				die($this->watemark.' does not exist.');
			}
			$this->target = imagecreatefromjpeg($this->watemark);
			

			$this->target_w = $sx = imagesx($this->target);
			$this->target_h = $sy = imagesy($this->target);
			
			if($this->target_h > $this->local_h || $this->target_w > $this->local_w){
				die('水印图片尺寸大于原始图片尺寸');
			}

			// 以 50% 的透明度合并水印和图像
			imagecopymerge($this->source, $this->target, $sx - $sx - $this->right, $sy - $sy - $this->bottom, 0, 0, $sx, $sy, $this->transparent);
		}
		
		return $this;
	}
	public function save(){
		switch($this->imageType){
			case 1:  imagegif($this->source,$this->local.'.gif'); break;
			case 2: imagejpeg($this->source,$this->local.'.jpg',100); break;
			case 3:  imagepng($this->source,$this->local.'.png'); break;
		}
		imagedestroy($this->source);
	}
}
?>