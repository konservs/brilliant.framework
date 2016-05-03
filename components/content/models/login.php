<?php
/**
 * Content.article page model
 *
 * @author Andrii Biriev
 */
defined('BEXEC') or die('No direct access!');

bimport('mvc.component');
bimport('mvc.model');
bimport('http.request');

class Model_content_article extends BModel{
	/**
	 * Model - get necessary data.
	 */
	public function get_data($segments){
		$data=new stdClass;
		$data->error=-1;
		//

		$data->error=0;
		return $data;
		}
	}
