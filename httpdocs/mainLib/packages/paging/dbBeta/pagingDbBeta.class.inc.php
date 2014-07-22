<?php
/*
Sample :
	$sqlQuery='SELECT * FROM TABLE';
	$paging=new CPaging(null,40,$sqlQuery); //first parameter doesn't require except you prefer to give number of result rows by yourself. second parameter is limit.
	$paging->link='http://test.com?test=1'; //if you have any other
	$paging->sorting_enabled=true;//if you want to have sorting system
	$sqlQuery=$paging->get_limited_query();//gives you limited sql query of the sql query that you set above ->result sample: SELECT * FROM TABLE LIMIT 1,5
	$paging->show_as_hyper_links();
*/

//adding sort system
class cmfcRichPagingBeta
{
	var $qsvn_from='from';
	var $qsvn_to='to';
	var $qsvn_next='next';
	var $qsvn_prev='prev';
	var $qsvn_sort_by='sort_by';
	var $qsvn_sort_type='sort_type';
	var $qsvn_id='id';
	
	var $coln_id='id';

	var $next_word='Next';
	var $prev_word='Previous';
	var $total;
	var $from=0;
	var $id;
	var $to;
	var $limit=40;
	var $link;
	var $begin_value=1;
	var $sqlQuery;
	var $auto_recognize=true;

	var $prev_from;
	var $prev_to;
	var $next_from;
	var $next_to;

	var $has_next;
	var $has_prev;

	var $first_page_number=1;
	var $total_pages;
	var $page_number;

	var $sort_by='id';
	var $sort_type=true;
	var $sorting_enabled=false;

	function has_next() {
		return $this->has_next;
	}

	function has_prev() {
		return $this->has_prev;
	}

	function cmfcRichPagingBeta($options)
	{
		if (!is_null($options['sqlQuery'])) {$options['total']=cmfcMySql::getTotalRowsNumberViaSqlQuery($options['sqlQuery']);}
		$this->total=$options['total'];
		$this->limit=$options['limit'];
		$this->sqlQuery=$options['sqlQuery'];
		
		if (isset($_GET[$this->qsvn_id]) and $this->limit==1) {
			$_GET[$this->qsvn_from]=cmfcMySql::getRowNumber($this->sqlQuery,$this->coln_id,$_GET[$this->qsvn_id])+1;
			$_GET[$this->qsvn_to]=$_GET[$this->qsvn_from]+1;
		}
        
		$this->set_variables();
		if ($_POST[$this->qsvn_prev]==$this->prev_word) {
			$_POST[$this->qsvn_from]=$this->prev_from;
			$_POST[$this->qsvn_to]=$this->prev_to;
		}
		if ($_POST[$this->qsvn_next]==$this->next_word) {
			$_POST[$this->qsvn_from]=$this->next_from;
			$_POST[$this->qsvn_to]=$this->next_to;
		}
		$this->set_variables();
	}


	function get_limited_query($sqlQuery=null)
	{
		$this->set_variables();
		if (is_null($sqlQuery)) {$sqlQuery=$this->sqlQuery;}
        
		$from=$this->from-$this->begin_value;
		if ($this->sorting_enabled==true) {$sqlQuery=$this->get_sorted_query($sqlQuery);}
		
		return cmfcMySql::getLimitedQuery($sqlQuery,$from,abs($this->to-$from));
	}

	function get_sorted_query($sqlQuery=null)
	{
		if ($this->auto_recognize) {
			if (!empty($_GET[$this->qsvn_sort_type]))
				$this->sort_type=$_GET[$this->qsvn_sort_type];
			elseif (empty($this->sort_type))
				$this->sort_type='ASC';
			if (!empty($_GET[$this->qsvn_sort_by]))
				$this->sort_by=$_GET[$this->qsvn_sort_by];
		}

		if (is_null($sqlQuery)) {$sqlQuery=$this->sqlQuery;}
		
		if (!empty($this->sort_by))
			return cmfcMySql::getSortedQuery($sqlQuery,$this->sort_by,$this->sort_type);
		else
			return $sqlQuery;
	}

