<?php
class IntervalObject{
	public $lowerBound;
    public $upperBound;

    private $children = [];
    public $people = [];

	public $depth = 0;

	public function __construct($lowerBound, $upperBound, $person){	
		$this->lowerBound = $lowerBound;
		$this->upperBound = $upperBound;
		
		if(is_array($person))
			$this->people = $person;
		else
			$this->people[] = $person;
	}

    public function getTop(&$x, array & $ret, array & $parents = array()){
        if(count($this->children) > 0 ){
            // sort by depth
            usort($this->children, 'self::compareDepth');

            $parents = array_merge($parents, $this->people);

            foreach($this->children as $child) {
                $copy = [];

                if (strcmp($this->people[0], 'ROOT') != 0)
                    $copy = $parents;

                $child->getTop($x, $ret, $copy);

               // if(count($ret) >= $x)
                 //   return;
            }

            if (strcmp($this->people[0], 'ROOT') != 0)
                if(count($parents) > 1 && count($parents) == count(array_unique($parents)) && floor($this->getLower()/1440) == floor($this->getUpper()/1440))
                    $ret[] = array('people' => $parents, 'startTime' => $this->getLower(), 'endTime' => $this->getUpper());
        }
        else if($this->people[0] != 'ROOT'){
            $peeps = array_merge($parents, $this->people);
            if(count($peeps) > 1 && count($peeps) == count(array_unique($peeps)) && floor($this->getLower()/1440) == floor($this->getUpper()/1440))
                $ret[] = array('people' => $peeps, 'startTime' => $this->getLower(), 'endTime' => $this->getUpper());
        }
    }
	
	public function getLower(){
		return $this->lowerBound;
	}
	
	public function getUpper(){
		return $this->upperBound;
	}
	
	public function getDepth(){
		return $this->depth;
	}

    private function insertChild($index, &$newChild){
        if($index == count($this->children)){
            $this->children[] = $newChild;
        }
        else{
            $temp = [];

            for($i = 0; $i < $index; $i++)
                $temp[] = $this->children[$i];
            $temp[] = $newChild;
            for($i = $index; $i < count($this->children); $i++)
                $temp[] = $this->children[$i];

            $this->children = $temp;
        }
    }

	public function insertInterval(&$name, $lowerBound, $upperBound){
		// if no children
		if(count($this->children) == 0){
			$this->children[] = new IntervalObject($lowerBound, $upperBound, $name);
			$this->depth++;
		}
		// at least 1 child exists
		else{
			$leftNode = $this->getLeftNode($lowerBound);

			if($leftNode == count($this->children))
                $leftNode--;
			
			// new is a larger bound OR new is a smaller bound
			if( ($this->children[$leftNode]->getLower() > $lowerBound && $this->children[$leftNode]->getUpper() > $upperBound) || ($this->children[$leftNode]->getLower() < $lowerBound && $this->children[$leftNode]->getUpper() < $upperBound) ){
                $this->insertChild($leftNode+1, new IntervalObject($lowerBound, $upperBound, $name));

                // check if subinterval is large enough to intersect with left
                if($this->children[$leftNode]->getUpper() - $lowerBound >= CalIntervalDiff::getLength()){
                    $this->insertIntoChild($leftNode, $lowerBound, $this->children[$leftNode]->getUpper(), $name);
                }

                // check if subinterval is large enough to intersect with right
                if(isset($this->children[$leftNode+2]) && $upperBound - $this->children[$leftNode+2]->getLower() >= CalIntervalDiff::getLength()){
                    $this->insertIntoChild($leftNode+2, $this->children[$leftNode+2]->getLower(), $upperBound, $name);
                }
			}
			// new is the same interval
			else if($this->children[$leftNode]->getLower() == $lowerBound && $this->children[$leftNode]->getUpper() == $upperBound){
				if(is_array($name))
					$this->children[$leftNode]->people = array_merge($this->people, $name);
				else
					$this->children[$leftNode]->people[] = $name;

                $this->children[$leftNode]->depth++;
                if($this->depth <= $this->children[$leftNode]->depth)
                    $this->depth = $this->children[$leftNode]->depth+1;
			}
			// new is a subset
			else if($this->children[$leftNode]->getLower() <= $lowerBound && $this->children[$leftNode]->getUpper() >= $upperBound){
				$this->insertIntoChild($leftNode, $lowerBound, $upperBound, $name);
			}
			// new is a superset (or superset multiple)
			else if($this->children[$leftNode]->getLower() >= $lowerBound && $this->children[$leftNode]->getUpper() <= $upperBound){
                $this->insertIntoChild($leftNode, $this->children[$leftNode]->lowerBound,
                    $this->children[$leftNode]->upperBound, $this->children[$leftNode]->people);
					
				// replace this properties with the new properties
				$this->children[$leftNode]->lowerBound = $lowerBound;
				$this->children[$leftNode]->upperBound = $upperBound;
			
				if(is_array($name))
					$this->children[$leftNode]->people = $name;
				else
					$this->children[$leftNode]->people = array($name);


				// Check next interval (in case of superset multiple)
				while(isset($this->children[$leftNode+1]) && $this->children[$leftNode+1]->getLower() >= $lowerBound && $this->children[$leftNode+1]->getUpper() <= $upperBound){
                    $temp = clone $this->children[$leftNode+1];
                    $this->insertIntoChild($leftNode, $temp->lowerBound, $temp->upperBound, $temp->people);
					$this->children = array_merge(array_slice($this->children,0,$leftNode+1), array_slice($this->children,$leftNode+1+1));
                    $leftNode++;
				}

			}
			else{
                die('this should never happen....');
			}
			
		}
		
		return $this->depth;
	}
	
