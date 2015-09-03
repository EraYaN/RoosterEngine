<?php
$start_timestamp = microtime(true);
require_once('main.php');
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Index - <?=PRODUCT_NAME.' '.PRODUCT_VERSION?></title>
</head>
<body>
	<h1><?=PRODUCT_NAME.' '.PRODUCT_VERSION?></h1>
	<h2>Index</h2>
	<a href="/get_link.php" target="_blank">Get a new link</a>
	<?php	
	$payload = array();
	if(!empty($_GET['pl'])){		
		$payload = json_decode(gzuncompress(base64_decode($_GET['pl'])),true);
		if(json_last_error()!=JSON_ERROR_NONE){
			die('JSON payload was malformed. Error: '.json_last_error_msg());
		}
		//echo 'Got GET URL from external source: '.$url.'<br>'.PHP_EOL;
		$icallink = BASE_URL_L.BASE_URL_L_ICAL.'?'.$_SERVER['QUERY_STRING'];
		?>
	<h3>Current Payload</h3>
			<a href="<?=$icallink?>" target="_blank">iCal link (subscription)</a><br />
		<?php
		$rooster = new Rooster($icallink);

		foreach($payload as $v){
			$rooster->AddRooster($v);
		}
		$calendername = 'Rooster'.(count($rooster->names)?'s':'').' '.$rooster->Name;
		$calenderdesc = 'Collegerooster'.(count($rooster->names)?'s':'').' voor '.$rooster->Name.' aan Universiteit Leiden';
		?>
	<p><strong>Calendar Details</strong><br />
		<em>Name:</em> <?=$calendername?><br />
		<em>Published on:</em> <?=$rooster->PublishDate->format('Y-m-d H:i')?><br />
		<em>Description:</em><br/><?=$calenderdesc?>
	</p>
		<?php
		$rooster->Expand();
		$rooster->Sort();		
		if(isset($rooster->RoosterItems[0]->start)){
			$firsteventdate = $rooster->RoosterItems[0]->start;
			
			echo '<h3>First event @ '.$firsteventdate->format('Y-m-d H:i:s').' (that is in '.autoFormatSeconds($firsteventdate->getTimestamp()-time(),1).')</h3>';
			echo '<p>'.$rooster->RoosterItems[0]->getDescriptionHTML().'</p>';			
		}
		$end_timestamp = microtime(true);
		echo '<p>Parsed '.count($payload).' rooster'.(count($payload)==1?'':'s').' in '.autoFormatSeconds(($end_timestamp-$start_timestamp),2).'.</p>';
	} else {
		echo '<p>No payload, get a link <a href="/get_link.php">here</a>';
	}
	

	?>
</body>
</html>
