<?php
defined('BEXEC')or die('No direct access!');
bimport('html.general');
bimport('http.request');
bimport('cms.breadcrumbs');
$printversion=BRequest::GetInt('printversion');
$bhtml=BHTML::getInstance();
$bhtml->add_meta('viewport','width=device-width, initial-scale=0.35, maximum-scale=1, user-scalable=yes');
$brouter=BRouter::getInstance();
if((defined('DEBUG_SITENOINDEX'))&&(DEBUG_SITENOINDEX)){
	$bhtml->add_meta('robots','NOINDEX, NOFOLLOW');
	}
$bhtml->add_js('','
	window.roothostname="'.BHOSTNAME.'";
	');
$bhtml->use_framework('jquery');
$bhtml->use_framework('brilliant');
$bhtml->use_framework('bajaxslider');
$bhtml->add_css('//'.BHOSTNAME_STATIC.'/css/main.css?v=1.0.5');
$bhtml->add_meta('','text/html; charset=utf-8','Content-Type');

$canonical=array();
$canonical['href']='//'.$brouter->generateURLmain('',false);
$canonical['rel']='canonical';
$bhtml->add_link($canonical);
$url_main='//'.$brouter->generateUrl('mainpage',BLang::$langcode,array('view'=>'mainpage'));


$col_left=($this->countcomponents('leftcolumn')>0);
$col_right=($this->countcomponents('rightcolumn')>0);
$clspref='';
if($col_left)
	$clspref.='l';
if($col_right)
	$clspref.='r';
$clspref.='c-';

$now=new DateTime();
$currentdatestr=BLang::_('DAYOFWEEK'.$now->format('N')).', ';//Понеділок,
$currentdatestr.=$now->format('d').' ';//01 
$currentdatestr.=BLang::_('MONTH_GENITIVE'.$now->format('n')).' ';//Січня
$currentdatestr.=$now->format('Y');//01 
//$currentdatestr=', 11 січня 2016';
?>
<!DOCTYPE html>
<html<?php echo($printversion?' class="printversion"':''); ?>>
	<head>
		<?php $bhtml->out_head();?>
		<?php if($printversion): ?>
			<script>
				window.print();
			</script>
		<?php endif; ?>
	</head>
<body lang="<?php echo(BLang::$langcode_web) ?>" itemscope itemtype="http://schema.org/WebPage">
<div id="footer-pusher">
	<div id="before-head">
		<div class="wrapper">
			<div class="lang">{{position:headlang}}</div>
			{{position:headmenu}}
			<div class="clear"></div>
		</div>
	</div>

	<?php if($this->countcomponents('bgstyling')>0): ?>
		<div id="bgstyling">{{position:bgstyling}}</div>
	<?php endif; ?>


	<?php if($this->countcomponents('topbigbanner')>0): ?>
		<div id="topbigbanner" class="wrapper">{{position:topbigbanner}}</div>
	<?php endif; ?>
	<div id="head">
		<div id="headw" class="wrapperx">
			<a class="logo" href="<?php echo $url_main; ?>"><div class="logofooter"><b>Brilliant Framework</b>&nbsp;&ndash; <span class="date"><?php echo $currentdatestr; ?></span></div></a>
			<div class="afterlogo"></div>
			<div class="header_rec">{{position:headerad}}</div>
			<div class="clear"></div>
		</div>
	</div>
	<div id="menusbar">
	<?php if($this->countcomponents('mainmenu')>0): ?>
		<div class="redmenu">
			<div class="wrapper wrapperx">
				<div class="wrapper redwrapper">
					<div class="redmenuinner">{{position:mainmenu}}</div>
					<div class="redmenusearch">{{position:newssearch}}</div>
					<div class="redmenubutton">{{position:addbutton}}</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	</div>

<div class="wrapperx" id="mainwrapper">
{{position:beforeall}}
<div id="position-breadcrumbs">
{{position:beforebreadcrumbs}}
<?php BGeneralBreadcrumbs::staticdraw(); ?>
<div class="clear"></div>
</div>
<?php
if($col_left){
	echo('<div class="leftcolumn '.$clspref.'leftcolumn">{{position:leftcolumn}}</div>');
	}
?>
<div class="content <?php echo($clspref); ?>content">
{{position:beforefcols}}
	<div class="firstcolumns">
		<div class="w50 fcol fcol-left">
			<div class="wr">{{position:fcolleft}}</div>
		</div>
		<div class="w50 fcol fcol-right">
			<div class="wr">{{position:fcolright}}</div>
		</div>
		<div class="clear"></div>
	</div>

	{{position:beforecontent}}
	{{position:content}}
	{{position:aftercontent}}

	<div class="aftercolumns">
		<div class="w50 acol acol-left">
			<div class="wr">{{position:acolleft}}</div>
		</div>
		<div class="w50 acol acol-right">
			<div class="wr">{{position:acolright}}</div>
		</div>
		<div class="clear"></div>
	</div>
</div>
<?php
if($col_right){
	echo('<div class="rightcolumn '.$clspref.'rightcolumn">{{position:rightcolumn}}</div>');
	}
?>
<a href="#top" id="gotop" class="pg pg-gotop"></a>
<div style="clear:both"></div>
{{position:afterall}}
</div>
<div class="push"></div>
</div>

<div id="footer">
</div>
</body></html>