	function get_next_limited_query() {
		$sqlQuery=$this->sqlQuery;
		$from=$this->next_from-$this->begin_value;
		if ($this->sorting_enabled==true) {$sqlQuery=$this->get_sorted_query($sqlQuery);}
		return get_limited_query($sqlQuery,$from,abs($this->next_to-$from));
	}

	function get_prev_limited_query() {
		$sqlQuery=$this->sqlQuery;
		$from=$this->prev_from-$this->begin_value;
		if ($this->sorting_enabled==true) {$sqlQuery=$this->get_sorted_query($sqlQuery);}
		return get_limited_query($sqlQuery,$from,abs($this->prev_to-$from));
	}

	function get_custom_limited_query($from,$to) {
		$sqlQuery=$this->sqlQuery;
		$from=$from-$this->begin_value;
		if ($this->sorting_enabled==true) {$sqlQuery=$this->get_sorted_query($sqlQuery);}
		return get_limited_query($sqlQuery,$from,abs($to-$from));
	}

	//use it when you want to change sort by or sort type parameter by a link
	//$sort_type you can pass 'ASC' & 'DESC' or false & true
	function get_sorting_query_string($sort_by=null,$sort_type=null)
	{
		if (strtoupper($sort_type)=='ASC'){$sort_type='ASC';}
		if (strtoupper($sort_type)=='DESC'){$sort_type='DESC';}
		if (is_null($sort_by)) {$sort_by=$this->sort_by;}
		if (is_null($sort_type)) {$sort_type=$this->sort_type;}
		if ($this->sorting_enabled==true)
			$result="&$this->qsvn_sort_by=$sort_by&$this->qsvn_sort_type=$sort_type";
		//$result.="&$this->qsvn_from=$this->from&$this->qsvn_to=$this->to";
		return $result;
	}

	function get_query_string()
	{
		$sort_by=null;
		$sort_type=null;
		if (!is_null($sort_by)) {$this->sort_by=$sort_by;}
		if (!is_null($sort_type)) {$this->sort_type=$sort_type;}
		if ($sort_type='ASC'){$sort_type=false;}
		if ($sort_type='DESC'){$sort_type=true;}
		if ($this->sorting_enabled==true)
			$result="&$this->qsvn_sort_by=$this->sort_by&$this->qsvn_sort_type=$this->sort_type";
		$result.="&$this->qsvn_from=$this->from&$this->qsvn_to=$this->to";
		return $result;
	}


	function set_variables()
	{
		$this->total_pages=$this->get_number_of_pages();

		if ($this->auto_recognize) {
			$this->from=$_GET[$this->qsvn_from];
			$this->to=$_GET[$this->qsvn_to];

			if (isset($_POST[$this->qsvn_from])) {$this->from=$_POST[$this->qsvn_from];}
			if (isset($_POST[$this->qsvn_to])) {$this->to=$_POST[$this->qsvn_to];}
		}

		if ($this->from<$this->begin_value) {$this->from=$this->begin_value;}
		if ($this->to<$this->begin_value) {$this->to=$this->limit;}
		if ($this->to>$this->total) {$this->to=$this->total+$this->begin_value;}

		$from=$this->from;
		$to=$this->to;
		$total=$this->total;//+$this->begin_value;


		$this->set_next_prev_from_to();

		if (isset($_POST[$this->qsvn_next])) {
			$this->from=$this->next_from;
			$this->to=$this->next_to;
		} elseif (isset($_POST[$this->qsvn_prev])) {
			$this->from=$this->prev_from;
			$this->to=$this->prev_to;
		}

		$this->set_next_prev_from_to();
	}

	//fill next_from,next_to,prev_from,prev_to
	function set_next_prev_from_to() {
		$total=$this->total;
		$this->has_prev=false;
		$this->has_next=false;
		if ($total > $this->limit)
		{
//			$total=$this->total+$this->begin_value;

			if ($this->from > $this->begin_value)
			{
				$from=$this->from-$this->limit;
				if ($from<$this->begin_value) {$from=$this->begin_value;}
				$to=$from+$this->limit-1;
				if ($to>$total) {$to=$total;}
				$this->prev_from=$from;
				$this->prev_to=$to;
				$this->has_prev=true;
			}

			if ($this->to < $total)
			{
				$to=$this->to+$this->limit;
				if ($to>$total) {$to=$total;}
				$from=$to-$this->limit+1;
				if ($from<$this->begin_value) {$from=$this->begin_value;}
				if ($from<=$this->to) {$from=$this->from+$this->limit;}
				$this->next_from=$from;
				$this->next_to=$to;
				$this->has_next=true;
			}
		}
	}


