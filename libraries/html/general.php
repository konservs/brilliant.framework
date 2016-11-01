<?php
/**
 * Sets of functions and classes to work with HTML
 * 
 * @author Andrii Biriev
 *
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
define('DOCTYPE_UNKNOWN',0);
define('DOCTYPE_HTML4_TRANSITIONAL',1);
//
define('JS_PRIORITY_FRAMEWORK',-1);
define('JS_PRIORITY_FRAMEWORK2',0); //for frameworks 
define('JS_PRIORITY_NORMAL',1);
bimport('log.general');

class BHTML{
	protected static $instance=NULL;
	protected $meta=array();
	protected $link=array();
	protected $style=array();
	protected $css=array();
	protected $js=array();
	protected $frameworks=array();
	protected $locationurl;
	public $doctype=DOCTYPE_HTML4_TRANSITIONAL;
	public $title='';
	public $body='';
	public $last_modified=NULL;
	public $link_canonical='';
	public $locationtime;
	/**
	 * If set into true - we will use js lazy-load for loading JS & CSS files
	 * @var boolean 
	 */
	public $lazyload=false;
	/**
	 * After body
	 * 
	 * @var string
	 */
	public $afterbody='';
	/**
	 * BHTML Constructor
	 */
	public function __construct(){
		//Maybe, move this somewhere else?
		$this->add_link(array('rel'=>'shortcut icon','href'=>'/favicon.ico'));
		}
	/**
	 * Returns the global Session object, only creating it
	 * if it doesn't already exist.
	 * 
	 * @return null|\BHTML instance of BHTML class
	 */
	public static function getInstance(){
		if(!is_object(self::$instance)){
			self::$instance=new BHTML();
			}
		return self::$instance;
		}
	/**
	 * 
	 * @param type $lu
	 * @param type $lt
	 */
	public function setLocationUrl($lu,$lt){
		BLog::addtolog('[HTML] setLocationUrl()');
		$this->locationurl=$lu;
		$this->locationtime=$lt;
		}
	/**
	 * 
	 * @param DateTime $dt
	 */
	public function setLastModified($dt){
		if($dt instanceof DateTime){
			$last_modified=$dt;
			}else{
			$last_modified=new DateTime($dt);
			}
		if ((!empty($this->last_modified))){
			$interval=date_diff($this->last_modified,$last_modified);
                        }
		if ((empty($this->last_modified))||($interval->invert==0)){
			$this->last_modified=$last_modified;
			}
		}
	/**
	 * 
	 * @param type $name
	 * @param type $content
	 * @param type $http_equiv
	 */
	public function add_meta($name,$content,$http_equiv=''){
		$this->meta[]=array(
			'name'=>$name,
			'http_equiv'=>$http_equiv,
			'content'=>$content
			);
		}
	/**
	 * 
	 * @param type $array
	 */
	public function add_link($array){
		$this->link[]=$array;
		}
	/**
	 * 
	 * @param type $style
	 */
	public function add_css_declaration($style){
		$this->style[]=$style;
		}
	/**
	 * 
	 * @param type $name
	 * @param type $media
	 */
	public function add_css($name,$media=''){
		$lnk=array();
		$lnk['rel']='stylesheet';
		$lnk['href']=$name;
		$lnk['type']='text/css';
		if(!empty($media)){
			$lnk['media']=$media;
			}
		$this->add_link($lnk);
		}
	/**
	 * 
	 * @param type $filename
	 * @param type $name
	 * @param type $media
	 * @return boolean
	 */
	public function add_less($filename,$name,$media=''){
		BLog::addtolog('[BHTML]: Adding LESS ('.$filename.')');
		$fn_in=$filename;
		$fn_out=$filename.'.css';
		//
		if(!file_exists($fn_in)){
			BLog::addtolog('[BHTML]: LESS file does not exists!',LL_ERROR);
			return false;
			}
		if(file_exists($fn_out)){
			$time_in=filemtime($fn_in);
			$time_out=filemtime($fn_out);
			if(DEBUG_MODE){
				BLog::addtolog('[BHTML]: Time of LESS: '.date('Y-m-d H:i:s',$time_in));
				BLog::addtolog('[BHTML]: Time of CSS : '.date('Y-m-d H:i:s',$time_out));
				}
			//The CSS file is newest
			if($time_in<=$time_out){ //TODO remove rthis fix
				if(DEBUG_MODE){
					BLog::addtolog('[BHTML]: The CSS is actual!');
					}
				$this->add_css($name,$media);
				return true;
				}
			}
		//
		if(DEBUG_MODE){
			BLog::addtolog('[BHTML]: Time to compile CSS!');
			}
		bimport('html.less.Less');
		$options=array();
		$options['compress']=true;
		if(DEBUG_MODE){
			$options['sourceMap']=true;
			}
		$parser = new Less_Parser($options);
		$parser->parseFile( $filename,'');
		$css = $parser->getCss();
		//TODO caching css
		file_put_contents($filename.'.css',$css);
		$this->add_css($name,$media);
		return true;
		}
	//====================================================
	//
	//====================================================
	public function use_framework($alias=''){
		$this->frameworks[$alias]=$alias;
		if($alias=='jquery-ui'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='polls-desktop'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='bajaxslider'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='the-modal'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='brilliant-mobile'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='isotope'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='brillcalendar'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='datetimepicker'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='simplemodal'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='bxslider'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='masonry'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='mselect'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='sortablelist'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='jquery-sortable'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='nestable'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='summernote'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='jquery-file-upload'){
			$this->frameworks['jquery']='jquery';
			$this->frameworks['javascript-templates']='javascript-templates';
			}
		if($alias=='jquery-imgareaselect'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='owl-carousel'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='fotorama'){
			$this->frameworks['jquery']='jquery';
			}
		if($alias=='slick'){
			$this->frameworks['jquery']='jquery';
			}
		}
	//====================================================
	//
	//====================================================
	public function add_js($src,$val='',$priority=100){
		$this->js[]=array(
			'src' => $src,
			'val' => $val,
			'priority'=>$priority,
			'lazy'=>true,//Load after, if it is possible.
			);
		}
	//====================================================
	//
	//====================================================
	public function out_doctype(){
		switch($this->doctype){
			case DOCTYPE_HTML4_TRANSITIONAL:
				echo('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'.PHP_EOL);
				break;
			}
		}
	/**
	 * Set the title of HTML head
	 * 
	 * @param string $title
	 */
	public function setTitle($title){
		$this->title=$title;
		}
	/**
	 * 
	 */
	public function js_sort(){
		//sort js with priority
		$n=count($this->js);
		for($i=0; $i<$n; $i++){
			$min=$i;
			for($j=$i+1; $j<$n; $j++){
				if($this->js[$j]['priority']<$this->js[$min]['priority']){
					$min=$j;
					}
				}
			if($min<>$i){
				$t=$this->js[$min];
				$this->js[$min]=$this->js[$i];
				$this->js[$i]=$t;
				}
			}
		}
	/**
	 * Pre-processing the JS/CSS frameworks ...
	 */
	public function frameworks_process(){
		foreach($this->frameworks as $framework){
			switch($framework){
				case 'jquery': 
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery/2.1.1/jquery.min.js','',JS_PRIORITY_FRAMEWORK);
					break;
				case 'jquery-ui': 
					$this->add_js('//'.BHOSTNAME_STATIC.'/js/jquery-ui.min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'jquery-ui-datepicker-ru': 
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery.ui/datepicker-ru.js','',JS_PRIORITY_FRAMEWORK+2);
					break;
				case 'jquery-ui-datepicker-uk': 
				case 'jquery-ui-datepicker-ua': 
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery.ui/datepicker-uk.js','',JS_PRIORITY_FRAMEWORK+2);
					break;
				case 'brilliant': 
					$this->add_js('//'.BHOSTNAME_STATIC.'/js/main.js?v=1.0.3','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'brilliant-mobile':
					$this->add_js('//'.BHOSTNAME_STATIC.'/js/main-mobile.js?v=1.0.2','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'select2': 
					bimport('cms.language');
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/select2/select2.min.js');
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/select2/select2_locale_'.BLang::$langcode.'.js');
					break;					
				case 'polls-desktop': 
					$this->add_js('//'.BHOSTNAME_STATIC.'/js/polls.d.js?v=1.0.1','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'bajaxslider':
					$this->add_js('//'.BHOSTNAME_STATIC.'/js/bajaxslider.d.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'the-modal':
					$this->add_js('//'.BHOSTNAME_STATIC.'/js/the-modal.d.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'brillcalendar': 
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/brillcalendar/brillcalendar.js?v=1.0.1','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'datetimepicker':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/datetimepicker/jquery.datetimepicker.js','',JS_PRIORITY_FRAMEWORK+1);
					$this->add_css('//'.BHOSTNAME_STATIC.'/libs/datetimepicker/jquery.datetimepicker.css');
					break;
				case 'sortablelist':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/sortablelist/sortablelist.js','',JS_PRIORITY_FRAMEWORK+1);
					//$this->add_css('//'.BHOSTNAME_STATIC.'/libs/sortablelist/sortablelist.js');
					break;
				case 'jquery-sortable':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery-sortable/jquery-sortable-min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'nestable':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/nestable/jquery.nestable.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'recaptcha2':
					$this->add_js('//www.google.com/recaptcha/api.js','',JS_PRIORITY_FRAMEWORK);
					break;
				case 'simplemodal':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery.simplemodal/jquery.simplemodal.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'bxslider':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery.bxslider/jquery.bxslider.min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'masonry':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery.masonry/jquery.masonry.min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'mselect':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery.mselect/mselect.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'worktimeedit':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery.worktimeedit/worktimeedit.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'summernote':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/summernote-master/dist/summernote.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'tooltip':
					$this->add_js('//'.BHOSTNAME_STATIC.'/js/opentip-jquery-excanvas.min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'tooltipster':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery.tooltipster/js/jquery.tooltipster.min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'isotope':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery.isotope/jquery.isotope.min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'javascript-templates':
					//<!-- The Templates plugin is included to render the upload/download listings -->
					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/javascript-templates/js/tmpl.min.js','',JS_PRIORITY_FRAMEWORK+1);

					break;
				case 'jquery-file-upload':
					//<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/js/vendor/jquery.ui.widget.js','',JS_PRIORITY_FRAMEWORK+1);
					//<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/js/vendor/load-image.all.min.js','',JS_PRIORITY_FRAMEWORK+1);


					//
					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/js/jquery.iframe-transport.js','',JS_PRIORITY_FRAMEWORK+1);


					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/js/jquery.fileupload.js','',JS_PRIORITY_FRAMEWORK+2);
					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/js/jquery.fileupload-process.js','',JS_PRIORITY_FRAMEWORK+3);
					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/js/jquery.fileupload-image.js','',JS_PRIORITY_FRAMEWORK+3);
					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/js/jquery.fileupload-validate.js','',JS_PRIORITY_FRAMEWORK+3);
					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/js/jquery.fileupload-ui.js','',JS_PRIORITY_FRAMEWORK+3);

					$this->add_js('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/js/main.js','',JS_PRIORITY_FRAMEWORK+4);


					$this->add_css('//'.BHOSTNAME_STATIC.'/admin/jquery-file-upload/css/jquery.fileupload.css');
					break;
				case 'jquery-imgareaselect':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/jquery-imgareaselect/scripts/jquery.imgareaselect.min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'owl-carousel':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/owl.carousel/owl.carousel.min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'fotorama':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/fotorama/fotorama.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				case 'slick':
					$this->add_js('//'.BHOSTNAME_STATIC.'/libs/slick/slick/slick.min.js','',JS_PRIORITY_FRAMEWORK+1);
					break;
				}
			}
		return true;
		}
	/**
	 * 
	 */ 
	public function lazyload_prepare(){
		//Process styles...
		$css_links=array();
		foreach($this->link as $k=>$lnk){
			if($lnk['rel']=='stylesheet'){
				$css_links[]=$lnk;
				unset($this->link[$k]);
				}
			}
		//
		$js='var lazy_scripts=[];'.PHP_EOL;
		$this->js_sort();
		//finish scripts
		$js.='	function loadjsfinish(){'.PHP_EOL;
		$js.='		window.console&&console.log("[BLazy]: finish JS files...");'.PHP_EOL;
		$i=0;
		foreach($this->js as $k=>$jjs){
			if(!empty($jjs['val'])){
				$js.='		window.console&&console.log(\'[BLazy]: script['.$i.']...\');'.PHP_EOL;
				$js.='		try{'.PHP_EOL;
				$js.='			'.$jjs['val'].PHP_EOL;
				$js.='		}catch(err){'.PHP_EOL;
				$js.='			window.console&&console.log(\'%c[BLazy]: Fetched error: \'+err.message,\'color: red; font-weight: bold;\');'.PHP_EOL;
				$js.='			}'.PHP_EOL;
				$i++;
				unset($this->js[$k]);
				}
			}
		$js.='		window.console&&console.log("[BLazy]: Loading done!");'.PHP_EOL;
		$js.='		var event = new CustomEvent("BLazyDone", {});'.PHP_EOL;
		$js.='		window.dispatchEvent(event);'.PHP_EOL;
		$js.='		if(window.onblazydone){'.PHP_EOL;
		$js.='			window.onblazydone()'.PHP_EOL;
		$js.='			}'.PHP_EOL;
		$js.='	}'.PHP_EOL;
		//get script from stack and load it
		$js.='	function loadjsnext(){'.PHP_EOL;
		$js.='		var a=lazy_scripts.pop();'.PHP_EOL;
		$js.='		if(a==undefined){'.PHP_EOL;
		$js.='			return loadjsfinish();'.PHP_EOL;
		$js.='			}'.PHP_EOL;
		$js.='		else{'.PHP_EOL;
		$js.='			window.console&&console.log(\'[BLazy]: loadjsnext.load(\'+a+\');\');'.PHP_EOL;
		$js.='			var s=document.createElement(\'script\');'.PHP_EOL;
		$js.='			s.setAttribute("type","text/javascript");'.PHP_EOL;
		$js.='			s.setAttribute("src", a);'.PHP_EOL;
		$js.='			s.onreadystatechange = loadjsnext;'.PHP_EOL;
		$js.='			s.onload = loadjsnext;'.PHP_EOL;
		$js.='			var x=document.getElementsByTagName(\'head\')[0];'.PHP_EOL;
		$js.='			x.appendChild(s);'.PHP_EOL;
		$js.='			}'.PHP_EOL;
		$js.='		}'.PHP_EOL;

		$js.='	function lazy_loadcssfile(filename){'.PHP_EOL.
			'		var s=document.createElement("link");'.PHP_EOL.
			'		s.setAttribute("rel", "stylesheet");'.PHP_EOL.
			'		s.setAttribute("type", "text/css");'.PHP_EOL.
			'		s.setAttribute("href",filename);'.PHP_EOL.
			'		var x=document.getElementsByTagName("head")[0];'.PHP_EOL.
			'		x.appendChild(s);'.PHP_EOL.
			'		}'.PHP_EOL;
		
		$js.='	function lazy_initall(){'.PHP_EOL;
		$js.='		window.console&&console.log("[BLazy]: The DOM is ready. loading additional CSS & js files...");'.PHP_EOL;
		//
		if(!empty($css_links)){
			$this->afterbody.='<!-- lazy-load noscript -->'.PHP_EOL;
			$this->afterbody.='<noscript>'.PHP_EOL;
			foreach($css_links as $lnk){
				$this->afterbody.='<link rel="stylesheet" href="'.$lnk['href'].'">'.PHP_EOL;
				$js.='	window.console&&console.log("[BLazy]: loading css file ('.$lnk['href'].')...");'.PHP_EOL;
				$js.='	lazy_loadcssfile("'.$lnk['href'].'");'.PHP_EOL;
				}
			$this->afterbody.='</noscript>'.PHP_EOL;
			}
		//JS files
		foreach($this->js as $k=>$jjs){
			if(!empty($jjs['src'])){
				$js.='		window.console&&console.log(\'[BLazy]: initall.push script ('.$jjs['priority'].','.$jjs['src'].')\');'.PHP_EOL;
				$js.='		lazy_scripts.push(\''.$jjs['src'].'\');'.PHP_EOL;
				unset($this->js[$k]);
				}
			}
		$js.='		lazy_scripts=lazy_scripts.reverse();'.PHP_EOL;
		$js.='		loadjsnext();'.PHP_EOL;
		$js.='		}'.PHP_EOL;
		//Generate additional
		$js.='window.console&&console.log("[BLazy]: initializing...");'.PHP_EOL;
		$js.='if(window.addEventListener){'.PHP_EOL.
			'	window.addEventListener("load", lazy_initall, false);'.PHP_EOL.
			'	}else'.PHP_EOL.
			'if(window.attachEvent){'.PHP_EOL.
			'	window.attachEvent("onload", lazy_initall);'.PHP_EOL.
			'	}else{'.PHP_EOL.
			'	window.onload=lazy_initall;'.PHP_EOL.
			'	}'.PHP_EOL;
		$this->add_js('',$js);
		}
	/**
	 * Output the head of the document
	 */
	public function out_head(){
		$this->frameworks_process();
		if($this->lazyload){
			$this->lazyload_prepare();
			}
		//Outputing
		$tab='  ';
		echo($tab.'<title>'.$this->title.'</title>'.PHP_EOL);
		if(!empty($this->link_canonical)){
			echo($tab.'<link rel="canonical" href="'.$this->link_canonical.'" />'.PHP_EOL);
			}
		//Add meta info...
		foreach($this->meta as $mi){
			echo($tab.'<meta');
			if(!empty($mi['name'])){
				echo(' name="'.$mi['name'].'"');
				}
			if(!empty($mi['http_equiv'])){
				echo(' http-equiv="'.$mi['http_equiv'].'"');
				}
			echo(' content="'.htmlspecialchars($mi['content']).'"');
			echo('>'.PHP_EOL);
			}
		//Add CSS, author, etc.
		foreach($this->link as $lnk){
			echo($tab.'<link');
			foreach($lnk as $key=>$value){
				echo(' '.$key.'="'.htmlspecialchars($value).'"');
				}
			echo(' />'.PHP_EOL);
			}
		//sort js with priority
		$this->js_sort();
		//Outputing style declaration...
		foreach($this->style as $style){
			echo($tab.'<style>');
			echo($style);
			echo($tab.'</style>'.PHP_EOL);
			}
		//Add javascript...
		foreach($this->js as $js){
			echo($tab.'<script type="text/javascript"');
			if(!empty($js['src'])){
				echo(' src="'.$js['src'].'"');
				}
			echo('>'.$js['val'].'</script>'.PHP_EOL);
			}
		}
	//====================================================
	//
	//====================================================
	public function headers_check(){
		if(!empty($this->locationurl)){
			if($this->locationtime!=0){
				header('Refresh: '.$this->locationtime.'; url='.$this->locationurl);
				}
			else{
				if(!DEBUG_MODE)
					header("Location: ".$this->locationurl);
				//TODO 303 redirect
				}
			}
		if(!empty($this->last_modified)){
			header('Last-Modified: '.$this->last_modified->format('D, d M Y H:i:s').' GMT');
			}
		header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache'); // HTTP/1.0
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		}
	}
