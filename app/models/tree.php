<?php
class IntervalObject{
	private $lowerBound;
	private $upperBound;

	private $children = [];
	private $people = [];

	private $depth;

	public function __construct($events){

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

	public function insert(&$name, $lowerBound, $upperBound){

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

			$this->root = new IntervalObject();

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