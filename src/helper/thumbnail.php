<?php
/** 缩略图*/
/***
 * 小图
 * 大图
 * 源图
 */
class helper_thumbnail
{
	private $width;
	private $height;
	
	private $filename;
	private $target = '';
	
	private $src_w;
	private $src_h;
	
	private $fileinfo = array();
	
	private $type = '';
	
	private $isThumb = 1;
	public function __construct ()
	{
		
		
	}
	
	public function init($filename,$width = 150,$height = 100,$isThumb = 1,$target = '')
	{
		$this->filename = $filename;
		$this->width = $width;
		$this->height = $height;
		$this->isThumb = $isThumb;
		$this->target = $target;
		$this->fileinfo = getimagesize($this->filename);
		$this->get();
		$this->output();
		return $this;
	}
	
	public function getFile(){
		return $this->filename;
	}
	public function get()
	{
		list($src_w, $src_h, $src_type) = $this->fileinfo; 
		$this->src_w = $src_w; 
		$this->src_h = $src_h; 
		switch($src_type) 
		{  
			case 1 :  
				$this->type= 'gif';  
				break;  
			case 2 :  
				$this->type = 'jpeg';  
				break;  
			case 3 :  
				$this->type = 'png';  
				break;  
			case 15 :  
				$this->type = 'wbmp';  
				break;  
		}  
	}
	
	public function output()
	{
		if(!$this->type){
			return false;
		}
		$imagecreatefunc = 'imagecreatefrom' .$this->type; 
		$src_img = $imagecreatefunc($this->filename);  
		if(in_array($this->type,array('gif','png')))
		{
			imagesavealpha($src_img,true);
		}
		$pic_width = $this->src_w;
		$pic_height = $this->src_h;
		$maxwidth = $this->width;
		
		if($maxwidth < 1){
			$maxwidth = $pic_width;
		}
		$maxheight = $this->height;
		if($maxheight < 1){
			$maxheight = $pic_height;
		}
		$ratio = 0.5;
		if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight))
		{
			if($maxwidth && $pic_width>$maxwidth)
			{
				$widthratio = $maxwidth/$pic_width;
				$resizewidth_tag = true;
			}

			if($maxheight && $pic_height>$maxheight)
			{
				$heightratio = $maxheight/$pic_height;
				$resizeheight_tag = true;
			}

			if($resizewidth_tag && $resizeheight_tag)
			{
				if($widthratio<$heightratio)
					$ratio = $widthratio;
				else
					$ratio = $heightratio;
			}

			if($resizewidth_tag && !$resizeheight_tag)
			{
				$ratio = $widthratio;
			}
			if($resizeheight_tag && !$resizewidth_tag)
			{
				$ratio = $heightratio;
			}
		}
		
  
        $this->width = $pic_width * $ratio;
        $this->height = $pic_height * $ratio;
		
			//var_dump($this->width,$this->height);
		$dest_img = imagecreatetruecolor($this->width, $this->height); 
		switch($this->type)
		{
			case 'png':
				imagealphablending($dest_img,false);
				imagesavealpha($dest_img,true);
				break;
			case 'gif':
				//2.上色 
				$color=imagecolorallocate($dest_img,255,255,255); 
				//3.设置透明 
				imagecolortransparent($dest_img,$color); 
				imagefill($dest_img,0,0,$color); 
				break;
		}
		imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $this->width, $this->height, $this->src_w, $this->src_h);  
		
		$imagefunc = 'image'.$this->type;  
		$file_arr = explode('.',$this->filename);
		
		if($this->isThumb){
			$thumbnailFile = $this->filename; 
			if($this->target){
				$thumbnailFile = $this->target.'.thumb.'.$file_arr[count($file_arr)-1];
			}else{
				$thumbnailFile = $this->filename.'.thumb.'.$file_arr[count($file_arr)-1];
			}
		}else{
			$thumbnailFile = $this->target;
		}
		
		$result = $imagefunc($dest_img,$thumbnailFile);  
			
    	imagedestroy($src_img);  
    	imagedestroy($dest_img);  
    	return true;  
	}
}
?>