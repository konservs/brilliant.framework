<?php
/**
 * General configuration
 * 
 * @author Andrii Biriev
 */
class BConfigGeneral extends BConfigCategory{
	public $alias='general';
	/**
	 * Constructor - fill default fields
	 */
	public function __construct(){
		//parent::__construct();
		$this->name=BLang::_('ADMIN_CONFIG_GENERAL');
		$this->description=BLang::_('ADMIN_CONFIG_GENERALD');
		$this->addgroup_general();
		$this->addgroup_database();
		$this->addgroup_email();
		$this->addgroup_emails();
		$this->addgroup_contacts();
		$this->addgroup_hostnames();
		$this->addgroup_ssl();
		}
	/**
	 * General settings - debug mode, caching, etc.
	 */
	protected function addgroup_general(){
		$fields=array();
		$fld_cachetype=new BConfigFieldList(
			'CACHE_TYPE',
			BLang::_('ADMIN_CONFIG_GENERAL_CACHETYPE'),
			array(
				'nocache'=>BLang::_('ADMIN_CONFIG_GENERAL_CACHETYPE_NOCACHE'),
				'files'=>BLang::_('ADMIN_CONFIG_GENERAL_CACHETYPE_FILES'),
				'memcache'=>BLang::_('ADMIN_CONFIG_GENERAL_CACHETYPE_MEMCACHE'),
				'memcached'=>BLang::_('ADMIN_CONFIG_GENERAL_CACHETYPE_MEMCACHED')
				),
			'memcached');
		$fields[]=$fld_cachetype;
		$fld_cachetype=new BConfigFieldList(
			'DBCACHE_ENABLED',
			'Кеширование URLов к базе',
			array(
				'1'=>'Включено',
				'0'=>'Отключено',
				),
			'0');
		$fields[]=$fld_cachetype;

		switch ($fld_cachetype->getVal()){
			case 'files':
				//Files cache settings...
				$fields[]=new BConfigFieldPath(
					'PATH_CACHE',
					BLang::_('ADMIN_CONFIG_GENERAL_CACHEPATH'),
					BROOTPATH.'filecache'.DIRECTORY_SEPARATOR
					);
				break;
			case 'memcache':
				break;
			case 'memcached':
				break;
			}
		$fields[]=new BConfigFieldList(
			'DEBUG_MODE',
			BLang::_('ADMIN_CONFIG_GENERAL_DEBUGMODE'),
			array(
				0=>BLang::_('ADMIN_CONFIG_GENERAL_DEBUGMODE_OFF'),
				1=>BLang::_('ADMIN_CONFIG_GENERAL_DEBUGMODE_ON')
				),
			0);
		$fields[]=new BConfigFieldList(
			'DEBUG_SITENOINDEX',
			BLang::_('ADMIN_CONFIG_GENERAL_SITENOINDEX'),
			array(
				0=>BLang::_('ADMIN_CONFIG_GENERAL_SITENOINDEX_OFF'),
				1=>BLang::_('ADMIN_CONFIG_GENERAL_SITENOINDEX_ON')
				),
			0);
		$fields[]=new BConfigFieldList(
			'DEBUG_PAGES_CACHE',
			BLang::_('ADMIN_CONFIG_GENERAL_DEBUG_PAGES_CACHE'),
			array(
				0=>BLang::_('ADMIN_CONFIG_GENERAL_DEBUG_PAGES_CACHE_OFF'),
				1=>BLang::_('ADMIN_CONFIG_GENERAL_DEBUG_PAGES_CACHE_ON')
				),
			0);
		//
		$fields[]=new BConfigFieldString(
			'STATIC_PATH',
			BLang::_('ADMIN_CONFIG_GENERAL_STATIC_PATH'),
			'');
		$fields[]=new BConfigFieldString(
			'FILES_PATH',
			BLang::_('ADMIN_CONFIG_GENERAL_FILES_PATH'),
			'');
		//Maximum password chars
		$fields[]=new BConfigFieldInt(
			'USERS_PASSWORD_CHARSMIN', //old - PASSWORD_CHARSMIN
			BLang::_('ADMIN_CONFIG_GENERAL_PASSWORD_CHARSMIN'),
			5);
		//
		$fields[]=new BConfigFieldInt(
			'FIRMS_MAX_EMPLOYEES',
			BLang::_('ADMIN_CONFIG_GENERAL_FIRMS_MAXEMPLOYEES'),
			10);
		//
		$fields[]=new BConfigFieldInt(
			'USERS_PHONES_MAX',
			BLang::_('ADMIN_CONFIG_GENERAL_USERS_PHONES_MAX'),
			3);

		$fields[]=new BConfigFieldString(
			'GOOGLE_ANALYTICS_ID',
			BLang::_('ADMIN_CONFIG_GOOGLE_ANALYTICS_ID'),
			'');		
		//
		$this->registerGroup(BLang::_('ADMIN_CONFIG_GENERAL_GENERAL'),'general',$fields);
		}
	/**
	 * Adding the group of database settings - MySQL host, username, password
	 * and database name
	 */
	protected function addgroup_database(){
		$this->registerGroup(BLang::_('ADMIN_CONFIG_GENERAL_MYSQL'),'database',array(
			new BConfigFieldString(
				'MYSQL_DB_HOST',
				BLang::_('ADMIN_CONFIG_GENERAL_MYSQL_DBHOST'),
				'127.0.0.1'),
			new BConfigFieldString(
				'MYSQL_DB_USERNAME',
				BLang::_('ADMIN_CONFIG_GENERAL_MYSQL_DBUSERNAME'),
				'root'),
			new BConfigFieldPassword(
				'MYSQL_DB_PASSWORD',
				BLang::_('ADMIN_CONFIG_GENERAL_MYSQL_DBPASSWORD'),
				''),
			new BConfigFieldString(
				'MYSQL_DB_NAME',
				BLang::_('ADMIN_CONFIG_GENERAL_MYSQL_DBNAME'),
				'vidido')
			));
		}
	/**
	 * Adding the group of email & contacts settings
	 */
	protected function addgroup_email(){
		$this->registerGroup(BLang::_('ADMIN_CONFIG_GENERAL_EMAIL'),'email',array(
			new BConfigFieldString(
				'EMAIL_SEND_EFROM',
				BLang::_('ADMIN_CONFIG_GENERAL_EMAIL_SENDEFROM'),
				'noreply@vidido.ua'),
			new BConfigFieldString(
				'EMAIL_SEND_NFROM',
				BLang::_('ADMIN_CONFIG_GENERAL_EMAIL_SENDNFROM'),
				'Vid i DO noreply'),
			new BConfigFieldList(
				'EMAIL_SEND_TYPE',
				BLang::_('ADMIN_CONFIG_GENERAL_EMAIL_SENDTYPE'),
				array(
					1=>BLang::_('ADMIN_CONFIG_GENERAL_EMAIL_SENDTYPE1'),
					2=>BLang::_('ADMIN_CONFIG_GENERAL_EMAIL_SENDTYPE2')
					)
				),
			));
		}
	/**
	 * Adding the group of admin / moderators emails.
	 */
	protected function addgroup_emails(){
		$this->registerGroup('Электронные адреса администраторов и модераторов','emails',array(
			new BConfigFieldString(
				'EMAIL_ADDRESS_NEWSPAPER',
				'Email редактора газеты (действия "отправить в газету")',
				'admin@vidido.ua'),
			new BConfigFieldString(
				'EMAIL_ADDRESS_COMPLAIN',
				'Email для жалоб',
				'admin@vidido.ua'),
			new BConfigFieldString(
				'EMAIL_ADDRESS_HELP',
				'Email формы обратной связи в справке',
				'admin@vidido.ua'),

			));
		}
	/**
	 * Adding the group of contacts.
	 */
	protected function addgroup_contacts(){
		$this->registerGroup('Контактная информация','contacts',array(
			new BConfigFieldString(
				'CONTACTS_PHONES_SUPPORT',
				'Телефоны тех. поддержки',
				'(0372) 51-82-51; (0372) 520-111'),
			new BConfigFieldString(
				'CONTACTS_LINK_FACEBOOK',
				'Страница Facebook',
				'https://www.facebook.com/vididoua'),
			new BConfigFieldString(
				'CONTACTS_LINK_VKONTAKTE',
				'Страница vk.com',
				'https://vk.com/vidido_ua'),
			new BConfigFieldString(
				'CONTACTS_LINK_GPLUS',
				'Страница Google+',
				'https://plus.google.com/109378434743458608081'),
			new BConfigFieldString(
				'CONTACTS_LINK_TWITTER',
				'Страница Twitter',
				''),
			));
		}

