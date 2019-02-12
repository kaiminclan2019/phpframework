<?php
class view_driver_json
{
	
	private $data = array();
	public function set_data($data)
	{
		$this->data = $data;
	}
	
	public function dispather($data)
	{
		if($data['msg']){
			$result = array(
				'status'=>$data['status'],'msg'=>$data['msg']
			);
		}else{
			if(isset($data['data'])){
				$data['data']['status'] = 200;
				$data['data']['msg'] = '';
				
				$result = $data['data'];
			}else{
				unset($data['breadcrumb']);
				unset($data['seodescription']);
				unset($data['seokeyword']);
				unset($data['seotitle']);
				
				$status = $data['status'];
				unset($data['status']);
				$result = array(
					'status'=>$status,'msg'=>isset($data['msg'])?$data['msg']:'','data'=>$data
				);
			}
		}
		if(array_key_exists('redirect_uri',$data))
		{
			$result['redirect_uri'] = $data['redirect_uri'];
		}
		header('Content-type:application/json');
		header('Content-type:text/html;charset=utf-8');
		echo json_encode($result,JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	/**
	 * 弃用，所有写数据接口。统一只返回状态以及消息
	 * @by jqm 20170520 18:01
	 */
	public static function display($data)
	{
		if($data['status'] == 200)
		{
			echo json_encode(array('status'=>$data['status'],'msg'=>$data['msg'],'data'=>$data['data']),JSON_UNESCAPED_UNICODE); 
		}else{
			$redirect_uri = $data['redirect_uri'];
			if($redirect_uri)
			{
				echo json_encode(array('status'=>$data['status'],'msg'=>$data['msg'],'redirect_uri'=>$redirect_uri),JSON_UNESCAPED_UNICODE); 
			}else{
				echo json_encode(array('status'=>$data['status'],'msg'=>$data['msg']),JSON_UNESCAPED_UNICODE); 
			}
		}
		exit();
	}
}