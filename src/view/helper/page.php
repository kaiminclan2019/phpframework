<?php
class view_helper_page
{
	private $num = 1;
	private $page = 10;
	private $curpage = 1;
	private $realpage = 1;
	private $perpage = 10;
	private $maxpage = 1000;
	
	private $url = '';
	public function init($total,$perpage,$curpage,$url = '',$page = 10,$maxpage = 1000)
	{
		if(is_array($total)){
			$paramField = $total;
			$keyword = array();
			foreach($paramField as $field=>$value){
				if(!in_array($field,array('total','perpage','curpage','url','page','maxpage'))){
					continue;
				}
				$keyword[$field] = $value;
				unset($paramField[$field]);
			}
			extract($keyword);
			if(!empty($paramField)){
				//其他参数处理
			}
			
		}
		
		$totalpage = ceil($total/$perpage);
		
		$this->curpage = intval($curpage);
		$this->curpage = $this->curpage < 1?1:$this->curpage;
		
		$requestUri = $_SERVER['REQUEST_URI'];
		if($url){
			if(strpos($requestUri,'?') !== false){
				list(,$query) = explode('?',$requestUri);
				$url = $url.'?'.$query;
			}
		}
		$this->url = strlen($url) < 1?$requestUri:$url;
		
		$num = intval($total);
		$num = $num < 1?1:$num;
		$this->num = $num;
		
		$perpage = intval($perpage);
		$this->perpage = $perpage < 1?1:$perpage;
		
		$this->realpage = ceil($this->num/$this->perpage);
		$this->maxpage = $maxpage;
		
		$this->page = $page;
		//总条数
		$this->output['count'] = $total;
		//总页数
		$this->output['realPage'] = $this->realpage;
		//第一页
		$this->output['first'] = array('value'=>1,'url'=>$this->format_url(1));
		//上一页
		$prev = $this->curpage > 1?$this->curpage-1:0;
		$this->output['prev'] = array('value'=>$prev,'url'=>$this->format_url($prev));
		//页面列表
		$this->output['list'] = $this->multi();
		//下一页
		$next = $this->curpage < $totalpage?$this->curpage+1:0;
		$this->output['next'] = array('value'=>$next,'url'=>$this->format_url($next));
		//最后一页
		$this->output['last'] = array('value'=>$totalpage,'url'=>$this->format_url($totalpage));
	}
	
	private function format_url($page)
	{
		if(strpos($this->url,'{page}') !== false)
		{
			$url = str_replace('{page}',$page,$this->url);
		}
		elseif(strpos($this->url,'.html') !== false)
		{
			$url = $this->url;
			$query = '';
			if(strpos($this->url,'?') !== false){
				list($url,$query) = explode('?',$this->url);
			}
			if(strpos($url,'_') !== false){
				$urlList = explode('_',$url);				
				$url = implode('_',array_slice($urlList,0,count($urlList)-1));
			}else{
				list($url) =  explode('.',$url);
			}
			$url = $url.'_'.$page.'.html'.(!empty($query)?'?'.$query:'');
		}
		elseif(strpos($this->url,'?') !== false)
		{
			$url = $this->url.'&start='.$page ;
		}
		elseif(strpos($this->url,'.php') !== false){
			$url = $this->url.'?page='.$page;
		}
		elseif(strpos($this->url,'/start/') !== false){
			list($url) = explode('/start/',$this->url);
			if($page > 1){
				$url = $url.'/start/'.$page;
			}
		}else{
			$url = $this->url.'/start/'.$page;
		}
		return $url;
	}
	public function multi() {
		
		$multipage = array();
		$num = $this->num;
		
		$perpage = $this->perpage; 
		$curpage = $this->curpage; 
		$maxpages = $this->maxpage; 
		$page = $this->page;
		$realpages = 1;
		$page -= strlen($curpage) - 1;
		if($page <= 0) {
			$page = 1;
		}
		if($num > $perpage) {

			$offset = floor($page * 0.5);

			$realpages = $this->realpage;
			$curpage = $curpage > $realpages ? $realpages : $curpage;
			$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

			if($page > $pages) {
				$from = 1;
				$to = $pages;
			} else {
				$from = $curpage - $offset;
				$to = $from + $page - 1;
				if($from < 1) {
					$to = $curpage + 1 - $from;
					$from = 1;
					if($to - $from < $page) {
						$to = $page;
					}
				} elseif($to > $pages) {
					$from = $pages - $page + 1;
					$to = $pages;
				}
			}
			
			for($i = $from; $i <= $to; $i++) {
				$multipage[] = array('value'=>$i,'url'=>$this->format_url($i),'hovered'=>$this->curpage == $i?1:0);
			}
                
		}
		
		return $multipage;
	}
	
	public function get()
	{
		return $this->output;
	}
}