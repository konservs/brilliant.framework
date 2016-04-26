<?php
//============================================================
//   Sets of functions and classes to work with GET & POST
// variables
//
// Author: Andrii Biriev
//============================================================


//============================================================
// 
//============================================================
class BRequest{
	protected static $variables=array();
	protected static $initialized=false;
	/**
	 * @return bool
	 */
	public static function requestmethod(){
		$s=$_SERVER['REQUEST_METHOD'];
		if(empty($s)){
			$s='GET';
			}
		return $s;
		}
	//====================================================
	// Init get variables
	//====================================================
	public static function init(){
		if(self::$initialized){
			return true;
			}
		$surl=$_SERVER["REQUEST_URI"];
		if(empty($surl)){
			return false;
			}
		$squery=parse_url($surl,PHP_URL_QUERY);
			if(empty($squery)){
			return false;
			}
		//Parsing string "a=1&b=3&c=abc" into array
		$query=array();
		parse_str($squery,$query);
		self::$variables=$query;
		self::$initialized=true;
		return true;
		}
	//====================================================
	// Set the variable in GET/POST variables
	//
	// $vartype - 'get' or 'post' or 'cookie'?
	//====================================================
	public static function setVar($name,$value){
		self::init();
		self::$variables[$name]=$value;
		return true;
		}
	/**
	 * Remove variable;
	 */
	public static function rmVar($name){
		self::init();
		if(isset(self::$variables[$name])){
			unset(self::$variables[$name]);
			}
		return true;
		}

	//====================================================
	//
	//====================================================
	public static function getGetString(){
		self::init();
		$values=array_filter(self::$variables,function($value){return $value!='';});
		if(empty($values)) return '';
		return '?'.http_build_query($values);
		}
	//====================================================
	//
	//====================================================
	public static function getVarPost($name,$default){
		if(!isset($_POST[$name])){
			return $default;
			}
		return $_POST[$name];
		}
	//====================================================
	//
	//====================================================
	public static function getVarCookie($name,$default){
		if(!isset($_COOKIE[$name])){
			return $default;
			}
		return $_COOKIE[$name];
		}
	//====================================================
	//
	//====================================================
	public static function getVarGet($name,$default){
		self::init();
		$res=isset(self::$variables[$name])?self::$variables[$name]:$default;
		return $res;
		}
	//====================================================
	// Get all Get variables
	//====================================================
	public static function getAllVarsGet(){
		self::init();
		return self::$variables;
		}
	//====================================================
	//
	//
	// $vartype - 'get' or 'post' or 'cookie'?
	//====================================================
	public static function getVar($name,$default,$vartype=''){
		switch($vartype){
			case 'post':
				return self::getVarPost($name,$default);
			case 'get':
				return self::getVarGet($name,$default);
			case 'cookie':
				return self::getVarCookie($name,$default);
				}
		$v=self::getVarGet($name,NULL);
		if($v===NULL){
			$v=self::getVarPost($name,NULL);
			}
		if(($v===NULL)||($v===false)){
			return $default;
			}
		return $v;
		}
	/**
	 *
	 */
	public static function getInt($name,$default=0,$vartype=''){
		$i=self::getVar($name,$default,$vartype);
		return (int)$i;
		}
	/**
	 * Get array of int values: [7,15,21,126,5,3]
	 */
	public static function getIntArray($name,$vartype=''){
		$i=self::getVar($name,array(),$vartype);
		if(!is_array($i)){
			return array();
			}
		$r=array();
		foreach($i as $elem){
			$r[]=(int)$elem;
			}
		return $r;
		}
	/**
	 *
	 */
	public static function getFloat($name,$default=0,$vartype=''){
		$i=self::getVar($name,$default,$vartype);
		$i=str_replace(',','.',$i);
		return (float)$i;
		}
	/**
	 *
	 */
	public static function getString($name,$default='',$vartype=''){
		$s=self::getVar($name,$default,$vartype);
		return $s;
		}
	/**
	 * Get strings array by keys...
	 */
	public static function getStrings($names=array(),$vartype=''){
		$result=array();
		if(!is_array($names)){
			return $result;
			}
		foreach($names as $name){
			$result[$name]=self::getVar($name,'',$vartype);
			}
		return $result;
		}
	}