	function show_as_hyper_links()
	{
		$total=$this->total;//+$this->begin_value;

		if ($total > $this->limit)
		{
			$prev_hyper_link='';
			$next_hyper_link='';

			if ($this->from > $this->begin_value)
			{
	    		$prev_hyper_link = '<a href="'.$this->get_url($this->prev_from,$this->prev_to).$this->get_sorting_query_string().'">&lt;'.$this->prev_word.'</a>';
			}

			if ($this->to < $total)
			{
	    		$next_hyper_link = '<a href="'.$this->get_url($this->next_from,$this->next_to).$this->get_sorting_query_string().'">'.$this->next_word.'&gt;</a>';
			}
			$result=$prev_hyper_link.'&nbsp;('.$this->from.'-'.$this->to.')/('.$total.')&nbsp;'.$next_hyper_link;
		}
		return $result;
	}

	// its result is array(0=>'from',1=>'to');
	function get_page_range($page_number) {
		if ($page_number<$this->total_pages+$this->first_page_number) {
			$result[0]=$page_number*$this->limit-$this->limit+$this->begin_value;
			$result[1]=$result[0]+$this->limit-1;
		} else {
			$result=array($this->total-$this->limit+1,$this->total);
		}
		return $result;
	}

	// its result is array('from','to');
	function get_page_number_by_from($from=null) {
		if (is_null($from)) {$from=$this->from;}
		if ($this->limit<2) $from=$from-1;
		$page_number=floor(($from+$this->limit)/$this->limit);
		if ($page_number<$this->first_page_number) {$page_number=1;}
		return $page_number;
	}

	function get_number_of_pages() {
		return ceil(($this->total/$this->limit))+1;
	}

	/* like : 1,2,3,4,5, */
	function show_as_list_of_pages() {
		$total_pages=$this->get_number_of_pages();
		for ($pn=1;$pn<$total_pages;$pn++) {
			list($from,$to)=$this->get_page_range($pn);
			echo "<a href='$this->link&$this->qsvn_from=$from&$this->qsvn_to=$to'>$pn</a> , ";
		}
	}

