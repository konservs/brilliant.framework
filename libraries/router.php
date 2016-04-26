<?php
/**
 * Sets of functions and classes to work with routes: Get the
 * URL and convert it to MVC, load component
 * 
 * Get the MVC and convert into URL
 * 
 * @author Andrii Biriev
 * 
 * @copyright © Andrii Biriev, <a@konservs.com>
 */

bimport('cms.router');
bimport('log.general');

class BRouter extends BRouterBase{
	protected static $starttime=0;
	protected static $instance=NULL;
	protected $components=array('news','blogs','menu','htmlblocks','other','polls');
	protected $router=array();
	protected $positions=array();
	protected $rules=array();
	protected $soft_rules=array();
	protected $maincom=NULL;
	protected $langcode='';
	public $rubric=NULL; //Will use in "smart" menu items.
	public $newscat=NULL; //News category. Will use in "smart" menu items.
	public $templatename='default';
	public $frontendtemplate='default';
	/**
	 * Get logged user object. Helper function.
	 * 
	 * @return \BUser|NULL Logged user
	 */
	public function getLoggedUser(){
		bimport('users.general');
		$busers=BUsers::getInstance();
		return $busers->getLoggedUser();
		}
	/**
	 * Add some fixed rules - languages switch, etc.
	 */
	public function addfixedrules(){
		bimport('users.session');
		$session=BUsersSession::getInstanceAndStart();
		if(!empty($session)){
			bimport('users.general');
			$uid=$session->userid;
			}else{	
			$uid=NULL;
			}
		bimport('http.useragent');
		$device=BBrowserUseragent::detectDevice();

		if(($device!=DEVICE_TYPE_MPHONE)&&($device!=DEVICE_TYPE_TABLET)){
			$this->rules[]=(object)array(
				'com' => 'menu',
				'position' => 'headmenu',
				'segments' => array('view'=>'menu','id'=>2,'url'=>$this->host.$this->url),
				);
			$this->rules[]=(object)array(
				'com' => 'menu',
				'position' => 'mainmenu',
				'segments' => array('view'=>'menu','id'=>1,'url'=>$this->host.$this->url),
				);
			$this->rules[]=(object)array(
				'com' => 'news',
				'position' => 'newssearch',
				'segments' => array('view'=>'mod_search'),
				);
			$this->rules[]=(object)array(
				'com' => 'other',
				'position' => 'addbutton',
				'segments' => array('view'=>'addoffer'),
				);
			$this->rules[]=(object)array(
				'com' => 'menu',
				'position' => 'footermenu',
				'segments' => array('view'=>'menu','id'=>4,'url'=>$this->host.$this->url),
				);
			$this->rules[]=(object)array(
				'com' => 'news',
				'position' => 'footerphoto',
				'segments' => array('view'=>'mod_photo','style'=>'footer'),
				);
			$this->rules[]=(object)array(
				'com' => 'news',
				'position' => 'footervideo',
				'segments' => array('view'=>'mod_video','style'=>'footer'),
				);
			$this->rules[]=(object)array(
				'com' => 'htmlblocks',
				'position' => 'footerbanner',
				'segments' => array('view'=>'banner','id'=>9),
				);
			$this->rules[]=(object)array(
				'com' => 'menu',
				'position' => 'footermenunews',
				'segments' => array('view'=>'menu','id'=>5,'url'=>$this->host.$this->url),
				);
			$this->rules[]=(object)array(
				'com' => 'menu',
				'position' => 'footermenuabout',
				'segments' => array('view'=>'menu','id'=>6,'url'=>$this->host.$this->url),
				);
			$this->rules[]=(object)array(
				'com' => 'menu',
				'position' => 'footermenusocial',
				'segments' => array('view'=>'menu','id'=>7,'url'=>$this->host.$this->url),
				);
			}
		else{
			$this->rules[]=(object)array(
				'com' => 'menu',
				'position' => 'topmenu',
				'segments' => array('view'=>'menu','id'=>6,'url'=>$this->host.$this->url),
				);
			}
		$this->rules[]=(object)array(
			'com' => 'language',
			'position' => 'headlang',
			'segments' => array('view'=>'languagespanel','lang'=>BLang::$langcode,'url'=>$this->host.$this->url),
			);
		if(!empty($uid)){
			$this->rules[]=(object)array(
				'com' => 'users',
				'position' => 'userpanel',
				'segments' => array('view'=>'userpanel','uid'=>$uid),
				);
			}else{
			$this->rules[]=(object)array(
				'com' => 'users',
				'position' => 'userpanel',
				'segments' => array('view'=>'userpanel'),
				);
			}
		}
	/**
	 * Return list of news categories (for softmodules).
	 * Using this in admin panel
	 *
	 * @return array array of softmodules objects
	 */
	public function getsoftmodules_news(){
		$lnews=(object)array(
			'active'=>false,
			'name'=>BLang::_('ADMIN_SOFTMODULES_PAGE_NEWS'),
			'alias'=>'',
			'subalias'=>'',
			'children'=>array(),
			);
		//
		$lnews->children[]=(object)array(
			'active'=>true,
			'name'=>'Поиск',
			'alias'=>'news:search',
			'subalias'=>'',
			//'subname'=>BLang::_('ADMIN_SOFTMODULES_PAGE_CATNEWS'),
			'children'=>array(),
			);
		//
		$lnews->children[]=(object)array(
			'active'=>true,
			'name'=>'Фото',
			'alias'=>'news:photo',
			'subalias'=>'',
			//'subname'=>BLang::_('ADMIN_SOFTMODULES_PAGE_CATNEWS'),
			'children'=>array(),
			);
		//
		$lnews->children[]=(object)array(
			'active'=>true,
			'name'=>'Видео',
			'alias'=>'news:video',
			'subalias'=>'',
			//'subname'=>BLang::_('ADMIN_SOFTMODULES_PAGE_CATNEWS'),
			'children'=>array(),
			);
		//
		$lnews->children[]=(object)array(
			'active'=>true,
			'name'=>'Архив',
			'alias'=>'news:archive',
			'subalias'=>'',
			//'subname'=>BLang::_('ADMIN_SOFTMODULES_PAGE_CATNEWS'),
			'children'=>array(),
			);
		//
		$lnewscats=(object)array(
			'active'=>false,
			'name'=>'Новости: категории',
			'alias'=>'',
			'subalias'=>'',
			'children'=>array(),
			);
		//
		bimport('news.categories');
		$bnewsc=BNewsCategories::getInstance();
		if(empty($bnewsc)){
			return $lnews;
			}
		$tree=$bnewsc->getsimpletree(array('level','articles'),array('name'));
		foreach($tree as $value){
			$itm=(object)array(
				'active'=>true,
				'name'=>'Категория "'.$value->name.'"',
				'alias'=>'news:category:'.$value->id,
				'subname'=>'новости категории',
				'subalias'=>'news:newscat:'.$value->id,
				'children'=>array(),
				);
			$lnewscats->children[]=$itm;
			}
		return array($lnews,$lnewscats);
		}
	/**
	 * Return list of
	 * Using this in admin panel
	 *
	 * @return array array of softmodules objects
	 */
	public function getsoftmodules_blogs(){
		$lsm=(object)array(
			'active'=>true,
			'name'=>'Блоги: главная',
			'alias'=>'blogs:home',
			'subalias'=>'',
			'children'=>array(),
			);
		//
		$lsm->children[]=(object)array(
			'active'=>true,
			'name'=>'Блоги: поиск',
			'alias'=>'blogs:search',
			'subalias'=>'',
			'children'=>array(),
			);
		//
		$lsm->children[]=(object)array(
			'active'=>true,
			'name'=>'Блоги: темы',
			'alias'=>'blogs:categories',
			'subalias'=>'',
			'children'=>array(),
			);

		return $lsm;
		}

