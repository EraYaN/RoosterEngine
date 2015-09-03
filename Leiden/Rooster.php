<?php

/**
 * Rooster short summary.
 *
 * Rooster description.
 *
 * @version 1.0
 * @author Erwin
 */
class Rooster
{
	const EOL = "\r\n"; 
	public $RoosterItemSets = array();
    public $RoosterItems = array();
	public $Name = NULL;
	public $URL = NULL;
	public $PublishDate = NULL;

	public $names = array();

	private $base_url = BASE_URL_R;

	private $c_url = NULL;
	private $c_config = NULL;
	private $c_raw = NULL;
	private $c_dom = NULL;
	private $c_rows = NULL;
	private $c_name = NULL;
	private $c_publishdate = NULL;

	public function __construct($requestURL=NULL){
		$this->URL = $requestURL;
		$this->base_url .= 'letteren/roosters/rooster';
	}

	public function AddRooster(array $config){		
		$this->c_url = $this->base_url;
		$this->c_config = $config;
		$this->ProcessConfig();
		debug_msg('Processed Config...');
		$this->BuildURL();
		debug_msg('Built URL...');
		$this->GetRaw();
		debug_msg('Got Raw...');
		$this->GetMetadataAndClean();
		debug_msg('Got Metadata and Cleaned...');
		$this->Parse();
		debug_msg('Parsed...');
		$this->Merge();
		debug_msg('Merged...');
		$this->Cleanup();
		debug_msg('Cleaned up...');		
	}
	private function ProcessConfig(){
		if(empty($this->c_config['year'])){
			if(date('W')<SEPARATION_WEEK_SCHOOLYEAR){
				$this->c_config['year'] = intval(date('Y')-1);
			} else {
				$this->c_config['year'] = intval(date('Y'));
			}
		}

		if(empty($this->c_config['faculty'])){			
			$this->c_config['faculty'] = DEFAULT_ROOSTER_FACULTY;
		}

		if(empty($this->c_config['kind'])){			
			$this->c_config['kind'] = DEFAULT_ROOSTER_KIND;
		}

		if(empty($this->c_config['programme'])){			
			$this->c_config['programme'] = DEFAULT_ROOSTER_PROGRAMME;
		}

		if(empty($this->c_config['semester'])){			
			$this->c_config['semester'] = DEFAULT_ROOSTER_SEMESTER;
		}

		if(empty($this->c_config['filters'])){			
			$this->c_config['filters'] = array();
		}
	}
	private function BuildURL(){
		
		$this->c_url .= $this->c_config['year'].'/';
		
		$this->c_url .= $this->c_config['faculty'].'/';
		
		$this->c_url .= $this->c_config['kind'].'/';
		
		$this->c_url .= $this->c_config['programme'].'-S';
		
		$this->c_url .= $this->c_config['semester'].'.html';
		return true;
	}	
	private function GetRaw(){
		$curl = curl_init($this->c_url);
		curl_setopt($curl,CURLOPT_USERAGENT,PRODUCT_NAME.'/'.PRODUCT_VERSION);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_FAILONERROR,true);
		curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
		$this->c_raw = curl_exec($curl);
		if(curl_errno($curl)!=0){
			throw new Exception('cURL error: '.curl_error($curl).' for url: '.$this->c_url);
		}	
		if(mb_strlen($this->c_raw)==0){
			throw new Exception('Rooster server response was empty.');
		}
		curl_close($curl);
		return true;
	}
	private function GetMetadataAndClean(){
		//find name
		$matches = array();
		$this->c_name = 'Rooster';
		if(preg_match('/<br><b>([^<]*)<\/b><\/br>/i',$this->c_raw,$matches)){
			$this->c_name = trim($matches[1]);
		}
		$this->c_raw = cleanupBadHTML($this->c_raw);
		//find publish date
		$matches = array();
		$this->c_publishdate = new DateTime();
		if(preg_match('/Rooster gepubliceerd op ([0-9]{2}-[0-9]{2}-[0-9]{4} [0-9]{2}:[0-9]{2})/i',$this->c_raw,$matches)){			
			$this->c_publishdate = new DateTime($matches[1]);
		}
		
		$this->c_raw = mb_strstr($this->c_raw,"<!--- END Kalenderlines       -->",false);
		
		$this->c_raw = mb_strstr($this->c_raw,'</table>',true)."</table>";
	}

	private function Parse(){
		$this->c_dom = new DOMDocument();
		$this->c_dom->loadHTML($this->c_raw);

		$this->c_rows = Rooster::FindRows($this->c_dom);	
		
		$this->PrettifyResult();

		$this->ProcessResult();	

		return true;
	}
	private function Merge(){
		if(is_array($this->c_rows)){			
			$this->RoosterItemSets = array_merge($this->RoosterItemSets,$this->c_rows);			
			if(!in_array($this->c_name,$this->names)){
				$this->names[] = $this->c_name;				
			}
			$this->Name = join(', ',$this->names);
			if($this->PublishDate<$this->c_publishdate||$this->c_publishdate==NULL){
				$this->PublishDate = $this->c_publishdate;
			}
			return true;
		}
		return false;
	}
	private function Cleanup(){		
		$this->c_url = NULL;
		$this->c_config = NULL;
		$this->c_raw = NULL;
		$this->c_dom = NULL;
		$this->c_rows = NULL;
		$this->c_name = NULL;
		$this->c_publishdate = NULL;
		return true;
	}
	
	private static function FindRows(DOMNode $dom, $level = 0){
		$rows = array();
		foreach($dom->childNodes as $v){
			$type = get_class($v);
			switch($type){
				case "DOMElement":	
					if($v->nodeName == "tr"){
						if(count($v->childNodes)>0){
							$rows[] = $v->childNodes;
						}
					}
					$tmp = self::FindRows($v,$level+1);
					if(count($tmp)){
						$rows[] = $tmp;
					}
					break;
				default:
					break;
			}			
		}
		return $rows;
	}

	private function PrettifyResult(){
		$this->c_rows = $this->c_rows[0][0][0];	
		foreach($this->c_rows as $k=>$v){
			$this->c_rows[$k] = array();
			foreach($v as $v2){
				$tmp = trim($v2->textContent);
				if(mb_strlen($tmp)>0){
					$this->c_rows[$k][] = $tmp;
				}
			}
		}
		return true;
	}

	private function ProcessResult(){
		global $dagen_search;
		$c_day = 0;
		$ris = array();
		$heeft_datum = false;
		foreach($this->c_rows as $v){
			if(array_key_exists($v[0],$dagen_search)){
				//Day
				$c_day = $dagen_search[$v[0]];
				if(trim(mb_strtolower($v[5]))=='datum'){
					$heeft_datum = true;
				} else {
					$heeft_datum = false;
				}
			} else {
				//RoosterItemSet
				$ri = new RoosterItemSet();
				//$ri->dag = $c_day;

				$tijdvak = explode("-",$v[0]);
				if(count($tijdvak)==1){
					$ri->start = $tijdvak[0];
					$ri->end = $tijdvak[0];
					debug_msg('Fail?',$tijdvak);
				} else if(count($tijdvak>=2)){
					$ri->start = $tijdvak[0];
					$ri->end = $tijdvak[1];
				}
				
				$ri->studyguidenumber = mb_substr($v[3],0,-1);
				$ri->academicyear = $this->c_config['year'].'-'.($this->c_config['year']+1);
				$ri->type = mb_substr($v[3],-1,1);

				$ri->course = $v[1];
				$ri->group = $v[2];				
				
				$ri->teacher = $v[4];

				if($heeft_datum){
					$datumsplit = explode('/',$v[5]);
					$datum = new DateTime();
					$datum->setDate($this->c_config['year'],$datumsplit[1],$datumsplit[0]);
					if($datum->format('W')<=SEPARATION_WEEK_SCHOOLYEAR){						
						$datum->setDate($this->c_config['year']+1,$datumsplit[1],$datumsplit[0]);
					}
					$datum->setTime(0,0,0);
					$ri->data[] = $datum;
				} else {
					$weken = array();
					$wekensets = explode(',',$v[5]);
					foreach($wekensets as $weekset){
						$weekset = explode('-',$weekset);
						if(count($weekset)>=2){
							for($i = $weekset[0];$i<=$weekset[1];$i++){
								$datum = new DateTime();
								if($i<=SEPARATION_WEEK_SCHOOLYEAR){						
									$datum->setISODate($this->c_config['year']+1,intval($i),$c_day);
								} else {
									$datum->setISODate($this->c_config['year'],intval($i),$c_day);
								}
								$datum->setTime(0,0,0);
								$ri->data[] = $datum;
							}
						} elseif(count($weekset)==1) {
							$datum = new DateTime();							
							if($weekset[0]<=SEPARATION_WEEK_SCHOOLYEAR){						
								$datum->setISODate($this->c_config['year']+1,intval($weekset[0]),$c_day);
							} else {
								$datum->setISODate($this->c_config['year'],intval($weekset[0]),$c_day);
							}
							$datum->setTime(0,0,0);
							$ri->data[] = $datum;
						}
					}
					$ri->weken = $weken;
				}

				$locatie=explode('/',$v[6]);
				if(count($locatie)==1){
					$ri->building = $locatie[0];
				} else if(count($locatie)>=2){
					$ri->building = $locatie[0];
					$ri->room = $locatie[1];
				}

				$ri->usisactnumber = $v[7];
				$ri->zrsid = $v[8];
				//filters
                if(isset($this->c_config['filters'][$ri->studyguidenumber])){
                    if(in_array($ri->group,$this->c_config['filters'][$ri->studyguidenumber])){
                        //only show if matched
                        $ris[]=$ri;
                    }
                } else {
                    //no filter, thus display
                    $ris[] = $ri;
                }
				
			}
		}
		$this->c_rows = $ris;
		return true;
	}

	public function Expand(){
		if(count($this->RoosterItemSets)<=0){
			return false;
		}
		$this->RoosterItems = array();
		foreach($this->RoosterItemSets as $k=>$v){
			//var_dump($v,$v->Expand());
			$this->RoosterItems = array_merge($this->RoosterItems,$v->Expand());
		}
		debug_msg('Expanded '.count($this->RoosterItemSets).' sets into '.count($this->RoosterItems).' items');
		return true;
	}

	public function Sort(){
		usort($this->RoosterItems,array('Rooster','DefaultRoosterItemSort'));
	}

	private static function DefaultRoosterItemSort($a,$b){
		if(!isset($a->start)||!isset($b->start))
			return 0;
		if ($a->start == $b->start) {
			return 0;
		}
		return ($a->start < $b->start) ? -1 : 1;
	}

	public function SortSets(){
		foreach($this->RoosterItemSets as $k=>$v){
			usort($this->RoosterItemSets[$k]->data,array('Rooster','DefaultDateTimeSort'));
		}
		usort($this->RoosterItemSets,array('Rooster','DefaultSortedRoosterItemSetSort'));
	}

	private static function DefaultDateTimeSort($a,$b){
		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}

	private static function DefaultSortedRoosterItemSetSort($a,$b){
		if(!isset($a->data[0])||!isset($b->data[0]))
			return 0;
		if ($a->data[0] == $b->data[0]) {
			return 0;
		}
		return ($a->data[0] < $b->data[0]) ? -1 : 1;
	}

	public function WriteiCal(){
		global $types;
		if(count($this->RoosterItems)<=0){
			return false;
		}
		$calendername = 'Rooster'.(count($this->names)?'s':'').' '.$this->Name;
		$calenderdesc = 'Collegerooster'.(count($this->names)?'s':'').' voor '.$this->Name.' aan Universiteit Leiden';
		$eol = Rooster::EOL;
		echo 'BEGIN:VCALENDAR'.$eol;
		echo 'VERSION:2.0'.$eol;
		echo 'PRODID:-//EraYaN//NONSGML '.PRODUCT_NAME.' '.PRODUCT_VERSION.'//EN'.$eol;
		echo 'CALSCALE:GREGORIAN'.$eol;
		echo 'METHOD:PUBLISH'.$eol;
		echo 'NAME:'.$calendername.$eol;
		echo 'X-WR-CALNAME:'.$calendername.$eol;
		echo 'DESCRIPTION:'.$calenderdesc.$eol;
		echo 'X-WR-CALDESC:'.$calenderdesc.$eol;
		echo 'REFRESH-INTERVAL;VALUE=DURATION:PT2M'.$eol;
		echo 'X-PUBLISHED-TTL:PT2M'.$eol;
		echo 'BEGIN:VTIMEZONE'.$eol;
		echo 'TZID:'.ICAL_TZ.$eol;
		echo 'BEGIN:STANDARD'.$eol;
		echo 'DTSTART:16011028T030000'.$eol;
		echo 'RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10'.$eol;
		echo 'TZOFFSETFROM:+0200'.$eol;
		echo 'TZOFFSETTO:+0100'.$eol;
		echo 'END:STANDARD'.$eol;
		echo 'BEGIN:DAYLIGHT'.$eol;
		echo 'DTSTART:16010325T020000'.$eol;
		echo 'RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3'.$eol;
		echo 'TZOFFSETFROM:+0100'.$eol;
		echo 'TZOFFSETTO:+0200'.$eol;
		echo 'END:DAYLIGHT'.$eol;
		echo 'END:VTIMEZONE'.$eol;
		if(!empty($this->URL)){
			echo 'URL:'.$this->URL.$eol; 
		}
		foreach($this->RoosterItems as $k=>$v){
			
			echo 'BEGIN:VEVENT'.$eol;
			echo 'UID:'.$v->getUID().$eol;
			echo 'CREATED;TZID='.ICAL_TZ.':'.toiCalDate($this->PublishDate->getTimestamp()).$eol;
			echo 'DTSTAMP;TZID='.ICAL_TZ.':'.toiCalDate(time()).$eol;
			echo 'LAST-MODIFIED;TZID='.ICAL_TZ.':'.toiCalDate(time()).$eol;
			
			echo 'DTSTART;TZID='.ICAL_TZ.':'.toiCalDate($v->start->getTimestamp()).$eol;
			
			echo 'DTEND;TZID='.ICAL_TZ.':'.toiCalDate($v->end->getTimestamp()).$eol;
			
			echo 'DESCRIPTION;LANGUAGE=nl:'.$v->getDescription().$eol;
			echo 'X-ALT-DESC;FMTTYPE=text/html;LANGUAGE=nl:'.$v->getDescriptionHTML().$eol;
			echo 'URL:https://studiegids.leidenuniv.nl/search/courses/?code='.$v->studyguidenumber.'&edition='.$v->academicyear.$eol;
			echo 'SUMMARY;LANGUAGE=nl:'.$v->course.$eol;
            echo 'TRANSP:OPAQUE'.$eol;
			echo 'STATUS:CONFIRMED'.$eol;
            
			echo 'LOCATION:'.$v->building.'/'.$v->room.$eol;
			echo 'CLASS:PUBLIC'.$eol;
			echo 'CATEGORIES:Onderwijs,'.$types[$v->type].$eol;
			echo 'END:VEVENT'.$eol;
			
			
		}
		echo 'END:VCALENDAR'.$eol;
		return true;
	}
}
