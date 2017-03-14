<?php
namespace Brilliant\mvc;
/**
 * Basic component class. Allow to control models / views and some lists - 
 * additional fields & software modules. Very useful class.
 * 
 * @author Andrii Biriev <a@konservs.com>
 */
class BController{
	public $status;
	public $componentname;
	public $templatename;
	public $title;		//set HTML Title tag
	public $meta;		//array of meta tags
	public $js;			//array of
	public $style;		//array of
	public $link;		//array of link head tags
	public $modified;	//Last modified date/time
	public $cachecontrol;	//true or false
	public $cachetime;	//in seconds
	public $frameworks;
	public $breadcrumbs;//Breadcrumbs array
	/**
	 * The constructor. No comments.
	 */
	public function __construct(){
		//fill-up default values
		$this->status=200;
		$this->title=NULL;
		$this->modified=NULL;
		$this->cachecontrol=false;
		$this->cachetime=0;
		$this->meta=array();
		$this->link=array();
		$this->js=array();
		$this->style=array();
		$this->frameworks=array();
		}
	/**
	 * Load fields objects by component & field type
	 * @return null|\BControllerField 
	 */
	public function field_get($type,$com=''){
		if(empty($com)){
			$fn=BLIBRARIESFRAMEWORKPATH.'mvc'.DIRECTORY_SEPARATOR.'field'.DIRECTORY_SEPARATOR.$type.'.php';
			$class='BControllerField_'.$type;
			} else {
			$fn=BCOMPONENTSAPPLICATIONPATH.$com.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.$type.'.php';
			if(!file_exists($fn)){
				$fn=BCOMPONENTSFRAMEWORKPATH.$com.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.$type.'.php';
				}
			$class='BControllerField_'.$com.'_'.$type;
			}
		if(!file_exists($fn)){
			if(DEBUG_MODE){
				BDebug::error('File "'.$fn.'" does not exist! type='.$type.', com='.$com);
				}
			return NULL;
			}
		require_once($fn);
		$field=new $class;
		$field->controller=$this;
		$field->com=$com;
		return $field;
		}
	/**
	 * Load fields objects list...
	 * @return array soft modules list for current component
	 */
	public function softmodule_fields_list($fview,$fcontroller=NULL){
		$mylist=$this->softmodules_list();
		$myitm=NULL;
		//Search the necessary softmodule...
		foreach($mylist as $itm){
			if($itm->view==$fview){
				$myitm=$itm;
				}
			}
		if(empty($myitm)){
			return false;
			}
		$params=array();
		foreach($myitm->params as $p){
			$fld=$this->field_get($p->fieldtype,$p->com);
			if(empty($fld)){
				continue;
				}
			$fld->controller=empty($fcontroller)?$this:$fcontroller;
			$fld->name=$p->name;
			$fld->id=$p->id;
			$fld->fieldtype=$p->fieldtype;
			$fld->params=$p->params;
			$fld->prepare();
			$params[]=$fld;
			}
		return $params;
		}
	/**
	 * Get current component softmodules list 
	 * @return array soft modules list for current component
	 */
	public function softmodules_list(){
		return array();
		}
	/**
	 * Load the model of current component by alias
	 * 
	 * @param type $mdlname
	 * @return null|\BModel
	 */
	public function LoadModel($mdlname=''){
		if(empty($mdlname)){
			bimport('mvc.model');
			return new BModel();
			}
		$fn=BCOMPONENTSAPPLICATIONPATH.$this->componentname.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$mdlname.'.php';
		if(!file_exists($fn)){
			$fn=BCOMPONENTSFRAMEWORKPATH.$this->componentname.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$mdlname.'.php';
			}
		$class='Model_'.$this->componentname.'_'.$mdlname;
		if(!file_exists($fn)){
			echo('File "'.$fn.'" does not exist!');
			return NULL;
			}
		require_once($fn);
		$model=new $class;
		$model->controller=$this;
		return $model;
		}
	/**
	 * Load the view of current component by alias
	 * 
	 * @param string $viewname
	 * @return null|\BView
	 */
	public function LoadView($viewname){
		$fn=BCOMPONENTSAPPLICATIONPATH.$this->componentname.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$viewname.'.php';
		if(!file_exists($fn)){
			$fn=BCOMPONENTSFRAMEWORKPATH.$this->componentname.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$viewname.'.php';
			}
		$class='View_'.$this->componentname.'_'.$viewname;
		if(!file_exists($fn)){
			return NULL;
			}
		require_once($fn);
		$view=new $class;
		$view->controller=$this;
		$view->componentname=$this->componentname;
		$view->templatename=$this->templatename;
		$view->viewname=$viewname;
		$view->init();
		return $view;
		}
	}
