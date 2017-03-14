<?php
/**
 * Basic view class
 *
 * Author: Andrii Biriev
 */
namespace Brilliant\mvc;
use Brilliant\log\BLog;

class BView{
	public $paths;
	public $componentname;
	public $viewname;
	public $controller;
	public $templatename;
	/**
	 * Simple constructor
	 */
	public function __construct(){
		$this->paths=array();
		$this->controller=NULL;
		}
	//====================================================
	// Init the view: detect user client type and add
	// some paths to seek
	//====================================================
	public function init(){
		return true;
		}
	//====================================================
	//
	//====================================================
	public function addpathes(){
		$this->AddPath(BTEMPLATESPATH.$this->templatename.DIRECTORY_SEPARATOR);
		$this->AddPath(BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR);
		}
	//====================================================
	//
	//====================================================
	public function settitle($value){
		if(isset($this->controller)){
			$this->controller->title=$value;
			}
		}
	/**
	 * Set status (403 | 404 | 500)
	 */
	public function setStatus($value){
		if(isset($this->controller)){
			$this->controller->status=$value;
			}
		}
	//====================================================
	//
	//====================================================
	public function setcache($cachecontrol,$cachetime=3600){
		if(isset($this->controller)){
			$this->controller->cachecontrol=$cachecontrol;
			$this->controller->cachetime=$cachetime;
			}	
		}
	//====================================================
	//
	//====================================================
	public function setLocation($url,$time=0){
		if(DEBUG_MODE){
			BLog::addtolog('[BView] setLocation('.$url.','.$time.')');
			}
		if(isset($this->controller)){
			$this->controller->locationurl=$url;
			$this->controller->locationtime=$time;
			}
		}
	//====================================================
	//
	//====================================================
	public function setlastmodified($value){
		if(!isset($this->controller)){
			return false;
			}
		if(empty($value)){
			return false;
			}
		if(empty($this->controller->modified)){
			$this->controller->modified=$value;
			return true;
			}
		$interval=date_diff($this->controller->modified,$value);
		if($interval->invert==0){
			$this->controller->modified=$value;
			}
		return true;
		}
	//====================================================
	//
	//====================================================
	public function addmeta($name,$content,$http_equiv=''){
		if(isset($this->controller)){
			$this->controller->meta[]=array(
				'name'=>$name,
				'http_equiv'=>$http_equiv,
				'content'=>$content
				);
			}
		}
	/**
	 * Add external CSS file or internal style.
	 * 
	 * @param string $name
	 * @param string $media
	 * @param string $data
	 */
	public function add_css($name,$media='',$data=''){
		$lnk=array();
		$lnk['rel']='stylesheet';
		$lnk['href']=$name;
		$lnk['data']=$data;
		$lnk['type']='text/css';
		if(!empty($media)){
			$lnk['media']=$media;
			}
		return $this->add_link($lnk);
		}
	/**
	 * Add head link tag
	 * 
	 * @param type $array
	 */
	public function add_link($array){
		if(!isset($this->controller)){
			return false;
			}
		$this->controller->link[]=$array;
		return true;
		}

