<?php
/**
 * Main template for mobile version.
 */
defined('BEXEC')or die('No direct access!');
bimport('html.general');
bimport('cms.breadcrumbs');
$bhtml=BHTML::getInstance();
$bhtml->add_meta('viewport','width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
$bhtml->lazyload=true;
//
$brouter=BRouter::getInstance();
$canonical=array();
$canonical['rel']='canonical';
$canonical['href']='//'.$brouter->generateURLmain('',false);
$bhtml->add_link($canonical);
//
$bhtml->add_css('//'.BHOSTNAME_STATIC.'/css/mobile.css?v=1.0.1');

$bhtml->add_js('','
	window.roothostname="'.BHOSTNAME.'";
	');
$bhtml->add_css_declaration(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'template.m.css'));
$bhtml->add_js('',file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'template.m.js'),0);
if((defined('DEBUG_SITENOINDEX'))&&(DEBUG_SITENOINDEX)){
	$bhtml->add_meta('robots','NOINDEX, NOFOLLOW');
	}
?><!DOCTYPE html>
<html>
<head>
<?php $bhtml->out_head(); ?>
</head>

<body lang="<?php echo(BLang::$langcode_web) ?>">
<div id="header">
	<div id="mainmenu"><i class="pg pg-burgermenu" id="mainmenuicon"></i>Menu</div>
	<span id="mainsearch"><i class="pg pg-search" id="mainsearchicon"></i></span>
	<a class="logo" href='//<?php echo BHOSTNAME;?>/'><span>www.vidido.ua</span></a>

	<div id="gbz">
		<div class="menu-switch-lang">{{position:headlang}}</div>
		{{position:mobilemenu}}
		{{position:topmenu}}
	</div>
</div>

<div id="content">
{{position:beforecontent}}
{{position:content}}
{{position:aftercontent}}
</div>


<div id="footer">
</div>

<?php echo $bhtml->afterbody; ?>	
</body>
</html>
