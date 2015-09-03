<?php

function debug_dump($var){
	echo '<pre>';
	var_dump($var);
	echo '</pre><br>';
}

function debug_msg($msg,$val=NULL,$html=true,$pre=false){
	if(DEBUG){
		echo $msg;
		if($val!=NULL){
			if($pre&&$html) echo '<pre>';
			var_dump($val);
			if($pre&&$html) echo '</pre>';
		}
		if($html){
			echo '<br>';
		}
		echo PHP_EOL;
	}
}

function cleanupBadHTML($html){
	$search = array(
		'<! --'
		);
	$replace = array(
		'<!--'
		);
	$html = str_replace($search,$replace,$html);

	/*$search_regex = array(
	'/ ?style=".*?" ?/im'
	);
	$replace_regex = array(
	' '
	);
	$html = preg_replace($search_regex,$replace_regex,$html);*/

	//$html = strip_tags($html, '<td><tr><table><body><b><br><html><head><title><meta><p><style><script>');

	// Specify configuration
	$config = array(
			'indent'			=> true,
			'output-xhtml'		=> true,
			'wrap'				=> 0,
			'wrap-attributes'	=> false,
			'clean'				=> true,
			'markup'			=> true,
			'uppercase-tags'	=> false,
			'uppercase-attributes' => false,
			'doctype'			=> '<!doctype html>',
			'sort-attributes'	=> 'alpha'
		);

	// Tidy
	$tidy = new tidy;
	$tidy->parseString($html, $config, 'utf8');
	$tidy->cleanRepair();
	$html = (string)$tidy;
	return $html;
}

function toiCalDate($time){
	return date(ICAL_DATE,$time);
}

function autoFormatSeconds($s,$decimals=2){
	if($s>86400){
		return number_format($s/86400,$decimals). ' days';
	} else if($s>3600){
		return number_format($s/3600,$decimals). ' hours';
	} else if($s>60){
		return number_format($s/60,$decimals). ' minutes';
	} else if($s>1){
		return number_format($s,$decimals). ' seconds';
	} else if($s>0.001){
		return number_format($s*1000,$decimals). ' milliseconds';
	} else if($s>0.000001){
		return number_format($s*1000000,$decimals). ' microseconds';
	} else {
		return number_format($s*1000000000,$decimals). ' nanoseconds';
	}
}
function cmp($a, $b)
{
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
?>