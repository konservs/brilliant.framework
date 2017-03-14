<?php
/**
 * Logout page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class Model_users_logout extends \Brilliant\mvc\BModel{
	public function get_data($segments){
		$data=new stdClass;
		bimport('users.session');
		$session=BUsersSession::getInstanceAndStart();
		if(!empty($session))
			$session->close();
		return $data;
		}
	}
