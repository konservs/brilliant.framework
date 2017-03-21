<?php
namespace Brilliant\cms;
/**
 * Sets of functions and classes to work with breadcrumbs
 * 
 * @author Andrii Birev
 */
class BBreadcrumbs{
	protected $data;
	public $elements;
	/**
	 * Constructor - store current microtime, fill some
	 * default values
	 */
	public function __construct(){
		$this->divider=' → ';
		$this->elements=array();
		}
	/**
	 * Add breadcrumb element into array.
	 */
	public function add_element($url,$name,$active=true,$class='',$children=array()){
		$this->elements[]=(object)array(
			'url'=>$url,
			'name'=>$name,
			'active'=>$active,
			'class'=>$class,
			'children'=>$children,
			);
		}
	/**
	 * Draw breadcrumbs HTML
	 */
	public function draw(){
		$bRouter=\Application\BRouter::getInstance();
		$template=$bRouter->templatename;
		$suffix=\Brilliant\HTTP\BBrowserUseragent::getDeviceSuffix();

		$fn=BTEMPLATESPATH.$template.DIRECTORY_SEPARATOR.'breadcrumbs'.$suffix.'.php';
		if(!file_exists($fn)){
			$fn=BTEMPLATESPATH.$template.DIRECTORY_SEPARATOR.'breadcrumbs.d.php';
			}
		if(!file_exists($fn)){
			$fn=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'breadcrumbs'.$suffix.'.php';
			}
		if(!file_exists($fn)){
			$fn=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'breadcrumbs.d.php';
			}

		if(!file_exists($fn)){
			return '';
			}
		include($fn);
		}
	}