	/**
	 * Return list of
	 * Using this in admin panel
	 *
	 * @return array array of softmodules objects
	 */
	public function getsoftmodules_affiche(){
		$lsm=(object)array(
			'active'=>true,
			'name'=>'Афиша: главная',
			'alias'=>'affiche:home',
			'subalias'=>'',
			'children'=>array(),
			);
		return $lsm;
		}
	/**
	 * Return list of
	 * Using this in admin panel
	 *
	 * @return array array of softmodules objects
	 */
	public function getsoftmodules_quizzes(){
		$lsm=(object)array(
			'active'=>true,
			'name'=>'Викторины: главная',
			'alias'=>'quizzes:home',
			'subalias'=>'',
			'children'=>array(),
			);
		return $lsm;
		}
	/**
	 * Return list of
	 * Using this in admin panel
	 *
	 * @return array array of softmodules objects
	 */
	public function getsoftmodules_contests(){
		$lsm=(object)array(
			'active'=>true,
			'name'=>'Конкурсы: главная',
			'alias'=>'contests:home',
			'subalias'=>'',
			'children'=>array(),
			);
		return $lsm;
		}
	/**
	 * Return list of general pages (without some categories)..
	 *
	 * Using this in admin panel
	 *
	 * @return array array of softmodules objects
	 */
	public function getsoftmodules_general(){
		$lsm=(object)array(
			'active'=>true,
			'name'=>'Главная страница',
			'alias'=>'mainpage',
			'subalias'=>'',
			'children'=>array(),
			);
		return $lsm;
		}

	/**
	 * Return list of soft modules categories.
	 * Using this in admin panel to group softmodules.
	 *
	 * @return array array of softmodules groups
	 */
	public function getsoftmodulescats(){
		$children=array();
		$children['general']=(object)array(
			'alias'=>'news',
			'name'=>'Общие страницы',
			'url'=>'/softmodules/general/',
			);
		$children['news']=(object)array(
			'alias'=>'news',
			'name'=>'Новости',
			'url'=>'/softmodules/news/',
			);
		$children['blogs']=(object)array(
			'alias'=>'blogs',
			'name'=>'Блоги',
			'url'=>'/softmodules/blogs/',
			);
		$children['affiche']=(object)array(
			'alias'=>'affiche',
			'name'=>'Афиша',
			'url'=>'/softmodules/affiche/',
			);
		$children['quizzes']=(object)array(
			'alias'=>'quizzes',
			'name'=>'Викторины',
			'url'=>'/softmodules/quizzes/',
			);
		$children['contests']=(object)array(
			'alias'=>'contests',
			'name'=>'Конкурсы',
			'url'=>'/softmodules/contests/',
			);
		return $children;
		}
	/**
	 * Using in breadcrumbs & close buttons in admin panel.
	 */
	public function getsoftmodules_group($alias){
		$cats=$this->getsoftmodulescats();
		if(!isset($cats[$alias])){
			return NULL;
			}
		return $cats[$alias];
		}
	/**
	 * Using this in admin panel
	 *
	 * @return array array of softmodules objects
	 */
	public function getsoftmodules($alias){
		$list=array();
		$s='getsoftmodules_'.$alias;
		$res=$this->$s();
		if(is_array($res)){
			$list=array_merge($list,$res);
			}
		elseif(is_object($res)){
			$list[]=$res;
			}
		else{
			return false;
			}
		return $list;
		}
	/**
	 *
	 */
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
	/**
	 * Generate URL for help
	 */
	public function softmodulesget($alias){
		bimport('softmodules.general');
		$bsoftmodules=BSoftmodules::getInstance();
		$this->soft_rules=$bsoftmodules->get($alias);
		$this->rules=array_merge($this->rules,$this->soft_rules);
		}
	/**
	 *
	 */
	public function getmainurl($lang){
		$URL_main=BHOSTNAME.'/';
		if($lang!='ua'){
			$URL_main.=$lang.'/';
			}
		return $URL_main;
		}
	/**
	 *
	 */
	public function generate_newsurl($lang,$segments){
		$url_news=$this->getmainurl($lang).'news/';
		$view=isset($segments['view'])?$segments['view']:'';
		switch($view){
			case 'article':
				BLog::addtolog('[Router]: generating news article URL...');
				$artid=(int)$segments['id'];
				bimport('news.articles');
				$bnart=BNewsArticles::getInstance();
				$article=$bnart->item_get($artid);
				if(empty($article)){
					BLog::addtolog('[Router]: Could not get article with id='.$artid,LL_ERROR);
					return '';
					}
				if(empty($article->category)){
					BLog::addtolog('[Router]: The article category is empty!',LL_ERROR);
					return '';
					}
				$url=$this->generate_newsurl($lang,array('view'=>'blog','category'=>$article->category));
				$url.=$article->getalias($lang).'-'.$article->id;
				return $url;
			case 'blog':
				BLog::addtolog('[Router]: generating news blog URL...');
				$url=$url_news;
				bimport('news.categories');
				$categoryid=isset($segments['category'])?$segments['category']:0;
				if(empty($categoryid)){
					return '';
					}
				$bncats=BNewsCategories::getInstance();
				$category=$bncats->item_get($categoryid);
				if(empty($category)){
					return '';
					}
				$list=$category->getparentchain();
				if((isset($list[1]))&&($list[1]->id==1)){
					unset($list[1]);
					}
				$url=$url_news;
				foreach($list as $f){
					$url.=$f->getalias($lang).'/';
					}
				return $url;
			case 'archives':
				$url=$this->getmainurl($lang).'news/archive/';
				if(!empty($segments['year'])){
					$now=new DateTime();
					$year_now=(int)$now->format('Y');
					if($segments['year']<$year_now){
						$url.='year-'.$segments['year'];
					}
				}
				return $url;
			case 'archive_date':
				$url=$this->getmainurl($lang).'news/archive/';

				$iyear=(int)$segments['year'];
				$imonth=(int)$segments['month'];
				$iday=(int)$segments['day'];
				//
				if((empty($iyear))||(empty($iday))||(empty($imonth))){
					return '';
				}
				$ddate=new DateTime($iyear.'-'.$imonth.'-'.$iday);
				$url.='date-'.$ddate->format('Y-m-d');
				return $url;
			case 'photo':
				return $url_news.'photo/';
			case 'mod_photo_content':
				return $url_news.'mod_photo.json/';
			case 'mod_bycategory_content':
				return $url_news.'mod_bycategory.json/';
			case 'mod_bytag_content':
				return $url_news.'mod_bytag.json/';
			case 'video':
				return $url_news.'video/';
			case 'search':
				return $url_news.'search/';
			case 'pr_content':
				return $url_news.'pr/pr_content.json';
			}
		return '';
		}
	/**
	 *
	 */
	public function generate_blogsurl($lang,$segments){
		$url_blogs=$this->getmainurl($lang).'blogs/';
		$view=isset($segments['view'])?$segments['view']:'';

		if($view=='home'){
			return $this->getmainurl($lang).'blogs/';
			}
		if($view=='home_content'){
			return $this->getmainurl($lang).'blogs/more.json';
			}
		if($view=='authors'){
			return $this->getmainurl($lang).'blogs/authors/';
			}
		if($view=='authors_more'){
			return $this->getmainurl($lang).'blogs/authors_more.json/';
			}
		if($view=='author'){
			bimport('blogs.authors');
			$bba=BBlogsAuthors::getInstance();
			BLog::addtolog('[Router]: blogs - looking for author with ID='.$segments['id']);
			$author=$bba->item_get($segments['id']);
			if(empty($author)){
				BLog::addtolog('Router: Could not get blog author with such id!',LL_ERROR);
				return false;
				}
			$URL=$url_blogs.'authors/'.$author->getalias($lang).'-'.$author->id;
			return $URL;
			}
		if($view=='author_details'){
			bimport('blogs.authors');
			$bba=BBlogsAuthors::getInstance();
			BLog::addtolog('[Router]: blogs - looking for author with ID='.$segments['id']);
			$author=$bba->item_get($segments['id']);
			if(empty($author)){
				BLog::addtolog('Router: Could not get blog author with such id!',LL_ERROR);
				return false;
			}
			$URL=$url_blogs.'authors/'.$author->getalias($lang).'-'.$author->id.'/details';
			return $URL;
			}
		if($view=='archives'){
			$url=$this->getmainurl($lang).'blogs/archive/';
			if(!empty($segments['year'])){
				$now=new DateTime();
				$year_now=(int)$now->format('Y');
				if($segments['year']<$year_now){
					$url.='year-'.$segments['year'];
					}
				}
			return $url;
			}
		if($view=='archive_date'){
			$url=$this->getmainurl($lang).'blogs/archive/';

			$iyear=(int)$segments['year'];
			$imonth=(int)$segments['month'];
			$iday=(int)$segments['day'];
			//
			if((empty($iyear))||(empty($iday))||(empty($imonth))){
				return '';
				}
			$ddate=new DateTime($iyear.'-'.$imonth.'-'.$iday);
			$url.='date-'.$ddate->format('Y-m-d');
			return $url;
			}
		if($view=='article'){
			BLog::addtolog('[Router]: generating blogs article URL...');
			$artid=(int)$segments['id'];
			bimport('blogs.articles');
			$bgart=BBlogsArticles::getInstance();
			$article=$bgart->item_get($artid);
			if(empty($article)){
				BLog::addtolog('[Router]: Could not get article with id='.$artid,LL_ERROR);
				return '';
				}
			if(empty($article->category)){
				BLog::addtolog('[Router]: The blog article category is empty!',LL_ERROR);
				return '';
				}
			$url=$this->generate_blogsurl($lang,array('view'=>'category','id'=>$article->category));
			$url.=$article->getalias($lang).'-'.$article->id;
			return $url;
			}
		if($view=='categories'){
			$url=$url_blogs.'topics/';
			return $url;
			}
		if($view=='category'){
			BLog::addtolog('[Router]: generating blogs category URL...');
			$url=$url_blogs;
			bimport('blogs.categories');
			$categoryid=isset($segments['id'])?$segments['id']:0;
			if(empty($categoryid)){
				return '';
				}
			$bbcats=BBlogsCategories::getInstance();
			$category=$bbcats->item_get($categoryid);
			if(empty($category)){
				return '';
				}
			$list=$category->getparentchain();
			if((isset($list[1]))&&($list[1]->id==1)){
				unset($list[1]);
				}
			$url=$url_blogs;
			foreach($list as $f){
				$url.=$f->getalias($lang).'/';
				}
			return $url;
			}
		if($view=='mod_latest_content'){
			$url=$url_blogs.'mod_latest_content.json';
			return $url;
			}
		}
	/**
	 *
	 * @param $lang
	 * @param $segments
	 * @return string
	 */
	public function generate_quizzesurl($lang,$segments){
		$url_quizzes=$this->getmainurl($lang).'quizzes/';
		$view=isset($segments['view'])?$segments['view']:'';
		switch($view){
			case 'home':
				return $url_quizzes;
			case 'quiz':
				BLog::addtolog('[Router]: generating quizzes quiz URL...');
				$quizid=(int)$segments['id'];
				bimport('quizzes.quizzes');
				$bquiz=BQuizzesQuizzes::getInstance();
				$quiz=$bquiz->item_get($quizid);
				if(empty($quiz)){
					BLog::addtolog('[Router]: Could not get quiz with id='.$quiz,LL_ERROR);
					return '';
					}
				$url=$url_quizzes;
				$url.=$quiz->getalias($lang).'-'.$quiz->id.'/';
				return $url;
			case 'finish':
				$url=$this->generate_quizzesurl($lang,array('view'=>'quiz','id'=>$segments['id']));
				$url.='finish/';
				return $url;
			case 'phone_sent':
				$url=$this->generate_quizzesurl($lang,array('view'=>'quiz','id'=>$segments['id']));
				$url.='phone_sent/';
				return $url;
			case 'result';
				bimport('quizzes.participants');
				$bp=BQuizzesParticipants::getInstance();
				$pid=$segments['pid'];
				$participant=$bp->item_get((int)$segments['pid']);
				if(!$participant){
					return false;
					}
				$url=$this->generate_quizzesurl($lang,array('view'=>'quiz','id'=>$segments['quiz']));
				$url.='result-'.$pid.'/';
				return $url;
			case 'question':
				bimport('quizzes.quizzes');
				$bques=BQuizzesQuizzes::getInstance();
				$questionid=$segments['question'];
				$quiz=$bques->item_get((int)$segments['quiz']);
				if(empty($quiz)){
					return false;
					}
				if(!$quiz->question_exist($questionid)){
					return false;
					}
				$url=$this->generate_quizzesurl($lang,array('view'=>'quiz','id'=>$segments['quiz']));
				$url.='question-'.$questionid.'/';
				return $url;
			case 'timeout';
				$url=$this->generate_quizzesurl($lang,array('view'=>'quiz','id'=>$segments['id']));
				$url.='timeout/';
				return $url;
			case 'rules':
				return $url_quizzes.'rules/';
				}
		return '';
		}
	/**
	 * Generate contests URLs
	 *
	 * @param $lang
	 * @param $segments
	 * @return string
	 */
	public function generate_contestsurl($lang,$segments){
		$url_contests=$this->getmainurl($lang).'contests/';
		$view=isset($segments['view'])?$segments['view']:'';
		switch($view){
			case 'home':
				return $url_contests;
			case 'contest':
				return $url_contests.$segments['id'].'/';
			case 'rules':
				return $url_contests.'rules/';
			case 'imgupload_json':
				return $url_contests.'imgupload.json';
			case 'submitanswer_json':
				return $url_contests.'submitanswer.json';
			case 'answer':
				bimport('contests.answers');
				$bca=BContestsAnswers::GetInstance();
				$answer=$bca->item_get($segments['id']);
				if(empty($answer)){
					return '';
					}
				$contest=$answer->getcontest();
				if(empty($contest)){
					return '';
					}
				return $url_contests.$contest->id.'/answer-'.$answer->id;
				}
		return '';
		}
	/**
	 *
	 */
	public function generate_tagsurl($lang,$segments){
		$url_tags=$this->getmainurl($lang).'tags/';
		$view=isset($segments['view'])?$segments['view']:'';

		if($view=='home'){
			return $url_tags;
			}
		if($view=='tag'){
			bimport('tags.tags');
			$btags=BTagsTags::getInstance();
			BLog::addtolog('[Router]: tags - looking for tag with ID='.$segments['id']);
			$tag=$btags->item_get($segments['id']);
			if(empty($tag)){
				BLog::addtolog('Router: Could not get tag with such id!',LL_ERROR);
				return '';
				}
			$URL=$url_tags.$tag->getalias($lang).'-'.$tag->id;
			return $URL;
			}
		return '';
		}
	/**
	 *
	 */
	public function generate_socialurl($lang,$segments){
		$url_social=$this->getmainurl($lang).'social/';
		$view=isset($segments['view'])?$segments['view']:'';

		if($view=='auth'){
			BLog::addtolog('[Router]: generating social auth URL...');
			$network=isset($segments['network'])?$segments['network']:'';
			$URL=$url_social.'auth/'.$network;
			return $URL;
			}
		if($view=='complete'){
			BLog::addtolog('[Router]: generating social complete URL...');
			$network=isset($segments['network'])?$segments['network']:'';
			$URL=$url_social.'complete/'.$network;
			return $URL;
			}

		return '';
		}