	/**
	 * Adding fields group with hostnames
	 */
	protected function addgroup_hostnames(){
		$this->registerGroup(BLang::_('ADMIN_CONFIG_GENERAL_HOSTNAMES'),'hostnames',array(
			new BConfigFieldString(
				'BHOSTNAME',
				BLang::_('ADMIN_CONFIG_GENERAL_BHOSTNAME'),
				'vidido.ua'),
			new BConfigFieldString(
				'BHOSTNAME_STATIC',
				BLang::_('ADMIN_CONFIG_GENERAL_BHOSTNAME_STATIC'),
				'static.vidido.ua'),
			new BConfigFieldString(
				'BHOSTNAME_MEDIA',
				BLang::_('ADMIN_CONFIG_GENERAL_BHOSTNAME_MEDIA'),
				'media.vidido.ua')
			));
		}
	/**
	 * Adding fields group with hostnames
	 */
	protected function addgroup_ssl(){
		$this->registerGroup('Настройки HTTPS (SSL)','ssl',array(
			new BConfigFieldList(
				'SSL_ACCOUNT_ENABLED',
				'Поддержка HTTPS для личного кабинета',
				array(
					0=>'Не использовать (приоритет HTTP протокола)',
					1=>'Использовать (приоритет HTTPS протокола)'
					)
				),

			));
		}
	}
//Auto-register category
BConfig::getInstance()->registerCategory('BConfigGeneral');
