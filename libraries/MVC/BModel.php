<?php
namespace Brilliant\MVC;
//============================================================
// Basic model class
//
// Author: Andrii Biriev
//============================================================

abstract class BModel{
	function __construct() {
		return true;
		}
	abstract public function getData($segments);
	}
