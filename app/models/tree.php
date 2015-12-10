<?php
class IntervalObject{
	private $lowerBound;
	private $upperBound;

	private $children = [];
	private $people = [];

	private $depth = 0;

	public function __construct($lowerBound, $upperBound, $person){	
		$this->$lowerBound = $lowerBound;
		$this->$upperBound = $upperBound;
		
		if(is_array($person))
			$this->people = $person;
		else
			$this->people[] = $person;
	}

	public function getTop(&$x, array & $ret=array()){
		$people = array_merge($people, $this->people);

		if($this->depth > 0){

			foreach($children as $child){
				$copy = $people;
				$child->getTop($x, $ret, $copy);
			}
		}
		else{
			$ref[] = $people;
		}	

	}
	
	public function getLower(){
		return $this->lowerBound;
	}
	
	public function getUpper(){
		return $this->upperBound;
	}

	public function insert(&$name, $lowerBound, $upperBound){
		// if no children
		if(count($this->children) == 0){
			$this->children = new IntervalObject($lowerBound, $upperBound, $name);
		}
		// only one child exists
		else if(count($this->children) == 1){
			// new is a larger bound
			if($this->children[0]->getLower() < $lowerBound && $this->children[0]->getUpper() < $upperBound){
				$this->children[1] = new IntervalObject($lowerBound, $upperBound, $name);
			}
			// new is a smaller bound
			else if($this->children[0]->getLower() > $lowerBound && $this->children[0]->getUpper() > $upperBound){
				$this->children[1] = $this->children[0];
				$this->children[0] = new IntervalObject($lowerBound, $upperBound, $name);
			}
			// new is the same interval
			else if($this->children[0]->getLower() == $lowerBound && $this->children[0]->getUpper() == $upperBound){
				if(is_array($name))
					$this->people = array_merge($this->people, $name);
				else
					$this->people[] = $name;
			}
			// new is a subset
			else if($this->children[0]->getLower() <= $lowerBound && $this->children[0]->getUpper() >= $upperBound){
				$this->insert($name, $lowerBound, $upperBound);
			}
			// new is a superset
			else{
				// sorted insert of this properties in new object
				
				
				// replace this properities with the new properties
				$this->$lowerBound = $lowerBound;
				$this->$upperBound = $upperBound;
		
				if(is_array($person))
					$this->people = $person;
				else
					$this->people[] = $person;
			}
		}
		// at least 1 child exists
		else{
			
		}
	}

}

class CalIntervalDiff{
	private $workdayLowerBound;
	private $workdayUpperBound;
	private $rangeLowerBound;
	private $rangeUpperBound;
	private $topSlots;

	private $intervalObjects = [];

	public function __construct(&$events, $rangeLowerBound, $rangeUpperBound, $workdayLowerBound = 0, $workdayUpperBound = 24, $topSlots = 5, $length){
		/*
		 * convert json workday to minutes
		 */

		if($workdayLowerBound > $workdayUpperBound || $workdayUpperBound > 1440)
			die("ERROR: Invalid workday interval.");
		
		else{
			$this->$workdayLowerBound = $workdayLowerBound;
			$this->$workdayUpperBound = $workdayUpperBound;
			$this->$rangeLowerBound = $rangeLowerBound;
			$this->$rangeUpperBound = $rangeUpperBound;
			$this->$topSlots = $topSlots;

			$this->root = new IntervalObject($rangeLowerBound, $rangeUpperBound, 'ROOT');

			foreach($events as $email => $interval) {
				if( $interval['endTime'] - $interval['startTime'] < $length)
					continue;

				if( ($interval['startTime'] % 1440) < $workdayLowerBound ){
					$interval['startTime'] = ($interval['startEnd']/1440) + workdayLowerBound;
				}

				if( ($interval['endTime'] % 1440) > $workdayUpperBound ){
					$interval['endTime'] = ($interval['endTime']/1440) + workdayUpperBound;
				}

				$this->root->insert($email, $interval['startTime'], $interval['endTime']);
			}
		}

	}

	public function getTop($x){
		$ret = [];
		array_slice( $this->root->getTop($x,$ret), 0, 5 );
	}

}

?>