<?php

/**
 * RoosterItem short summary.
 *
 * RoosterItem description.
 *
 * @version 1.0
 * @author Erwin
 */
class RoosterItem extends RoosterItemBase
{
	

	public function getString(){
		global $types;
		return $this->start->format('l H:i').'-'.$this->end->format('H:i').': '.$this->course.($this->type!==false?' - '.$types[$this->type]:'').($this->group!==false?' ('.$this->group.')':'').' door '.$this->teacher.' in '.$this->building.'/'.$this->room.' '.($this->start!=NULL?'op '.$this->start->format('d-m-Y').' ':'').'('.$this->studyguidenumber.', '.$this->usisactnumber.', '.$this->zrsid.')';
	}
	public function getDescription(){
		global $types;
		return $this->start->format('l H:i').'-'.$this->end->format('H:i').'\n'.
			$this->course.($this->type!==false?' - '.$types[$this->type]:'').($this->group!==false?' ('.$this->group.')':'').'\n'.
			'Teacher: '.$this->teacher.'\n'.
			'Building/Room: '.$this->building.'/'.$this->room.'\n'.
			($this->start!=NULL?'Date: '.$this->start->format('d-m-Y').'\n':'').
			'Studyguidenumber: '.$this->studyguidenumber.' ('.$this->academicyear.')\n'.
			'uSIS Act.nr: '.$this->usisactnumber.'\n'.
			'ZRS-id: '.$this->zrsid;
	}
	public function getDescriptionHTML(){
		global $types;
		return '<!doctype html><html><head><meta name="Generator" content="'.PRODUCT_NAME.' '.PRODUCT_VERSION.'"><title></title></head><body><p><strong>'.
			$this->course.($this->type!==false?' - '.$types[$this->type]:'').($this->group!==false?' ('.$this->group.')':'').'</strong><br>'.
			'<em>Teacher: </em>'.$this->start->format('l H:i').'-'.$this->end->format('H:i').'<br>'.
			'<em>Docent:</em> '.$this->teacher.'<br>'.
			'<em>Building/Room:</em> <a href="http://www.leidenuniv.nl/loc/index.html?lang=eng">'.$this->building.'/'.$this->room.'</a><br>'.		
			($this->start!=NULL?'<em>Date:</em> '.$this->start->format('d-m-Y').'<br>':'').
			'<em>Studyguidenumber:</em> <a href="https://studiegids.leidenuniv.nl/search/courses/?code='.$this->studyguidenumber.'&edition='.$this->academicyear.'">'.$this->studyguidenumber.'</a><br>'.
			'<em>uSIS Act.nr:</em> <a href="https://usis.leidenuniv.nl/psp/S040PRD/?cmd=login">'.$this->usisactnumber.'</a><br>'.
			'<em>ZRS-id:</em> '.$this->zrsid.'</p></body></html>';
	}
	public function getUID(){
		$data = $this->academicyear.$this->start->format('d').$this->course.$this->teacher.$this->studyguidenumber.$this->group;
		if($this->start==NULL){
			return md5($data);
		} else {
			return md5($data.$this->start->getTimestamp());
		}
	}

}