	/**
	 * @param $file
	 * @param string $src
	 * @param int $priority
	 * @return bool
	 */
	public function add_js($file,$src='',$priority=100){
		if(!isset($this->controller)){
			return false;
			}
		$this->controller->js[]=array('file'=>$file,'src'=>$src,'priority'=>$priority);
		return true;
		}
	/**
	 * Add frameform declaration
	 * 
	 * @param string $alias the framework alias
	 * @return boolean true if ok
	 */
	public function use_framework($alias){
		if(!isset($this->controller)){
			return false;
			}
		$this->controller->frameworks[$alias]=$alias;
		return true;
		}
	/**
	 * Load JS file into head
	 * 
	 * @param string $file
	 * @param int $priority
	 * @return boolean
	 */
	public function load_js($file,$priority=100){
		if(!isset($this->controller)){
			if(DEBUG_MODE){
				BLog::addtolog('[MVC.View]: Could not load js file, because controller is empty!',LL_ERROR);
				}
			return false;
			}
		if(!file_exists($file)){
			if(DEBUG_MODE){
				BLog::addtolog('[MVC.View]: Could not load js file ('.$file.'), because it does not exists!',LL_ERROR);
				}
			return false;
			}
		$src=file_get_contents($file);
		$this->controller->js[]=array('file'=>'','src'=>$src,'priority'=>$priority);
		}
	/**
	 * Load CSS file into head
	 * 
	 * @param string $file filename
	 * @param int $priority priority of the loaded file. The files
	 * with less priority will be loaded before
	 * @return boolean true if ok
	 */
	public function load_css($file,$priority=100){
		if(!isset($this->controller)){
			if(DEBUG_MODE){
				BLog::addtolog('[MVC.View]: Could not load css file, because controller is empty!',LL_ERROR);
				}
			return false;
			}
		if(!file_exists($file)){
			if(DEBUG_MODE){
				BLog::addtolog('[MVC.View]: Could not load css file, because it does not exists!',LL_ERROR);
				}
			return false;
			}
		$src=file_get_contents($file);
		if(!empty($src)){
			$this->add_css_declaration($src);
			}
		return true;
		}
	/**
	 * Add style
	 * 
	 * @param string $style
	 * @return boolean
	 */
	public function add_css_declaration($style){
		if(!isset($this->controller)){
			return false;
			}
		$this->controller->style[]=$style;
		return true;
		}
	/**
	 * Add breadcrumbs element.
	 * 
	 * @param type $url
	 * @param type $name
	 * @param type $active
	 * @param type $class
	 * @return boolean
	 */
	public function breadcrumbs_add($url,$name,$active=true,$class='',$children=array()){
		if(!isset($this->controller)){
			return false;
			}
		$this->controller->breadcrumbs[]=(object)array(
			'url'=>$url,
			'name'=>$name,
			'active'=>$active,
			'class'=>$class,
			'children'=>$children
			);
		return true;
		}
	/**
	 * Add breadcrumbs homepage element
	 */
	public function breadcrumbs_add_homepage(){
		$brouter=BRouter::getInstance();
		return $this->breadcrumbs_add(
			'//'.$brouter->generateurl('mainpage',BLang::$langcode,array('view'=>'mainpage')),
			BLang::_('BREADCRUMBS_HOMEPAGE'),//'vidido.ua'
			true,'homepage');
		}
	/**
	 * Add breadcrumbs user dashboard
	 */
	public function breadcrumbs_add_userdashboard(){
		$brouter=BRouter::getInstance();
		return $this->breadcrumbs_add(
			'//'.$brouter->generateurl('users',BLang::$langcode,array('view'=>'dashboard')),
			BLang::_('USERS_DASHBOARD_HEADING'),
			true);
		}

	/**
	 * Add rubric breadcrumbs element (in most cases - second emelent of breadcrumbs).
	 *
	 * @param $id rubric ID
	 * @return bool
	 */
	public function breadcrumbs_add_rubric($id){
		bimport('rubrics.general');
		$brubrics=BRubrics::getInstance();
		$rub=$brubrics->getrubric_by_id($id);
		if(empty($rub)){
			return false;
			}
		$brouter=BRouter::getInstance();
		$url='//'.$brouter->generateURL('rubric',BLang::$langcode,array('view'=>'rubric','id'=>$id));
		$name=$rub->getname();
		return $this->breadcrumbs_add($url,$name,true,'rubric');
		}
	/**
	 *  Add region breadcrumbs element
	 *
	 * @param $id
	 * @return bool
	 */
	public function breadcrumbs_add_region($id){
		bimport('regions.general');
		$bregions=BRegions::getInstance();
		$regions=$bregions->regions_get_all();
		$reg=$bregions->region_get($id);
		if(empty($reg)){
			return false;
			}
		$brouter=BRouter::getInstance();
		$url='//'.$brouter->generateURL('regions',BLang::$langcode,array('view'=>'region','id'=>$id));
		$children=array();
		foreach($regions as $r){
			$children[$r->id]=(object)array(
				'active'=>true,
				'url'=>'//'.$brouter->generateURL('regions',BLang::$langcode,array('view'=>'region','id'=>$r->id)),
				'name'=>$r->getname()
				);
			}
		$name=$reg->getname();
		return $this->breadcrumbs_add($url,$name,true,'region',$children);
		}

