<?php
include(__DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
include(__DIR__.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'config.php');

$logger = new \Brilliant\Log\BLoggerFile();
$logger->logsDirectory = __DIR__.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR;
\Brilliant\Log\BLog::RegisterLogger($logger);
