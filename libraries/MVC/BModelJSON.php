<?php
/**
 *
 */
namespace Brilliant\MVC;

defined('BEXEC') or die('No direct access!');

abstract class BModelJSON extends \Brilliant\MVC\BModel{
	protected $error;
	protected $json;
	/**
	 * @param $segments
	 * @return bool
	 */
	abstract protected function getDataJson($segments, &$json);
	/**
	 * Get data
	 *
	 * @param $segments
	 * @return stdClass
	 */
	public function getData($segments){
		$data=new \stdClass();
		$this->json=new \stdClass();
		$this->getDataJson($segments, $this->json);

		$data->error=$this->error;
		$data->json=$this->json;
		$this->json->error=$this->error;
		return $data;
		}
	}