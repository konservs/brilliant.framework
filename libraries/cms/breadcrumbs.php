<?php
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
		$brouter=BRouter::getInstance();
		$template=$brouter->templatename;
		bimport('http.useragent');
		$suffix=BBrowserUseragent::getDeviceSuffix();

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

/**
 * General class for inter-component breadcrumbs.
 *
 * @author Andrii Biriev
 */
class BGeneralBreadcrumbs extends BBreadcrumbs{
	public static $instance=NULL;
	/**
	 * Return breadcrumbs class
	 *
	 * @return \BGeneralBreadcrumbs|NULL breadcrumbs object or NULL
	 */
	public static function getInstance(){
		if(!is_object(self::$instance)){
			self::$instance=new BGeneralBreadcrumbs();
			}
		return self::$instance;
		}
	/**
	 *
	 * @return string HTML
	 */
	public static function staticdraw(){
		if(!is_object(self::$instance)){
			return '';
			}
		self::$instance->draw();
		}
	}
