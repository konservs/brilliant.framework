<?php
/**
 * Register success page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class View_users_register_success extends \Brilliant\MVC\BView {
	//========================================================
	// Process data, necessary for registration, set headers
	// and load template.
	//========================================================
	public function generate($data){
		$this->setTitle(BLang::_('USERS_REGISTER_HTML_TITLE'));
		$this->addMeta('description',BLang::_('USERS_REGISTER_METADESC'));
		return $this->templateLoad();
		}
	}
