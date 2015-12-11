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
            //var_dump($this->children);
            //die();

            $parents = array_merge($parents, $this->people);

            foreach($this->children as $child) {
                $copy = [];

                if (strcmp($this->people[0], 'ROOT') != 0)
                    $copy = $parents;

                $child->getTop($x, $ret, $copy);

                if(count($ret) >= $x)
                    return;
            }

            if (strcmp($this->people[0], 'ROOT') != 0)
                if(count($parents) > 1)
                    $ret[] = array('people' => $parents, 'startTime' => $this->getLower(), 'endTime' => $this->getUpper());
        }
        else if($this->people[0] != 'ROOT'){
            $peeps = array_merge($parents, $this->people);
            if(count($peeps) > 1)
                $ret[] = array('people' => $peeps, 'startTime' => $this->getLower(), 'endTime' => $this->getUpper());
        }
    }

    /*
	public function getTop(&$x, array & $ret=array()){
		if(count($this->children) > 0){
			// sort by depth
			usort($this->children, 'self::compareDepth');
			
			foreach($this->children as $child){
                $copy = [];

                if(strcmp($this->people[0], 'ROOT') != 0)
                    $copy = $this->people;

				$child->getTop($x, $copy);

                if(strcmp($this->people[0], 'ROOT') == 0) {
                    $ret[] = $copy;
                    echo "\n--------------ROOT LINE----------------\n";
                }
                else {
                    if (!is_array($copy)) $copy = array($copy);
                    $ret[] = array('people' => array_merge_recursive($this->people, $copy), 'startTime' => $this->getLower(), 'endTime' => $this->getUpper());
                }

				if(count($ret) >= $x)
					break;	
			}
		}
		else{
			if(!is_array($this->people)) $this->people = array($this->people);
			$ret[] = array('people' => $this->people, 'startTime' => $this->getLower(), 'endTime' => $this->getUpper());
		}
	}*/
	
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
        //echo "INDEX INSERT IS => $index \n";
        //var_dump($newChild);
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
        //echo "LOWER => $lowerBound HIGHER => $upperBound \n";
		// if no children
		if(count($this->children) == 0){
			$this->children[] = new IntervalObject($lowerBound, $upperBound, $name);
			$this->depth++;
		}
		// at least 1 child exists
		else{
			$leftNode = $this->getLeftNode($lowerBound);
			//echo "$leftNode \n";
			if($leftNode == count($this->children)) $leftNode--;
			//var_dump($this);
			//echo 'leftNode => '.$leftNode;
			
			// new is a larger bound OR new is a smaller bound
			if( ($this->children[$leftNode]->getLower() > $lowerBound && $this->children[$leftNode]->getUpper() > $upperBound) || ($this->children[$leftNode]->getLower() < $lowerBound && $this->children[$leftNode]->getUpper() < $upperBound) ){
                //array_splice($this->children, $leftNode, 0, -1);
				//$this->children[$leftNode] = new IntervalObject($lowerBound, $upperBound, $name);
                $this->insertChild($leftNode+1, new IntervalObject($lowerBound, $upperBound, $name));
				//$this->sortedInsertChild(new IntervalObject($lowerBound, $upperBound, $name));

                // check if subinterval is large enough to intersect with left
                if($this->children[$leftNode]->getUpper() - $lowerBound >= CalIntervalDiff::getLength()){
                    $this->insertIntoChild($leftNode, $lowerBound, $this->children[$leftNode]->getUpper(), $name);
                }

                // check if subinterval is large enough to intersect with right
                if(isset($this->children[$leftNode+2]) && $upperBound - $this->children[$leftNode+2]->getLower() >= CalIntervalDiff::getLength()){
                    $this->insertIntoChild($leftNode+2, $this->children[$leftNode+2]->getLower(), $upperBound, $name);
                }
			}
			/*else if(){
				array_splice($this->children, $leftNode+1, 0, -1);
				$this->children[$leftNode+1] = new IntervalObject($lowerBound, $upperBound, $name);
			}*/
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
				//echo 'subset';
				$this->insertIntoChild($leftNode, $lowerBound, $upperBound, $name);
			}
			// new is a superset (or superset multiple)
			else if($this->children[$leftNode]->getLower() >= $lowerBound && $this->children[$leftNode]->getUpper() <= $upperBound){
				//echo 'superset';
                //var_dump($leftNode);
                //var_dump($lowerBound);
                //var_dump($upperBound);
                //var_dump($name);
				// sorted insert of this properties in new object
				//array_splice($this->children, $leftNode, 0, -1);
				//$this->children[$leftNode] = new IntervalObject($this->lowerBound, $this->upperBound, $this->people);
                //$this->children[$leftNode]->insertInterval($this->children[$leftNode]->people,
                    //$this->children[$leftNode]->lowerBound, $this->children[$leftNode]->upperBound);
                $this->insertIntoChild($leftNode, $this->children[$leftNode]->lowerBound,
                    $this->children[$leftNode]->upperBound, $this->children[$leftNode]->people);
                //$this->sortedInsertChild(new IntervalObject($this->lowerBound, $this->upperBound, $this->people));
					
				// replace this properties with the new properties
				$this->children[$leftNode]->lowerBound = $lowerBound;
				$this->children[$leftNode]->upperBound = $upperBound;
			
				if(is_array($name))
					$this->children[$leftNode]->people = $name;
				else
					$this->children[$leftNode]->people = array($name);

                //$this->children[$leftNode]->depth++;
                //if($this->depth <= $this->children[$leftNode]->depth)
                    //$this->depth = $this->children[$leftNode]->depth+1;


				// Check next interval (in case of superset multiple)
				while(isset($this->children[$leftNode+1]) && $this->children[$leftNode+1]->getLower() >= $lowerBound && $this->children[$leftNode+1]->getUpper() <= $upperBound){
					//echo "\n\n RIGHT NODE IS -> ".($leftNode+1)."\n\n";
                    //var_dump($this->children);
                    //echo "\n-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-\n";
                    $temp = clone $this->children[$leftNode+1];
					//array_splice($this->children, $leftNode+1, 0, -1);
					//$this->children[$leftNode+1] = $temp;
                    //$this->children[$leftNode]->insertInterval($temp->people, $temp->lowerBound, $temp->upperBound);
                    $this->insertIntoChild($leftNode, $temp->lowerBound, $temp->upperBound, $temp->people);
					//$this->sortedInsertChild(clone $this->children[$leftNode+1]);
					$this->children = array_merge(array_slice($this->children,0,$leftNode+1), array_slice($this->children,$leftNode+1+1));
                    $leftNode++;
				}

			}
			// new is intersection (or intersects multiple)
			else{
				echo 'intersect';
				// intersection means insert normally no matter what
				//array_splice($this->children, $leftNode, 0, -1);
				//$this->children[$leftNode] = new IntervalObject($lowerBound, $upperBound, $name);
                $this->insertChild($leftNode+1, new IntervalObject($lowerBound, $upperBound, $name));
				
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
		
		//var_dump($mid);
		//var_dump($this->children);
		//echo '+++++++++++++++++++++++++++++++';
		
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

			$this->root = new IntervalObject($rangeLowerBound, $rangeUpperBound, 'ROOT');
			
			self::$length = $length;
			
			//var_dump($events);
            //$c = 0;

			foreach($events as $email => $intervals) {
                //$c2 = 0;
				foreach($intervals as $interval){
                    //echo $interval['endTime'].' - '.$interval['startTime'].' = '.($interval['endTime'] - $interval['startTime']).'\n';
                    if( ($interval['startTime'] % 1440) < $workdayLowerBound ){
                        //echo "trimming start \n";
                        $interval['startTime'] = 1440*floor($interval['startTime']/1440) + $workdayLowerBound;
                        //echo $interval['startTime']."\n";
                    }

                    if( ($interval['endTime'] % 1440) > $workdayUpperBound ){
                        //echo "trimming end \n";
                        $interval['endTime'] = 1440*floor($interval['endTime']/1440) + $workdayUpperBound;
                        //echo $interval['endTime']."\n";
                    }

					if( $interval['endTime'] - $interval['startTime'] < $length)
						continue;
	
					$this->root->insertInterval($email, $interval['startTime'], $interval['endTime']);

                   // if($c > 2 && $c2 <= 2)
					  //  var_dump($this->root);
                    //$c2++;
					//echo '-------------------------------------';
				}


//                if($c==2)
  //                  die();
    //            else
                   // $c++;
				//echo 'NEXT PERSON +++++++++++++++++';
			}
		}

	}

	public function getTop($x){
        var_dump($this->root);
        echo "-------------------------------------\n";

		$ret = [];
		$this->root->getTop($x,$ret);

        return $ret;
		//return array_slice( $ret, 0, 5 );
	}
	
	public static function getLength(){
		return self::$length;
	}
}