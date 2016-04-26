<?php
/**
 * Settings for payment systems & general payments settings. 
 * 
 * @author Andrii Biriev
 * @author Andrii Karepin
 * @copyright © Brilliant IT corporation, http://it.brilliant.ua
 */
class BConfigPayment extends BConfigCategory{
	public $alias='payment';
	public $name;
	/**
	 * Simple constructor
	 */
	public function __construct(){
		//parent::__construct();
		$this->name=BLang::_('ADMIN_CONFIG_PAYMENT');
		$this->addgroup_privat24();
		$this->addgroup_liqpay();
		$this->addgroup_24nonstop();
		}
	/**
	 * Privat24 settings
	 */
	protected function addgroup_privat24(){
		$this->registerGroup(BLang::_('ADMIN_CONFIG_PAYMENT_PRIVAT24'),'privat24',array(
			new BConfigFieldInt(
				'PAYMENT_PRIVAT24_MERCHANT_ID',
				BLang::_('ADMIN_CONFIG_PAYMENT_PRIVAT24_MERCHANT_ID')),
			new BConfigFieldString(
				'PAYMENT_PRIVAT24_MERCHANT_PASS',
				BLang::_('ADMIN_CONFIG_PAYMENT_PRIVAT24_MERCHANT_PASS')),
			new BConfigFieldList(
				'PAYMENT_PRIVAT24_MERCHANT_CURRENCY',
				BLang::_('ADMIN_CONFIG_PAYMENT_PRIVAT24_MERCHANT_CURRENCY'),
				array(
					'UAH'=>'UAH',
					'USD'=>'USD',
					'EUR'=>'EUR',
					)
				),
			new BConfigFieldList(
				'PAYMENT_PRIVAT24_MERCHANT_ISTEST',
				BLang::_('ADMIN_CONFIG_PAYMENT_PRIVAT24_MERCHANT_ISTEST'),
				array(
					0=>BLang::_('ADMIN_CONFIG_PAYMENT_PRIVAT24_MERCHANT_ISTEST_0'),
					1=>BLang::_('ADMIN_CONFIG_PAYMENT_PRIVAT24_MERCHANT_ISTEST_1'),
					),
				0
				),

			));
		
		}
	/**
	 * LiqPay settings
	 */
	protected function addgroup_liqpay(){
		$this->registerGroup(BLang::_('ADMIN_CONFIG_PAYMENT_LIQPAY'),'liqpay',array(
			new BConfigFieldString(
				'PAYMENT_LIQPAY_PUBLIC_KEY',
				BLang::_('ADMIN_CONFIG_PAYMENT_LIQPAY_PUBLIC_KEY')),
			new BConfigFieldString(
				'PAYMENT_LIQPAY_PRIVATE_KEY',
				BLang::_('ADMIN_CONFIG_PAYMENT_LIQPAY_PRIVATE_KEY')),
			new BConfigFieldList(
				'PAYMENT_LIQPAY_CURRENCY',
				BLang::_('ADMIN_CONFIG_PAYMENT_LIQPAY_CURRENCY'),
				array(
					'UAH'=>'UAH',
					'USD'=>'USD',
					'EUR'=>'EUR',
					'RUB'=>'RUB',
					)
				),
			new BConfigFieldList(
				'PAYMENT_LIQPAY_ISTEST',
				BLang::_('ADMIN_CONFIG_PAYMENT_LIQPAY_ISTEST'),
				array(
					0=>BLang::_('ADMIN_CONFIG_PAYMENT_LIQPAY_ISTEST_0'),
					1=>BLang::_('ADMIN_CONFIG_PAYMENT_LIQPAY_ISTEST_1'),
					),
				0
				),

			));
		}
	protected function addgroup_24nonstop(){
		$this->registerGroup(BLang::_('ADMIN_CONFIG_PAYMENT_24NONSTOP'),'24nonstop',array(
			new BConfigFieldString(
				'PAYMENT_24NONSTOP_SECRET',
				BLang::_('ADMIN_CONFIG_PAYMENT_24NONSTOP_SECRET')),
			new BConfigFieldString(
				'PAYMENT_24NONSTOP_NAME',
				BLang::_('ADMIN_CONFIG_PAYMENT_24NONSTOP_NAME')),
			));
		}

	}
//Auto-register category
BConfig::getInstance()->registerCategory('BConfigPayment');
