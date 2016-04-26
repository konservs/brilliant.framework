<?php
/**
 *
 */
defined('BEXEC') or die('No direct access!');
bimport('mvc.component');
bimport('mvc.model');

abstract class BModelJSON extends BModel{
	protected $error;
	protected $json;
	/**
	 * @param $segments
	 * @return bool
	 */
	abstract protected function get_data_json($segments);
	/**
	 * Get data
	 *
	 * @param $segments
	 * @return stdClass
	 */
	public function get_data($segments){
		$data=new stdClass();
		$this->json=new stdClass();

		$this->get_data_json($segments);

		$data->error=$this->error;
		$data->json=$this->json;
		$this->json->error=$this->error;
		return $data;
		}
	}