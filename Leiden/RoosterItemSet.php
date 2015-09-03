<?php

/**
 * RoosterItemSet short summary.
 *
 * RoosterItemSet description.
 *
 * @version 1.0
 * @author Erwin
 */
class RoosterItemSet extends RoosterItemBase
{
    
	public $data = array();

	
	
	public function Expand(){
		$set = array();
		$starttime = explode(':',$this->start);
		$endtime = explode(':',$this->end);
		foreach($this->data as $datum){
			$tmp = new RoosterItem($this);		
   
			$tmp->start = clone $datum;				
			$tmp->start->setTime($starttime[0],$starttime[1],0);	
			$tmp->end = clone $datum;			
			$tmp->end->setTime($endtime[0],$endtime[1],0);	

			$tmp->course = $this->course;
			$tmp->type = $this->type;
			$tmp->group = $this->group;
			$tmp->teacher = $this->teacher;
			$tmp->studyguidenumber = $this->studyguidenumber;
			$tmp->academicyear = $this->academicyear;
			$tmp->zrsid = $this->zrsid;
			$tmp->building = $this->building;
			$tmp->room = $this->room;
			$tmp->usisactnumber = $this->usisactnumber;
			$set[] = $tmp;
		}
		return $set;
	}

}
