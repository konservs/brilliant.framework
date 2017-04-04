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
	abstract protected function getDataJson($segments);
	/**
	 * Get data
	 *
	 * @param $segments
	 * @return stdClass
	 */
	public function getData($segments){
		$data=new stdClass();
		$this->json=new stdClass();

		$this->getDataJson($segments);

		$data->error=$this->error;
		$data->json=$this->json;
		$this->json->error=$this->error;
		return $data;
		}
	}