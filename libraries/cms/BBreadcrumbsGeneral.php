<?php
namespace Brilliant\cms;
use Brilliant\cms\BBreadcrumbs;

/**
 * General class for inter-component breadcrumbs.
 *
 * @author Andrii Biriev
 */
class BBreadcrumbsGeneral extends BBreadcrumbs{
	use \Brilliant\BSingleton;
	/**
	 *
	 * @return string HTML
	 */
	public static function staticdraw(){
		if(!is_object(self::$instance)){
			return '';
			}
		self::$instance->draw();
		}
	}
