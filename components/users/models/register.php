<?php
/**
 * Registration page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');
use \Brilliant\Users\BUsers;
use \Brilliant\Users\BUser;
use \Brilliant\Users\BSocial;
use \Brilliant\HTTP\BRequest;

class Model_users_register extends \Brilliant\mvc\BModel {
	/**
	 * Get Data and try to registar user, if need.
	 * @param $segments
	 * @return stdClass
	 */
	public function getData($segments) {
		$data = new stdClass;
		$data->errors = array();
		$data->email = BRequest::getString('email');
		$data->name = BRequest::getString('name');
		$data->password = BRequest::getString('password');
		$data->result = false;

		$data->do = BRequest::getString('do');
		if ($data->do == 'register') {
			//Validation
			if (empty($data->name)) {
				$data->errors['name'] = 'Could not';
				return $data;
			}
			//
			if (strlen($data->password) < 6) {
				$data->errors['password'] = 'Password length < 6 chars';
				return $data;
			}
			$user = new BUser();
			$user->name = $data->name;
			$user->email = $data->email;
			$user->setpassword($data->password);
			$r = $user->saveToDB();
			if (!empty($r)) {
				$data->errors = $r;
			}
			if ($r === true) {
				$data->result = true;
				$data->errors = array();
			}
		}
		return $data;
	}
}
