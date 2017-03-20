<?php
bimport('router');

class BSiteMapXML{
	protected static $instance=NULL;
	public $smsuffix='</urlset>';
	public $filenames=array();
	public $blimit=10485760;
	public $ilimit=10000;
	public $bcount;//bytes count
	public $icount;//items count


	public static function getInstance(){
		if (!is_object(self::$instance))
			self::$instance=new BSiteMapXML();
		return self::$instance;
		}
	
	public function addURL($url,$lastmod,$changefreq,$priority){
		if($lastmod==NULL)$lastmod=new DateTime('2014');
		if(DEBUG_MODE){
			$tab='	';
			$eol=PHP_EOL;
			}
		$string=''.
		'<url>'.$eol.
			$tab.'<loc>'.$url.'</loc>'.$eol.
			$tab.'<lastmod>'.$lastmod->format('c').'</lastmod>'.$eol.
			$tab.'<changefreq>'.$changefreq.'</changefreq>'.$eol.
			$tab.'<priority>'.$priority.'</priority>'.$eol.
		'</url>'.$eol;
		$bytes=strlen($string);
		if(($this->bcount+$bytes<$this->blimit-strlen($this->smsuffix))&&
		   ($this->icount+1<$this->ilimit)){
			$this->bcount+=$bytes;
			$this->icount++;
			}
		else{
			file_put_contents(end($this->filenames), $this->smsuffix, FILE_APPEND);
			$this->newfile();
			}
		file_put_contents(end($this->filenames), $string, FILE_APPEND);
		unset($url);
		unset($lastmod);
		unset($changefreq);
		unset($priority);
		unset($tab);
		unset($eol);
		unset($string);
		unset($bytes);
		}
	public function newfile(){
		$this->icount=0;
		$this->filenames[]=BROOTPATH.'temp/sitemap-'.microtime(true);
		$s='<?xml version="1.0" encoding="UTF-8"?>'.
			'<urlset'.
			' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
			' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
			' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.
			' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" >';
		$this->bcount=strlen($s);
		file_put_contents(end($this->filenames), $s);
		unset($s);
		}
	///
	public function generatesitemap(){
		$this->newfile();
		$brouter=BRouter::getInstance();
		echo 'mem='.memory_get_usage().' before adding sitemap urls'.PHP_EOL;
		$this->addURL('http://'.BHOSTNAME.'/ua/',$brouter->getlastmod(BHOSTNAME.'/',BHOSTNAME),'weekly',1);//TODO universality
		$this->addURL('http://'.BHOSTNAME.'/',$brouter->getlastmod(BHOSTNAME.'/',BHOSTNAME),'weekly',1);//TODO universality
		$this->add_urls_news();//published articles & categories
		echo 'mem='.memory_get_usage().' added news urls'.PHP_EOL;
		$this->add_urls_classified();//published ads & category page
		
		echo 'mem='.memory_get_usage().' added classified urls'.PHP_EOL;
		
		$this->add_urls_regions();//regions & published cities
		echo 'mem='.memory_get_usage().' added regions urls'.PHP_EOL;
		$this->add_urls_seopages();
		echo 'mem='.memory_get_usage().' added seopages urls'.PHP_EOL;
		
		//$this->add_urls_firms();
		//$this->add_urls_work();
		//$this->add_urls_rubrics();
		//$this->add_urls_help();
		file_put_contents(end($this->filenames), $this->smsuffix, FILE_APPEND);
		if(count($this->filenames)>1){
			file_put_contents(BROOTPATH.'/htdocs/static/sitemaps/sitemap.xml', '<?xml version="1.0" encoding="UTF-8"?>
   <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			foreach($this->filenames as $key=>$file){
				copy($file,BROOTPATH.'/htdocs/static/sitemaps/sitemap-'.$key.'.xml');
				$string=''.
					'<sitemap>'.
					'<loc>http://'.BHOSTNAME.'/sitemap-'.$key.'.xml</loc>'.
					'<lastmod>'.date('c').'</lastmod>'.
					'</sitemap>';
				file_put_contents(BROOTPATH.'/htdocs/static/sitemaps/sitemap.xml', $string, FILE_APPEND);
				}
			file_put_contents(BROOTPATH.'/htdocs/static/sitemaps/sitemap.xml',  '</sitemapindex>', FILE_APPEND);
			}
		else{
			rename($this->filenames[0],BROOTPATH.'/htdocs/static/sitemaps/sitemap.xml');//TODO check
			}
		}
	///
	public function add_urls_regions(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		if(DEBUG_MODE){
			BDebug::message('--------------------------------------------------');
			BDebug::message(' Adding regions pages..');
			}
		$brouter=BRouter::getInstance();
		$qr='select id from regions';
		$q=$db->Query($qr);
		if(!empty($q)){
			while($l=$db->fetch($q)){
				$url=$brouter->generateurl('regions','ru',array('view'=>'region','id'=>$l['id']));
				$hostname=explode('/',$url);
				$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);

				$url=$brouter->generateurl('regions','ua',array('view'=>'region','id'=>$l['id']));
				$hostname=explode('/',$url);
				$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);

				}
			}
		if(DEBUG_MODE){
			BDebug::message('--------------------------------------------------');
			BDebug::message(' Adding cities pages..');
			}
		$qr='select id from regions_cities where published=1';
		$q=$db->Query($qr);
		if(!empty($q)){
			while($l=$db->fetch($q)){
				$url=$brouter->generateurl('regions','ru',array('view'=>'city','id'=>$l['id']));
				$hostname=explode('/',$url);
				$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);

				$url=$brouter->generateurl('regions','ua',array('view'=>'city','id'=>$l['id']));
				$hostname=explode('/',$url);
				$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
				}
			}
		if(DEBUG_MODE){
			BDebug::message(' Cities added.');
			}
		}
	public function add_urls_news(){
		bimport('sql.mysql');
		$brouter=BRouter::getInstance();
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db))return;
		
		$qr='SELECT id from `news_categories` ';
		$q=$db->Query($qr);
		if(!empty($q)){
			while($l=$db->fetch($q)){
				$url=$brouter->generateurl('news','ru',array('view'=>'blog','id'=>$l['id']));
				$hostname=explode('/',$url);
				$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);

				$url=$brouter->generateurl('news','ua',array('view'=>'blog','id'=>$l['id']));
				$hostname=explode('/',$url);
				$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);

				}
			}
		
		
		$wh=array();
		$wh[]='(moderated=1)';
		$wh[]='(published=1)';
		$wh[]='(published_from<NOW() or published_from is NULL)';
		$wh[]='(published_to>NOW() or published_to is NULL)';
		$qr='SELECT id from news_aricles '.implode('AND',$wh);
		$q=$db->Query($qr);
		if(!empty($q)){
			while($l=$db->fetch($q)){
				$url=$brouter->generateurl('news','ru',array('view'=>'article','id'=>$l['id']));
				$hostname=explode('/',$url);
				$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);

				$url=$brouter->generateurl('news','ua',array('view'=>'article','id'=>$l['id']));
				$hostname=explode('/',$url);
				$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
				}
			}
		}
	/**
	 *
	 */
	public function add_urls_classified(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		bimport('classified.general');
		$bc=BCLassified::getInstance();
		$qr='select id from classified_ads where published=1';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$brouter=BRouter::getInstance();
		while($l=$db->fetch($q)){
			$url=$brouter->generateurl('classified','ru',array('view'=>'ad','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			
			$url=$brouter->generateurl('classified','ua',array('view'=>'ad','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			unset($l);
			}
		unset($q);
		$bc->unsetinternalcache();
		$qr='select id from classified_categories';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		while($l=$db->fetch($q)){
			$url=$brouter->generateurl('classified','ru',array('view'=>'category','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			
			$url=$brouter->generateurl('classified','ua',array('view'=>'category','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			}
		$bc->unsetinternalcache();
		return true;
		}
	public function add_urls_firms(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$qr='select id from firms';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$brouter=BRouter::getInstance();
		while($l=$db->fetch($q)){
			$url=$brouter->generateurl('firms','ru',array('view'=>'firm','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			
			$url=$brouter->generateurl('firms','ua',array('view'=>'firm','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			}
		}
	public function add_urls_work(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$qr='select id from work_resume';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$brouter=BRouter::getInstance();
		while($l=$db->fetch($q)){
			$url=$brouter->generateurl('work','ru',array('view'=>'resume','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			
			$url=$brouter->generateurl('work','ua',array('view'=>'resume','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			}
		$qr='select id from work_vacancy';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		while($l=$db->fetch($q)){
			$url=$brouter->generateurl('work','ru',array('view'=>'vacancy','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			
			$url=$brouter->generateurl('work','ua',array('view'=>'vacancy','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			}
		}
	public function add_urls_rubrics(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$qr='select id from rubrics';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$brouter=BRouter::getInstance();
		while($l=$db->fetch($q)){
			$url=$brouter->generateurl('rubric','ru',array('view'=>'rubric','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			
			$url=$brouter->generateurl('rubric','ua',array('view'=>'rubric','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			}
		}
	/**
	 *
	 */
	public function add_urls_seopages(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		if(DEBUG_MODE){
			BDebug::message('--------------------------------------------------');
			BDebug::message(' Adding Category + city SEO pages...');
			}
		$qr='select id from classified_categories';
		bimport('regions.general');
		$brouter=BRouter::getInstance();
		$bregions=BRegions::getInstance();
		$rreg=$bregions->rcities_get_tree('ru');
		$q=$db->Query($qr);
		bimport('classified.general');
		$bc=BClassified::getInstance();
		bimport('regions.general');
		$br=BRegions::getInstance();
		while($l=$db->fetch($q)){
			foreach($rreg as $region){
				foreach($region->cities as $city){
					$segments=array('view'=>'category_city','category'=>$l['id'],'region'=>$region->id,'city'=>$city->id);
					$url=$brouter->generateurl('classified','ru',$segments);

					$hostname=explode('/',$url);
					$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);

					$url=$brouter->generateurl('classified','ua',$segments);
					$hostname=explode('/',$url);
					$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
					unset($segments);
					unset($hostname);
					unset($city);
					}
				unset($region);
				}
			$br->unsetinternalcache();
			unset($bc->cat_cache);
			unset($l);
			}
		unset($q);
		
		}
	public function add_urls_help(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$qr='select id from help_categories';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$brouter=BRouter::getInstance();
		while($l=$db->fetch($q)){
			$url==$brouter->generateurl('help','ru',array('view'=>'category','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			
			$url=$brouter->generateurl('help','ua',array('view'=>'category','id'=>$l['id']));
			$hostname=explode('/',$url);
			$this->addURL('http://'.$url,$brouter->getlastmod($url,$hostname[0]),'weekly',1);
			}
		}
	}
