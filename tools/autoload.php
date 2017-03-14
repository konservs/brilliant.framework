<?php
include(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
date_default_timezone_set('Europe/Kiev');
use Brilliant\BFactory;
use Brilliant\log\BLog;
use Brilliant\log\BLoggerConsole;
use Application\BRouter;

BLog::RegisterLogger(new BLoggerConsole());