	/* sample result :
		[jumpt_to_first_page]
			[from]= //from
			[to]= //to
			[link]
		[jumpt_to_prev_pages]
			[from]= //from
			[to]= //to
			[link]
		[jumpt_to_prev_page]
			[from]= //from
			[to]= //to
			[link]
		[has_prev_page]=true // or false
		[pages]
			[0]
				[page_number]=1
				[link]=
				[selected]=true
			[1]
				[page_number]=1
				[link]=
				[selected]=true
		[has_next_page]=true // or false
		[jumpt_to_next_page]
			[from]= //from
			[to]= //to
			[link]
		[jumpt_to_next_pages]
			[from]= //from
			[to]= //to
			[link]
		[jumpt_to_last_page]
			[from]= //from
			[to]= //to
			[link]
	*/
	function get_limited_list_of_pages($number_of_page_to_show=10) {
		$sorting_qs=$this->get_sorting_query_string();

		if ($this->total_pages < $number_of_page_to_show) {$number_of_page_to_show=$this->total_pages;}
		$result='';
		$result['pages']=array();

		$page_number=$this->get_page_number_by_from();

		if ($page_number>$number_of_page_to_show+$this->first_page_number) {
			/* |<< jump to the first page */
			if ($page_number>$this->first_page_number) {
				list($from,$to)=$this->get_page_range($this->first_page_number);
				$result['jumpt_to_first_page']=array('link'=>"$this->link&$sorting_qs&$this->qsvn_from=$from&$this->qsvn_to=$to",
													'from'=>$from,
													'to'=>$to);
			}

			/* << jump to prev pages */
			if ($page_number-$number_of_page_to_show>=$this->first_page_number)  {
				list($from,$to)=$this->get_page_range($page_number-$number_of_page_to_show);
				$result['jumpt_to_prev_pages']=array('link'=>"$this->link&$sorting_qs&$this->qsvn_from=$from&$this->qsvn_to=$to",
													'from'=>$from,
													'to'=>$to);
			}


			/* show that there is more next page */
			if ($page_number>$this->first_page_number) {
				$result['has_prev_page']=true;
			} else $result['has_prev_page']=false;
		}

		/* < jump to prev page */
		if ($page_number-1>=$this->first_page_number)  {
			list($from,$to)=$this->get_page_range($page_number-1);
			$result['jumpt_to_prev_page']=array('link'=>"$this->link&$sorting_qs&$this->qsvn_from=$from&$this->qsvn_to=$to",
												'from'=>$from,
												'to'=>$to);
		}

		if ($page_number<$this->first_page_number+$number_of_page_to_show) {$begin_page=$this->first_page_number;} else {$begin_page=$page_number-$number_of_page_to_show;}
		for ($pn=$begin_page;$pn<$number_of_page_to_show+$page_number;$pn++)
		if ($pn<$this->total_pages)
		{
				list($from,$to)=$this->get_page_range($pn);
				if ($page_number==$pn) {$selected=true;} else {$selected=false;}
				$result['pages'][]=array('link'=>"$this->link&$sorting_qs&$this->qsvn_from=$from&$this->qsvn_to=$to",
										'from'=>$from,
										'to'=>$to,
										'page_number'=>$pn,
										'selected'=>$selected);
		}


		/* > jump to next page */
		if ($page_number+1<=$this->total_pages-$this->first_page_number)  {
			list($from,$to)=$this->get_page_range($page_number+1);
			$result['jumpt_to_next_page']=array('link'=>"$this->link&$sorting_qs&$this->qsvn_from=$from&$this->qsvn_to=$to",
												 'from'=>$from,
												 'to'=>$to);
		}
		/* show that there is more next page */
		if ($page_number<$this->total_pages-$number_of_page_to_show+$this->first_page_number) {
			if ($number_of_page_to_show+$page_number<$this->total_pages)  {
				$result['has_next_page']=true;
			} else $result['has_next_page']=false;


			/* >> jump to next pages */
			if ($page_number+$number_of_page_to_show<=$this->total_pages)  {
				list($from,$to)=$this->get_page_range($page_number+$number_of_page_to_show);
				$result['jumpt_to_next_pages']=array('link'=>"$this->link&$sorting_qs&$this->qsvn_from=$from&$this->qsvn_to=$to",
													 'from'=>$from,
													 'to'=>$to);
			}

			/* >>| jump to the last page */
			if ($number_of_page_to_show+$page_number<$this->total_pages)  {
				list($from,$to)=$this->get_page_range($this->total_pages-$this->first_page_number);
				$result['jumpt_to_last_page']=array('link'=>"$this->link&$sorting_qs&$this->qsvn_from=$from&$this->qsvn_to=$to",
													 'from'=>$from,
													 'to'=>$to);

			}
		}
		return $result;
	}


	/* like : << < ... 1 2 3 4 [5] 6 7 8 9 ... > >> */
	function _____________show_as_limited_list_of_pages________($number_of_page_to_show=10,$prefix='paging') {
		$pages_info=$this->get_limited_list_of_pages($number_of_page_to_show);
		//print_r($pages_info);
		$result='<span class="'.$prefix.'PagesNumbersList">'."\n";
		if ($pages_info['jumpt_to_first_page']) $result.= "<span class=\"jumpToFirstPage\"><a href='".$pages_info['jumpt_to_first_page']['link']."'> |&lt;&lt; </a> </span>";
		if ($pages_info['jumpt_to_prev_pages']) $result.= "<span class=\"jumpToPrevPages\"><a href='".$pages_info['jumpt_to_prev_pages']['link']."'> &lt;&lt; </a> </span>";
		if ($pages_info['jumpt_to_prev_page']) $result.= "<span class=\"jumpToPrevPage\"><a href='".$pages_info['jumpt_to_prev_page']['link']."'> &lt; </a> </span>";
		if ($pages_info['has_prev_page']) $result.="<span class=\"hasPrevPage\"> ... </span>";

		foreach ($pages_info['pages'] as $page) {
			if ($page['selected']==true) {
				$result.= "<span class=\"currentPageNumber\"><a href='$page[link]'>"."\n";
				$result.="[$page[page_number]]";
			} else {
				$result.= "<span class=\"pageNumber\"><a href='$page[link]'>";
				$result.="$page[page_number]";
			}
			$result.="</a></span>"."\n";
		}

		if ($pages_info['has_next_page']) $result.="<span class=\"hasNextPage\"> ... </span>";
		if ($pages_info['jumpt_to_next_page']) $result.= "<span class=\"jumpToNextPage\"><a href='".$pages_info['jumpt_to_next_page']['link']."'> &gt; </a> </span>";
		if ($pages_info['jumpt_to_next_pages']) $result.= "<span class=\"jumpToNextPages\"><a href='".$pages_info['jumpt_to_next_pages']['link']."'> &gt;&gt; </a> </span>";
		if ($pages_info['jumpt_to_last_page'])	$result.= "<span class=\"jumpToLastPage\"><a href='".$pages_info['jumpt_to_last_page']['link']."'> &gt;&gt;| </a> </span>";
		$result.='</span>';
		return $result;
	}

