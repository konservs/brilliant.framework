<?php
namespace Brilliant\cms;
use Brilliant\BSingleton;
use Brilliant\log\BLog;
use Brilliant\SQL\BMySQL;

//============================================================
// Sets of functions and classes to work with language.
//
// Author: Andrii Biriev
//============================================================
define('DEBUG_BLANG',0);

//============================================================
// Main class for language.
//============================================================
class BLang{
	use \Brilliant\BSingleton;
	public static $strings;
	public static $langcode;
	public static $langcode_web;
	public static $suffix;
	public static $translitconverter=array(
		'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e',
        	'ё'=>'e', 'ж'=>'zh','з'=>'z', 'и'=>'i', 'й'=>'y', 'к'=>'k',
		'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r',
		'с' => 's',   'т' => 't',   'у' => 'u', 'ф' => 'f',   'х' => 'h',   'ц' => 'c',
		'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch', 'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
		'э' => 'e',   'ю' => 'yu',  'я' => 'ya', 'ґ'=>'g', 'ї'=>'yi', 'і'=>'i', 'є'=>'e',
       
		'А' => 'A',   'Б' => 'B',   'В' => 'V','Г' => 'G',   'Д' => 'D',   'Е' => 'E',
		'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z','И' => 'I',   'Й' => 'Y',   'К' => 'K',
		'Л' => 'L',   'М' => 'M',   'Н' => 'N','О' => 'O',   'П' => 'P',   'Р' => 'R',
		'С' => 'S',   'Т' => 'T',   'У' => 'U','Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
		'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch','Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
		'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya', 'Ґ'=>'G', 'Ї'=>'Yi', 'І'=>'I', 'Є'=>'E'
		);
	public static $letters=array(
		'а'=>1, 'б'=>2, 'в'=>3, 'г'=>4, 'ґ'=>5, 'д'=>6, 'е'=>7, 'є'=>8, 'ё'=>9, 'ж'=>10,
		'з'=>11,'и'=>12,'і'=>13,'ї'=>14,'й'=>15,'к'=>16,'л'=>17,'м'=>18,'н'=>19,'о'=>20,
		'п'=>21,'р'=>22,'с'=>23,'т'=>24,'у'=>25,'ф'=>26,'х'=>27,'ц'=>28,'ч'=>29,'ш'=>30,
		'щ'=>31,'ь'=>32,'ы'=>33,'ъ'=>34,'э'=>35,'ю'=>36,'я'=>37, 
       		);
	//=====================================================
	// Init the language
	//=====================================================
	public static function init($lang,$suffix=''){
		$fn=BLANGUAGESPATH.$lang.$suffix.'.php';
		if(!file_exists($fn)){
			self::regeneratecache();
			}
		if(!file_exists($fn)){
			return false;
			}
		self::$suffix=$suffix;
		if(DEBUG_BLANG){
			BLog::addToLog('[BLang]: Initializing language ('.$lang.')');
			BLog::addToLog('[BLang]: Loading file "'.$fn.'"...');
			}
		include($fn);
		self::$langcode=$lang;
		switch(self::$langcode){
			case 'ru':
				self::$langcode_web='ru-RU';
				break;
			case 'ua':
				self::$langcode_web='uk-UA';
				break;
			}
		}
	//=====================================================
	// Function to get language translated string
	//=====================================================
	public static function _($short){
		if(is_numeric($short)){
			return $short;
			}
		if(empty($short)){
			return $short;
			}
		if(isset(self::$strings[$short])){
			$res=self::$strings[$short];
			}else{
			$res=$short;
			}
		if(DEBUG_BLANG){
			BLog::addToLog('[BLang]: Get language text "'.$short.'"=>"'.$res.'"');
			}
		return $res;
		}
	//=====================================================
	//
	//=====================================================
	public static function plural($number,$after){
		$cases = array (2, 0, 1, 1, 1, 2);
		return $after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
		}
	//=====================================================
	//
	//=====================================================
	public static function plural_after($number,$after){
		$cases = array (2, 0, 1, 1, 1, 2);
		return $number.' '.BLang::_($after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ]);	
		}
	//=====================================================
	//
	//=====================================================
	public static function sprintf(){
		$args = func_get_args();
		if (count($args) > 0) {
			$args[0] = self::_($args[0]);
			return @call_user_func_array('sprintf',$args);
			}
		return '';
		}
	//=====================================================
	//
	//=====================================================
	public function regeneratecache(){
		if(DEBUG_MODE){
			BLog::addToLog('[Lang]: Regenerating cache...');
			}
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)){
			return;
			}
		$qr='SELECT * from `languages`';
		$q=$db->Query($qr);
		if(DEBUG_MODE){
			BLog::addToLog('[Lang]: query executed. Writing into file...');
			}
		if(empty($q)){
			return;
			}
		$fru=BLANGUAGESPATH.'ru.php';
		$fua=BLANGUAGESPATH.'ua.php';

		$file1='<?php'.PHP_EOL.'self::$strings=array('.PHP_EOL;
		$file2='<?php'.PHP_EOL.'self::$strings=array('.PHP_EOL;
		while($l=$db->fetch($q)){
			$l['ru']=str_replace("'","\\'",$l['ru']);
			$l['ua']=str_replace("'","\\'",$l['ua']);
			$file1.='\''.$l['const'].'\' =>\''.$l['ru'].'\','.PHP_EOL;
			$file2.='\''.$l['const'].'\' =>\''.$l['ua'].'\','.PHP_EOL;
			}
		$file1.=');'.PHP_EOL;
		$file2.=');'.PHP_EOL;

		@file_put_contents($fru,$file1);
		@file_put_contents($fua,$file2);
		if(DEBUG_MODE){
			BLog::addToLog('[Lang]: Regenerating cache done!');
			}
		return true;
		}
	//=====================================================
	//
	//=====================================================
	public function getcount($keyword){
		$keyword=mb_strtolower($keyword,'utf8');
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)){
			return NULL;
			}
		$qr='SELECT count(*) as cnt from `languages`';
		if(!empty($keyword)){
			$qr.=' where (lower(const) like '.$db->escapeString('%'.$keyword.'%').')or'.
				       '(lower(ru) like '.$db->escapeString('%'.$keyword.'%').')or'.
				       '(lower(ua) like '.$db->escapeString('%'.$keyword.'%').')';;
			
			}
		$q=$db->Query($qr);
		if(empty($q)){
			echo('<br>error!<br>'.$qr);
			return NULL;
			}
		$l=$db->fetch($q);
		return $l['cnt'];
		}
	/**
	 *
	 */
	public static function translate($short,$lang=''){
		if(empty($lang)||$lang==self::$langcode){
				return self::_($short);
				}
		if(!$db=BFactory::getDBO()){
			return '';
			}
		$qr='select '.$lang.' as res from languages where const='.$db->escapeString($short);
		$q=$db->Query($qr);
		$l=$db->fetch($q);
		return $l['res'];
		}
	/**
	 *
	 */
	public static function translate_sprintf(){
		$args = func_get_args();
		if (count($args) > 0) {
			$args[0] = self::translate($args[0],$args[1]);
			unset($args[1]);
			return @call_user_func_array('sprintf',$args);
			}
		}	
	//=====================================================
	//
	//=====================================================
	public function getlanguages($limit=10,$offset=0,$keyword=''){	
//		echo $limit.PHP_EOL;
//		echo $offset.PHP_EOL;
//		echo $keyword.PHP_EOL;
		$keyword=mb_strtolower($keyword,'utf8');
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)){
			return NULL;
			}
		$qr='SELECT * from `languages`';
		
		if(!empty($keyword)){
			$qr.=' where (lower(const) like '.$db->escapeString('%'.$keyword.'%').')or'.
				       '(lower(ru) like '.$db->escapeString('%'.$keyword.'%').')or'.
				       '(lower(ua) like '.$db->escapeString('%'.$keyword.'%').')';
			}
			
		if($limit!=0)
			$qr.=' limit '.$limit.' offset '.$offset; 
		$q=$db->Query($qr);
		if(empty($q)){
			echo('<br>error!<br>'.$qr);
			return NULL;
			}
		$languages=array();
		while($l=$db->fetch($q)){
			$languages[]=$l;
			}	
		return $languages;

		}
	//=====================================================
	//
	//=====================================================
	public function save($const,$ru,$ua){
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)){
			return NULL;
			}
		$qr='update `languages` set ru='.$db->escapeString($ru).', ua='.$db->escapeString($ua).' where const='.$db->escapeString($const).' ';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
		}
		$this->regeneratecache();
	}
	//=====================================================
	//
	//=====================================================
	public static function translit($string){
		//if(function_exists('mb_strtr')){
		//	return mb_strtr($string,self::$translitconverter);
		//	}
		if(function_exists('mb_str_split')){
			return str_replace(
				mb_str_split(array_keys(self::$translitconverter)),
				mb_str_split(array_values(self::$translitconverter)),
				$string);
			}else{
			return str_replace(
				array_keys(self::$translitconverter),
				array_values(self::$translitconverter),
				$string);
			}
		}
	/**
	 *
	 */
	public static function langlist(){
		return array('ru','ua');
		}
	/**
	 *
	 */
	public static function bmb_str_split($str, $encoding = 'utf-8'){
		$cnt=mb_strlen($str, $encoding);
		$arr=array();
		$i=0;
		while($cnt > $i){
			$arr[]=mb_substr($str, $i++, 1, $encoding);
			}
		return $arr;
		} 
	/**
	 *
	 */
	public static function text2intarray($string){
		$intarray=array();
		$array=self::bmb_str_split($string);
		foreach($array as $item){
			$intarray[]=isset(self::$letters[$item])?self::$letters[$item]:999;
			}
		return $intarray;
		}
	/**
	 *
	 */
	public static function compare2strings($str1,$str2){
		$sstr1=mb_strtolower($str1,'UTF-8');
		$sstr2=mb_strtolower($str2,'UTF-8');
		$arr1=self::text2intarray($sstr1);
		$arr2=self::text2intarray($sstr2);
		$i=0;
		while(($i<count($arr1))&&($i<count($arr1))){
			if($arr1[$i]>$arr2[$i]){
				return 1;
				}
			if($arr1[$i]<$arr2[$i]){
				return -1;
				}
			$i++;
			}
		if((count($arr1))>(count($arr2))){
			return 1;
			}
		if((count($arr1))<(count($arr2))){
			return -1;
			}
		return 0;
		}
	//=====================================================
	//
	//=====================================================
	public static function generatealias($string){
		$str=self::translit($string);
		//echo('translit ('.$string.')='.$str.'<br>');
		$str=mb_strtolower($str,'UTF-8');
		//echo('alias ('.$string.')='.$str.'<br>');
		$str=str_replace('\'', '', $str);
		//echo('alias ('.$string.')='.$str.'<br>');
		$str=preg_replace('~[^-a-z0-9_]+~u', '-', $str);
		//echo('alias ('.$string.')='.$str.'<br>');
		$str=trim($str, "-");
		//echo('alias ('.$string.')='.$str.'<br>');
		return $str;
		}
	}

