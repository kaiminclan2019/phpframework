<?php
/**
 * 分页类
 * 
 */
class helper_page
{
	
	/**
	 * @var int 翻页总页数
	 */
	private $total;
	
	/**
	 * @var string 翻页链接
	 */
	private $url;
	
	/**
	 * @var int 总条数
	 */
	private $number;
	
	/**
	 * @var int 每页显示条数
	 */
	private $perpage;
	
	/**
	 * @var int 当前页数
	 */
	private $curpage;
	
	/**
	 *@var int 显示页数
	 */
	private $showpage;
	
	private $param;
	
	/**
     * init  初始化（获取分页所需参数)
     * @param int $number   总条数
     * @param int $curpage  当前页数
     * @param int $perpage  每页条数
     * @param int $total	总页数
	 * @param int $showpage 显示页数
     * @param string $url   分页连接
     */
	public function init($number,$perpage,$curpage,$url = '',$showpage=10,$param = array())
	{
		if(!$url)
		{
			$url = $_SERVER['REQUEST_URI'];
		}
		$this->url = $url;
		$this->number = intval($number);
		$this->perpage = intval($perpage);
		$this->showpage = intval($showpage);
		$this->param = $param;
		
		if($number > $perpage)
		{
			$total = ceil($number/$perpage);
			$curpage = $curpage <= $total ? $curpage : $total;
			$this->total = intval($total);
		}
		$this->curpage = $curpage;
		return $this;
	}
	
	/**
	 * @param string $url  链接(不设置则取当前链)
	 * @param string $page 翻页页码
	 * @return string 翻页链接
	 */
	private function seturl($page=1)
	{
		$url = $this->url;
		$page = max(1,$page);
		if($page == $this->curpage)
		{
			return 'javascript:void(0)';
		}
		
		
		
		if(!$url)
		{
			$href = htmlentities($_SERVER['PHP_SELF']);
			$_GET['page'] = $page;
			$url = $href.'?'.http_build_query($_GET);
		}else{
			if(strpos($url,'{page}') !== false){
				$url = trim(str_replace('{page}',$page,$url));
			}else{
				if(strpos($url,'.') !== false)
				{
					$symbol = strpos($url, '?') !== FALSE ? '&amp;' : '?';
					$url = $url.$symbol.'page='.$page;
				}else{
					$url = $url.'/Page/'.$page;
				}
			}
		}
		return $url;
	}
	
	private function paramextract($key='')
	{
		if(array_key_exists($key,$this->param))
		{
			return $this->param[$key];
		}
	}
	
	
	/**
     * @first() 翻页列表首页
	 * return Array
     */
	public  function first()
	{
		$paginal = array();
		if($this->curpage > 1 && $this->total >= $this->curpage)
		{
			$paginal = array('page'=>1,'url'=>self::seturl(1),'paginal'=>'first');
		}
		
		return $paginal;
	}
	
	/**
     * @last() 翻页列表尾页
	 * return Array
     */
	public  function last()
	{
		$paginal = array();
		if($this->curpage < $this->total)
		{
			$paginal = array('page'=>$this->total,'url'=>self::seturl($this->total),'paginal'=>'last');
		}
		return $paginal;
	}
	
	/**
     * @upper() 翻页列表上一页
	 * return Array
     */
	public  function upper()
	{
		$paginal = array();
		if($this->curpage > 1 && $this->total >= $this->curpage)
		{
			$paginal = array('page'=>$this->curpage - 1,'url'=>self::seturl($this->curpage - 1),'paginal'=>'upper');
		}
		return $paginal;
	}
	
