<?php
/**
 * Social users
 *
 * @author: Andrii Biriev
 */
bimport('cms.singleton');
bimport('items.general');
bimport('items.list');
bimport('users.social.user');

class BUsersSocialUsers extends BItemsList{
	use BSingleton;
	protected $tablename='users_social';
	protected $itemclassname='BUsersSocialUser';
	protected $primarykey=array('user','provider');
	/**
	 *
	 */
	public function itemsFilterSql($params,&$wh,&$jn){
		parent::itemsFilterSql($params,$wh,$jn);
		$db=BFactory::getDBO();
		if(!empty($params['provider'])){
			$wh[]='(`provider`='.$db->escapeString($params['provider']).')';
			}
		if(!empty($params['user'])){
			$wh[]='(`user`='.(int)$params['user'].')';
			}
		if(!empty($params['social_id'])){
			$social_id=(string)$params['social_id'];
			$wh[]='(`social_id`='.$db->escapeString($params['social_id']).')';
			}
		return true;
		}
	}