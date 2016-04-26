<?php
bimport('cms.config.general');
bimport('cms.config.systems');
bimport('cms.config.payment');
/**
 * Configuration library class
 * 
 * @author Andrii Biriev
 */
class BConfig{
	protected static $instance=NULL;
	public $categories=array();
	/**
	 * Singleton
	 * 
	 * @return \BConfig|NULL created object
	 */
	public static function getInstance(){
		if (!is_object(self::$instance)){
			self::$instance=new BConfig();
			}
		return self::$instance;
		}
	/**
	 * Register category
	 * 
	 * @return boolean result
	 */
	public function registerCategory($classname){
		if(!class_exists($classname)){
			return false;
			}
		$obj=new $classname();
		if(empty($obj->alias)){
			return false;
			}
		$this->categories[$obj->alias]=$obj;
		return true;
		}
	/**
	 * Get all fields objects
	 * 
	 * @return array array of fields objects
	 */
	public function getallfields(){
		$res=array();
		foreach($this->categories as $cat){
			foreach($cat->groups as $group){
				foreach($group->fields as $fld){
					$res[$fld->alias]=$fld;
					}
				}
			}
		return $res;
		}
	/**
	 * Get field value by alias.
	 * 
	 * @return mixed data
	 */
	public function getVal($alias,$default=0){
		//Check for define
		if(defined($alias)){
			return constant($alias);
			}
		//If nothing found - return default value
		return $default;
		}
	/**
	 * Save configuration file
	 * 
	 * @return boolean result
	 */
	public function saveconfig(){
		$fields=$this->getallfields();
		foreach($fields as $k=>&$v){
			$v->value=BRequest::getVar($k,$v->default);
			}
		$cfgfile='<?php'.PHP_EOL;
		foreach($fields as $fld){
			$cfgfile.=$fld->getcfg();
			}
		$fn_config=BROOTPATH.'config'.DIRECTORY_SEPARATOR.'config.php';
		return file_put_contents($fn_config,$cfgfile);
		}
	}
/**
 * General abstract class for config category
 * 
 * @author Andrii Biriev
 */
class BConfigCategory{
	public $alias;
	public $name;
	public $description;
	public $groups=array();
	/**
	 * Register group
	 * Create BConfigCategoryGroup object & register it
	 * 
	 * @param string $name name of the group
	 * @param string $alias group alias
	 * @param array $fields array of BConfigField objects
	 * 
	 * @return \BConfigCategoryGroup created group object
	 */
	public function registerGroup($name,$alias,$fields){
		$grp=new BConfigCategoryGroup();
		$grp->alias=$alias;
		$grp->name=$name;
		foreach($fields as $fld){
			$grp->fields[$fld->alias]=$fld;
			}
		$this->groups[$alias]=$grp;
		return $grp;
		}
	}
/**
 * General abstract class for config category group
 * 
 * @author Andrii Biriev
 */
class BConfigCategoryGroup{
	public $alias;
	public $name;
	public $fields=array();
	}

/**
 * General abstract class for config field
 * 
 * @author Andrii Biriev
 */
class BConfigField{
	public $value=NULL;//re-defined value
	public $alias;
	public $name;
	public $default;
	/**
	 * Create object and fill fields
	 * 
	 * @param string $alias alias of field. Storing in the database.
	 * @param string $name name of the field. Showing into control panel
	 * @param untypes $default default value
	 */
	public function __construct($alias='',$name='',$default=NULL){
		//parent::__construct();
		$this->name=$name;
		$this->alias=$alias;
		$this->default=$default;
		}
	/**
	 * Get field value.
	 * Check, if the variable is defined and return value,
	 * or return default data, if field not defined
	 * 
	 * @return mixed data
	 */
	public function getVal(){
		//Check for re-defined value (for example, by POST query)
		if(isset($this->value)){
			return $this->value;
			}
		//Check for define
		if(defined($this->alias)){
			return constant($this->alias);
			}
		//If nothing found - return default value
		return $this->default;
		}
	/**
	 * Print bootstrap HTML data
	 * 
	 * @return string HTML data
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_input($this->name,$this->alias,$this->getVal(),'');
		}
	/**
	 * Get config strings.
	 * 
	 * @return string config file strings.
	 */
	public function getcfg(){
		$str='//'.$this->name.PHP_EOL;
		$str.='define(\''.$this->alias.'\',\''.addslashes($this->getVal()).'\');'.PHP_EOL;
		return $str;
		}
	}