	/**
     * @nextper() 翻页列表下一页
	 * return Array;
     */
	public  function nextper()
	{
		$paginal = array();
		if($this->curpage < $this->total)
		{
			$paginal = array('page'=>$this->curpage + 1,'url'=>self::seturl($this->curpage + 1),'paginal'=>'nextper');
		}
		return $paginal;
	}
	
	
	/**
     * @listper() 翻页列表显示页码
	 * 页码由参数[ $showpage ]定义显示几个 默认10
	 * 以页码为key,url为值
	 * array(
	 * [1] => Array([url] => page.php?page=1)
     * [2] => Array([url] => page.php?page=2)
     * [3] => Array([url] => page.php?page=3)
	 * )
	 * return Array;
     */
	private  function listper()
	{
		if($this->showpage == 0)
		{
			return ;
		}
		if($this->showpage > $this->total)
		{
			$this->showpage = $this->total;
		}
		
		$odd = $this->showpage%2 ? 0 : 1;
		$floor = floor($this->showpage/2);
		$upper = $floor;
		$nextper = $floor;
		if($nextper >= $this->curpage)
		{
			$nextper = $this->curpage - 1;
		}else{
			$nextper = $floor;
		}
		
		if(($this->total - $this->curpage) <= $upper)
		{
			$upper = $this->total - $this->curpage;	
		}
		
		if($upper < $floor)
		{
			$nextper += ($floor - $upper);
		}
		
		if($nextper < $floor)
		{
			$upper += ($floor - $nextper);
		}
		
		if($nextper >= $floor)
		{
			$nextper -= $odd;
		}else{
			if($upper >= $floor)
			{
				$upper -= $odd;
			}
		}
		
		$paginal = array();
		for($i = 1;$i <= $this->total;$i++)
		{
			if($upper && $this->curpage < $i)
			{
				$paginal[$i] = array('url'=>self::seturl($i));
				$upper--;
			}
			if($this->curpage == $i){
				$paginal[$i] = array('url'=>self::seturl($i));
			}
			
			if($nextper && $this->curpage > $i)
			{
				$num = $this->curpage - $nextper ? $this->curpage - $nextper: 1;
				$paginal[$num] = array('url'=>self::seturl($num));
				$nextper--;
			}
		}
		
		return $paginal;
	}
	
	
	/**
     * @multi() 返回翻页a标签列表
	 * return string
     */
	public function multi()
	{
		$multi = '';
		$paginal = self::listper();
		foreach($paginal as $page=>$value)
		{
			if($page == $this->curpage)
			{
				$multi .= '<li class="active"><a '.self::paramextract('curpage').' href="'.$value['url'].'">'.$page.'</a></li>';
			}else{
				$multi .= '<li><a href="'.$value['url'].'">'.$page.'</a></li>';
			}
		}
		return $multi;
	}
	
	/**
	 * @_multi() 返回翻页数组
	 * return Array;
	 */
	public function _multi()
	{
		$paginal = self::listper();
		return $paginal;
	}
	
	public function __multi()
	{
		
		$first = 1;
		$last = $this->total;
		$multi = array();
		$upper = self::upper();
		if($upper)
		{
			$multi['upper'] = $upper['url'];
		}
		$nextper = self::nextper();
		
		if($nextper)
		{
			$multi['nextper'] = $nextper['url'];
		}
		
		$paginal = self::listper();
		if(!array_key_exists($first,$paginal))
		{
			$firstlist = self::first();
			$multi['first'] = $firstlist['url'];
		}
		
		if(!array_key_exists($last,$paginal))
		{
			$lastlist = self::last();
			$multi['last'] = $lastlist['url'];
		}
		$multi['perlist'] = $paginal;
		return $multi;
	}
	public function multi_tag_list()
	{
		$multi = self::__multi();
		$html = '';
		if($multi['upper'])
		{
			$html .= '<a href="'.$multi['upper'].'" class="page">上一页</a>';
		}
		if($multi['first'])
		{
			$html .= '<a href="'.$multi['first'].'" class="more-page">1...</a>';
		}
		if($multi['perlist'])
		{
			foreach($multi['perlist'] as $page=>$value)
			{
				$html .= '<a href="'.$value['url'].'" '.($this->curpage == $page ? 'class="current"':'').'>'.$page.'</a>';
			}
		}
		if($multi['last'])
		{
			$html .= '<a href="'.$multi['last'].'" class="more-page">...'.$this->total.'</a>';
		}
		if($multi['nextper'])
		{
			$html .= '<a href="'.$multi['nextper'].'" class="page">下一页</a>';
		}
		return $html;
	}
	
}
?>