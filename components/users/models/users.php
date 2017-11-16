<?php
/**
 * Model for all users list
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class Model_users_users extends \Brilliant\MVC\BModel{
	public function getData($segments){
		$page=isset($segments['page'])?$segments['page']:1;
		$page=$page-1;
		$limit=10;

		$offset=$page*$limit;

		$users=BUsers::getInstance();
		$data=new stdClass;
		$data->users=$users->getusers($limit,$offset,array('active'=>true));

		return $data;
		}

	}
