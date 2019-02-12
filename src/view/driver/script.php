<?php
class view_driver_script
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
			echo json_encode(array('status'=>$data['status'],'msg'=>$data['msg'],'data'=>$data['data'])); 
		}else{
			$redirect_uri = $data['redirect_uri'];
			if($redirect_uri)
			{
				echo json_encode(array('status'=>$data['status'],'msg'=>$data['msg'],'redirect_uri'=>$redirect_uri)); 
			}else{
				echo json_encode(array('status'=>$data['status'],'msg'=>$data['msg'])); 
			}
		}
		exit();
	}
}