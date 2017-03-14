<?php
/**
 * Confirm registration page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class Model_users_confirm extends \Brilliant\mvc\BModel{
	/**
	 *
	 */
	public function get_data($segments){
		$data=new stdClass;
		$busers=BUsers::getInstance();
		$r=$busers->confirm($segments['id'],$segments['confirmcode']);
		if($r==true){
			$data->status=0;
			}
		else{
			$data->status=1;
			}
		return $data;
		}
	}
