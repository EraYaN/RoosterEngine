<?php
require_once('main.php');
ini_set('html_errors',false);
if(DEBUG){	
	header('Content-Type: text/plain; charset=utf-8;');	
}
$payload = array();
if(!empty($_GET['pl'])){
	$payload = json_decode(gzuncompress(base64_decode($_GET['pl'])),true);
	if(json_last_error()!=JSON_ERROR_NONE){
		die('JSON payload was malformed. Error: '.json_last_error_msg());
	}
	//echo 'Got GET URL from external source: '.$url.'<br>'.PHP_EOL;
} else {
	$payload[] = array();
}

$rooster = new Rooster(BASE_URL_L.BASE_URL_L_ICAL.'?'.$_SERVER['QUERY_STRING']);

foreach($payload as $v){
	$rooster->AddRooster($v);
}
$rooster->Expand();
//echo 'Writing iCal...'.PHP_EOL;
$rooster->WriteiCal();

if(!DEBUG){
	header('Content-Type: text/calendar; charset=utf-8; method="PUBLISH"; component="VEVENT"');
	header('Content-Disposition: attachment;filename=ical.ics');
}

?>
