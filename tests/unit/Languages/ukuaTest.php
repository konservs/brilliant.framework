<?php
use \PHPUnit\Framework\TestCase;
use \Brilliant\Languages\Lang;

class ukuaTest extends TestCase{
	/**
	 *
	 */
	public function testTransliteration(){
		Lang::init('uk-ua');
		Lang::$language->setTranslitMode(Lang::$language::MODE_KMU);
		$expected = 'koshyk';
		$got = Lang::transliterate('');
		}
	}
