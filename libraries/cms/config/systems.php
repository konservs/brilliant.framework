<?php
/**
 * Settings for media subsystem. 
 * 
 * @author Andrii Biriev
 */
class BConfigSystems extends BConfigCategory{
	public $alias='systems';
	public $name;
	/**
	 * Simple constructor
	 */
	public function __construct(){
		//parent::__construct();
		$this->name=BLang::_('ADMIN_CONFIG_SYSTEMS');
		$this->addgroup_googleapi();
		$this->addgroup_sms();
		$this->addgroup_news();
		$this->addgroup_media();
		$this->addgroup_mainpage();
		$this->addgroup_deskoffers();
		$this->addgroup_other();
		}
	/**
	 * Google API subsystem settings
	 */
	protected function addgroup_sms(){
		//AIzaSyBmU8f5jfgSTB49ZjxzmvnlYeEhNuh96qY
		$this->registerGroup('Настройки SMS (TurboSMS)','sms',array(
			new BConfigFieldString(
				'TURBOSMS_GATE_LOGIN',
				'Логин шлюза TurboSMS',
				''
				),
			new BConfigFieldString(
				'TURBOSMS_GATE_PASSWORD',
				'Пароль шлюза TurboSMS',
				''
				),
			new BConfigFieldString(
				'TURBOSMS_GATE_SIGN',
				'Подпись TurboSMS',
				''
				),
			));
		}

