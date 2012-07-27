<?php
setlocale(LC_ALL, 'ru_RU.UTF-8');
set_time_limit(60 * 60);
require_once dirname(__FILE__) . '/yii/yii.php';
require_once dirname(__FILE__) . '/protected/config/VERSION.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
Yii::createWebApplication($config)->run();