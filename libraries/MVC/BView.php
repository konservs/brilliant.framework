<?php
/**
 * Basic view class
 *
 * Author: Andrii Biriev
 */
namespace Brilliant\MVC;

use Brilliant\Log\BLog;
use Brilliant\HTTP\BBrowserUseragent;

class BView {
	public $paths;
	public $componentname;
	public $viewname;
	/**
	 * @var BController
	 */
	public $controller;
	public $templatename;

	/**
	 * Simple constructor
	 */
	public function __construct() {
		$this->paths = array();
		$this->controller = NULL;
	}
	//====================================================
	// Init the view: detect user client type and add
	// some paths to seek
	//====================================================
	public function init() {
		return true;
	}
	//====================================================
	//
	//====================================================
	public function addPathes() {
		$this->AddPath(BTEMPLATESPATH . $this->templatename . DIRECTORY_SEPARATOR);
		$this->AddPath(BTEMPLATESPATH . 'default' . DIRECTORY_SEPARATOR);
	}

	/**
	 * Set title
	 * @param $value
	 */
	public function setTitle($value) {
		if (isset($this->controller)) {
			$this->controller->title = $value;
		}
	}

	/**
	 * Set status (403 | 404 | 500)
	 */
	public function setStatus($value) {
		if (isset($this->controller)) {
			$this->controller->status = $value;
		}
	}
	//====================================================
	//
	//====================================================
	public function setcache($cachecontrol, $cachetime = 3600) {
		if (isset($this->controller)) {
			$this->controller->cachecontrol = $cachecontrol;
			$this->controller->cachetime = $cachetime;
		}
	}
	//====================================================
	//
	//====================================================
	public function setLocation($url, $time = 0) {
		if (DEBUG_MODE) {
			BLog::addToLog('[BView] setLocation(' . $url . ',' . $time . ')');
		}
		if (isset($this->controller)) {
			$this->controller->locationurl = $url;
			$this->controller->locationtime = $time;
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	public function setLastModified($value) {
		if (!isset($this->controller)) {
			return false;
		}
		if (empty($value)) {
			return false;
		}
		if (empty($this->controller->modified)) {
			$this->controller->modified = $value;
			return true;
		}
		$interval = date_diff($this->controller->modified, $value);
		if ($interval->invert == 0) {
			$this->controller->modified = $value;
		}
		return true;
	}

	/**
	 * @param $name
	 * @param $content
	 * @param string $http_equiv
	 */
	public function addMeta($name, $content, $http_equiv = '') {
		if (isset($this->controller)) {
			$this->controller->meta[] = array('name' => $name, 'http_equiv' => $http_equiv, 'content' => $content);
		}
	}

	/**
	 * Add external CSS file or internal style.
	 *
	 * @param $name
	 * @param string $media
	 * @param string $data
	 * @return bool
	 */
	public function addCss($name, $media = '', $data = '') {
		$lnk = array();
		$lnk['rel'] = 'stylesheet';
		$lnk['href'] = $name;
		$lnk['data'] = $data;
		$lnk['type'] = 'text/css';
		if (!empty($media)) {
			$lnk['media'] = $media;
		}
		return $this->addLink($lnk);
	}

	/**
	 * Add head link tag
	 *
	 * @param $array
	 * @return bool
	 */
	public function addLink($array) {
		if (!isset($this->controller)) {
			return false;
		}
		$this->controller->link[] = $array;
		return true;
	}

	/**
	 * @param $file
	 * @param string $src
	 * @param int $priority
	 * @return bool
	 */
	public function addJS($file, $src = '', $priority = 100) {
		if (!isset($this->controller)) {
			return false;
		}
		$this->controller->js[] = array('file' => $file, 'src' => $src, 'priority' => $priority);
		return true;
	}

	/**
	 * Add frameform declaration
	 *
	 * @param string $alias the framework alias
	 * @return boolean true if ok
	 */
	public function useFramework($alias) {
		if (!isset($this->controller)) {
			return false;
		}
		$this->controller->frameworks[$alias] = $alias;
		return true;
	}

	/**
	 * Load JS file into head
	 *
	 * @param string $file
	 * @param int $priority
	 * @return boolean
	 */
	public function loadJS($file, $priority = 100) {
		if (!isset($this->controller)) {
			if (DEBUG_MODE) {
				BLog::addToLog('[MVC.View]: Could not load js file, because controller is empty!', LL_ERROR);
			}
			return false;
		}
		if (!file_exists($file)) {
			if (DEBUG_MODE) {
				BLog::addToLog('[MVC.View]: Could not load js file (' . $file . '), because it does not exists!', LL_ERROR);
			}
			return false;
		}
		$src = file_get_contents($file);
		$this->controller->js[] = array('file' => '', 'src' => $src, 'priority' => $priority);
	}

	/**
	 * Load CSS file into head
	 *
	 * @param string $file filename
	 * @param int $priority priority of the loaded file. The files
	 * with less priority will be loaded before
	 * @return boolean true if ok
	 */
	public function loadCSS($file, $priority = 100) {
		if (!isset($this->controller)) {
			if (DEBUG_MODE) {
				BLog::addToLog('[MVC.View]: Could not load css file, because controller is empty!', LL_ERROR);
			}
			return false;
		}
		if (!file_exists($file)) {
			if (DEBUG_MODE) {
				BLog::addToLog('[MVC.View]: Could not load css file, because it does not exists!', LL_ERROR);
			}
			return false;
		}
		$src = file_get_contents($file);
		if (!empty($src)) {
			$this->addCSSDeclaration($src);
		}
		return true;
	}

	/**
	 * Add style
	 *
	 * @param string $style
	 * @return boolean
	 */
	public function addCSSDeclaration($style) {
		if (!isset($this->controller)) {
			return false;
		}
		$this->controller->style[] = $style;
		return true;
	}

	/**
	 * Add breadcrumbs element.
	 *
	 * @param $url
	 * @param $name
	 * @param bool $active
	 * @param string $class
	 * @param array $children
	 * @return bool
	 */
	public function breadcrumbs_add($url, $name, $active = true, $class = '', $children = array()) {
		if (!isset($this->controller)) {
			return false;
		}
		$this->controller->breadcrumbs[] = (object)array('url' => $url, 'name' => $name, 'active' => $active, 'class' => $class, 'children' => $children);
		return true;
	}

	/**
	 * Add breadcrumbs homepage element
	 */
	public function breadcrumbs_add_homepage() {
		$brouter = BRouter::getInstance();
		return $this->breadcrumbs_add('//' . $brouter->generateurl('mainpage', BLang::$langcode, array('view' => 'mainpage')), BLang::_('BREADCRUMBS_HOMEPAGE'),//'vidido.ua'
			true, 'homepage');
	}

	/**
	 * Add breadcrumbs user dashboard
	 */
	public function breadcrumbs_add_userdashboard() {
		$brouter = BRouter::getInstance();
		return $this->breadcrumbs_add('//' . $brouter->generateurl('users', BLang::$langcode, array('view' => 'dashboard')), BLang::_('USERS_DASHBOARD_HEADING'), true);
	}

	/**
	 * Add the layout path
	 *
	 * @param string $dir
	 */
	public function AddPath($dir) {
		$this->paths[] = $dir;
	}

	/**
	 *
	 * @return string HTML of the redirect
	 */
	public function renderredirect() {
		$fn = BTEMPLATESPATH . $this->templatename . DIRECTORY_SEPARATOR . 'redirect.php';
		if (!file_exists($fn)) {
			$fn = BTEMPLATESPATH . 'default' . DIRECTORY_SEPARATOR . 'redirect.php';
		}
		if (!file_exists($fn)) {
			return 'Redirecting...';
		}

		ob_start();
		include $fn;
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * Rendering layout into string
	 *
	 * @param string $subName sub name, if absolute or template name ('users.login') if not.
	 * @param bool $absolute true means absolute name
	 * @return string
	 */
	public function templateLoad($subName = '', $absolute = false) {
		$this->addPathes();
		$suffix = BBrowserUseragent::getDeviceSuffix();
		//
		if ($absolute) {
			$fNames = array($subName . $suffix . '.php', $subName . '.d.php');
		} else {
			if (!empty($subName)) {
				$subName = '.' . $subName;
			}
			$fNames = array($this->componentname . '.' . $this->viewname . $subName . $suffix . '.php', $this->componentname . '.' . $this->viewname . $subName . '.d.php', $this->componentname . '.' . $this->viewname . $suffix . '.php', $this->componentname . '.' . $this->viewname . '.d.php');
		}
		$filename = '';
		$subfname = '';
		foreach ($this->paths as $fp) {
			if (!empty($filename)) {
				break;
			}
			foreach ($fNames as $fn) {
				if (DEBUG_MODE) {
					BLog::addToLog('[View] try to load template:' . $fp . $fn);
				}
				if (file_exists($fp . $fn)) {
					$filename = $fp . $fn;
					$subfname = $fn;
					break;
				}
			}
		}
		if (empty($filename)) {
			return '';
		}
		//Rendering template file into string...
		ob_start();
		include $filename;
		$html = ob_get_clean();
		if (DEBUG_MODE) {
			$tp = \Brilliant\HTTP\BRequest::GetInt('tp');
			if ($tp) {
				$html = '<div style="font-size: 10px; color: #ccc;">' . $subfname . '</div>' . $html;
			}
		}
		return $html;
	}

	/**
	 * Abstract function. Should be overloaded in all
	 * children
	 */
	public function generate($data) {
		var_dump($data);
	}
}
