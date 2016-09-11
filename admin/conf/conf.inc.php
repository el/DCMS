<?php

$_db = array (
  'host' => 'localhost',
  'db' => 'dms',
  'user' => 'root',
  'pass' => 'root',
  'pre' => '',
);

$site = array (
  'name' => 'veprom | radio',
  'mail' => 'elizkilic@gmail.com',
  'url' => 'http://localhost/dms/',
  'urla' => 'admin/',
  'assets' => 'http://localhost/bulut/assets/',
  'timezone' => 'Europe/Istanbul',
  'languages' => 
  array (
    'tr' => 'Türkçe',
    'en' => 'English',
    'de' => 'Almanca',
    'sp' => 'İspanyolca',
    'fr' => 'Fransızca',
  ),
  'default_language' => 1,
  'analytics' => 'ga:',
  'updates' => false,
  'debug' => false,
  'cache' => 'files/cache/',
  'video-convert' => false,
  'site-mode' => true,
  'app-mode' => true,
  'google' => false,
  'cron' => 20,
  'blockip' => 
  array (
  ),
);

define('NAME', 'veprom | radio');
define('UURL', 'http://update.veprom.com/');

require_once(dirname(realpath(__FILE__))."/../inc/language.inc.php");
require_once("contents.inc.php");
require_once("parts.inc.php");
require_once("ext.inc.php");
if (!$site["debug"]) error_reporting(0);
