<?php
/**
 * Sets of functions and classes to work with routes: Get the
 * URL and convert it to MVC, load component .
 * 
 * Get the MVC and convert into URL.
 * 
 * @author Andrii Biriev
 */
namespace Brilliant\CMS;

use Application\BRouter;
use Brilliant\BFactory;
use Brilliant\Log\BLog;
use Brilliant\Cache\BCache;
use Brilliant\SQL\BMySQL;
use Brilliant\HTTP\BBrowserUseragent;
use Brilliant\HTML\BHTML;

define('ROUTER_DEBUG',1);
define('CTYPE_HTML',1);
define('CTYPE_JSON',2);
define('CTYPE_XML',3);
define('CTYPE_PLAINHTML',4);//FOR HTML BLOCKS WITHOUT TEMPLATE!
define('CTYPE_DOCX',5);
define('CTYPE_CSV',6);
define('CTYPE_PDF',7);
define('CTYPE_REDIRECT301',10);
define('CTYPE_REDIRECT302',11);

if(!defined(DEBUG_PAGES_CACHE)){
	define(DEBUG_PAGES_CACHE,1);
	}
//bimport('log.general');

//Router Class
class BRouterBase{
	public $url;
	public $host;
	public $templatename='default';
	public $htmllogger=NULL;
	protected $components=array();
	protected static $starttime=0;
	protected static $instance=NULL;
	protected $ctype=CTYPE_HTML;
	protected $router=array();
	protected $positions=array();
	protected $rules=array();
	protected $soft_rules=array();
	protected $maincom=NULL;
	protected $langcode='';
	protected $redirectURL;
	/**
	 * Init microtime, fill some default values
	 */
	protected function init(){
		//time of construct...
		$starttime = explode(' ', microtime());
		self::$starttime = $starttime[1] + $starttime[0];
		//
		$this->router=array();
		}
	/**
	 * Get current page generation time. Using this for profiling.
	 * 
	 * @return int count of microseconds
	 */
	public static function page_time(){
		$mtime = explode(' ', microtime());
		$totaltime = $mtime[0] + $mtime[1] - self::$starttime;
		return $totaltime;
		}
	/**
	 * Get list of softmodules by components. Parse components and
	 * return list.
	 */
	public function softmodules_list(){
		$list=array();
		foreach($this->components as $com){
			$controller=$this->component_load($com);
			if(empty($controller)){
				continue;
				}
			$clist=$controller->softmodules_list();
			$list=array_merge($list,$clist);
			}
		return $list;
		}
	/**
	 * Very useful function, often usings it in URLs parsing...
	 * 
	 * @param string $str string - the part of URL
	 * @return int preffix, if it exists
	 */
	protected function getIntPrefix($str){
		$a=(int)substr($str,0,strpos($str,'-'));
		return $a;
		}
	/**
	 * Very useful function, often usings it in URLs parsing...
	 * 
	 * @param string $str string - the part of URL
	 * @return int suffix, if it exists
	 */
	protected function getIntSuffix($str){
		$a=explode('-',$str);
		$b=(int)$a[count($a)-1];
		return $b;
		}
	/**
	 * Get the count of components in current position
	 * 
	 * @param string $pos template position
	 * @return int count of components in current position
	 */
	public function countcomponents($pos){
		return isset($this->positions[$pos])?count($this->positions[$pos]):0;
		}
	//====================================================
	//
	//====================================================
	public function addadminmenu(){
		$rules=array(
			(object)array(
				'com' => 'admin',
				'position' => 'adminmenu',
				'segments' => array('view'=>'adminmenu')
				),
			(object)array(
				'com' => 'admin',
				'position' => 'userpanel',
				'segments' => array('view'=>'userpanel','uid')
				),
				);
		$this->rules=array_merge($this->rules,$rules);
		}
	//====================================================
	//
	//====================================================
	public function parse_adminurl($f_path){
		$this->templatename='admin';
		$admincomname='admin';
		bimport('adminusers.general');
		$badminusers=BAdminUsers::getInstance();
		$segments=array();
		$me=$badminusers->get_logged_user();
		//Admin login
		if($f_path[0]=='login'){
			$segments['view']='login';
			$this->rules[]=(object)array(
				'com'=>'admin',
				'position'=>'content',
				'segments'=>$segments
				);
			return true;
			}
		//Check login...
		if(empty($me)){
			$this->ctype=CTYPE_REDIRECT302;
			$this->redirectURL='//admin.'.BHOSTNAME.'/login?redirect='.base64_encode($_SERVER['REQUEST_URI']);
			return true;
			}
		//Admin dashboard
		if((count($f_path)==1)&&($f_path[0]=='')){
			$segments['view']='dashboard';
			}
		//Logout...
		elseif($f_path[0]=='logout'){
			$segments['view']='logout';
			}
		//News...
		elseif($f_path[0]=='news'){
			$admincomname='admin_news';
			if($f_path[1]=='cats'){
				$segments['view']='cats';
				}
				if($f_path[1]=='cats' && $f_path[2]=='new'){
					$segments['view']='cat_new';
				}
				elseif($f_path[1]=='cats' && is_numeric($f_path[2])){
					$segments['view']='cat';
					$segments['id']=$f_path[2];
				}
				elseif($f_path[1]=='cats' && $f_path[2]=='action.json'){
					$segments['view']='cats_status_json';
					$this->ctype=CTYPE_JSON;
				}
				elseif($f_path[1]=='cats' && $f_path[2]=='ordering.json'){
					$segments['view']='cats_ordering_json';
					$this->ctype=CTYPE_JSON;
				}
				elseif($f_path[1]=='cats' && $f_path[2]=='refresh'){
					$segments['view']='cats_refresh';
				}
				elseif($f_path[1]=='cats' && $f_path[2]=='delete'){
					$segments['view']='cats_delete';
				}
			elseif($f_path[1]=='articles'){
				$segments['view']='articles';
				}
				if($f_path[1]=='articles' && $f_path[2]=='new'){
					$segments['view']='article_new';
				}
				elseif($f_path[1]=='articles' && is_numeric($f_path[2])){
					$segments['view']='article';
					$segments['id']=(int)$f_path[2];
				}
				elseif($f_path[1]=='articles' && $f_path[2]=='status.json'){
					$segments['view']='article_status_json';
					$this->ctype=CTYPE_JSON;
				}
				elseif($f_path[1]=='articles' && $f_path[2]=='filter.json'){
					$segments['view']='articles_filter_json';
					$this->ctype=CTYPE_JSON;
				}
			elseif($f_path[1]=='fileupload.json'){
				$segments['view']='fileupload_json';
				$this->ctype=CTYPE_JSON;
				}
			elseif($f_path[1]=='tempupload.json'){
				$segments['view']='tempupload_json';
				$this->ctype=CTYPE_JSON;
				}
			elseif($f_path[1]=='imgupload.json'){
				$segments['view']='imgupload_json';
				$this->ctype=CTYPE_JSON;
				}
			}
		//Admin users
		elseif($f_path[0]=="adminusers"){
			$segments['branch']='adminusers';
			array_shift($f_path);
			if($f_path[0]=='new'){
				$segments['view']='adminusersnew';
				}
			elseif($f_path[0]=='all'){
				$segments['view']='adminusers';
				}
			elseif($f_path[0]=='user'&&is_numeric($f_path[1])){
				$segments['view']='adminuserssingle';
				$segments['id']=$f_path[1];
				}
			elseif($f_path[0]=='groups'&&is_numeric($f_path[1])){
				$segments['view']='adminusersgroup';
				$segments['id']=$f_path[1];
				}
			elseif($f_path[0]=='groups'&&$f_path[1]=='delete'){
				$segments['view']='adminusersgroups_delete';
				}
			elseif($f_path[0]=='groups'&&$f_path[1]=='new'){
				$segments['view']='adminusersgroupnew';
				}
			elseif($f_path[0]=='groups'){
				$segments['view']='adminusersgroups';
				}
			}
		//Admin users
		elseif($f_path[0]=='htmlblocks'&&is_numeric($f_path[1])){
			$segments['view']='htmlblock';
			$segments['id']=$f_path[1];
		}elseif($f_path[0]=="htmlblocks"&&$f_path[1]=='new'){
			$segments['view']='htmlblocknew';
		}elseif($f_path[0]=="htmlblocks"){
			$segments['branch']='htmlblocks';
			array_shift($f_path);
			if($f_path[0]==''){
				$segments['view']='htmlblocks_all';
				}
			elseif($f_path[0]=='new'){
				$segments['view']='htmlblocks_new';
				}
			elseif($f_path[0]=='user'&&is_numeric($f_path[1])){
				$segments['view']='htmlblocks_edit';
				$segments['id']=$f_path[1];
				}
			}
		//IpBan
		elseif($f_path[0]=="ipban"){
			$segments['branch']='ipban';
			array_shift($f_path);
			$segments['view']='ipban';
			}
		//Language
		elseif($f_path[0]=='languages'){
			if($f_path[1]=='save'){
				$segments['view']='langsave';
				}else{
				$segments['view']='langall';
				}
			}
		//Rubrics
		elseif($f_path[0]=='rubrics'&&is_numeric($f_path[1])){
			$segments['view']='rubricssingle';
			$segments['id']=$f_path[1];
			}
		elseif($f_path[0]=='rubrics'&&$f_path[1]=='new'){
			$segments['view']='rubricsnew';
			}
		elseif($f_path[0]=='rubrics'){	
			$segments['view']='rubricsall';
			}
		elseif($f_path[0]=='subdomains'&&$f_path[1]=='new'){
			$segments['view']='subdomainnew';
			}
		elseif($f_path[0]=='subdomains'&&($f_path[1]!='')){
			$segments['view']='subdomain';
			$segments['subdomain']=$f_path[1];
			}
		elseif($f_path[0]=='subdomains'){
			$segments['view']='subdomains';
			}
		elseif($f_path[0]=='configuration'){
			$segments['view']='configuration';
			}
		elseif($f_path[0]=='users'){
			if($f_path[1]=='filter.json'){
				$segments['view']='users_filter_json';
				$this->ctype=CTYPE_JSON;
				}
			elseif((is_numeric($f_path[1]))&&($f_path[2]=='iphistory')){
				$segments['view']='user_iphistory';
				$segments['id']=$f_path[1];
				}
			elseif((is_numeric($f_path[1]))&&($f_path[2]=='fieldshistory')){
				$segments['view']='user_fieldshistory';
				$segments['id']=$f_path[1];
				}
			elseif(is_numeric($f_path[1])){
				$segments['view']='user';
				$segments['id']=$f_path[1];
				}
			elseif($f_path[1]=='new'){
				$segments['view']='usernew';
				}
			else{
				$segments['view']='users';
				}
		}elseif($f_path[0]=='softmodules'){
			if(($f_path[1]=='general')||($f_path[1]=='other')||($f_path[1]=='news')||($f_path[1]=='blogs')||($f_path[1]=='affiche')||($f_path[1]=='quizzes')||($f_path[1]=='contests')){
				$segments['view']='softmodules_group';
				$segments['group']=$f_path[1];
				}
			elseif($f_path[1]=='page'&&is_numeric($f_path[2])){
				$segments['view']='softmodules_page';
				$segments['id']=$f_path[2];
			}elseif($f_path[1]=='modules'&&is_numeric($f_path[2])){
				$segments['view']='softmodules_module';
				$segments['id']=$f_path[2];
			}elseif($f_path[1]=='modules'&&$f_path[2]=='delete'&&is_numeric($f_path[3])){
				$segments['view']='softmodules_module_delete';
				$segments['id']=$f_path[3];

			}elseif($f_path[1]=='modules'&&$f_path[2]=='new'){
				$segments['view']='softmodules_modulenew';
			}else{
				$segments['view']='softmodules_mainpage';
			}
		}
		//-----------------------------------------------------------------
		// Regions, Cities, Districts, Streets, etc.
		//-----------------------------------------------------------------
		elseif($f_path[0]=='vidido'){
			//Cities list
			if(empty($f_path[1])){
				$segments['branch']='vidido';
				$segments['view']='vidido_ads';
				}
			}
		//-----------------------------------------------------------------
		//
		//-----------------------------------------------------------------
		elseif($f_path[0]=='logs'){
			$segments['branch']='adminlogs';
			$segments['view']='adminlogs';
			}
		//-----------------------------------------------------------------
		//
		//-----------------------------------------------------------------
		elseif($f_path[0]=='systeminfo.json'){
			$segments['branch']='systeminfo';
			$segments['view']='systeminfo';
			$this->ctype=CTYPE_JSON;
			}
		//-----------------------------------------------------------------
		// Tags
		//-----------------------------------------------------------------
		elseif($f_path[0]=='tags'){
			$admincomname='admin_tags';
			if($f_path[1]=='tags'){
				$segments['view']='tags';
				}
			if($f_path[1]=='tags' && $f_path[2]=='new'){
				$segments['view']='tag_new';
				}
			elseif($f_path[1]=='tags' && is_numeric($f_path[2])){
				$segments['view']='tag';
				$segments['id']=$f_path[2];
				}
			elseif($f_path[1]=='tags' && $f_path[2]=='filter.json'){
				$segments['view']='tags_filter_json';
				$this->ctype=CTYPE_JSON;
				}
			}
		//-----------------------------------------------------------------
		// Blogs, Articles, Topics, Authors
		//-----------------------------------------------------------------
		elseif($f_path[0]=='blogs'){
			$admincomname='admin_blogs';
			if($f_path[1]=='authors'){
				$segments['view']='authors';
				}
				if($f_path[1]=='authors' && $f_path[2]=='new'){
					$segments['view']='author_new';
				}
				elseif($f_path[1]=='authors' && is_numeric($f_path[2])){
					$segments['view']='author';
					$segments['id']=$f_path[2];
				}elseif($f_path[1]=='authors' && $f_path[2]=='action.json'){
					$this->ctype=CTYPE_JSON;
					$segments['view']='authors_status_json';
				}
				elseif($f_path[1]=='authors' && $f_path[2]=='filter.json'){
					$segments['view']='authors_filter_json';
					$this->ctype=CTYPE_JSON;
				}
			elseif($f_path[1]=='topics'){
				$segments['view']='cats';
				}
				if($f_path[1]=='topics' && $f_path[2]=='new'){
					$segments['view']='cat_new';
				}
				elseif($f_path[1]=='topics' && is_numeric($f_path[2])){
					$segments['view']='cat';
					$segments['id']=$f_path[2];
				}
				elseif($f_path[1]=='topics' && $f_path[2]=='action.json'){
					$segments['view']='cats_status_json';
					$this->ctype=CTYPE_JSON;
				}
				elseif($f_path[1]=='topics' && $f_path[2]=='ordering.json'){
					$segments['view']='cats_ordering_json';
					$this->ctype=CTYPE_JSON;
				}
				elseif($f_path[1]=='topics' && $f_path[2]=='delete'){
					$segments['view']='cats_delete';
				}
				elseif($f_path[1]=='topics' && $f_path[2]=='refresh'){
					$segments['view']='cats_refresh';
				}
			elseif($f_path[1]=='articles'){
				$segments['view']='articles';
				}
				if($f_path[1]=='articles' && $f_path[2]=='new'){
					$segments['view']='article_new';
				}
				elseif($f_path[1]=='articles' && is_numeric($f_path[2])){
					$segments['view']='article';
					$segments['id']=$f_path[2];
				}
				elseif($f_path[1]=='articles' && $f_path[2]=='action.json'){
					$segments['view']='articles_status_json';
					$this->ctype=CTYPE_JSON;
				}
			}
		//-----------------------------------------------------------------
		// Affiche, Events, Places, Categories
		//-----------------------------------------------------------------
		elseif($f_path[0]=='affiche'){
			$admincomname='admin_affiche';
			if($f_path[1]=='events'){
				$segments['view']='events';
				}
				if($f_path[1]=='events' && $f_path[2]=='new'){
					$segments['view']='event_new';
				}
				elseif($f_path[1]=='events' && is_numeric($f_path[2])){
					$segments['view']='event';
					$segments['id']=$f_path[2];
				}
			elseif($f_path[1]=='places'){
				$segments['view']='places';
				}
				if($f_path[1]=='places' && $f_path[2]=='new'){
					$segments['view']='place_new';
				}
				elseif($f_path[1]=='places' && is_numeric($f_path[2])){
					$segments['view']='place';
					$segments['id']=$f_path[2];
				}
			elseif($f_path[1]=='cats'){
				$segments['view']='cats';
				}
				if($f_path[1]=='cats' && $f_path[2]=='new'){
					$segments['view']='cat_new';
				}
				elseif($f_path[1]=='cats' && is_numeric($f_path[2])){
					$segments['view']='cat';
					$segments['id']=$f_path[2];
				}
				elseif($f_path[1]=='cats' && $f_path[2]=='action.json'){
					$segments['view']='cats_status_json';
					$this->ctype=CTYPE_JSON;
				}
				elseif($f_path[1]=='cats' && $f_path[2]=='ordering.json'){
					$segments['view']='cats_ordering_json';
					$this->ctype=CTYPE_JSON;
				}
				elseif($f_path[1]=='cats' && $f_path[2]=='refresh'){
					$segments['view']='cats_refresh';
				}
				elseif($f_path[1]=='cats' && $f_path[2]=='delete'){
					$segments['view']='cats_delete';
				}
			}
		//-----------------------------------------------------------------
		// Polls Questions
		//-----------------------------------------------------------------
		elseif($f_path[0]=='polls'){
			$admincomname='admin_polls';
			if($f_path[1]=='polls'){
				$segments['view']='polls';
				}
				if($f_path[1]=='polls' && $f_path[2]=='new'){
					$segments['view']='poll_new';
				}
				elseif($f_path[1]=='polls' && is_numeric($f_path[2])){
					$segments['view']='poll';
					$segments['id']=$f_path[2];
				}
				elseif($f_path[1]=='polls' && $f_path[2]=='action.json'){
					$segments['view']='poll_action_json';
					$this->ctype=CTYPE_JSON;
				}
			elseif($f_path[1]=='poll_answers'){
					$segments['view']='poll_answers';
				}
			}
		//-----------------------------------------------------------------
		// Quizzes
		//-----------------------------------------------------------------
		elseif($f_path[0]=='quizzes'){
			$admincomname='admin_quizzes';
			if($f_path[1]=='quizzes'){
				$segments['view']='quizzes';
				}
			if($f_path[1]=='filter.json'){
				$segments['view']='quizz_filter_json';
				$this->ctype=CTYPE_JSON;
				}
			elseif($f_path[1]=='quizzes' && $f_path[2]=='new'){
				$segments['view']='quizz_new';
				}
			elseif($f_path[1]=='quizzes' && is_numeric($f_path[2])){
				$segments['view']='quizz';
				$segments['id']=$f_path[2];
				}
			elseif($f_path[1]=='participants'){
				$segments['view']='participants';
			}
		}
		//-----------------------------------------------------------------
		// Contests
		//-----------------------------------------------------------------
		elseif($f_path[0]=='contests'){
			$admincomname='admin_contests';
			if($f_path[1]=='contests'){
				$segments['view']='contests';
				}
			if($f_path[1]=='answers'){
				$segments['view']='answers';
				}
			if($f_path[1]=='answers' && $f_path[2]=='action.json'){
				$segments['view']='answers_status_json';
				$this->ctype=CTYPE_JSON;
				}
			if($f_path[1]=='contests' && $f_path[2]=='new'){
				$segments['view']='contest_new';
				}
			if($f_path[1]=='contests' && is_numeric($f_path[2])){
				$segments['view']='contest';
				$segments['id']=$f_path[2];
				}
			}
		//-----------------------------------------------------------------
		// Menu
		//-----------------------------------------------------------------
		elseif($f_path[0]=='menus'){
			$admincomname='admin_menus';
			if($f_path[1]=='menu'){
				$segments['view']='menu';
			}
			elseif($f_path[1]=='menuitems'){
				$segments['view']='menuitems';
				if($f_path[1]=='menuitems' && $f_path[2]=='new'){
					$segments['view']='menuitem_new';
				}
				elseif($f_path[1]=='menuitems' && is_numeric($f_path[2])){
					$segments['view']='menuitem';
					$segments['id']=$f_path[2];
				}
				elseif($f_path[1]=='menuitems' && $f_path[2]=='ordering.json'){
					$segments['view']='menuitems_ordering_json';
					$this->ctype=CTYPE_JSON;
				}
				elseif($f_path[1]=='menuitems' && $f_path[2]=='refresh'){
					$segments['view']='menuitems_refresh';
				}
				elseif($f_path[1]=='menuitems' && $f_path[2]=='delete'){
					$segments['view']='menuitems_delete';
				}
			}
		}
		//-----------------------------------------------------------------
		// Tickets
		//-----------------------------------------------------------------
		elseif($f_path[0]=='tickets'){
			$admincomname='admin_tickets';
			if($f_path[1]=='tickets'){
				$segments['view']='tickets';
			}
		}
		//-----------------------------------------------------------------
		// Others
		//-----------------------------------------------------------------
		elseif($f_path[0]=='media'){
			if($f_path[1]=='api.json'){
				$segments['view']='media_api_json';
				$this->ctype=CTYPE_JSON;
				}
			if($f_path[1]=='manager'){
				$segments['view']='media_manager';
				}
			}
		if(empty($segments)){
			return false;
			}
		$this->addadminmenu();
		$this->rules[]=(object)array(
			'com'=>$admincomname,
			'position'=>'content',
			'segments'=>$segments
			);
		return true;
		}
	//====================================================
	//
	//====================================================
	public function generate_adminurl($segments){
		$URL='admin.'.BHOSTNAME.'/';
		if(!empty($segments['branch'])) $URL.=$segments['branch'].'/';
		switch($segments['view']){
			case 'login':
				$URL.='login';
				if(!empty($segments['url'])) $URL.='?redirect='.$segments['url'];
				break;
			case 'logout':
				$URL.='logout';
				break;
			case 'adminusers':				
				$URL.='all/';
				break;
			case 'adminlogs':				
				$URL.='logs/';
				break;
			case 'systeminfo':				
				$URL.='systeminfo.json';
				break;
			case 'adminuserssingle':
				$URL.='user/'.$segments['id'];
				break;
			case 'adminusersgroups':
				$URL.='groups/';
				break;
			case 'adminusersgroup':
				$URL.='groups/'.$segments['id'];
				break;
			case 'adminusersgroupnew':
				$URL.='groups/new';
				break;
			case 'adminusersnew':
				$URL.='new/';
				break;
			case 'langall':
				$URL.='languages/';
				if(!empty($_SERVER['QUERY_STRING'])) $URL.='?'.$_SERVER['QUERY_STRING'];
				break;
			case 'langsave':
				$URL.='languages/save';
				break;
			case 'rubricsall':
				$URL.='rubrics/';
				break;
			case 'rubricssingle':
				$URL.='rubrics/'.$segments['id'];
				break;
			case 'rubricsnew':
				$URL.='rubrics/new';
				break;
			case 'subdomains':
				$URL.='subdomains/';
				break;
			case 'subdomain':
				$URL.='subdomains/'.$segments['subdomain'];
				break;
			case 'subdomainnew':
				$URL.='subdomains/new';
				break;
			case 'newscats':
				$URL.='cats/';
				break;
			case 'newscat':
				$URL.='cats/'.$segments['id'];
				break;
			case 'newscatnew':
				$URL.='cats/new';
				break;
			case 'articles':
				$URL.='articles/';
				if(!empty($_SERVER['QUERY_STRING'])) $URL.='?'.$_SERVER['QUERY_STRING'];
				break;
			case 'article':
				$URL.='articles/'.$segments['id'];
				break;
			case 'articlenew':
				$URL.='articles/new';
				break;
			case 'configuration':
				$URL.='configuration/';
				break;
			case 'softmodules_mainpage':
				$URL.='softmodules/';
				break;
			case 'softmodules_page':
				$URL.='softmodules/page/'.$segments['id'];
				break;
			case 'softmodules_module':
				$URL.='softmodules/modules/'.$segments['id'];
				break;
			case 'softmodules_modulenew':
				$URL.='softmodules/modules/new';
				if(!empty($_SERVER['QUERY_STRING'])) $URL.='?'.$_SERVER['QUERY_STRING'];
				break;
			case 'menu':
				$URL.='menu/';
				break;
			case 'menuitems':
				$URL.='menu/menuitems/'.$segments['id'];
				break;
			case 'menu_ordering_json':
				$URL.='menu/menuitems/ordering.json';
				break;
			case 'menuite_mnew':
				$URL.='menu/menuitem/new';
				break;
			case 'menunew':
				$URL.='menu/new';
				break;
			case 'user':
				$URL.='users/'.$segments['id'];
				break;
			case 'users':
				$URL.='users/';
				if(!empty($_SERVER['QUERY_STRING'])) $URL.='?'.$_SERVER['QUERY_STRING'];
				break;
			}
		return $URL;
		}
	//====================================================
	// For extended routers...
	//====================================================
	private function checkRule($str,$mask,&$items){
		if(ROUTER_DEBUG){
			BLog::addToLog('[Router]: Extended rule! mask="'.$mask.'" url="'.$str.'"');
			}
		$r=preg_match($mask,$str,$a);
		if(ROUTER_DEBUG){
			BLog::addToLog('[Router]: Result='.$r);
			BLog::addToLog('[Router]: Data='.var_export($a,true));
			}
		if(!$r)return false;
		for($i=1; $i<count($a); $i++)
			$items[$i-1]=$a[$i];
		return true;
		}
	//====================================================
	//
	//====================================================
	public function softmodulesget($alias){
		}
	//====================================================
	//
	//====================================================
	public function generateURL($component,$lang,$segments){//if it has ->return URL; else return false;
		}
	//====================================================
	//
	//====================================================
	public function redirect301($URL){
		if(empty($URL)){
			die('UNKNOWN ERROR');
			}
		if(DEBUG_MODE){
			echo('301 redirect: <a href="'.$URL.'">'.$URL.'</a>');
			return;
			}
		header($_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently');
		header('Location: '.$URL);
		}
	//====================================================
	//
	//====================================================
	public function redirect302($URL){
		if(empty($URL)){
			die('UNKNOWN ERROR');
			}
		if(DEBUG_MODE){
			echo('302 redirect: <a href="'.$URL.'">'.$URL.'</a>');
			return;
			}
		header($_SERVER['SERVER_PROTOCOL'].' 302 Found');
		header('Location: '.$URL);
		}
	//====================================================
	//
	//====================================================
	public function parseurl($URL,$host){
		}
	//====================================================
	//
	//====================================================
	public function getlastmod($URL,$host){
		//TODO some usefule things
		return new DateTime();
		$this->parseurl($URL,$host);
		$this->render_positions();
		
		$lastmod=NULL;
		foreach($this->rules as &$c){
			if(isset($c->modified)){
				if($c->modified instanceof DateTime){
					$last_modified=$c->modified;
					}else{
					$last_modified=new DateTime($c->modified);
					}
				if((!empty($lastmod))){
					$interval=date_diff($lastmod,$last_modified);
					}
				if((empty($lastmod))||($interval->invert==0)){
					$lastmod=$last_modified;
					}
				}
			}
		return $lastmod;		
		}
	//====================================================
	// Show error page
	//====================================================
	public function errorpage($page){
		$device=BBrowserUseragent::detectDevice();
		if($device==DEVICE_TYPE_MPHONE){
			$suffix='.m';
			}
		elseif($device==DEVICE_TYPE_TABLET){
			$suffix='.m';
			}
		else{
			$suffix='.d';
			}
		if(empty($this->templatename)){
			$this->templatename='default';
			}
		$fn=BTEMPLATESPATH.$this->templatename.DIRECTORY_SEPARATOR.'#error_'.$page.$suffix.'.php';
		if(!file_exists($fn)){
			$fn=BTEMPLATESPATH.$this->templatename.DIRECTORY_SEPARATOR.'#error_'.$page.'.d.php';
			}
		if((!file_exists($fn))&&($this->templatename!='default')){
			$fn=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'#error_'.$page.$suffix.'.php';
			}
		if((!file_exists($fn))&&($this->templatename!='default')){
			$fn=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'#error_'.$page.'.d.php';
			}
		//Outputing the template
		if(file_exists($fn)){
			include($fn);
			}else{
			echo('<h1>404</h1>');
			}
		if(!empty($this->htmllogger)){
			BLog::addToLog('[Router]: 404 page.',LL_ERROR);
			$this->htmllogger->print_html();
			}
		return false;
		}
	/**
	 * First function, that runs in router
	 */
	public function run($URL,$host=''){
		if(ROUTER_DEBUG){
			BLog::addToLog('[Router]: Router started! URL='.$URL.'; host='.$host);
			}
		//bimport('ip.ban');
		//$r=BIpBan::check();
		//if($r===false){
		//	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
		//	return $this->errorpage('ipban');
		//	}
		$this->url=$URL;
		$this->host=$host;

		$r=$this->parseurl($URL,$host);

		if(ROUTER_DEBUG){
			if($r){
				BLog::addToLog('[Router]: Rules successfully parsed.');
				}else{
				BLog::addToLog('[Router]: Error of parsing rules!',LL_ERROR);
				}
			}
		if($r===false){
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
			header('Content-type: text/html; charset=utf-8');
			return $this->errorpage('404');
			}
		if(ROUTER_DEBUG){
			BLog::addToLog('[Router]: rules:'.var_export($this->rules,true));
			}
		$this->render_positions();
		switch($this->ctype){
			case CTYPE_HTML:
				$this->generatepage_html();
				break;
			case CTYPE_PLAINHTML:
				$this->generatepage_plainhtml();
				break;
			case CTYPE_JSON:
				$this->generatepage_json();
				break;
			case CTYPE_XML:
				$this->generatepage_xml();
				break;
			case CTYPE_DOCX:
				$this->generatepage_docx();
				break;
			case CTYPE_CSV:
				$this->generatepage_csv();
				break;
			case CTYPE_PDF:
				$this->generatepage_pdf();
				break;
			case CTYPE_REDIRECT301:
				$this->redirect301($this->redirectURL);
				break;
			case CTYPE_REDIRECT302:
				$this->redirect302($this->redirectURL);
				break;
			default:
				echo('Unknown content type!');
				break;
			}
		}
	/**
	 * Create component controller object by component name...
	 * 
	 * @param type $cname component name
	 * @return null|\BController controller object
	 */
	public function component_load($cname){
		//Trying to include component controller file...
		$fn=BCOMPONENTSFRAMEWORKPATH.$cname.DIRECTORY_SEPARATOR.'controller.php';
		if(!file_exists($fn)){
			$fn=BCOMPONENTSAPPLICATIONPATH.$cname.DIRECTORY_SEPARATOR.'controller.php';
			}
		if(!file_exists($fn)){
			BLog::addToLog('[Router]: Could not load component ('.$cname.')!',LL_ERROR);
			return NULL;
			}
		require_once($fn);
		//Trying to create object of controller class...
		$class='Controller_'.$cname;
		if(!class_exists($class)){
			BLog::addToLog('[Router]: Could not find class ('.$class.')!',LL_ERROR);
			return NULL;
			}
		$controller=new $class();
		$controller->componentname=$cname;
		return $controller;
		}
	/**
	 * Get memcached URL by component / segments.
	 */
	public function getUrlCahceKey($com,$langcode,$suffix,$segments){
		$key='url:'.$com.':'.$langcode.$suffix;
		$seg='';
		if(is_array($segments)){
			foreach($segments as $k=>$v){
				if(is_object($v)){
					$v=json_encode($v);
					}
				$seg.=$k.'='.$v.',';
				}
			}
		$key.=':'.SHA1($seg);
		return $key;
		}
	/**
	 *
	 */
	public function render_component($component,$segments){
		$debug_pages_cache=defined('DEBUG_PAGES_CACHE')?DEBUG_PAGES_CACHE:1;
		if((CACHE_TYPE)&&($debug_pages_cache)){
			}
		}
	/**
	 * Render all positions.
	 */
	public function render_positions(){
		$debug_pages_cache=defined('DEBUG_PAGES_CACHE')?DEBUG_PAGES_CACHE:1;
		$bCache = BFactory::getCache();
		if(($bCache)&&($debug_pages_cache)){
			//Accumulating keys...
			$keys=array();
			$suffix=BBrowserUseragent::getDeviceSuffix();
			foreach($this->rules as &$c){
				$c->key=$this->getUrlCahceKey($c->com,$this->langcode,$suffix,$c->segments);
				$keys[]=$c->key;
				}
			//Multi-get from cache
			$list=$bCache->mget($keys);
			foreach($this->rules as &$c){
				if(($list[$c->key]!==false)&&($list[$c->key]!==NULL)){
					$c->status=$list[$c->key]->status;//200 | 403 | 404
					$c->output=$list[$c->key]->output;
					$c->title=$list[$c->key]->title;
					$c->meta=$list[$c->key]->meta;
					$c->link=$list[$c->key]->link;
					$c->js=$list[$c->key]->js;
					$c->style=$list[$c->key]->style;
					$c->frameworks=$list[$c->key]->frameworks;
					$c->breadcrumbs=$list[$c->key]->breadcrumbs;
					$c->modified=new DateTime($list[$c->key]->modified);
					$c->cachecontrol=$list[$c->key]->cachecontrol;
					$c->cachetime=$list[$c->key]->cachetime;
					$c->locationurl=$list[$c->key]->locationurl;
					$c->locationtime=$list[$c->key]->locationtime;
					$c->rendered=true;
					}
				}
			}
		//Rendering components, not loaded from cache...
		$tocache=array();
		foreach($this->rules as &$c){
			if(!$c->rendered){
				if(ROUTER_DEBUG){
					$view=isset($c->segments['view'])?$c->segments['view']:'';
					BLog::addToLog('[Router]: Rendering component ('.$c->com.(empty($view)?'':'.'.$view).')...');
					}
				$controller=$this->component_load($c->com);
				if(empty($controller)){
					continue;
					}
				$controller->templatename=$this->templatename;
				//Running component...
				$c->output=$controller->run($c->segments);
				$c->status=$controller->status;
				$c->title=$controller->title;
				$c->meta=$controller->meta;
				$c->link=$controller->link;
				$c->js=$controller->js;
				$c->style=$controller->style;
				$c->frameworks=$controller->frameworks;
				$c->breadcrumbs=$controller->breadcrumbs;
				$c->modified=empty($controller->modified)?NULL:$controller->modified->format('Y-m-d H:i:s');
				$c->cachecontrol=$controller->cachecontrol;
				$c->cachetime=$controller->cachetime;
				$c->locationurl=$controller->locationurl;
				$c->locationtime=$controller->locationtime;
				$c->rendered=true;
				//Caching the result, if necessary.
				if(($bCache)&&($debug_pages_cache)&&($c->cachecontrol)){
					$bCache->set($c->key,$c,$c->cachetime);
					}
				//Convert to object.
				$c->modified=$controller->modified;
				}
			}
		}	
	/**
	 * Generate final HTML.
	 */
	public function generatepage_html(){
		$bhtml=BHTML::getInstance();
		$status=200;
		//Forming page from blocks...
		$jsused=array();
		$cssused=array();
		foreach($this->rules as &$c){
			if(isset($this->positions[$c->position])){
				$this->positions[$c->position].=$c->output;
				}else{
				$this->positions[$c->position]=$c->output;
				}
			if($c->status!=200){
				$status=$c->status;
				}
			if(isset($c->locationurl)){
				$bhtml->setLocationUrl($c->locationurl,$c->locationtime);
				}
			if(isset($c->modified)){
				$bhtml->setLastModified($c->modified);
				}
			if(isset($c->title)){
				$bhtml->setTitle($c->title);
				}
			if(isset($c->meta)){
				foreach($c->meta as $meta){
					$bhtml->add_meta($meta['name'],$meta['content'],$meta['http_equiv']);
					}
				}
			if(isset($c->link)){
				foreach($c->link as $link){
					//Remove dublicates...
					$wasused=false;
					if($link['rel']=='stylesheet'){
						$wasused=isset($cssused[$link['href']]);
						$cssused[$link['href']]=1;
						}
					if(!$wasused){
						$bhtml->add_link($link);
						}
					}
				}
			if(isset($c->js)){
				foreach($c->js as $js){
					//Remove dublicates...
					$wasused=false;
					if(!empty($js['file'])){
						$wasused=isset($jsused[$js['file']]);
						$jsused[$js['file']]=1;
						}
					if(!$wasused){
						$bhtml->addJS($js['file'],$js['src'],$js['priority']);
						}
					}
				}
			if(isset($c->style)){
				foreach($c->style as $st){
					$bhtml->addCSSDeclaration($st);
					}
				}
			if(isset($c->frameworks)){
				foreach($c->frameworks as $framework){
					$bhtml->useFramework($framework);
					}
				}
			//Add breadcrumbs elements
			if(isset($c->breadcrumbs)){
				bimport('cms.breadcrumbs');
				$gbc=BGeneralBreadcrumbs::getInstance();
				foreach($c->breadcrumbs as $bc){
					$gbc->elements[]=$bc;
					}
				}
			}
		//Output the page
		header('Content-Type: text/html; charset=utf-8');
		if($status==403){
			header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
			}
		if($status==404){
			header($_SERVER['SERVER_PROTOCOL'].' 404 Page Not Found');
			}
		if($status==500){
			header($_SERVER['SERVER_PROTOCOL'].' 500 Server Error!');
			}
		$bhtml->headers_check();
		//Check for last-modified
		if((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))&&(isset($bhtml->last_modified))){
			$mod_since=new DateTime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			$interval=date_diff($bhtml->last_modified,$mod_since);
			if($interval->invert==0){
	        		header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
				return;//return nothing!
				}
			}
		if($this->templatename===NULL){
			$this->templatename='default';
			}
		//Detect device...
		$device=BBrowserUseragent::detectDevice();
		if($device==DEVICE_TYPE_MPHONE){
			$suffix='.m';
			}
		elseif($device==DEVICE_TYPE_TABLET){
			$suffix='.m';
			}else{
			$suffix='.d';
			}
		$fn=BTEMPLATESPATH.$this->templatename.DIRECTORY_SEPARATOR.'template'.$suffix.'.php';
		//
		if(!file_exists($fn)){
			$fn=BTEMPLATESPATH.$this->templatename.DIRECTORY_SEPARATOR.'template.d.php';
			}
		//
		if(!file_exists($fn)){
			$fn=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'template'.$suffix.'.php';
			}
		//
		if(!file_exists($fn)){
			$fn=BTEMPLATESPATH.'default'.DIRECTORY_SEPARATOR.'template.d.php';
			}

		//Starting render
		if(DEBUG_MODE){
			BLog::addToLog('[Router]: Rendering template...');
			}
		ob_start();
		include $fn;
		$tpl_str=ob_get_clean();
		$tpl_str=str_replace('{{head}}','',$tpl_str);
		if(DEBUG_MODE){
			BLog::addToLog('[Router]: Filling empty positions...');
			}
		$pattern='/{{position.(?P<position>\w+)*}}/';
		preg_match_all($pattern, $tpl_str, $matches);
		foreach($matches['position'] as $pos){
			if(!isset($this->positions[$pos])){
				$this->positions[$pos]='';
				}
			}
		if(DEBUG_MODE){
			BLog::addToLog('[Router]: Put content into positions...');
			}
		foreach($this->positions as $p=>$val){
			$tpl_str=str_replace('{{position:'.$p.'}}',$val,$tpl_str);
			}
		//Output debug information, if necessary.
		if(DEBUG_MODE){
			BLog::addToLog('[Router]: Generation time: '.sprintf('%7.7f',self::page_time()));
			BLog::addToLog('[Router]: MySQL queries:'.BMySQL::getQueriesCount());
			$qc=BCache::getQueriesCount();
			BLog::addToLog('[Router]: Cache queries: get-'.$qc['get'].' set-'.$qc['set'].' mset-'.$qc['mset'].' mget-'.$qc['mget'].' gc-'.$qc['gc']);
			BLog::addToLog('[Router]: Outputing the template...');
			}
		echo($tpl_str);
		BLog::addToLog('[Router]: All done!');
		if(!empty($this->htmllogger)){
			$this->htmllogger->print_html();
			}
		}
	/**
	 * Output final JSON
	 */
	public function generatepage_json(){
		$status=200;
		foreach($this->rules as &$c){
			$this->positions[$c->position]=$c->output;
			if($c->status!=200){
				$status=$c->status;
				}
			}
		header('Content-type: text/json');
		if($status==403){
			header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
			}
		if($status==404){
			header($_SERVER['SERVER_PROTOCOL'].' 404 Page Not Found');
			}
		if($status==500){
			header($_SERVER['SERVER_PROTOCOL'].' 500 Server Error!');
			}
		echo $this->positions['content'];	
		}
	/**
	 * Output final Plain HTML
	 */
	public function generatepage_plainhtml(){
		foreach($this->rules as &$c){
			$this->positions[$c->position]=$c->output;
			}
		header('Content-type: text/html');
		echo $this->positions['content'];	
		}
	/**
	 * Output final XML.
	 */
	public function generatepage_xml(){
		foreach($this->rules as &$c){
			$this->positions[$c->position]=$c->output;
			}
		header("Content-type: text/xml");
		echo $this->positions['content'];
		}
	/**
	 * Output final DOCX.
	 */
	public function generatepage_docx(){
		foreach($this->rules as &$c){
			$this->positions[$c->position]=$c->output;
			}
		header("Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
		echo $this->positions['content'];
		}
	/**
	 * Output final CSV.
	 */
	public function generatepage_csv(){
		foreach($this->rules as &$c){
			$this->positions[$c->position]=$c->output;
			}
		header("Content-type: text/csv");
		header("Content-Disposition: attachment;filename=file.csv");
		echo $this->positions['content'];
		}
	/**
	 * Output final PDF.
	 */
	public function generatepage_pdf(){
		foreach($this->rules as &$c){
			$this->positions[$c->position]=$c->output;
			}
		header("Content-type: application/pdf");
		echo $this->positions['content'];
		}

	}//End of router class