	/**
	 * 
	 */
	public function generate_usersurl($lang,$segments){
		$URL_main=$this->getmainurl($lang);
		return '';
		}
	/**
	 * 
	 */
	public function generate_afficheurl($lang,$segments){
		$URL_main=$this->getmainurl($lang);
		$view=isset($segments['view'])?$segments['view']:'';
		if($view=='home'){
			$url=$this->getmainurl($lang).'affiche/';
			if(!empty($segments['category'])){
				BLog::addtolog('[Router]: generating affiche events category URL...');
				bimport('affiche.categories');
				$categoryid=isset($segments['category'])?$segments['category']:0;
				if(empty($categoryid)){
					return '';
					}
				$bacats=BAfficheCategories::getInstance();
				$category=$bacats->item_get($categoryid);
				if(empty($category)){
					return '';
					}
				$list=$category->getparentchain();
				if((isset($list[1]))&&($list[1]->id==1)){
					unset($list[1]);
					}
				foreach($list as $f){
					$url.=$f->getalias($lang).'/';
					}
				return $url;
				}
			return $url;
			}
		if($view=='event'){
			BLog::addtolog('[Router]: generating affiche event URL...');
			$eventid=(int)$segments['id'];
			bimport('affiche.events');
			$bapc=BAfficheEvents::getInstance();
			$event=$bapc->item_get($eventid);
			if(empty($event)){
				BLog::addtolog('[Router]: Could not get event with id='.$artid,LL_ERROR);
				return '';
				}
			if(empty($event->category)){
				BLog::addtolog('[Router]: The event category is empty!',LL_ERROR);
				return '';
				}
			$url=$this->generate_afficheurl($lang,array('view'=>'home','category'=>$event->category));
			$url.=$event->getalias($lang).'-'.$event->id;
			return $url;
			}
		if($view=='archive'){
			$url=$this->getmainurl($lang).'affiche/archive/';
			if(!empty($segments['year'])){
				$now=new DateTime();
				$year_now=(int)$now->format('Y');
				if($segments['year']<$year_now){
					$url.='year-'.$segments['year'];
					}
				}
			return $url;
			}
		if($view=='places'){
			$url=$this->getmainurl($lang).'affiche/places/';
			if(!empty($segments['category'])){
				BLog::addtolog('[Router]: generating affiche places category URL...');
				bimport('affiche.placescategories');
				$categoryid=isset($segments['category'])?$segments['category']:0;
				if(empty($categoryid)){
					return '';
					}
				$bapcats=BAffichePlacesCategories::getInstance();
				$category=$bapcats->item_get($categoryid);

				if(empty($category)){
					return '';
					}
				$list=$category->getparentchain();
				if((isset($list[1]))&&($list[1]->id==1)){
					unset($list[1]);
					}
				foreach($list as $f){
					$url.=$f->getalias($lang).'/';
					}
				return $url;
				}
			return $url;
			}
		if($view=='places_content'){
			return $this->getmainurl($lang).'affiche/places/more.json';
			}
		if($view=='place'){
			BLog::addtolog('[Router]: generating affiche place URL...');
			$placeid=(int)$segments['id'];
			bimport('affiche.places');
			$bapc=BAffichePlaces::getInstance();
			$place=$bapc->item_get($placeid);
			if(empty($place)){
				BLog::addtolog('[Router]: Could not get place with id='.$artid,LL_ERROR);
				return '';
				}
			if(empty($place->category)){
				BLog::addtolog('[Router]: The place category is empty!',LL_ERROR);
				return '';
				}
			$url=$this->generate_afficheurl($lang,array('view'=>'places','category'=>$place->category));
			$url.=$place->getalias($lang).'-'.$place->id;
			return $url;
			}
		if($view=='archive_date'){
			$url=$this->getmainurl($lang).'affiche/archive/';

			$iyear=(int)$segments['year'];
			$imonth=(int)$segments['month'];
			$iday=(int)$segments['day'];
			//
			if((empty($iyear))||(empty($iday))||(empty($imonth))){
				return '';
				}
			$ddate=new DateTime($iyear.'-'.$imonth.'-'.$iday);
			$url.='date-'.$ddate->format('Y-m-d');
			return $url;
			}
		if($view=='search'){
			return $this->getmainurl($lang).'affiche/search/';
		}
		if('home_content'){
			return $this->getmainurl($lang).'affiche/content.json';
		}
		return '';
		}