	/* like : << < ... 1 2 3 4 [5] 6 7 8 9 ... > >> 
		css style :
			pagingPagesNumbersList,
			currentPageNumber,pageNumber,
			jumpToFirstPage,jumpToPrevPages,jumpToPrevPage,hasPrevPage
			hasNextPage,jumpToNextPage,jumpToNextPages,jumpToLastPage
	*/
	/* like : << < ... 1 2 3 4 [5] 6 7 8 9 ... > >> */
	function show_as_limited_list_of_pages($number_of_page_to_show=10,$prefix='paging') {
		$pages_info=$this->get_limited_list_of_pages($number_of_page_to_show);
		//print_r($pages_info);
		$result='<span class="'.$prefix.'PagesNumbersList">'."\n";
		if ($pages_info['jumpt_to_first_page']) $result.= "<span class=\"jumpToFirstPage\"><a href='".$pages_info['jumpt_to_first_page']['link']."'> |&lt;&lt; </a> </span>";
		if ($pages_info['jumpt_to_prev_pages']) $result.= "<span class=\"jumpToPrevPages\"><a href='".$pages_info['jumpt_to_prev_pages']['link']."'> &lt;&lt; </a> </span>";
		if ($pages_info['jumpt_to_prev_page']) $result.= "<span class=\"jumpToPrevPage\"><a href='".$pages_info['jumpt_to_prev_page']['link']."'> &lt; </a> </span>";
		if ($pages_info['has_prev_page']) $result.="<span class=\"hasPrevPage\"> ... </span>";

		foreach ($pages_info['pages'] as $page) {
			if ($page['selected']==true) {
				$result.= "<span class=\"currentPageNumber\"><a href='$page[link]'>"."\n";
				$result.="[$page[page_number]]";
			} else {
				$result.= "<span class=\"pageNumber\"><a href='$page[link]'>";
				$result.="$page[page_number]";
			}
			$result.="</a>&nbsp;</span>"."\n";
		}

		if ($pages_info['has_next_page']) $result.="<span class=\"hasNextPage\"> ... </span>";
		if ($pages_info['jumpt_to_next_page']) $result.= "<span class=\"jumpToNextPage\"><a href='".$pages_info['jumpt_to_next_page']['link']."'> &gt; </a> </span>";
		if ($pages_info['jumpt_to_next_pages']) $result.= "<span class=\"jumpToNextPages\"><a href='".$pages_info['jumpt_to_next_pages']['link']."'> &gt;&gt; </a> </span>";
		if ($pages_info['jumpt_to_last_page'])	$result.= "<span class=\"jumpToLastPage\"><a href='".$pages_info['jumpt_to_last_page']['link']."'> &gt;&gt;| </a> </span>";
		$result.='</span>';
		return $result;
	}
	
	
	/* like : << < ... 1 2 3 4 [5] 6 7 8 9 ... > >> 
		css style :
			pagingPagesNumbersList,
			currentPageNumber,pageNumber,
			jumpToFirstPage,jumpToPrevPages,jumpToPrevPage,hasPrevPage
			hasNextPage,jumpToNextPage,jumpToNextPages,jumpToLastPage
	*/
	function show_as_limited_list_of_pages_formated($number_of_page_to_show=10,$prefix='paging') {
		$pages_info=$this->get_limited_list_of_pages($number_of_page_to_show);
		//print_r($pages_info);
		$result='<div class="'.$prefix.'PagesNumbersList">'."\n";
		if ($pages_info['jumpt_to_first_page']) $result.= "<div class=\"jumpToFirstPage\"><a href='".$pages_info['jumpt_to_first_page']['link']."'> |&lt;&lt; </a></div>"."\n";
		if ($pages_info['jumpt_to_prev_pages']) $result.= "<div class=\"jumpToPrevPages\"><a href='".$pages_info['jumpt_to_prev_pages']['link']."'> &lt;&lt; </a></div>"."\n";
		if ($pages_info['jumpt_to_prev_page']) $result.= "<div class=\"jumpToPrevPage\"><a href='".$pages_info['jumpt_to_prev_page']['link']."'> &lt; </a></div>"."\n";
		if ($pages_info['has_prev_page']) $result.="<div class=\"hasPrevPage\">...</div>"."\n";

		foreach ($pages_info['pages'] as $page) {
			if ($page['selected']==true) {
				$result.= "<div class=\"currentPageNumber\"><a href='$page[link]'>"."\n";
				$result.="$page[page_number]";
			} else {
				$result.= "<div class=\"pageNumber\"><a href='$page[link]'>";
				$result.="$page[page_number]";
			}
			$result.="</a></div>"."\n";
		}

		if ($pages_info['has_next_page']) $result.="<div class=\"hasNextPage\">...</div>"."\n";
		if ($pages_info['jumpt_to_next_page']) $result.= "<div class=\"jumpToNextPage\"><a href='".$pages_info['jumpt_to_next_page']['link']."'> &gt; </a></div>"."\n";
		if ($pages_info['jumpt_to_next_pages']) $result.= "<div class=\"jumpToNextPages\"><a href='".$pages_info['jumpt_to_next_pages']['link']."'> &gt;&gt; </a></div>"."\n";
		if ($pages_info['jumpt_to_last_page'])	$result.= "<div class=\"jumpToLastPage\"><a href='".$pages_info['jumpt_to_last_page']['link']."'> &gt;&gt;| </a></div>"."\n";
		$result.='</div>';
		return $result;
	}



