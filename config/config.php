<?php
//Тип кеширования
define('CACHE_TYPE','files');
//Кеширование URLов к базе
define('DBCACHE_ENABLED','0');
//Режим отладки
define('DEBUG_MODE','1');
//Индексация сайта поисковыми системами
define('DEBUG_SITENOINDEX','1');
//Кеширование HTML блоков
define('DEBUG_PAGES_CACHE','1');
//Папка статических файлов
define('STATIC_PATH','/home/termop/brilliantframework.cv.ua/www/htdocs/static');
//Путь, где будут лежать файлы
define('FILES_PATH','');
//Минимальная длина пароля
define('USERS_PASSWORD_CHARSMIN',5);
//Максимальное количество сотрудников фирмы
define('FIRMS_MAX_EMPLOYEES',10);
//Максимальное количество телефонов
define('USERS_PHONES_MAX',3);
//ID Google аналитики
define('GOOGLE_ANALYTICS_ID','UA-42625773-2');
//Хост MySQL
define('MYSQL_DB_HOST','itbrill5.mysql.ukraine.com.ua');
//Имя пользователя
define('MYSQL_DB_USERNAME','itbrill5_brilliantframework');
//Пароль
define('MYSQL_DB_PASSWORD','8a8qe639');
//База данных
define('MYSQL_DB_NAME','itbrill5_brilliantframework');
//Отправитель (email)
define('EMAIL_SEND_EFROM','noreply@skates.com.ua');
//Отправитель (имя)
define('EMAIL_SEND_NFROM','Робот Від і дО');
//Тип отправки
define('EMAIL_SEND_TYPE','1');
//Email редактора газеты (действия "отправить в газету")
define('EMAIL_ADDRESS_NEWSPAPER','vpupkin97@gmail.com');
//Email для жалоб
define('EMAIL_ADDRESS_COMPLAIN','vpupkin97@gmail.com');
//Email формы обратной связи в справке
define('EMAIL_ADDRESS_HELP','vpupkin97@gmail.com');
//Основной хост
define('BHOSTNAME','brilliantframework.tv');
//Хост статики
define('BHOSTNAME_STATIC','static.brilliantframework.tv');
//Хост медиа
define('BHOSTNAME_MEDIA','media.brilliantframework.tv');
//Поддержка HTTPS для личного кабинета
define('SSL_ACCOUNT_ENABLED','0');
//Ключ Google Maps API V3
define('GOOGLEAPI_MAPS_KEY','');
//Ключ Google Maps Static
define('GOOGLEAPI_MAPS_STATIC_KEY','');
//Ключ Google Maps API V3
define('GOOGLEAPI_YOUTUBE_KEY','');
//Логин шлюза TurboSMS
define('TURBOSMS_GATE_LOGIN','');
//Пароль шлюза TurboSMS
define('TURBOSMS_GATE_PASSWORD','');
//Подпись TurboSMS
define('TURBOSMS_GATE_SIGN','');
//Модерация комментариев
define('NEWS_COMMENTS_MODERATION','1');
//Папка для оригиналов
define('MEDIA_PATH_ORIGINAL','D:\\WORK\\WWW\\brilliantframework.tv\\images');
//Папка для миниатюр
define('MEDIA_PATH_RESIZED','/home/termop/brilliantframework.cv.ua/www/htdocs/media');
//URL медиа сервера
define('MEDIA_URL','http://media.brilliantframework.tv');
//Допустимые размеры
define('MEDIA_TRUSTED_IMAGE_SIZES','r185x115,r870x425,r425x230,r570x280,r575x340,r300x160,r93x93,r110x110,r33x33,r435x275,r267x152,r400x250,r400x265,r434x375,r60x60,r300x200,r147x97,r420x230,r120x120,r150x150,r395x232,r147x87,r128x128,r50x50,r18x18,r230x150,r130x75,r545x375,r110x75,r449x257,r691x339,r99x66,r450x260,r270x270,r426x263,r270x152,w100,r350x250,w800,r695x430,w695,w1900,r245x160,r265x245,r400x400,r100x100,w245');
//файл вотермарки
define('WATERMARK_PATH','D:\\WORK\\WWW\\brilliantframework.tv\\images\\watermark.png');
//Позиция вотермарки
define('WATERMARK_POSITION','1');
//минимальная ширина картинки для наложения
define('WATERMARK_MINWIDTH',450);
//минимальная высота картинки для наложения
define('WATERMARK_MINHEIGHT',450);
//Title (RU)
define('COM_MAINPAGE_TITLE_RU','brilliantframework');
//Title (UA)
define('COM_MAINPAGE_TITLE_UA','brilliantframework');
//Meta Description (RU)
define('COM_MAINPAGE_METADESC_RU','Портал объявлений, на котором быстро продают и легко покупают! Доска объявлений c огромным выбором товаров и услуг. Бесплатные объявления с удобным поиском по областям и городам и простой регистрацией! Дать объявление в интернете можно бесплатно!');
//Meta Description (UA)
define('COM_MAINPAGE_METADESC_UA','Портал оголошень, на якому швидко продають і легко купують! Оголошення c величезним вибором товарів та послуг. Безкоштовні оголошення з зручним пошуком по областях і містах і простою реєстрацією! Дати оголошення в Інтернеті можна безкоштовно!');
//Meta Keywords (RU)
define('COM_MAINPAGE_METAKEYW_RU','');
//Meta Keywords (UA)
define('COM_MAINPAGE_METAKEYW_UA','');
//Цена рубрики "Авто"
define('TABLE_PRICE_AUTO','50');
//Цена рубрики "Недвижимость"
define('TABLE_PRICE_DOM','40');
//Рубрика раздела «Работа»
define('WORK_RUBRIC_ID','9');
//Рубрика авто
define('AUTO_RUBRIC_ID','1');
//Рубрика недвижимости
define('DOM_RUBRIC_ID','3');
//ADMIN_CONFIG_SYSTEMS_NEWS_HITS_DIVISOR
define('NEWS_HITS_DIVISOR',1);
//Делитель хитов объявлений
define('CLASSIFIED_HITS_DIVISOR',1);
//Делитель хитов фирм
define('FIRMS_FIRM_HITS_DIVISOR',1);
//Цена фишки
define('CHIPS_PRICE',1);
//Папка из которой брать файлы
define('IMPORT_DIRECTORY_IMPORT','');
//Папка в которую ложить файлы при успехе
define('IMPORT_DIRECTORY_SUCCESS','');
//Папка в которую ложить файлы при неудаче
define('IMPORT_DIRECTORY_FAIL','');
//удалить поиски по прошествию, дней
define('CLASSIFIED_SEARCH_DELETE_DAYS',10);
//ID мерчанта
define('PAYMENT_PRIVAT24_MERCHANT_ID',101941);
//Пароль мерчанта
define('PAYMENT_PRIVAT24_MERCHANT_PASS','VtO0tk0J88VnP8413j5JH85Y087r7Pc1');
//Валюта счёта мерчанта
define('PAYMENT_PRIVAT24_MERCHANT_CURRENCY','UAH');
//Разрешить использование тестовых мерчантов?
define('PAYMENT_PRIVAT24_MERCHANT_ISTEST','1');
//Публичный ключ
define('PAYMENT_LIQPAY_PUBLIC_KEY','i20722598592');
//Приватный ключ
define('PAYMENT_LIQPAY_PRIVATE_KEY','gjmjHPWg6g9pnof8mN7mriAVmC6PCgarOCbgJi3z');
//Валюта
define('PAYMENT_LIQPAY_CURRENCY','UAH');
//Тестовые аккаунт
define('PAYMENT_LIQPAY_ISTEST','1');
//Секретное слово
define('PAYMENT_24NONSTOP_SECRET','');
//Название сервиса
define('PAYMENT_24NONSTOP_NAME','');

define('SOCIAL_VK_ID','5382262');
define('SOCIAL_VK_SECRET','ghVUC9WSdYNLJAsmfmlH');

define('SOCIAL_FB_ID','1589646648020094');
define('SOCIAL_FB_SECRET','25a985ee069d9a88be94ddc670bb9fd5');

define('SOCIAL_GOOGLE_ID','596619458481-fc76be23d4ar27217g3ptgftpj4evaag.apps.googleusercontent.com');
define('SOCIAL_GOOGLE_SECRET','HzzCM7lu0J2lu_2dxcLyUOnY');