	private function insertIntoChild($index, $lower, $upper, $name){
		$childDepth = $this->children[$index]->insertInterval($name, $lower, $upper);
		
		if($childDepth >= $this->depth){
			$this->depth = $childDepth+1;
		}
	}
	
	private function getLeftNode(&$newChild){
		$alpha = 0;
		$beta = count($this->children) - 1;
		if($beta < 0)
			$beta = 0;
		
		while($alpha <= $beta){
			$mid = ((int)(($beta+$alpha)/2));
			$cmp = $this->children[$mid]->compare($newChild);
		
			if($cmp < 0){
				$alpha = $mid + 1;
			}
			else if($cmp == 0){
				return $mid;
			}
			else{
				$beta = $mid - 1;
			}
		}

        //echo "$alpha \n";
        if($alpha-1 >= 0)
		    return $alpha-1;
        else return 0;
	}
	
	private function compare(&$newChild){
		if(is_int($newChild) || is_float($newChild))
			return $this->lowerBound - $newChild;
		else
			return $this->lowerBound - $newChild->getLower();
	}

	public static function compareDepth($a, $b){
		if($a->getDepth() == $b->getDepth())    
			return 0;
    	else
    		return ($a->getDepth() > $b->getDepth()) ? -1 : 1;
	}
}

class CalIntervalDiff{
	private $workdayLowerBound;
	private $workdayUpperBound;
	private $rangeLowerBound;
	private $rangeUpperBound;
	private $topSlots;
	
	private static $length;

	private $root;

