<?php
use \PHPUnit\Framework\TestCase;
use \Brilliant\Cache\BCacheFiles;

class ufCacheFilesTest extends TestCase{
	/**
	 *
	 */
	public function testCache1(){
		$bcache = new BCacheFiles();
		$bcache->invalidate();
		//Start to check!
		$bcache->set('cache1','abc',3600);//Set for 1 hour
		$v = $bcache->get('cache1');
		$this->assertTrue($v === 'abc','Could not get cached value (received '.var_export($v,true).')!');

		unset($bcache);
		}
	}

