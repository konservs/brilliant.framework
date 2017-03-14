<?php
/**
 * Current user panel / login&register links model
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

use \Brilliant\users\BUsersSession;

class Model_users_userpanel extends \Brilliant\mvc\BModel{
	public function get_data($segments){
		$data=new stdClass;
		$session=BUsersSession::getInstanceAndStart();
		if(!empty($session)){
			$data->logged=true;
			$data->lastmod=$session->start;
			bimport('users.general');
			$busers=BUsers::getInstance();
			$data->user=$busers->get_single_user($session->userid);
			}
		else{
			$data->logged=false;
			$data->lastmod=NULL;
			}


		return $data;
		}
	}
