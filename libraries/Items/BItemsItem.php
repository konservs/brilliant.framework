<?php
/**
 * Abstract class for collection item
 *
 * @author Andrii Biriev
 *
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Brilliant\Items;

abstract class BItemsItem{
	public $id=0; //Necessary field
	public $isnew=true;
	protected $tableName='';
	protected $collectionName='';
	protected $primarykey='id';
	protected $fields=array();
	/**
	 * @var DateTime
	 */
	public $created;
	/**
	 * @var DateTime
	 */
	public $modified;
	/**
	 * Simple constructor.
	 */
	public function __construct() {
		//Do nothing.
		}
	/**
	 *
	 */
	public function getfields(){
		return $this->fields;
		}
	/**
	 * Add field into fields list.
	 *
	 * @param $name
	 * @param $type
	 * @param array $params
	 * @return bool
	 */
	protected function fieldAddRaw($name,$type,$params=array()){
		if(empty($name)){
			return false;
			}
		$fldobj=(object)$params;
		$fldobj->name=$name;
		$fldobj->type=$type;
		$this->fields[$name]=$fldobj;
		}
	/**
	 *
	 */
	public function getPrimaryKey(){
		return $this->{$this->primarykey};
		}
	/**
	 * Get RAW field value from DB.
	 *
	 * @param $value
	 * @param $type
	 * @return BDateTime|BImage|bool|int|null|string
	 */
	protected function fieldFromRaw($value,$type){
		switch($type){
			case 'int':
			case 'integer':
				return (int)$value;
			case 'float':
				return (float)$value;
			case 'itm':
			case 'item':
				return (int)$value;
			case 'bool':
			case 'boolean':
				return (bool)$value;
			case 'str':
			case 'string':
				return $value;
			case 'binary':
				return bin2hex($value);
			case 'enum':
				return $value;
			case 'dt':
				$obj=NULL;
				if(!empty($value)){
					$obj=new \Brilliant\BDateTime($value);
					}
				return $obj;
			case 'image':
				if(empty($value)){
					return NULL;
					}
				$img=new \Brilliant\Images\BImage();
				$img->url=$value;
				return $img;
			case 'json':
				if(empty($value)){
					return NULL;
					}
				$obj=json_decode($value);
				return $obj;
			}
		die('[BItem] fieldFromRaw() Unknown field type "'.$type.'"!');
		}
	/**
	 * Get RAW field value from DB.
	 */
	protected function fieldToSQL($name,$lang=''){
		$fldname=$name;
		if($lang!=''){
			$fldname.='_'.$lang;
			}
		$db=BFactory::getDBO();
		if(!isset($this->fields[$name])){
			return '';
			}
		$type=$this->fields[$name]->type;
		$emptynull=isset($this->fields[$name]->emptynull)?$this->fields[$name]->emptynull:false;
		switch($type){
			case 'int':
			case 'integer':
				$value=(int)$this->{$fldname};
				if(($emptynull)&&(empty($value))){
					return 'NULL';
					}
				return $value;
			case 'float':
				$value=(float)$this->{$fldname};
				if(($emptynull)&&(empty($value))){
					return 'NULL';
					}
				return $value;
			case 'itm':
			case 'item':
				$itemid=(int)$this->{$fldname};
				return empty($itemid)?'NULL':$itemid;
			case 'bool':
			case 'boolean':
				return (int)$this->{$fldname};
			case 'str':
			case 'string':
				$value=$this->{$fldname};
				if(($emptynull)&&(empty($value))){
					return 'NULL';
					}
				return $db->escape_string($this->{$fldname});
			case 'binary':
				$value=$this->{$fldname};
				if(($emptynull)&&(empty($value))){
					return 'NULL';
					}
				return 'UNHEX('.$db->escape_string($this->{$fldname}).')';
			case 'enum':
				return $db->escape_string($this->{$fldname});
			case 'dt':
				$obj=$this->{$fldname};
				if(!is_object($obj)){
					//return '""';
					return 'NULL';
					}
				$s=$obj->format('Y-m-d H:i:s');
				return '"'.$s.'"';
			case 'image':
				$obj=$this->{$fldname};
				if(!is_object($obj)){
					return NULL;
					}
				return $db->escape_string($obj->url);
			case 'json':
				$obj=$this->{$fldname};
				if(is_object($obj)){
					return $db->escape_string(json_encode($obj));
					}
				elseif(is_array($obj)){
					return $db->escape_string(json_encode($obj));
					}
				return '""';
				}
		die('[BItem] fieldToSQL() Unknown field type "'.$type.'"!');
		}
	/**
	 *
	 */
	public function load($obj){
		//Load primary key / keys
		if(is_array($this->primarykey)){
			foreach($this->primarykey as $pk){
				$this->{$pk}=$obj[$pk];
				}
			}else{
			$this->{$this->primarykey}=(int)$obj[$this->primarykey];
			}
		//
		$this->isnew=false;
		//Get languages list
		$languages=\Brilliant\CMS\BLang::langlist();
		//Process additional fields
		foreach($this->fields as $fld){
			//Multi-language field + general field
			if($fld->multilang==2){
				$this->{$fld->name}=$this->fieldFromRaw($obj[$fld->name],$fld->type);
				foreach($languages as $lng){
					$nn=$fld->name.'_'.$lng;
					$this->$nn=$this->fieldFromRaw($obj[$nn],$fld->type);
					}
				}
			//Simple multi-language
			elseif($fld->multilang){
				foreach($languages as $lng){
					$nn=$fld->name.'_'.$lng;
					$this->$nn=$this->fieldFromRaw($obj[$nn],$fld->type);
					}
				}
			//Single language
			else{
				$this->{$fld->name}=$this->fieldFromRaw($obj[$fld->name],$fld->type);
				}
			}

		return true;
		}
	/**
	 * @param $obj
	 * @param $list
	 * @return bool
	 */
	protected function loadItems(&$obj,$list){
		$arr=explode(',',$list);
		if(!is_array($arr)){
			return false;
			}
		foreach($arr as $itm){
			$this->loadItem($obj,trim($itm));
			}
		}
	/**
	 * Load item by type
	 *
	 * @param $obj
	 * @param $item
	 * @return bool
	 */
	protected function loadItem(&$obj,$item){
		switch($item){
			case 'id':$this->id=(int)$obj['id']; return true;
			case 'published': $this->published=$obj['published']; return true;
			case 'name':
				$this->name_ru=$obj['name_ru'];
				$this->name_ua=$obj['name_ua'];
				return true;
			case 'alias':
				$this->alias_ru=$obj['alias_ru'];
				$this->alias_ua=$obj['alias_ua'];
				return true;
			case 'created': $this->created=new DateTime($obj['created']); return true;
			case 'modified': $this->modified=new DateTime($obj['modified']); return true;
			}
		return false;
		}
	/**
	 *
	 */
	protected function detectLanguage($lang){
		if(empty($lang)){
			bimport('cms.language');
			$lang=BLang::$langcode;
			}
		return $lang;
		}
	/**
	 *
	 */
	public function getlangvar($varname,$lang=''){
		if(empty($lang)){
			bimport('cms.language');
			$lang=BLang::$langcode;
			//var_dump($lang); die('a');
			}
		$name=$varname.'_'.$lang;
		//var_dump($lang); die('b');
		$result=isset($this->$name)?$this->$name:'';
		return $result;
		}

	/**
	 * Set var value.
	 *
	 * @param $varname
	 * @param $value
	 * @param bool $required
	 * @return bool
	 */
	public function setvarval($varname,$value,$required=false){
		if(!isset($this->fields[$varname])){
			return false;
			}
		$type=$this->fields[$varname]->type;

		switch($type){
			case 'int':
			case 'integer':
				if((empty($value))&&($required)){
					return false;
					}
				$this->{$varname}=(int)$value;
				return true;
			case 'float':
				if((empty($value))&&($required)){
					return false;
					}
				$this->{$varname}=(float)$value;
				return true;
			case 'itm':
			case 'item':
				if((empty($value))&&($required)){
					return false;
					}
				$this->{$varname}=(int)$value;
				return true;
			case 'bool':
			case 'boolean':
				$this->{$varname}=(bool)$value;
				return true;
			case 'str':
			case 'string':
				$value2=$value;
				//If the fiels is alias, that
				if((empty($value2))&&(!empty($this->fields[$varname]->alias))){
					$str='';
					foreach($this->fields[$varname]->alias as $aliasfld){
						$str.=(empty($str)?'':'-').$this->{$aliasfld};
						}
					$value2=BLang::generatealias($str);
					}
				if((empty($value2))&&($required)){
					return false;
					}
				$this->{$varname}=$value2;
				return true;
			case 'binary':
				$this->{$varname}=$value;
				return true;
			case 'enum':
				//Check enum
				$this->{$varname}=$value;
				return true;
			case 'dt':
				$obj=NULL;
				if((empty($value))&&($required)){
					return false;
					}
				if(!empty($value)){
					$obj=new \Brilliant\BDateTime($value);
					}
				$this->{$varname}=$obj;
				return true;
			case 'image':
				bimport('images.single');
				if((empty($value))&&($required)){
					return false;
					}
				$img=new BImage();
				$img->url=$value;
				$this->{$varname}=$img;
				return true;
			case 'json':
				if((empty($value))&&($required)){
					return false;
					}
				$this->{$varname}=$value;
				return true;
			}
		return false;
		}

	/**
	 * Set var value by lang.
	 *
	 * @param $varname
	 * @param $value
	 * @param $lang
	 * @param bool $required
	 * @return bool
	 */
	public function setvarval_lang($varname,$value,$lang,$required=false){
		if(!isset($this->fields[$varname])){
			return false;
			}
		$type=$this->fields[$varname]->type;
		switch($type){
			case 'int':
			case 'integer':
				$this->{$varname.'_'.$lang}=(int)$value;
				return true;
			case 'float':
				$this->{$varname.'_'.$lang}=(float)$value;
				return true;
			case 'itm':
			case 'item':
				$this->{$varname}=(int)$value;
				return true;
			case 'bool':
			case 'boolean':
				$this->{$varname.'_'.$lang}=(bool)$value;
				return true;
			case 'str':
			case 'string':
				$value2=$value;
				//If the fiels is alias, that
				if((empty($value2))&&(!empty($this->fields[$varname]->alias))){
					$str='';
					foreach($this->fields[$varname]->alias as $aliasfld){
						$str.=(empty($str)?'':'-').$this->{$aliasfld.'_'.$lang};
						}
					$value2=BLang::generatealias($str);
					}
				if((empty($value2))&&($required)){
					return false;
					}
				$this->{$varname.'_'.$lang}=$value2;
				return true;
			case 'binary':
				$this->{$varname.'_'.$lang}=$value;
				return true;
			case 'enum':
				$this->{$varname.'_'.$lang}=$value;
				return true;
			case 'dt':
				$obj=NULL;
				if((empty($value))&&($required)){
					return false;
					}
				if(!empty($value)){
					$obj=new \Brilliant\BDateTime($value);
					}
				$this->{$varname.'_'.$lang}=$obj;
				return true;
			case 'image':
				if((empty($value))&&($required)){
					return false;
					}
				bimport('images.single');
				$img=new BImage();
				$img->url=$value;
				$this->{$varname.'_'.$lang}=$img;
				return true;
			case 'json':
				//return json_encode($value);
				return false;
			}
		return false;
		}
	/**
	 *
	 */
	protected function getfieldsvalues(&$qr_fields,&$qr_values){
		$qr_fields=array();
		$qr_values=array();
		//Get languages list
		$languages=BLang::langlist();
		//Process additional fields
		foreach($this->fields as $fld){
			//Multi-language field + general field
			if($fld->multilang===2){
				$qr_fields[]='`'.$fld->name.'`';
				$qr_values[]=$this->fieldToSQL($fld->name);
				foreach($languages as $lng){
					$qr_fields[]='`'.$fld->name.'_'.$lng.'`';
					$qr_values[]=$this->fieldToSQL($fld->name,$lng);
					}
				}
			//Simple multi-language
			elseif($fld->multilang){
				foreach($languages as $lng){
					$qr_fields[]='`'.$fld->name.'_'.$lng.'`';
					$qr_values[]=$this->fieldToSQL($fld->name,$lng);
					}
				}
			//Single language
			else{
				$qr_fields[]='`'.$fld->name.'`';
				$qr_values[]=$this->fieldToSQL($fld->name);
				}
			}
		return true;
		}
	/**
	 *
	 */
	protected function dbInsertQuery(){
		$qr_fields=array();
		$qr_values=array();
		$this->getfieldsvalues($qr_fields,$qr_values);
		$qr='INSERT INTO `'.$this->tableName.'` ('.implode(',',$qr_fields).') VALUES ('.implode(',',$qr_values).')';
		return $qr;
		}
	/**
	 *
	 */
	protected function dbupdatequery(){
		$qr_fields=array();
		$qr_values=array();
		$this->getfieldsvalues($qr_fields,$qr_values);
		$qr='UPDATE `'.$this->tableName.'` SET ';
		$first=true;
		foreach($qr_fields as $i=>$field){
			$qr.=($first?'':', ').$field.'='.$qr_values[$i];
			$first=false;
			}
		$qr.=' WHERE `'.$this->primarykey.'`='.$this->{$this->primarykey};
		return $qr;
		}

	/**
	 *
	 */
	public function updateCache(){
		$bcache=BFactory::getCache();
		if(empty($bcache)){
			return false;
			}
		$cachekey='';
		if(is_array($this->primarykey)){
			foreach($this->primarykey as $pk){
				$pkk=$this->{$pk};
				$cachekey.=(empty($cachekey)?'':':').$this->{$pk};
				}
			}else{
			$cachekey=$this->{$this->primarykey};
			}
		$cachekey=$this->tableName.':itemid:'.$cachekey;
		$bcache->delete($cachekey);
		return true;
		}
	//====================================================
	// Run Insert query in the database & reload cache
	// 
	// returns true if OK and false if not
	//====================================================
	public function dbInsert(){
		BLog::addtolog('[Items.Item.'.$this->tableName.']: Inserting data...');
		if(!$db=BFactory::getDBO()){
			return false;
			}
		//Forming query...
		$this->modified=new DateTime();
		//For import we need ability to set `created`
		if(empty($this->created)){
			$this->created=new DateTime();
			}
		$qr=$this->dbInsertQuery();
		//Running query...
		$q=$db->query($qr);
		if(empty($q)){
			return false;
			}
		$this->{$this->primarykey}=$db->insertId();
		$this->isnew=false;
		//Updating cache...
		$this->updateCache();
		//Return result
		return true;
		}
	//====================================================
	// Run Update query in the database & reload cache
	// 
	// returns true if OK and false if not
	//====================================================
	public function dbupdate(){
		BLog::addtolog('[Items.Item.'.$this->tableName.']: Updating data...');
		if(empty($this->id)){
			return false;
			}
		if(!$db=BFactory::getDBO()){
			return false;
			}
		//
		$this->modified=new DateTime();
		//Get query
		$qr=$this->dbupdatequery();
		//Running query...
		$q=$db->query($qr);
		if(empty($q)){
			return false;
			}
		//Updating cache...
		$this->updateCache();
		//Return result
		return true;
		}
	/**
	 *
	 */
	public function saveToDBTimes($limit=5){
		$i=0;
		$result=false;
		while((!$result)&&($i<$limit)){
			BLog::addtolog('[Items.Item.'.$this->tableName.']: saveToDBTimes('.$i.' / '.$limit.')');
			$result=$this->saveToDB();
			$i++;
			}
		return $result;
		}

	/**
	 * Check is and run insert or update query, reload cache.
	 * @return bool
	 */
	public function saveToDB(){
		BLog::addtolog('[Items.Item.'.$this->tableName.']: saveToDB()');
		if($this->isnew){
			return $this->dbInsert();
			}else{
			return $this->dbupdate();
			}
		}
	}