	public function __construct(&$events, $rangeLowerBound, $rangeUpperBound, $workdayLowerBound = 0, $workdayUpperBound = 24, $length, $topSlots = 5){
		/*
		 * convert json workday to minutes
		 */

		if($workdayLowerBound > $workdayUpperBound || $workdayUpperBound > 1440)
			die("ERROR: Invalid workday interval.");
		
		else{
			$this->workdayLowerBound = $workdayLowerBound;
			$this->workdayUpperBound = $workdayUpperBound;
			$this->rangeLowerBound = $rangeLowerBound;
			$this->rangeUpperBound = $rangeUpperBound;
			$this->topSlots = $topSlots;

			$this->root = new IntervalObject(0, 1E9, 'ROOT');
			
			self::$length = $length;

            //echo json_encode($rangeLowerBound);

           // $workdayLowerBound += $rangeLowerBound;
           // $workdayUpperBound += $rangeLowerBound;


            foreach($events as $email => $intervals) {
                for($j = 0; $j < count($events[$email]); $j++) {
                    $events[$email][$j]['startTime'] += $rangeLowerBound;
                    $events[$email][$j]['endTime'] += $rangeLowerBound;
                }
            }

			foreach($events as $email => $intervals) {
                $a = new ArrayIterator($intervals);
				foreach($a as $interval){

                   // echo json_encode("BEFORE -> ").json_encode($interval)."\n";

                    if( ($interval['startTime'] % 1440) > $workdayUpperBound){
                        $interval['startTime'] = 1440*(floor($interval['startTime']/1440)+1) + $workdayLowerBound;
                    }
                    else if( ($interval['startTime'] % 1440) < $workdayLowerBound ){
                        $interval['startTime'] = 1440*floor($interval['startTime']/1440) + $workdayLowerBound;
                    }

                    //if( ($interval['endTime'] % 1440) < $workdayLowerBound){
                      //  $interval['endTime'] = 1440*(floor($interval['startTime']/1440)-1) + $workdayLowerBound;
                    //}
                    //else
                    if( ($interval['endTime'] % 1440) > $workdayUpperBound ){
                        $interval['endTime'] = 1440*floor($interval['endTime']/1440) + $workdayUpperBound;
                    }

                    //echo json_encode("AFTER -> ").json_encode($interval)."\n";

					if( $interval['endTime'] - $interval['startTime'] < $length) {
                       // echo json_encode("INVALID RANGE => ").json_encode($interval)."\n";
                        continue;
                    }

					// instead of more than a day apart, divide by 1440 and see if the day matches
					if(floor($interval['startTime']/1440) < floor($interval['endTime']/1440)){
                        //$c = 0;
						do {
                          //  var_dump($interval);
                            $tempEnd = 1440*floor($interval['startTime']/1440) + $workdayUpperBound;

                            if($tempEnd > $interval['endTime'])
                                $tempEnd = $interval['endTime'];

                            $a->append(array('startTime' => $interval['startTime'],
                                'endTime' => $tempEnd));

                            $interval['startTime'] = 1440*(floor($interval['startTime']/1440)+1) + $workdayLowerBound;

                            //if($c >= 5)
                               // break;
                            //$c++;
                        }
						while ($interval['startTime'] / 1440 < $interval['endTime'] / 1440);

                        //die();

						// insert the final range
						$a->append(array('startTime' => $interval['startTime'], 'endTime' => $interval['endTime']));
					}
                    else{
                       // echo json_encode("Inserting: $email -> ")."\n";
                       // echo json_encode($interval)."\n";
                        $this->root->insertInterval($email, $interval['startTime'], $interval['endTime']);
                    }


                /*
                    if( $interval['endTime'] - $interval['startTime'] < 1440 ) {
                        echo json_encode("Inserting: $email -> ")."\n";
                        echo json_encode($interval)."\n";
                        $this->root->insertInterval($email, $interval['startTime'], $interval['endTime']);
                    }
                    else{
                        echo json_encode("TOO LARGE RANGE => ").json_encode($interval)."\n";

                        
                    }*/

				}

			}

           // echo json_encode($events);
		}

	}

	public function getTop($x){

       // var_dump($this->root);

		$ret = [];
		$this->root->getTop($x,$ret);


        for($i=0; $i<count($ret); $i++){
            $ret[$i]['startTime'] -= $this->rangeLowerBound;
            //$ret[$i]['endTime'] -= $this->rangeLowerBound;
			//This sets to earliest range since choosing range isn't in the UI
			$ret[$i]['endTime'] = $ret[$i]['startTime'] + self::getLength();
        }

		usort($ret, 'self::compareDepth');

        return array_slice($ret, 0, $x);
	}
	
	public static function getLength(){
		return self::$length;
	}

	public static function compareDepth($a, $b){
		if(count($a['people']) == count($b['people']))
			return 0;
		else
			return (count($a['people']) > count($b['people'])) ? -1 : 1;
	}

    /*
     *                         while($interval['endTime'] - $interval['startTime'] >= 1440) {
                           // echo json_encode("Appending: $email -> ");
                           // echo json_encode(array('startTime' => $interval['startTime'],
                            //    'endTime' => 1440*floor($interval['startTime']/1440) + $workdayUpperBound));
                            $a->append(array('startTime' => $interval['startTime'],
                                'endTime' => 1440*floor($interval['startTime']/1440) + $workdayUpperBound));
                            $interval['startTime'] += 1440*(floor($interval['startTime']/1440)+1);
                        }
                        if($interval['endTime'] - $interval['startTime'] < 1440) {
                            $a->append(array('startTime' => $interval['startTime'], 'endTime' => $interval['endTime']));
                           // echo json_encode("Appending: $email -> ");
                           // echo json_encode($interval);
                        }
                        //print_r($a);
                        //*/
     //*/
}