	/**
	 * @param $lang
	 * @param $segments
	 * @return string
	 */
	public function generate_pollsurl($lang,$segments){
		$URL_main=$this->getmainurl($lang);
		$view=isset($segments['view'])?$segments['view']:'';
		if($view=='home'){
			return $this->getmainurl($lang).'polls/';
			}
		if($view=='voices_json'){
			return $this->getmainurl($lang).'polls/voices.json';
			}
		return '';
		}
	/**
	 *
	 */
	public function generate_otherurl($lang,$segments){
		$URL_main=$this->getmainurl($lang);
		$view=isset($segments['view'])?$segments['view']:'';
		if($view=='home'){
			return $this->getmainurl($lang).'other/';
			}
		if($view=='contacts'){
			return $this->getmainurl($lang).'other/contacts';
			}
		if($view=='addticket'){
			return $this->getmainurl($lang).'other/addticket.json';
			}
		if($view=='submitnews'){
			return $this->getmainurl($lang).'other/submitnews.json';
			}
		if($view=='submitads'){
			return $this->getmainurl($lang).'other/submitads.json';
			}
		return '';
		}
	/**
	 * Generate URL by component, language and segments
	 * in case of sucessfull parse return URL, else return false;
	 *
	 * @param string $component
	 * @param string $lang
	 * @param array $segments
	 */
	public function generateURL($component,$lang,$segments){
		if(ROUTER_DEBUG){
			BLog::addtolog('[Router]: generating URL...');
			BLog::addtolog('[Router]: $component='.$component);
			BLog::addtolog('[Router]: $lang='.$lang);
			BLog::addtolog('[Router]: $segments='.var_export($segments,true));
			}
		$URL='';
		
		switch($component){
			case 'admin':
				return $this->generate_adminurl($segments);
			case 'news':
				return $this->generate_newsurl($lang,$segments);
			case 'blogs':
				return $this->generate_blogsurl($lang,$segments);
			case 'quizzes':
				return $this->generate_quizzesurl($lang,$segments);
			case 'contests':
				return $this->generate_contestsurl($lang,$segments);
			case 'users':
				return $this->generate_usersurl($lang,$segments);
			case 'affiche':
				return $this->generate_afficheurl($lang,$segments);
			case 'tags':
				return $this->generate_tagsurl($lang,$segments);
			case 'social':
				return $this->generate_socialurl($lang,$segments);
			case 'sitemap':
				if ($segments['view']=='xml'){
					return BHOSTNAME.'/sitemap.xml';					
					}
				break;
			case 'switchmobileversion':
				if ($segments['view']=='switch'){
					return BHOSTNAME.'/switchmobile';					
					}
				break;
			case 'polls':
				return $this->generate_pollsurl($lang,$segments);
			case 'other':
				return $this->generate_otherurl($lang,$segments);
			case 'mainpage':
				$URL=BHOSTNAME.'/';
				if($lang!='ua'){
					$URL.=$lang.'/';
					}
				return $URL;
				break;
			}
		}
	/**
	 *
	 */
	public function generateURLmain($lang='',$useparams=true){
		if(empty($lang)){
			$lang=BLang::$langcode;
			}
		$url=$this->generateURL($this->maincom->com,$lang,$this->maincom->segments);
		if($useparams){
			bimport('http.request');
			$url.=BRequest::getGetString();
			}
		return $url;
		}
	/**
	 * Parse /news/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseurl_news($f_path){
		if(ROUTER_DEBUG){
			BLog::addtolog('[Router]: We are in news branch now!');
			}
		//Remove lateset empty chain.
		if(end($f_path)==''){
			array_pop($f_path);
			}
		//
		$segments=array();
		//Get page.
		if(substr(end($f_path),0,4)=='page'){
			$num=substr(end($f_path),4);
			if(is_numeric($num)){
				$segments['page']=(int)$num;
				array_pop($f_path);
				}
			}
		//Новости - главная страница
		if(empty($f_path)){
			$segments['view']='blog';
			$segments['category']=1;
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('news:category:1');
			return true;
			}
		if((count($f_path)==1)&&($f_path[0]=='archive')){
			$segments['view']='archives';
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				'segments'=>$segments,
			);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('news:home');
			return true;
			}
		//
		if((count($f_path)==2)&&($f_path[0]=='archive')&&(substr($f_path[1],0,5)=='year-')){
			$year=(int)substr($f_path[1],5);
			if(empty($year)){
				return false;
				}
			$segments['view']='archives';
			$segments['year']=$year;
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('news:archive');
			return true;
			}
		//
		if((count($f_path)==2)&&($f_path[0]=='archive')&&(substr($f_path[1],0,5)=='date-')){
			//NOW datetime and URL
			$now=new DateTime();
			$nyear=(int)$now->format('Y');
			$nmonth=(int)$now->format('m');
			$nday=(int)$now->format('d');
			$url_dtnow=$this->generateURL('news',BLang::$langcode,array('view'=>'archive_date','year'=>$nyear,'month'=>$nmonth,'day'=>$nday));
			//Date of blogs start posting
			//$syear=2015;
			//$smonth=12;
			//$sday=17;
			//
			$date=substr($f_path[1],5);
			$xdate=explode('-',$date);
			if((count($xdate)!=3)||(strlen($date)!=10)){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$url_dtnow;
				return true;
				}
			$iyear=(int)$xdate[0];
			$imonth=(int)$xdate[1];
			$iday=(int)$xdate[2];
			if((empty($iyear))||(empty($iday))||(empty($imonth))){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$url_dtnow;
				return true;
				}
			$ddate=new DateTime($iyear.'-'.$imonth.'-'.$iday);
			//Chek for datetime in future.
			if(($iyear>$nyear)||(($iyear==$nyear)&&($imonth>$nmonth))||(($iyear==$nyear)&&($imonth==$nmonth)&&($iday>$nday))){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$url_dtnow;
				return true;
				}
			//Canonical URL
			$gen_url=$this->generateURL('news',BLang::$langcode,array('view'=>'archive_date','year'=>$iyear,'month'=>$imonth,'day'=>$iday));
			$cur_url=$this->host.parse_url($this->url,PHP_URL_PATH);
			if($cur_url!=$gen_url){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$gen_url;
				return;
				}
			$segments['view']='archive_date';
			$segments['year']=$iyear;
			$segments['month']=$imonth;
			$segments['day']=$iday;
			//
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('news:archive');
			return true;
			}
		//Новости - новости с фото / новости с видео / поиск
		if((count($f_path)==1)&&(($f_path[0]=='photo')||$f_path[0]=='video'||$f_path[0]=='search')){
			$segments['view']=$f_path[0];
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('news:'.$f_path[0]);
			return true;
			}
		//
		if((count($f_path)==1)&&($f_path[0]=='mod_photo.json')){
			$segments['view']='mod_photo_content';
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//Новости - модуль новостей по категории. Загрузка аяксом
		if((count($f_path)==1)&&($f_path[0]=='mod_bycategory.json')){
			$segments['view']='mod_bycategory_content';
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//Новости - модуль новостей по тегу. Загрузка аяксом
		if((count($f_path)==1)&&($f_path[0]=='mod_bytag.json')){

			$segments['view']='mod_bytag_content';
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				$this->ctype=CTYPE_JSON,
				'segments'=>$segments,
			);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('news: home');
			return true;
		}
		//
		$suffix=end(explode('-',end($f_path)));
		//Article
		if(is_numeric($suffix)){
			BLog::addtolog('[Router]: found something like news article');
			$articleid=(int)$suffix;
			$segments=array('view'=>'article','id'=>$articleid);
			bimport('news.articles');
			$bnewsarticles=BNewsArticles::getInstance();
			$article=$bnewsarticles->item_get($articleid);
			if(empty($article)){
				BLog::addtolog('[Router]: Could not load article!',LL_ERROR);
				return false;
				}
			$gen_url=$this->generateURL('news',BLang::$langcode,$segments);
			$cur_url=$this->host.parse_url($this->url,PHP_URL_PATH);
			if($cur_url!=$gen_url){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$gen_url;
				return;
				}
			$this->newscat=$article->category;
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('news:article:'.$article->id);
			$this->softmodulesget('news:newscat:'.$article->category);
			return true;
			}
		//Прес-релизы. Загрузка аяксом
		if($f_path[count($f_path)-1]=='pr_content.json'){
			BLog::addtolog('[Router]: found something like random news AJAX loader.');
			//
			bimport('news.categories');
			$bnewscat=BNewsCategories::getInstance();
			unset($f_path[count($f_path)-1]);
			$category=$bnewscat->getitembyaliaschain($f_path,BLang::$langcode);
			if(empty($category)){
				BLog::addtolog('[Router]: Could not load news category!',LL_ERROR);
				return false;
				}
			//
			if(!empty($category->template)){
				$this->templatename=$category->template;
				}
			bimport('http.request');
			$segments['view']='pr_content';
			$segments['basecat']=$category->id;
			$segments['category']=BRequest::GetInt('category');
			$segments['limit']=BRequest::GetInt('limit');
			$this->maincom=(object)array(
				'com'=>'news',
				'position'=>'content',
				'segments'=>$segments
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		BLog::addtolog('[Router]: found something like news category');
		bimport('news.categories');
		$bnewscat=BNewsCategories::getInstance();
		if(end($f_path)==''){
			array_pop($f_path);
			}
		//
		$category=$bnewscat->getitembyaliaschain($f_path,BLang::$langcode);
		if(empty($category)){
			BLog::addtolog('[Router]: Could not load news category!',LL_ERROR);
			return false;
			}
		//
		$this->newscategory=$category->id;
		$segments['view']='blog';
		$segments['category']=$category->id;

		if(!empty($category->template)){
			$this->templatename=$category->template;
			}
		//
		$this->maincom=(object)array(
			'com'=>'news',
			'position'=>'content',
			'segments'=>$segments
			);
		$this->addfixedrules();
		$this->rules[]=$this->maincom;
		$this->softmodulesget('news:category:'.$category->id);
		return true;
		}
	/**
	 * Parse /blogs/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseurl_blogs($f_path){
		BLog::addtolog('[Router]: We are in blogs branch now!');
		//Unset the latest empty "/" in url.
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_blogs() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//
		$segments=array();
		if(substr(end($f_path),0,4)=='page'){
			$num=substr(end($f_path),4);
			if(is_numeric($num)){
				$segments['page']=(int)$num;
				array_pop($f_path);
				}
			}
		//
		if(empty($f_path)){
			$segments['view']='home';
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('blogs:home');
			return true;
			}
		// Load more json
		if((count($f_path)==1)&&($f_path[0]=='more.json')){
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>array('view'=>'home_content'),
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		if((count($f_path)==1)&&($f_path[0]=='authors')){
			$segments['view']='authors';
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('blogs:home');
			return true;
			}
		// Load more json
		if((count($f_path)==1)&&($f_path[0]=='authors_more.json')){
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>array('view'=>'authors_more'),
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		if((count($f_path)==2)&&($f_path[0]=='authors')&&($this->getIntSuffix($f_path[1]))){
			$authorid=$this->getIntSuffix($f_path[1]);
			//
			bimport('blogs.authors');
			$bba=BBlogsAuthors::getInstance();
			BLog::addtolog('[Router]: looking for author with ID='.$authorid);
			$author=$bba->item_get($authorid);
			if(empty($author)){
				BLog::addtolog('Router: Could not get blog author with such id!',LL_ERROR);
				return false;
				}
			$segments['view']='author';
			$segments['id']=$author->id;
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('blogs:home');
			return true;
			}
		//
		if((count($f_path)==3)&&($f_path[0]=='authors')&&($this->getIntSuffix($f_path[1])) && ($f_path[2]=='details')){
			$authorid=$this->getIntSuffix($f_path[1]);
			//
			bimport('blogs.authors');
			$bba=BBlogsAuthors::getInstance();
			BLog::addtolog('[Router]: looking for author with ID='.$authorid);
			$author=$bba->item_get($authorid);

			if(empty($author)){
				BLog::addtolog('Router: Could not get blog author with such id!',LL_ERROR);
				return false;
			}
			$segments['view']='author_details';
			$segments['id']=$author->id;
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
			);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('blogs:home');
			return true;
			}
		//
		if((count($f_path)==1)&&($f_path[0]=='archive')){
			$segments['view']='archives';
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
			);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('blogs:home');
			return true;
			}
		//
		if((count($f_path)==2)&&($f_path[0]=='archive')&&(substr($f_path[1],0,5)=='year-')){
			$year=(int)substr($f_path[1],5);
			if(empty($year)){
				return false;
				}
			$segments['view']='archives';
			$segments['year']=$year;
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('blogs:home');
			return true;
			}
		//
		if((count($f_path)==2)&&($f_path[0]=='archive')&&(substr($f_path[1],0,5)=='date-')){
			//NOW datetime and URL
			$now=new DateTime();
			$nyear=(int)$now->format('Y');
			$nmonth=(int)$now->format('m');
			$nday=(int)$now->format('d');
			$url_dtnow=$this->generateURL('blogs',BLang::$langcode,array('view'=>'archive_date','year'=>$nyear,'month'=>$nmonth,'day'=>$nday));
			//Date of blogs start posting
			//$syear=2015;
			//$smonth=12;
			//$sday=17;
			//
			$date=substr($f_path[1],5);
			$xdate=explode('-',$date);
			if((count($xdate)!=3)||(strlen($date)!=10)){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$url_dtnow;
				return true;
				}
			$iyear=(int)$xdate[0];
			$imonth=(int)$xdate[1];
			$iday=(int)$xdate[2];
			if((empty($iyear))||(empty($iday))||(empty($imonth))){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$url_dtnow;
				return true;
				}
			$ddate=new DateTime($iyear.'-'.$imonth.'-'.$iday);
			//Chek for datetime in future.
			if(($iyear>$nyear)||(($iyear==$nyear)&&($imonth>$nmonth))||(($iyear==$nyear)&&($imonth==$nmonth)&&($iday>$nday))){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$url_dtnow;
				return true;
				}
			//Canonical URL
			$gen_url=$this->generateURL('blogs',BLang::$langcode,array('view'=>'archive_date','year'=>$iyear,'month'=>$imonth,'day'=>$iday));
			$cur_url=$this->host.parse_url($this->url,PHP_URL_PATH);
			if($cur_url!=$gen_url){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$gen_url;
				return;
				}
			$segments['view']='archive_date';
			$segments['year']=$iyear;
			$segments['month']=$imonth;
			$segments['day']=$iday;
			//
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('blogs:home');
			return true;
		}
		//
		if((count($f_path)==1)&&($f_path[0]=='topics')){
			$segments['view']='categories';
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('blogs:categories');
			return true;
			}
		//
		if((count($f_path)==1)&&($f_path[0]=='mod_latest_content.json')){
			bimport('http.request');
			$segments=array();
			$segments['view']='mod_latest_content';
			$segments['limit']=BRequest::getInt('limit');
			$segments['offset']=BRequest::getInt('offset');
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		$suffix=end(explode('-',end($f_path)));
		//Article
		if(is_numeric($suffix)){
			BLog::addtolog('[Router]: found something like blog article');
			$articleid=(int)$suffix;
			$segments=array('view'=>'article','id'=>$articleid);
			bimport('blogs.articles');
			$bblogarticles=BBlogsArticles::getInstance();
			$article=$bblogarticles->item_get($articleid);
			if(empty($article)){
				BLog::addtolog('[Router]: Could not load blog article!',LL_ERROR);
				return false;
				}
			$gen_url=$this->generateURL('blogs',BLang::$langcode,$segments);
			$cur_url=$this->host.parse_url($this->url,PHP_URL_PATH);
			if($cur_url!=$gen_url){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$gen_url;
				return;
				}
			$this->newscat=$article->category;
			$this->maincom=(object)array(
				'com'=>'blogs',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blog:article:'.$article->id);
			return true;
			}
		BLog::addtolog('[Router]: found something like blogs category');
		bimport('blogs.categories');
		$bblogscat=BBlogsCategories::getInstance();
		$limit=10; $offset=0;
		if(end($f_path)==''){
			array_pop($f_path);
			}
		//
		$category=$bblogscat->getitembyaliaschain($f_path,$lang);
		if(empty($category)){
			BLog::addtolog('[Router]: Could not load blogs category!',LL_ERROR);
			return false;
			}
		//
		$this->newscategory=$category->id;
		$segments['view']='category';
		$segments['id']=$category->id;
		//
		$this->maincom=(object)array(
			'com'=>'blogs',
			'position'=>'content',
			'segments'=>$segments
			);
		$this->addfixedrules();
		$this->rules[]=$this->maincom;
		$this->softmodulesget('blog:category:'.$category->id);
		return true;
		}
	/**
	 * Parse /affiche/places/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseurl_affiche_places($f_path){
		BLog::addtolog('[Router]: We are in /affiche/places/ branch now!');
		//Unset the latest empty "/" in url.
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_affiche() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//Get page...
		$segments=array();
		if(substr(end($f_path),0,4)=='page'){
			$num=substr(end($f_path),4);
			if(is_numeric($num)){
				$segments['page']=(int)$num;
				array_pop($f_path);
				}
			}
		//
		if(empty($f_path)){
			$segments['view']='places';
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('affiche:home');
			return true;
			}
		// Load more json
		if((count($f_path)==1)&&($f_path[0]=='more.json')){
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>array('view'=>'places_content'),
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		$suffix=end(explode('-',end($f_path)));
		//Affiche place?
		if(is_numeric($suffix)){
			BLog::addtolog('[Router]: found something like affiche place');
			$placeid=(int)$suffix;
			$segments=array('view'=>'place','id'=>$placeid);
			bimport('affiche.places');
			$bap=BAffichePlaces::getInstance();
			$place=$bap->item_get($placeid);
			if(empty($place)){
				BLog::addtolog('[Router]: Could not load affiche place!',LL_ERROR);
				return false;
				}
			$gen_url=$this->generateURL('affiche',BLang::$langcode,$segments);
			$cur_url=$this->host.parse_url($this->url,PHP_URL_PATH);
			if($cur_url!=$gen_url){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$gen_url;
				return;
				}
			//$this->newscat=$article->category;
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('affiche:home');
			//$this->softmodulesget('blog:article:'.$article->id);
			return true;
			}
		BLog::addtolog('[Router]: found something like affiche places category');
		bimport('affiche.placescategories');
		$bapc=BAffichePlacesCategories::getInstance();
		$limit=10; $offset=0;
		if(end($f_path)==''){
			array_pop($f_path);
			}
		//
		$lang=BLang::$langcode;
		$category=$bapc->getitembyaliaschain($f_path,$lang);
		if(empty($category)){
			BLog::addtolog('[Router]: Could not load affiche places category!',LL_ERROR);
			return false;
			}
		//
		//$this->newscategory=$category->id;
		$segments['view']='places';
		$segments['category']=$category->id;
		//
		$this->maincom=(object)array(
			'com'=>'affiche',
			'position'=>'content',
			'segments'=>$segments
			);
		$this->addfixedrules();
		$this->rules[]=$this->maincom;
		//$this->softmodulesget('affiche:places:category:'.$category->id);
		$this->softmodulesget('affiche:home');
		return true;
		}
	/**
	 * Parse /affiche/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseurl_affiche($f_path){
		BLog::addtolog('[Router]: We are in affiche branch now!');
		//Афиша - заведения (отдельная ветка)
		if($f_path[0]=='places'){
			array_shift($f_path);
			return $this->parseurl_affiche_places($f_path);
			}


		//Unset the latest empty "/" in url.
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_affiche() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//Get page...
		$segments=array();
		if(substr(end($f_path),0,4)=='page'){
			$num=substr(end($f_path),4);
			if(is_numeric($num)){
				$segments['page']=(int)$num;
				array_pop($f_path);
				}
			}
		//
		if(empty($f_path)){
			$segments['view']='home';
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('affiche:home');
			return true;
			}
		// Load more json
		if((count($f_path)==1)&&($f_path[0]=='content.json')){
			bimport('http.request');
			$segments=array();
			$segments['view']='home_content';
			$segments['date_from']=BRequest::getString('date_from');
			$segments['date_to']=BRequest::getString('date_to');
			$segments['category']=BRequest::getInt('category');
			//
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//Афиша - архив - главная
		if((count($f_path)==1)&&($f_path[0]=='archive')){
			$segments['view']='archive';
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('affiche:home');
			return true;
			}
		//Афиша - архив - год
		if((count($f_path)==2)&&($f_path[0]=='archive')&&(substr($f_path[1],0,5)=='year-')){
			$year=(int)substr($f_path[1],5);
			if(empty($year)){
				return false;
				}
			$segments['view']='archive';
			$segments['year']=$year;
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('affiche:home');
			return true;
			}
		//Афиша - архив - дата
		if((count($f_path)==2)&&($f_path[0]=='archive')&&(substr($f_path[1],0,5)=='date-')){
			//NOW datetime and URL
			$now=new DateTime();
			$nyear=(int)$now->format('Y');
			$nmonth=(int)$now->format('m');
			$nday=(int)$now->format('d');
			$url_dtnow=$this->generateURL('affiche',BLang::$langcode,array('view'=>'archive_date','year'=>$nyear,'month'=>$nmonth,'day'=>$nday));
			//Date of blogs start posting
			//$syear=2015;
			//$smonth=12;
			//$sday=17;
			//
			$date=substr($f_path[1],5);
			$xdate=explode('-',$date);
			if((count($xdate)!=3)||(strlen($date)!=10)){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$url_dtnow;
				return true;
				}
			$iyear=(int)$xdate[0];
			$imonth=(int)$xdate[1];
			$iday=(int)$xdate[2];
			if((empty($iyear))||(empty($iday))||(empty($imonth))){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$url_dtnow;
				return true;
				}
			$ddate=new DateTime($iyear.'-'.$imonth.'-'.$iday);
			//Chek for datetime in future.
			if(($iyear>$nyear)||(($iyear==$nyear)&&($imonth>$nmonth))||(($iyear==$nyear)&&($imonth==$nmonth)&&($iday>$nday))){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$url_dtnow;
				return true;
				}
			//Canonical URL
			$gen_url=$this->generateURL('affiche',BLang::$langcode,array('view'=>'archive_date','year'=>$iyear,'month'=>$imonth,'day'=>$iday));
			$cur_url=$this->host.parse_url($this->url,PHP_URL_PATH);
			if($cur_url!=$gen_url){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$gen_url;
				return;
				}
			$segments['view']='archive_date';
			$segments['year']=$iyear;
			$segments['month']=$imonth;
			$segments['day']=$iday;
			//
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('affiche:home');
			return true;
			}
		//Афиша - поиск
		if((count($f_path)==1)&&($f_path[0]=='search')){
			$segments['view']=$f_path[0];
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('affiche:'.$f_path[0]);
			return true;
			}
		//
		$suffix=end(explode('-',end($f_path)));
		//Article
		if(is_numeric($suffix)){
			BLog::addtolog('[Router]: found something like affiche event.');
			$eventid=(int)$suffix;
			$segments=array('view'=>'event','id'=>$eventid);
			bimport('affiche.events');
			$bae=BAfficheEvents::getInstance();
			$event=$bae->item_get($eventid);
			if(empty($event)){
				BLog::addtolog('[Router]: Could not load event!',LL_ERROR);
				return false;
				}
			$gen_url=$this->generateURL('affiche',BLang::$langcode,$segments);
			$cur_url=$this->host.parse_url($this->url,PHP_URL_PATH);
			if($cur_url!=$gen_url){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$gen_url;
				return;
				}
			//$this->newscat=$article->category;
			$this->maincom=(object)array(
				'com'=>'affiche',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('affiche:category:'.$event->category);
			$this->softmodulesget('affiche:home');
			return true;
			}
		BLog::addtolog('[Router]: found something like affiche category');
		bimport('affiche.categories');
		$baffcat=BAfficheCategories::getInstance();
		if(end($f_path)==''){
			array_pop($f_path);
			}
		//
		$category=$baffcat->getitembyaliaschain($f_path,$lang);
		if(empty($category)){
			BLog::addtolog('[Router]: Could not load affiche category!',LL_ERROR);
			return false;
			}
		//
		//$this->newscategory=$category->id;
		$segments['view']='home';
		$segments['category']=$category->id;
		//
		$this->maincom=(object)array(
			'com'=>'affiche',
			'position'=>'content',
			'segments'=>$segments
			);
		$this->addfixedrules();
		$this->rules[]=$this->maincom;
		//$this->softmodulesget('affiche:category:'.$category->id);
		$this->softmodulesget('affiche:home');
		return true;
		}
	/**
	 * Parse /quizzes/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseurl_quizzes($f_path){
		BLog::addtolog('[Router]: We are in quizzes branch now!');
		//Unset the latest empty "/" in url.
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_quizzes() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//
		//
		$segments=array();
		if(substr(end($f_path),0,4)=='page'){
			$num=substr(end($f_path),4);
			if(is_numeric($num)){
				$segments['page']=(int)$num;
				array_pop($f_path);
				}
			}
		//Quiz
		if($suffix=$this->getIntSuffix($f_path[0])){
			BLog::addtolog('[Router]: found something like quizzes quiz');
			$quizid=(int)$suffix;
			bimport('quizzes.quizzes');
			$bquizzes=BQuizzesQuizzes::getInstance();
			$quiz=$bquizzes->item_get($quizid);
			if(empty($quiz)){
				BLog::addtolog('[Router]: Could not load Quiz!',LL_ERROR);
				return false;
				}

			if(count($f_path)==1){
				$segments=array('view'=>'quiz','id'=>$quizid);
				}
			elseif((count($f_path)==2)&&($f_path[1]=='finish')){
				$segments=array('view'=>'finish','id'=>$quizid);
				}
			elseif((count($f_path)==2)&&($f_path[1]=='phone_sent')){
				$segments=array('view'=>'phone_sent','id'=>$quizid);
				}
			elseif((count($f_path)==2)&&(substr($f_path[1],0,7)=='result-')){
				$pid=(int)substr($f_path[1],7);
				bimport('quizzes.participants');
				$bp=BQuizzesParticipants::getInstance();
				$participant=$bp->item_get((int)$pid);
				if(!$participant){
					return false;
					}
				bimport('quizzes.quizzes');
				$bques=BQuizzesQuizzes::getInstance();
				$quiz=$bques->item_get((int)$participant->quiz);
				$url_thisq=$this->generateURL('quizzes',BLang::$langcode,array('view'=>'result','pid'=>$pid,'quiz'=>$participant->quiz));
				if($quiz->id!=$quizid){
					$this->ctype=CTYPE_REDIRECT301;
					$this->redirectURL='//'.$url_thisq;
					return true;
					}
				$segments=array('view'=>'result','pid'=>$pid,'quiz'=>$quizid);
				}
			elseif((count($f_path)==2)&&(substr($f_path[1],0,9)=='question-')){
				$questionid=(int)substr($f_path[1],9);
				//Check if our question is for quiz
				bimport('quizzes.quizzes');
				$bques=BQuizzesQuizzes::getInstance();
				$quiz=$bques->item_get((int)$quizid);
				if(!$quiz->question_exist($questionid)){
					return false;
					}
				$segments=array('view'=>'question','question'=>$questionid,'quiz'=>$quizid);
				}
			elseif((count($f_path)==2)&&($f_path[1]=='timeout')){
				$segments=array('view'=>'timeout','id'=>$quizid);
				}
				//
			else{
				return false;
				}

			$gen_url=$this->generateURL('quizzes',BLang::$langcode,$segments);
			$cur_url=$this->host.parse_url($this->url,PHP_URL_PATH);
			if($cur_url!=$gen_url){
				$this->ctype=CTYPE_REDIRECT301;
				$this->redirectURL='//'.$gen_url;
				return;
				}
			$this->maincom=(object)array(
				'com'=>'quizzes',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('quizzes:home');
			return true;
		}
		//
		if((count($f_path)==1) && ($f_path[0]=='rules')){
			$segments['view']='rules';

			$this->maincom=(object)array(
				'com'=>'quizzes',
				'position'=>'content',
				'segments'=>$segments,
			);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('quizzes:home');
			return true;
			}
		//
		if(empty($f_path)){
			$segments['view']='home';
			$this->maincom=(object)array(
				'com'=>'quizzes',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('quizzes:home');
			return true;
			}
		BLog::addtolog('[Router]: parseurl_quizzes() no rules! $f_path='.var_export($f_path,true),LL_ERROR);
		return false;
		}
	/**
	 * Parse /contests/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseurl_contests($f_path){
		BLog::addtolog('[Router]: We are in contests branch now!');
		//Unset the latest empty "/" in url.
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_contests() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//
		//
		$segments=array();
		if(substr(end($f_path),0,4)=='page'){
			$num=substr(end($f_path),4);
			if(is_numeric($num)){
				$segments['page']=(int)$num;
				array_pop($f_path);
				}
			}
		//
		if(empty($f_path)){
			$segments['view']='home';
			$this->maincom=(object)array(
				'com'=>'contests',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('contests:home');
			return true;
			}
		//Rules page
		if((count($f_path)==1) && $f_path[0]=='rules'){
			$segments['view']='rules';
			$this->maincom=(object)array(
				'com'=>'contests',
				'position'=>'content',
				'segments'=>$segments,
			);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('contests:home');
			return true;
			}
		//
		if((count($f_path)==1) && $f_path[0]=='imgupload.json'){
			$segments['view']='imgupload_json';
			$this->maincom=(object)array(
				'com'=>'contests',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		if((count($f_path)==1) && $f_path[0]=='submitanswer.json'){
			$segments['view']='submitanswer_json';
			$this->maincom=(object)array(
				'com'=>'contests',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		if((count($f_path)==1)&&(is_numeric($f_path[0]))){
			$contestid=(int)$f_path[0];
			$segments['view']='contest';
			$segments['id']=$contestid;
			$this->maincom=(object)array(
				'com'=>'contests',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('contests:home');
			return true;			
			}
		if((count($f_path)==2)&&(is_numeric($f_path[0]))&&(substr($f_path[1],0,7)=='answer-')){
			$contestid=(int)$f_path[0];
			$answerid=(int)substr($f_path[1],7);
			$segments['view']='answer';
            $segments['contest']=$contestid;
			$segments['id']=$answerid;
			$this->maincom=(object)array(
				'com'=>'contests',
				'position'=>'content',
				'segments'=>$segments,
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('contests:home');
			return true;			
			}

		BLog::addtolog('[Router]: parseurl_contests() no rules! $f_path='.var_export($f_path,true),LL_ERROR);
		return false;
		}
	/**
	 * Parse /polls/ branch.
	 *
	 * Language - $this->langcode
	 */
	public function parseurl_polls($f_path){
		BLog::addtolog('[Router]: We are in polls branch now!');
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_polls() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		if((count($f_path)==1)&&($f_path[0]=='voices.json')){
			$this->maincom=(object)array(
				'com'=>'polls',
				'position'=>'content',
				'segments'=>array('view'=>'voices_json'),
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		if(empty($f_path)){
			$this->maincom=(object)array(
				'com'=>'polls',
				'position'=>'content',
				'segments'=>array('view'=>'home'),
			);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('polls:home');
			return true;
		}
		BLog::addtolog('[Router]: parseurl_polls() no rules! $f_path='.var_export($f_path,true),LL_ERROR);
		return false;
	}
	/**
	 * Parse /tags/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseurl_tags($f_path){
		BLog::addtolog('[Router]: We are in tags branch now!');
		//Unset the latest empty "/" in url.
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_tags() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//
		if(empty($f_path)){
			$this->maincom=(object)array(
				'com'=>'tags',
				'position'=>'content',
				'segments'=>array('view'=>'home'),
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('tags:home');
			return true;
			}
		if((count($f_path)==1)&&($this->getIntSuffix($f_path[0]))){
			$tagid=$this->getIntSuffix($f_path[0]);
			//
			bimport('tags.tags');
			$btags=BTagsTags::getInstance();
			BLog::addtolog('[Router]: looking for tag with ID='.$tagid);
			$tag=$btags->item_get($tagid);
			if(empty($tag)){
				BLog::addtolog('Router: Could not get tag with such id!',LL_ERROR);
				return false;
				}
			$this->maincom=(object)array(
				'com'=>'tags',
				'position'=>'content',
				'segments'=>array('view'=>'tag','id'=>$tag->id),
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('tags:home');
			return true;
			}

		BLog::addtolog('[Router]: parseurl_tags() no rules! $f_path='.var_export($f_path,true),LL_ERROR);
		return false;
		}
	/**
	 * Parse /social/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseurl_social($f_path){
		BLog::addtolog('[Router]: We are in social branch now!');
		//Unset the latest empty "/" in url.
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_social() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//
		if((count($f_path)==2)&&($f_path[0]=='auth')){
			$sn=$f_path[1];//Social Network
			$this->maincom=(object)array(
				'com'=>'social',
				'position'=>'content',
				'segments'=>array('view'=>'auth','network'=>$sn),
				);
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		if((count($f_path)==2)&&($f_path[0]=='complete')){
			$sn=$f_path[1];//Social Network
			$this->maincom=(object)array(
				'com'=>'social',
				'position'=>'content',
				'segments'=>array('view'=>'complete','network'=>$sn),
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		if((count($f_path)==1)&&($f_path[0]=='privacy-policy')){
			$sn=$f_path[1];//Social Network
			$this->maincom=(object)array(
				'com'=>'social',
				'position'=>'content',
				'segments'=>array('view'=>'ppolicy'),
				);
			$this->rules[]=$this->maincom;
			return true;
			}

		BLog::addtolog('[Router]: parseurl_social() no rules! $f_path='.var_export($f_path,true),LL_ERROR);
		return false;
		}
	/**
	 * Parse /users/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseurl_users($f_path){
		BLog::addtolog('[Router]: We are in users branch now!');
		//Unset the latest empty "/" in url.
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_users() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//
		if((count($f_path)==1)&&(($f_path[0]=='login')||($f_path[0]=='logout'))){
			$sn=$f_path[1];//users Network
			$this->maincom=(object)array(
				'com'=>'users',
				'position'=>'content',
				'segments'=>array('view'=>$f_path[0]),
				);
			$this->rules[]=$this->maincom;
			return true;
			}
		BLog::addtolog('[Router]: parseurl_users() no rules! $f_path='.var_export($f_path,true),LL_ERROR);
		return false;
		}
	/**
	 * Parse /other/ branch.
	 *
	 * Language - $this->langcode
	 */
	public function parseurl_other($f_path){
		BLog::addtolog('[Router]: We are in other branch now!');
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseurl_other() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//
		if((count($f_path)==1)&&($f_path[0]=='submitnews.json')){
			$this->maincom=(object)array(
				'com'=>'other',
				'position'=>'content',
				'segments'=>array('view'=>'submitnews'),
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		if((count($f_path)==1)&&($f_path[0]=='submitads.json')){
			$this->maincom=(object)array(
				'com'=>'other',
				'position'=>'content',
				'segments'=>array('view'=>'submitads'),
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		if((count($f_path)==1)&&($f_path[0]=='addticket.json')){
			$this->maincom=(object)array(
				'com'=>'other',
				'position'=>'content',
				'segments'=>array('view'=>'addticket'),
				);
			$this->ctype=CTYPE_JSON;
			$this->rules[]=$this->maincom;
			return true;
			}
		//
		if((count($f_path)==1)&&($f_path[0]=='contacts')){
			$this->maincom=(object)array(
				'com'=>'other',
				'position'=>'content',
				'segments'=>array('view'=>'contacts'),
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			//$this->softmodulesget('blogs:authors');
			$this->softmodulesget('other:home');
			return true;
			}
		//
		if(empty($f_path)){
			$this->maincom=(object)array(
				'com'=>'other',
				'position'=>'content',
				'segments'=>array('view'=>'home'),
			);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('other:home');
			return true;
			}
		BLog::addtolog('[Router]: parseurl_other() no rules! $f_path='.var_export($f_path,true),LL_ERROR);
		return false;
		}
	/**
	 * Parse URL and returns segments, if all is ok.
	 *
	 *
	 */
	public function parseurl($URL,$host){
		$u=parse_url($URL);
		$u_path=$u['path'];
		$u_query=$u['query'];
		$u_fragment=$u['fragment'];


		parse_str($u_query,$f_query);
		$f_path=explode('/',$u_path);
		array_shift($f_path);
		//Get subdomain type
		$exploded_host=explode('.',$host);
		if($exploded_host[0]=='www'){
			$this->ctype=CTYPE_REDIRECT301;
			$this->redirectURL='//'.BHOSTNAME.$URL;
			return;
			}
		if($exploded_host[0]=='admin'){
			bimport('cms.language');
			BLang::init('ru','admin');// adminlagugages
			return $this->parse_adminurl($f_path);
			}
		//Detect language
		if($f_path[0]==='ru'){
			$this->langcode='ru';
			array_shift($f_path);
			}else{
			$this->langcode='ua';
			}
		$lang=$this->langcode;
		if(ROUTER_DEBUG){
			BLog::addtolog('Router lang='.$lang);
			}
		bimport('cms.language');
		BLang::init($this->langcode);

		if($f_path[0]=='switchmobile'){
			$this->maincom=(object)array(
				'com'=>'switchmobileversion',
				'position'=>'content',
				'segments'=>array('view'=>'switch')
				);
			$this->rules[]=$this->maincom;
			return true;
			}
		elseif($f_path[0]=='news'){
			array_shift($f_path);
			return $this->parseurl_news($f_path);
			}
		elseif($f_path[0]=='blogs'){
			array_shift($f_path);
			return $this->parseurl_blogs($f_path);
			}
		elseif($f_path[0]=='affiche'){
			array_shift($f_path);
			return $this->parseurl_affiche($f_path);
			}
		elseif($f_path[0]=='quizzes'){
			array_shift($f_path);
			return $this->parseurl_quizzes($f_path);
			}
		elseif($f_path[0]=='contests'){
			array_shift($f_path);
			return $this->parseurl_contests($f_path);
			}
		elseif($f_path['0']=='polls'){
			array_shift($f_path);
			return $this->parseurl_polls($f_path);
			}
		elseif($f_path[0]=='tags'){
			array_shift($f_path);
			return $this->parseurl_tags($f_path);
			}
		elseif($f_path[0]=='social'){
			array_shift($f_path);
			return $this->parseurl_social($f_path);
			}
		elseif($f_path[0]=='users'){
			array_shift($f_path);
			return $this->parseurl_users($f_path);
			}
		elseif($f_path[0]=='other'){
			array_shift($f_path);
			return $this->parseurl_other($f_path);
			}
		elseif(count($f_path)==0||(count($f_path)==1&&$f_path[0]=='')){
			$this->maincom=(object)array(
				'com'=>'mainpage',
				'position'=>'content',
				'segments'=>array('view'=>'mainpage',)
				);
			$this->addfixedrules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('mainpage');
			return true;
			}

		return false;
		}//end of ParseURL
	}
