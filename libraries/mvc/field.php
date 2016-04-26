<?php
/**
 * Basic component field class. Allow to 
 * 
 * @author Andrii Biriev <a@konservs.com>
 * @author Andrii Karepin <karepinandrei@gmail.com>
 * @author Yuriy Galin <ygalin21@gmail.com>
 * @copyright © 2014 Brilliant IT corporation, www.it.brilliant.ua
 */
class BControllerField{
	public $id;
	public $name;
	public $com;
	public $value;
	public $params;
	public $controller;

	/**
	 * Generate admin key by field id.
	 * 
	 * Perform some converts: 
	 * catid            -> bfields[catid]
	 * tab[1][catid]    -> bfields[tab][1][catid]
	 * tab[1][ordering] -> bfields[tab][1][ordering]
	 */
	public function getid($id){
		$n=strlen($id);
		$i=0;
		$arrname='';
		while(($i<$n)&&($id[$i]!='[')){
			$arrname.=$id[$i];
			$i++;
			}
		if($id[$i]=='['){
			$i++;
			}
		$appendix='';
		$brackets=1;//opened brackets

		while(($i<$n)&&($brackets>=0)){
			if($id[$i]=='['){
				$brackets++;
				}
			if($id[$i]==']'){
				$brackets--;
				}

			$appendix.=$id[$i];
			$i++;
			}
		if(strlen($appendix)>0){
			$appendix=substr($appendix, 0, -1);
			}
		$name='bfields['.$arrname.']'.(empty($appendix)?'':'['.$appendix.']');
		//echo('name="'.$name.'", id="'.$id.'"');
		return $name;
		}
	/**
	 * Generate html input
	 * 
	 * @return string HTML formated string
	 */
	public function adminhtml(){
		return '<input type="hidden" name="'.htmlspecialchars($this->getid($this->id)).'" value="'.htmlspecialchars($this->value).'">';
		}
	/**
	 * After all configures prepare lists.
	 */
	public function prepare(){
		}
	/**
	 * Init control with start value
	 */
	public function initialize($val){
		$this->value=$val;
		}
	/**
	 * ???
	 * 
	 * @param type $file
	 * @param type $src
	 * @param type $priority
	 * 
	 * @return bool true if ok
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
				BDebug::error('[MVC.View]: Could not load js file, because controller is empty!');
				}
			return false;
			}
		if(!file_exists($file)){
			if(DEBUG_MODE){
				BDebug::error('[MVC.View]: Could not load js file ('.$file.'), because it does not exists!');
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
				BDebug::error('[MVC.View]: Could not load css file, because controller is empty!');
				}
			return false;
			}
		if(!file_exists($file)){
			if(DEBUG_MODE){
				BDebug::error('[MVC.View]: Could not load css file, because it does not exists!');
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

	}
