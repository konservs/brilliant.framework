<?php
/**
 * Current user panel / login&register links model
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

use \Brilliant\Users\BUsers;
use \Brilliant\Users\BUsersSession;

class Model_users_userpanel extends \Brilliant\MVC\BModel{
	public function getData($segments){
		$data=new stdClass;
		$session=BUsersSession::getInstanceAndStart();
		if(!empty($session)){
			$data->logged=true;
			$data->lastmod=$session->start;
			$busers=BUsers::getInstance();
			$data->user=$busers->itemGet($session->userid);
			}
		else{
			$data->logged=false;
			$data->lastmod=NULL;
			}


		return $data;
		}
	}
