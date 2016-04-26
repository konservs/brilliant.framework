<?php
/**
 * Model class for admin panel.
 *
 */
bimport('mvc.model');
bimport('mvc.admin.modelitem_new');

abstract class BModelAdminItemTreeNew extends BModelAdminItemNew{
	/**
	 *
	 */
	protected function itemsetfields(&$item){
		$parentid=BRequest::getInt('parent');
		$item->parent=$parentid;
		$r=parent::itemsetfields($item);
		return $r;
		}
	}