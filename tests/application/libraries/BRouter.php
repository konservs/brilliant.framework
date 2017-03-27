<?php
/**
 * Test Application router
 * 
 * @author Andrii Biriev, <a@konservs.com>
 * 
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Application;
use Brilliant\cms\BLang;
use Brilliant\cms\BRouterBase;
use Brilliant\log\BLog;
use Brilliant\users\BUsers;

class BRouter extends BRouterBase{
	use \Brilliant\BSingleton;
	protected static $starttime=0;
	/**
	 * Generate URL by component, language and segments
	 * in case of sucessfull parse return URL, else return false;
	 *
	 * @param string $component
	 * @param string $lang
	 * @param array $segments
	 */
	public function generateURL($component,$segments,$options=array()){
		$opt_protocol=isset($options['protocol'])?$options['protocol']:'//';
		$opt_hostname=isset($options['usehostname'])?$options['usehostname']:false;
		//forming preffix
		$pref='';
		if($opt_hostname){
			$pref=$opt_protocol.BHOSTNAME;
			}
		$pref.='/';
		$lang=isset($segments['lang'])?$segments['lang']:\Brilliant\CMS\BLang::$langcode;
		if(($lang!=='en')&&(!empty($lang))){
			$pref.=$lang.'/';
			}
		return $pref;
		}
	/**
	 * Parse URL and returns segments, if all is ok.
	 */
	public function parseUrl($URL,$host){
		$u=parse_url($URL);
		$u_path=$u['path'];
		$u_query=$u['query'];
		$u_fragment=$u['fragment'];
		//
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
		return false;
		}//end of ParseURL
	}