/**
 * Integer field class
 * 
 * @author Andrii Biriev
 */
class BConfigFieldInt extends BConfigField{
	public $min;
	public $max;
	/**
	 * Create object and fill fields
	 * 
	 * @param string $alias alias of field. Storing in the database.
	 * @param string $name name of the field. Showing into control panel
	 * @param int $default default value
	 * @param int $min min value
	 * @param int $max max value
	 */
	public function __construct($alias='',$name='',$default=0,$min=NULL,$max=NULL){
		parent::__construct($alias,$name,$default);
		$this->min=$min;
		$this->max=$max;
		}
	/**
	 * Get config strings.
	 * 
	 * @return string config file strings.
	 */
	public function getcfg(){
		$str='//'.$this->name.PHP_EOL;
		$str.='define(\''.$this->alias.'\','.(int)$this->getVal().');'.PHP_EOL;
		return $str;
		}
	}

/**
 * String field
 * 
 * @author Andrii Biriev
 */
class BConfigFieldString extends BConfigField{
	}

/**
 * Password field class
 * 
 * @author Andrii Biriev
 */
class BConfigFieldPassword extends BConfigField{
	/**
	 * Print bootstrap HTML data
	 * 
	 * @return string HTML data
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_password($this->name,$this->alias,$this->getVal(),'');
		}
	}

/**
 * Folder selector class
 * 
 * @author Andrii Biriev
 */
class BConfigFieldPath extends BConfigField{
	/**
	 * Create object and fill fields
	 * 
	 * @param string $alias alias of field. Storing in the database.
	 * @param string $name name of the field. Showing into control panel
	 * @param string $default default value
	 */
	public function __construct($alias='',$name='',$default=''){
		parent::__construct($alias,$name,$default);
		}
	/**
	 * Print Bootstrap HTML
	 * 
	 * @return string HYML string
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_input($this->name,$this->alias,$this->getVal(),'');
		}
	}
	
/**
 * Rubric selector class
 * 
 * @author Andrii Biriev
 */
class BConfigFieldRubric extends BConfigField{
	/**
	 * Create object and fill fields
	 * 
	 * @param string $alias alias of field. Storing in the database.
	 * @param string $name name of the field. Showing into control panel
	 * @param string $default default value
	 */
	public function __construct($alias='',$name='',$default=0){
		parent::__construct($alias,$name,$default);
		}
	/**
	 * Print Bootstrap HTML
	 * 
	 * @return string HYML string
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_input($this->name,$this->alias,$this->getVal(),'');
		}
	/**
	 * Get config strings.
	 * 
	 * @return string config file strings.
	 */
	public function getcfg(){
		$str='//'.$this->name.PHP_EOL;
		$str.='define(\''.$this->alias.'\','.$this->getVal().');'.PHP_EOL;
		return $str;
		}
	}

/**
 * Full-text field (for meta description, for example)
 * 
 * @author Andrii Biriev
 */
class BConfigFieldText extends BConfigField{
	/**
	 * Print bootstrap HTML data
	 * 
	 * @return string HTML data
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_textarea($this->name,$this->alias,$this->getVal(),'');
		}
	}

/**
 * Full-text field (for meta description, for example)
 * 
 * @author Andrii Biriev
 */
class BConfigFieldHTML extends BConfigField{
	//
	}

/**
 * List field (for example, cache type)
 * 
 * @author Andrii Biriev
 */
class BConfigFieldList extends BConfigField{
	public $values=array();
	/**
	 * Create object and fill fields
	 * 
	 * @param string $alias alias of field. Storing in the database.
	 * @param string $name name of the field. Showing into control panel
	 * @param array $values
	 * @param string $default default value
	 */
	public function __construct($alias='',$name='',$values=array(),$default=''){
		parent::__construct($alias,$name,$default);
		$this->values=$values;
		}
	/**
	 * Print 
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_select($this->name,$this->alias,$this->values,$this->getVal());
		}
	}