	/**
	 * @param $id
	 * @param $cat
	 * @return bool
	 */
	public function breadcrumbs_add_marka($id,$cat){
		bimport('avto_model.general');
		$bmm=BAvtoMM::getInstance();
		$brands=$bmm->brands_get_forcat($cat);
		$b=$bmm->brand_get($id);
		$name=$b->getname();
		if(empty($b)){
			return false;
			}
		$brouter=BRouter::getInstance();
		$url='//'.$brouter->generateUrl('auto',BLang::$langcode,array('view'=>'brand','id'=>$id));
		$children=array();
		foreach($brands as $b){
			$children[$b->id]=(object)array(
				'active'=>true,
				'url'=>'//'.$brouter->generateURL('auto',BLang::$langcode,array('view'=>'brand','id'=>$b->id)),
				'name'=>$b->getname()
				);
			}

		return $this->breadcrumbs_add($url,$name,true,'brand',$children);
		}

	/**
	 * @param $marka
	 * @param $id
	 * @return bool
	 */
	public function breadcrumbs_add_model($marka,$id){
		bimport('avto_model.general');
		$bmm=BAvtoMM::getInstance();
		$models=$bmm->getmodels(0,0,array('brand'=>$marka));
		$model=$bmm->model_get($id);
		if(empty($model)){
			return false;
			}
		$name=$model->getname();
		$brouter=BRouter::getInstance();
		$url='//'.$brouter->generateUrl('auto',BLang::$langcode,array('view'=>'brand','id'=>$id));
		$children=array();
		foreach($models as $m){
			$children[$m->id]=(object)array(
				'active'=>true,
				'url'=>'//'.$brouter->generateURL('auto',BLang::$langcode,array('view'=>'model','id'=>$m->id)),
				'name'=>$m->getname()
				);
			}
		return $this->breadcrumbs_add($url,$name,true,'model',$children);
		}
	/**
	 *  Add cat/region SEOpages breadcrumbs element
	 *
	 * @param $catid
	 * @param $id
	 * @return bool
	 */
	public function breadcrumbs_add_catregion($catid,$id){
		bimport('regions.general');
		bimport('classified.general');
		$bregions=BRegions::getInstance();
		$regions=$bregions->regions_get_all();
		$reg=$bregions->region_get($id);
		if(empty($reg)){
			return false;
			}
		$bclassified=BClassified::getInstance();
		$cat=$bclassified->getcatbyid($catid);
		if(empty($cat)){
			return false;
			}
		$brouter=BRouter::getInstance();
		$url='//'.$brouter->generateURL('classified',BLang::$langcode,array('view'=>'category_region','category'=>$catid,'region'=>$id));
		$children=array();
		foreach($regions as $r){
			$children[$r->id]=(object)array(
				'active'=>true,
				'url'=>'//'.$brouter->generateURL('classified',BLang::$langcode,array('view'=>'category_region','category'=>$catid,'region'=>$r->id)),
				'name'=>$r->getname()
				);
			}
		$name=$reg->getname();
		return $this->breadcrumbs_add($url,$name,true,'region',$children);
		}
	/**
	 * Add city breadcrumbs element
	 *
	 * @param $id
	 * @return bool
	 */
	public function breadcrumbs_add_city($id){
		bimport('regions.general');
		$bregions=BRegions::getInstance();
		$city=$bregions->city_get($id);
		if(empty($city)){
			return false;
			}
		$region=$city->getregion();
		if(empty($region)){
			return false;
			}
		$subcities=$region->cities_get_all();
		//
		$brouter=BRouter::getInstance();
		$url='//'.$brouter->generateURL('regions',BLang::$langcode,array('view'=>'city','id'=>$id));
		$name=$city->getname();
		if(!empty($subcities)){
			$children=array();
			foreach($subcities as $c){
				$children[$c->id]=(object)array(
					'active'=>true,
					'url'=>'//'.$brouter->generateURL('regions',BLang::$langcode,array('view'=>'city','id'=>$c->id)),
					'name'=>$c->getname()
					);
				}
			return $this->breadcrumbs_add($url,$name,true,'city',$children);
			}
       		return $this->breadcrumbs_add($url,$name,true,'city');
		}
	/**
	 * Add cat/region SEOpages breadcrumbs element
	 * 
	 * @param int $id region id
	 */
	public function breadcrumbs_add_catcity($catid,$id){
		bimport('regions.general');
		bimport('classified.general');
		$bregions=BRegions::getInstance();
		$city=$bregions->city_get($id);
		if(empty($city)){
			return false;
			}
		$region=$city->getregion();
		if(empty($region)){
			return false;
			}
		$bclassified=BClassified::getInstance();
		$cat=$bclassified->getcatbyid($catid);
		if(empty($cat)){
			return false;
			}
		$subcities_unsorted=$region->cities_get_all();
		$subcities=array();
		//Sort cities...
		foreach($subcities_unsorted as $sc){
			$subcities[]=$sc;
			}
		$n=count($subcities);
		for($i=0; $i<$n; $i++){
			$m=$i;
			for($j=$i; $j<$n; $j++){
				if($subcities[$j]->citycompare($subcities[$m]) < 0){
					$m=$j;
					}
				}
			if($m!=$i){
				$t=$subcities[$m];
				$subcities[$m]=$subcities[$i];
				$subcities[$i]=$t;
				}
			}
		//Add cities..
		$brouter=BRouter::getInstance();
		$url='//'.$brouter->generateURL('classified',BLang::$langcode,array('view'=>'category_city','category'=>$catid,'city'=>$id));
		$name=$city->getname();
		if(!empty($subcities)){
			$children=array();
			foreach($subcities as $c){
				$children[$c->id]=(object)array(
					'active'=>true,
					'url'=>'//'.$brouter->generateURL('classified',BLang::$langcode,array('view'=>'category_city','category'=>$catid,'city'=>$c->id)),
					'name'=>$c->getname()
					);
				}
			return $this->breadcrumbs_add($url,$name,true,'city',$children);
			}
       		return $this->breadcrumbs_add($url,$name,true,'city');
		}
	/**
	 * Add the layout path
	 * 
	 * @param string $dir
	 */
	public function AddPath($dir){
		$this->paths[]=$dir;
		}
	/**
	 * 
	 * @return string HTML of the redirect
	 */
	public function renderredirect(){
		$fn=BTEMPLATESPATH.$this->templatename.DIRECTORY_SEPARATOR.'redirect.php';
		if(!file_exists($fn)){
			$fn=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'redirect.php';
			}
		if(!file_exists($fn)){
			return 'Redirecting...';
			}

		ob_start();
		include $fn;
		$html=ob_get_clean();
		return $html;
		}
	/**
	 * Rendering layout into string
	 * 
	 * @param string $subname subname, if absolute or template name ('users.login') if not.
	 * @param type $absolute true means absolute name
	 * @return string
	 */
	public function template_load($subname='',$absolute=false){
		$this->addpathes();
		bimport('http.useragent');
		$suffix=BBrowserUseragent::getDeviceSuffix();
		//
		if($absolute){
			$fnames=array(
				$subname.$suffix.'.php',
				$subname.'.d.php'
				);
			}else{
			if(!empty($subname)){
				$subname='.'.$subname;
				}
			$fnames=array(
				$this->componentname.'.'.$this->viewname.$subname.$suffix.'.php',
				$this->componentname.'.'.$this->viewname.$subname.'.d.php',
				$this->componentname.'.'.$this->viewname.$suffix.'.php',
				$this->componentname.'.'.$this->viewname.'.d.php'
				);
			}
		$filename='';
		$subfname='';
		foreach($this->paths as $fp){
			if(!empty($filename)){
				break;
				}
			foreach($fnames as $fn){
				if(DEBUG_MODE){
					BLog::addtolog('[View] try to load template:'.$fp.$fn);
					}
				if(file_exists($fp.$fn)){
					$filename=$fp.$fn;
					$subfname=$fn;
					break;
					}
				}
			}
		if(empty($filename)){
			return '';
			}
		//Rendering template file into string...
		ob_start();
		include $filename;
		$html=ob_get_clean();
		if(DEBUG_MODE){
			bimport('http.request');
			$tp=BRequest::GetInt('tp');
			if($tp){
				$html='<div style="font-size: 10px; color: #ccc;">'.$subfname.'</div>'.$html;
				}
			}
		return $html;
		}
	/**
	 * Abstract function. Should be overloaded in all
	 * children
	 */
	public function generate($data = null){
		var_dump($data);
		}
	}
