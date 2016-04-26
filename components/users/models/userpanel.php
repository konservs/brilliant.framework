<?php
/**
 * User panel module.
 *
 * @author Andrii Biriev
 */
defined('BEXEC') or die('No direct access!');

bimport('mvc.component');
bimport('mvc.model');

class Model_users_userpanel extends BModel{
	/**
	 *
	 */
	public function get_data($segments){
		$data=new stdClass;

		if(isset($segments['uid'])){
			bimport('users.session');
			$session=BUsersSession::getInstanceAndStart();
			$data->lastmod=$session->start;

			bimport('users.general');
			$busers=BUsers::getInstance();
			$data->user=$busers->get_single_user($segments['uid']);			
			$data->logged=true;
			}
		else{
			$data->logged=false;
			}


		return $data;
		}
	}
