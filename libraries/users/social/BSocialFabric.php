<?php
/**
 * Social authorization fabric
 *
 * @author: Andrii Biriev
 */
namespace Brilliant\users\social;

use \Brilliant\users\social\BSocialAuther;

class BSocialFabric{
	/**
	 *
	 */
	public static function getAdapter($network){
		$brouter=BRouter::getInstance();
		$completeurl='http://'.$brouter->generateUrl('social',BLang::$langcode,array('view'=>'complete','network'=>$network));

		switch($network){
			case 'vk':
			case 'vkontakte':
				bimport('users.social.vk');
				$config = array(
					'client_id'     => SOCIAL_VK_ID,
					'client_secret' => SOCIAL_VK_SECRET,
					'redirect_uri'  => $completeurl
					);
				$adapter=new BSocialAdapterVk($config);
				break;
			case 'fb':
			case 'facebook':
				bimport('users.social.fb');
				$config = array(
					'client_id'     => SOCIAL_FB_ID,
					'client_secret' => SOCIAL_FB_SECRET,
					'redirect_uri'  => $completeurl
					);
				$adapter=new BSocialAdapterFb($config);
				break;
			case 'google':
				bimport('users.social.google');
				$config = array(
					'client_id'     => SOCIAL_GOOGLE_ID,
					'client_secret' => SOCIAL_GOOGLE_SECRET,
					'redirect_uri'  => $completeurl
					);
				$adapter=new BSocialAdapterGoogle($config);
				break;
			default:
				$adapter=NULL;
			}
		return $adapter;
		}
	/**
	 *
	 */
	public static function getSocialList(){
		return array('vk','fb','google');
		}
	}