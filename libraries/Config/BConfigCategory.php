<?php
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
