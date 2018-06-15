<?php

use \Brilliant\Languages\Storage;

class Lang{
	$storageDefault;
	//To take English if main language does not have such translation
	$storageBackup;

	public static function init($languageCode){
	}
	public static function getStorage(){
	}
	public static function storagePreload($languageCode){
		$storage = self::getStorage();
		$storage->init($languageCode);
	}
	public static function loadFromStorage(){
	}
	public status function _($languageConstant){
		$storage->translate($languageConstant);
	}
}
