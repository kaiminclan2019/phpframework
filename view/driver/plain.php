<?php
class view_driver_plain
{
	
	private $data = array();
	public function set_data($data)
	{
		$this->data = $data;
	}
	public function dispather($data)
	{ 
		if($data['status'] == 200)
		{
			var_dump($data['data']); 
		}else{
			$redirect_uri = $data['redirect_uri'];
			if($redirect_uri)
			{
				var_dump(array('status'=>$data['status'],'msg'=>$data['msg'],'redirect_uri'=>$redirect_uri)); 
			}else{
				var_dump(array('status'=>$data['status'],'msg'=>$data['msg'])); 
			}
		}
		exit();
	}
	
}