	/**
	 * Google API subsystem settings
	 */
	protected function addgroup_googleapi(){
		//AIzaSyBmU8f5jfgSTB49ZjxzmvnlYeEhNuh96qY
		$this->registerGroup('Настройки Google API','googleapi',array(
			new BConfigFieldString(
				'GOOGLEAPI_MAPS_KEY',
				'Ключ Google Maps API V3',
				''
				),
			new BConfigFieldString(
				'GOOGLEAPI_MAPS_STATIC_KEY',
				'Ключ Google Maps Static',
				''
				),
			new BConfigFieldString(
				'GOOGLEAPI_YOUTUBE_KEY',
				'Ключ Google Maps API V3',
				''
				),
			));
		}
	/**
	 * News subsystem settings
	 */
	protected function addgroup_news(){
		$this->registerGroup('Настройки новостей','news',array(
			new BConfigFieldList(
				'NEWS_COMMENTS_MODERATION',
				'Модерация комментариев',
				array(
					1=>'Премодерация (Комментарии публикуются вручную)',
					2=>'Постмодерация (Комментарии публикуются автоматически)'
					)
				),
			));
		}
	/**
	 * Media subsystem settings
	 */
	protected function addgroup_media(){
		$this->registerGroup(BLang::_('ADMIN_CONFIG_SYSTEMS_MEDIA'),'media',array(
			new BConfigFieldPath(
				'MEDIA_PATH_ORIGINAL',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MEDIA_PATH_ORIG'),
				BROOTPATH.'htdocs'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'original'
				),
			new BConfigFieldPath(
				'MEDIA_PATH_RESIZED',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MEDIA_PATH_RESIZED'),
				BROOTPATH.'htdocs'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'resized'
				),
			new BConfigFieldString(
				'MEDIA_URL',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MEDIA_URL'),
				'http://media.vidido.ua'
				),
			new BConfigFieldString(
				'MEDIA_TRUSTED_IMAGE_SIZES',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MEDIA_TRUSTEDSIZES'),
				'127.0.0.1'
				),
			new BConfigFieldPath(
				'WATERMARK_PATH',
				'файл вотермарки',
				BROOTPATH.'htdocs'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'watermark.png'
				),
			new BConfigFieldList(
				'WATERMARK_POSITION',
				'Позиция вотермарки',
				array(
					1=>'лево верх',
					2=>'центр верх',
					3=>'право верх',
					4=>'лево центр',
					5=>'центр центр',
					6=>'право центр',
					7=>'лево низ',
					8=>'центр низ',
					9=>'право низ',
					)
				),
			new BConfigFieldInt(
				'WATERMARK_MINWIDTH',
				'минимальная ширина картинки для наложения',
				100
				),
			new BConfigFieldInt(
				'WATERMARK_MINHEIGHT',
				'минимальная высота картинки для наложения',
				100
				)
			));
		}
	/**
	 * Media subsystem settings
	 */
	protected function addgroup_other(){
		$this->registerGroup(BLang::_('ADMIN_CONFIG_SYSTEMS_OTHER'),'other',array(
			new BConfigFieldPath(
				'WORK_RUBRIC_ID',
				BLang::_('ADMIN_CONFIG_SYSTEMS_WORK_RUBRIC')
				),
			new BConfigFieldPath(
				'AUTO_RUBRIC_ID',
				BLang::_('ADMIN_CONFIG_SYSTEMS_AUTO_RUBRIC')
				),
			new BConfigFieldPath(
				'DOM_RUBRIC_ID',
				BLang::_('ADMIN_CONFIG_SYSTEMS_DOM_RUBRIC')
				),
			new BConfigFieldInt(
				'NEWS_HITS_DIVISOR',
				BLang::_('ADMIN_CONFIG_SYSTEMS_NEWS_HITS_DIVISOR'),
				1
				),
			new BConfigFieldInt(
				'CLASSIFIED_HITS_DIVISOR',
				BLang::_('ADMIN_CONFIG_SYSTEMS_CLASSIFIED_HITS_DIVISOR'),
				1
				),
			new BConfigFieldInt(
				'FIRMS_FIRM_HITS_DIVISOR',
				BLang::_('ADMIN_CONFIG_SYSTEMS_FIRMS_FIRM_HITS_DIVISOR'),
				1
				),
			new BConfigFieldInt(
				'NEWAUTOS_HITS_DIVISOR',
				'Делитель просмотров новых авто',
				1
				),
			new BConfigFieldInt(
				'CHIPS_PRICE',
				BLang::_('ADMIN_CONFIG_SYSTEMS_CLASSIFIED_CHIPS_PRICE'),
				1
				),
			new BConfigFieldPath(
				'IMPORT_DIRECTORY_IMPORT',
				BLang::_('ADMIN_CONFIG_SYSTEMS_IMPORT_DIRECTORY_IMPORT')
				),
			new BConfigFieldPath(
				'IMPORT_DIRECTORY_SUCCESS',
				BLang::_('ADMIN_CONFIG_SYSTEMS_IMPORT_DIRECTORY_SUCCESS')
				),
			new BConfigFieldPath(
				'IMPORT_DIRECTORY_FAIL',
				BLang::_('ADMIN_CONFIG_SYSTEMS_IMPORT_DIRECTORY_FAIL')
				),
			new BConfigFieldInt(
				'CLASSIFIED_SEARCH_DELETE_DAYS',
				BLang::_('ADMIN_CONFIG_CLASSIFIED_SEARCH_DELETE_DAYS'),
				10
				),
			));
		}
	/**
	 * Mainpage settings
	 */
	protected function addgroup_mainpage(){
		$this->registerGroup(BLang::_('ADMIN_CONFIG_SYSTEMS_MAINPAGE'),'mainpage',array(
			/*new BConfigFieldString(
				'COM_MAINPAGE_METAIMG',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MAINPAGE_METAIMG'),
				''),*/
			new BConfigFieldString(
				'COM_MAINPAGE_TITLE_RU',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MAINPAGE_TITLERU'),
				''),
			//
			new BConfigFieldString(
				'COM_MAINPAGE_TITLE_UA',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MAINPAGE_TITLEUA'),
				''),
			//
			new BConfigFieldText(
				'COM_MAINPAGE_METADESC_RU',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MAINPAGE_METADESCRU'),
				''),
			//
			new BConfigFieldText(
				'COM_MAINPAGE_METADESC_UA',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MAINPAGE_METADESCUA'),
				''),
			//
			new BConfigFieldString(
				'COM_MAINPAGE_METAKEYW_RU',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MAINPAGE_METAKEYWRU'),
				''),
			//
			new BConfigFieldString(
				'COM_MAINPAGE_METAKEYW_UA',
				BLang::_('ADMIN_CONFIG_SYSTEMS_MAINPAGE_METAKEYWUA'),
				''),
			));
		}
	/**
	 * DeskOffers contacts.
	 */
	protected function addgroup_deskoffers(){
		$this->registerGroup('Стол заказов','deskoffers',array(
			new BConfigFieldString(
				'TABLE_PRICE_AUTO',
				'Цена рубрики "Авто"',
				''),
			//
			new BConfigFieldString(
				'TABLE_PRICE_DOM',
				'Цена рубрики "Недвижимость"',
				''),
			));
		}
	}
//Auto-register category
BConfig::getInstance()->registerCategory('BConfigSystems');
