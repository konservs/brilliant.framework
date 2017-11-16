<?php
/**
 * Logout page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

use \Brilliant\Users\BUsersSession;

class Model_users_logout extends \Brilliant\MVC\BModel{
	public function getData($segments){
		$data=new stdClass;
		$session=BUsersSession::getInstanceAndStart();
		if(!empty($session))
			$session->close();
		return $data;
		}
	}
