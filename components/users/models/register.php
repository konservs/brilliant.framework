<?php
/**
 * Registration page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class Model_users_register extends \Brilliant\mvc\BModel{
	public function get_data($segments){
		$data=new stdClass;
		$data->email=isset($_POST['email'])?$_POST['email']:'';
		$data->name=isset($_POST['name'])?$_POST['name']:'';
		$data->password=$_POST['password'];
		$data->password_check=$_POST['password_check'];
		$data->result=false;

		$busers=BUsers::getInstance();
		if($_POST['do']=='register'){
			$r=$busers->register($_POST);	
			if(!empty($r)){
				$data->errors=$r;
				}
			if($r===true){
				$data->result=true;
				$data->errors=array();
				}
			$data->user=$_POST;
			}
		return $data;
		}
	}