	function show_as_form_fields($add_form_tag=false,$action=null,$name=null,$additional_fields=null)
	{
		$total=$this->total;//+$this->begin_value;
		if ($total > $this->limit)
		{
			$prev_html='';
			$next_html='';
			$this->set_variables();

			if ($this->from > $this->begin_value)
			{
				$prev_html="<input type=\"submit\" name=\"$this->qsvn_prev\" value=\"$this->prev_word\"/>";
			}

			if ($this->to < $total)
			{
				$next_html="<input type=\"submit\" name=\"$this->qsvn_next\" value=\"$this->next_word\"/>";

			}
			if (is_null($action)) {$action=$this->link;}
			if ($add_form_tag==true) {$result="<form $name $action method=\"POST\">";}
			$result.="$prev_html&nbsp;<input type=\"text\" size=\"2\" name=\"$this->qsvn_from\" value=\"$this->from\"/>-<input type=\"text\" size=\"2\" name=\"$this->qsvn_to\" value=\"$this->to\"/> / $total &nbsp;$next_html";
			if ($add_form_tag=true) {$result.="$additional_fields</form>";}
		}
		return $result;
	}


	function get_url($from=null,$to=null,$link=null)
	{
		if (is_null($from)) {$from=$this->from;}
		if (is_null($to)) {$to=$this->to;}
		if (is_null($link)) {$link=$this->link;}

		$result=$link;
		if (strpos($link,'?')===false) {$result.='?';} else {$result.='&';}
		$result.="$this->qsvn_from=$from&$this->qsvn_to=$to".$this->get_sorting_query_string();
		return $result;
	}

	function get_next_url()
	{
		return $this->get_url($this->next_from,$this->next_to);
	}

	function get_prev_url()
	{
		return $this->get_url($this->prev_from,$this->prev_to);
	}
}