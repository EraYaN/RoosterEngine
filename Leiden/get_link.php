<?php
require_once('main.php');
if(!isset($_SESSION['pl'])){
	$_SESSION['pl']=array();
}
if(!isset($_SESSION['debug'])){
	$_SESSION['debug']=false;
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Link Gen - <?=PRODUCT_NAME.' '.PRODUCT_VERSION?></title>
</head>
<body>
	<h1><?=PRODUCT_NAME.' '.PRODUCT_VERSION?></h1>
	<h2>Link Gen</h2>
	<?php
    if(@$_POST['reset']==1){
        $_SESSION['pl'] = array();
        $_SESSION['debug'] = false;
        echo '<p>Payload gereset</p>';
    }
    if(@$_POST['submit']==1){
        $rooster = array();
        
        if(!empty($_POST['kind'])){
            $rooster['kind'] = $_POST['kind'];	
        }
        if(!empty($_POST['faculty'])){
            $rooster['faculty'] = $_POST['faculty'];	
        }
        if(!empty($_POST['programme'])){
            $rooster['programme'] = $_POST['programme'];
        }
        if(!empty($_POST['filters'])){
            if(is_array($_POST['filters'])){  
                $rooster['filters'] = array();
                foreach($_POST['filters'] as $v){
                    $tmp = explode(';',$v);
                    if(preg_match('/[0-9,]/i',$tmp[1])){
                        $rooster['filters'][$tmp[0]] = explode(',',$tmp[1]);
                    }
                }                
            }
        }
        if(!empty($_POST['semester'])){	
            if(is_numeric($_POST['semester'])){
                $rooster['semester'] = $_POST['semester'];
            }
        }
        if(!in_array($rooster,$_SESSION['pl'])){
            $_SESSION['pl'][] = $rooster;
            echo '<p>Toegevoegd</p>';
        } else {
            echo '<p>Al in payload</p>';
        }
		
    }
    if(isset($_POST['setdebug'])){
        if($_POST['setdebug']==1){
            $_SESSION['debug'] = true;
            echo '<p>Debug mode ON</p>';
        } else if($_POST['setdebug']==0){
            $_SESSION['debug'] = false;
            echo '<p>Debug mode OFF</p>';
        }
    }
    if(count($_SESSION['pl'])){	

        
        $data = json_encode($_SESSION['pl']);
        //echo strlen(lzf_compress($data)).'<br>';
        $compress = gzcompress($data,9);
        $b64 = base64_encode($compress);
        
        $url_b64 = urlencode($b64);
        if(DEBUG){
            echo '<hr>';
            echo '<pre>';
            var_dump($_SESSION['pl']);
            echo '</pre>';
            echo '<hr>';
            echo strlen($data).': data<br>';
            echo strlen(base64_encode($data)).': b64(data)<br>';
            echo strlen($compress).': compressed<br>';
            echo strlen($b64).': b64(compressed)<br>';
            echo $b64.': b64<br>';
            echo $url_b64.': url_b64<br>';
            echo strlen($url_b64).': url_b64<br>';
            echo '<hr>';
        }

        //debug_dump($_SESSION['pl']);
        $calurl = BASE_URL_L.BASE_URL_L_ICAL.'?pl=';
        $calurl .= $url_b64;

        if($_SESSION['debug']){			
            $calurl .= '&d=1';		
        }
        $infourl = BASE_URL_L.'?pl=';
        $infourl .= $url_b64;

        if($_SESSION['debug']){			
            $infourl .= '&d=1';		
        }
	?>
	<p style="font-size: small;"><strong>Info Link:</strong> <a href="<?=$infourl?>" target="_blank"><?=$infourl?></a></p>
	<p style="font-size: small;"><strong>iCal Link:</strong> <a href="<?=$calurl?>" target="_blank"><?=$calurl?></a></p>
	<?php
    }


	?>
	<script type="text/javascript">
	    var currentFilterNumber = 0;
	    function removeFilter(el) {
	        var div = document.getElementById('filters');
	        var filterdiv = document.getElementById(el.target.dataset.id);
	        div.removeChild(filterdiv);
	    }
	    function addFilter() {
	        var div = document.getElementById('filters');

	        var filterdiv = document.createElement('div');
	        filterdiv.id = 'filterjs' + (String)(currentFilterNumber++);
	        var span = document.createElement('span');
	        span.innerHTML = (String)(currentFilterNumber - 1) + ': ';
	        filterdiv.appendChild(span);
	        var input = document.createElement('input');
	        input.type = 'text';
	        input.name = 'filters[]';
	        input.id = filterdiv.id + '_input';
	        input.placeholder = 'VASH5000;101,102';
	        filterdiv.appendChild(input);
	        var button = document.createElement('button');
	        button.innerHTML = '-';
	        button.dataset.id = filterdiv.id;
	        button.onclick = removeFilter;
	        button.type = 'button';
	        filterdiv.appendChild(button);

	        div.appendChild(filterdiv);
	    }
	</script>
	<p><strong>Aantal roosters in link: <?php echo count($_SESSION['pl']);?></strong></p>
	<form method="post">
		<button type="submit" name="reset" value="1">Link resetten</button><br />
		<?php if($_SESSION['debug']){ ?>
		<button type="submit" name="setdebug" value="0">Unset debug</button><?php } else { ?>
		<button type="submit" name="setdebug" value="1">Set debug</button><?php } ?>
		<h3>Rooster toevoegen</h3>
		<label for="f">Faculteit (afkorting)</label><br />
		<input type="text" name="faculty" placeholder="<?=DEFAULT_ROOSTER_FACULTY?>" value="<?=@$_POST['faculty']?>"/><br />
		<label for="r">Rooster (zonder .html)</label><br />
		<input type="text" name="rooster" placeholder="<?=DEFAULT_ROOSTER_PROGRAMME?>" value="<?=@$_POST['rooster']?>"/><br />

		<label for="r">Groep filters (filters zien er zo uit: &lt;(zonder W, H, X of T)&gt;;groep1,groep2) Vakcodes waar geen filter voor is worden allemaal weergeven.</label><br />
		<button type="button" onclick="addFilter();">Add filter</button><br />
		<div id="filters">
			<?php 
            if(is_array(@$_POST['filters'])){
                foreach($_POST['filters'] as $k=>$v){
                    echo '<div id="filterphp'.$k.'"><span>'.$k.': </span><input type="text" name="filters[]" id="filterphp'.$k.'_input" placeholder="VASH5000;101,102" value="'.$v.'"/><button type="button" data-id="filterphp'.$k.'" onclick="removeFilter(event)">-</button></div>';
                }
            }
			?>
		</div>
		<label>
			<input type="radio" name="semester" id="s1" value="1" <?php if(@$_POST['semester']==1) {echo 'checked="checked"';} ?>/>
			Semester 1</label><br />
		<label>
			<input type="radio" name="semester" id="s2" value="2" <?php if(@$_POST['semester']==2) {echo 'checked="checked"';} ?>/>
			Semester 2</label><br />
		<br />
		<label>
			<input type="radio" name="kind" id="klec" value="lec" <?php if(@$_POST['kind']=='lec') {echo 'checked="checked"';} ?>/>
			Lectures</label><br />
		<label>
			<input type="radio" name="kind" id="kten" value="ten" <?php if(@$_POST['kind']=='ten') {echo 'checked="checked"';} ?>/>
			Tentamens</label><br />

		<button type="submit" name="submit" value="1">Toevoegen</button>
	</form>
</body>
</html>
