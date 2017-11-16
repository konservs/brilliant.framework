<?php
//============================================================
// Sets of functions and classes to work with pagination.
//
//
// Author: Andrii Birev
//============================================================
namespace Brilliant\CMS;

use Brilliant\http\BRequest;

//Some defines...
define('PGTYPE_GET',0);
define('PGTYPE_POST',1);
//============================================================
// Class for pagination
//============================================================
class BPagination{
	protected static $instance=NULL;
	public $type=PGTYPE_GET;
	public $items_count;
	public $page;
	protected $limit;
	protected $offset;
	//====================================================
	//
	//====================================================
	public static function getInstance(){
		if(!is_object(self::$instance)){
			self::$instance=new BPagination();
			}
		return self::$instance;
		}
	//====================================================
	//
	//====================================================
	public function setLimit($limit){
		if(!empty($limit))
			$this->limit=$limit;
		else
			$this->limit=10;
		}
	public function setOffset($offset){
		if(!empty($offset))
			$this->offset=$offset;
		else
			$this->offset=0;
		}
	public function setpage($page){
		if(!empty($page))
			$this->page=$page;
		else
			$this->page=0;
		}
	/**
	 *
	 */
	public function getLimit(){
		if(isset($this->limit)){
			return $this->limit;
			}
		if($this->type==PTYPE_GET){
			$this->limit=BRequest::getString('itemsperpage');
			if(empty($this->limit)){
				$this->limit=10;
				}
			if($this->limit=='*'){
				$this->limit=0;
				}
			}
		return $this->limit;
		}
	//====================================================
	//
	//====================================================
	public function getOffset(){
		if(!empty($this->offset)){
			return $this->offset;
			}
		if($this->type==PTYPE_GET){
			$this->offset=$this->getPage()*$this->getLimit();	
			}
		return $this->offset;
		}
	//====================================================
	// Get current page num
	//====================================================
	public function getPage(){
		if(isset($this->page)){
			return $this->page;
			}
		if($this->type==PTYPE_GET){
			return BRequest::getInt('page');
			}
		
		}
	//====================================================
	// Check for page_prev
	//====================================================
	protected function pages_prepare_prev(){
		if($this->page_prev>=0){
			$url=$this->generateurl($this->page_prev);
			$this->pages[]=(object)array(
				'name'=>'&lt;',
				'url'=>$url,
				'active'=>($this->page_prev==$this->page_active),
				'disabled'=>false
				);
			}else{
			$this->pages[]=(object)array(
				'name'=>'&lt;',
				'url'=>'',
				'active'=>false,
				'disabled'=>true
				);
			}
		}
	//====================================================
	//
	//====================================================
	public function setBaseUrl($url){
		$this->baseurl=$url;
		}
	/**
	 *
	 */
	public function generateurl($page){
		if($this->type==PGTYPE_GET){
			$baseurl=$this->baseurl;
			if(empty($baseurl)){
				$brouter=BRouter::getInstance();
				$baseurl=parse_url($brouter->url,PHP_URL_PATH);
				}

			BRequest::setVar('page',$page);
			$res=BRequest::getGetString();
			$res=$baseurl.$res;
			return $res;
			}
		elseif($this->type==PGTYPE_POST){
			if($page!=0){
				return $this->baseurl.'page'.$page.'/';
				}
			else{
				return $this->baseurl;
				}
			}

		}
	//====================================================
	// Check for pages in center of pagination
	// 
	// [1] [2] [3] ... [67] [68]
	// 
	// [1] ... [23] [24] [25] ... [68]
	// 
	//====================================================
	protected function pages_prepare_middle(){
		$pm_left=2;
		$pm_right=2;
		//Add first page, if we are somewhere in middle of pages...
		if($this->page_active>$pm_left){
			$url=$this->generateurl(0);
			$this->pages[]=(object)array(
				'name'=>'1',
				'url'=>$url,
				'active'=>false,
				'disabled'=>false
				);
			$this->pages[]=(object)array(
				'name'=>'...',
				'url'=>'',
				'active'=>false,
				'disabled'=>true
				);
			}
		//Get Min and max middle pages
		$pm_min=$this->page_active-$pm_left;
		if($pm_min<0){
			$pm_min=0;
			}
		$pm_max=$this->page_active+$pm_right;
		if($pm_max>$this->pages_count){
			$pm_max=$this->pages_count;
			}
		//Add current page and some prev and next page nums
		for($i=$pm_min;$i<=$pm_max;$i++){
			$url=$this->generateurl($i);
			$this->pages[]=(object)array(
				'name'=>$i+1,
				'url'=>$url,
				'active'=>($i==$this->page_active),
				'disabled'=>false
				);
			}
		//Add last page, if we are somewhere in middle of pages
		if($this->page_active<($this->pages_count-$pm_right)){
			$this->pages[]=(object)array(
				'name'=>'...',
				'url'=>'',
				'active'=>false,
				'disabled'=>true
				);
			$url=$this->generateurl($this->pages_count);
			$this->pages[]=(object)array(
				'name'=>$this->pages_count+1,
				'url'=>$url,
				'active'=>false,
				'disabled'=>false
				);
			}

		}
	//====================================================
	// Check for "Next Page" link
	//====================================================
	protected function pages_prepare_next(){
		if($this->page_next<=$this->pages_count){
			$url=$this->generateurl($this->page_next);
			$this->pages[]=(object)array(
				'name'=>'&gt;',
				'url'=>$url,
				'active'=>($this->page_next==$this->page_active),
				'disabled'=>false
				);
			}else{
			$this->pages[]=(object)array(
				'name'=>'&gt;',
				'url'=>'',
				'active'=>false,
				'disabled'=>true
				);
			}
		}
	//====================================================
	//
	//====================================================
	protected function pages_prepare(){
		//Cache some pages pre-counted data
		$limit=$this->getLimit();
		$count=$this->items_count;
		if(($limit<=0)||($count<=0)){
			return false;
			}
		$this->pages_count=(int)($count/$limit);
		if(($count%$limit==0)&&($this->pages_count>0)){
			$this->pages_count--;
			}
		$this->page_prev=$this->getPage()-1;
		$this->page_next=$this->getPage()+1;
		$this->page_active=$this->getPage();
		$this->pages=array();
		//Add links
		$this->pages_prepare_prev();
		$this->pages_prepare_middle();
		$this->pages_prepare_next();
		BRequest::setVar('page',$this->getPage());
		return true;
		}
	//====================================================
	//
	//====================================================
	public function draw($tpl=''){
		//Prepare links
		$this->pages_prepare();
		//Load pagination template
		$brouter=BRouter::getInstance();
		$template=$brouter->templatename;
		bimport('http.useragent');
		$suffix=BBrowserUseragent::getDeviceSuffix();
		if(!empty($tpl)){
			$tpl='.'.$tpl;
			}
		$files=array();

		$files[]=BTEMPLATESPATH.$template.DIRECTORY_SEPARATOR.'pagination'.$tpl.$suffix.'.php';
		$files[]=BTEMPLATESPATH.$template.DIRECTORY_SEPARATOR.'pagination'.$tpl.'.d.php';
		$files[]=BTEMPLATESPATH.$template.DIRECTORY_SEPARATOR.'pagination'.$suffix.'.php';
		$files[]=BTEMPLATESPATH.$template.DIRECTORY_SEPARATOR.'pagination.d.php';
		$files[]=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'pagination'.$tpl.$suffix.'.php';
		$files[]=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'pagination'.$tpl.'.d.php';
		$files[]=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'pagination'.$suffix.'.php';
		$files[]=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'pagination.d.php';

		foreach($files as $file){
			if(file_exists($file)){
				$fn=$file;
				break;
				}
			}
		include($fn);
		}
	}

