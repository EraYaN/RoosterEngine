<?php
// ----- set mb_http_output encoding to UTF-8 -----
mb_http_output('UTF-8');

// ----- setup php for working with Unicode data -----
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('neutral');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
if(!headers_sent()){
	session_start();
}
error_reporting(E_ALL);
setlocale(LC_ALL,'nl_NL.utf8');
setlocale(LC_NUMERIC, 'en_US.utf8'); // let's overwrite the decimal separator

if(@$_GET['d']==1){
	define('DEBUG',true);
	
} else {
	define('DEBUG',false);
}

$dagen = array('Zondag','Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag');
$dagen_search = array_flip($dagen);
$types = array('H'=>'Hoorcollege','W'=>'Werkgroep','X'=>'Mentoraat','T'=>'Tentamen','R'=>'Anders');
define('BASE_URL_R','http://www.leidenuniv.nl/'); //rooster host
define('DEFAULT_ROOSTER_PROGRAMME','tcj-ba1'); //rooster file default
define('DEFAULT_ROOSTER_FACULTY','fgw'); //rooster faculty default
define('DEFAULT_ROOSTER_KIND','lec'); //rooster kind default
define('DEFAULT_ROOSTER_SEMESTER',1); //rooster semester default
define('DEFAULT_ROOSTER_GROUPS',false); //rooster groups default (false is all)
define('BASE_URL_L','http://labs.erayan.com/roosters/leiden/'); //local host
define('BASE_URL_L_ICAL','ical.php'); //local host

define('SEPARATION_MONTH_SCHOOLYEAR','8');
define('SEPARATION_WEEK_SCHOOLYEAR','32');

define('ICAL_DATE','Ymd\THis');
define('ICAL_TZ','Europe/Amsterdam');

define('PRODUCT_NAME','EraYaN\'s Leiden Rooster Parser');
define('PRODUCT_VERSION','v0.9');

require_once('RoosterItemBase.php');
require_once('RoosterItem.php');
require_once('RoosterItemSet.php');
require_once('Rooster.php');
require_once('functions.php');
?>