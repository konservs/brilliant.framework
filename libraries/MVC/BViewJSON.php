<?php
/**
 * Special JSON view.
 * Defined general methods to 
 */
namespace Brilliant\MVC;

defined('BEXEC') or die('No direct access!');

class BViewJSON extends \Brilliant\MVC\BView{
	public function generate($data){
		return json_encode($data->json);
		}
	}