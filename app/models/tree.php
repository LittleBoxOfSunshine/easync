<?php
class IntervalObject{
	private $lowerBound;
	private $upperBound;

	private $children = [];
	private $people = [];

	private $depth = 0;

	public function __construct($lowerBound, $upperBound, $person){	
		$this->lowerBound = $lowerBound;
		$this->upperBound = $upperBound;
		
		if(is_array($person))
			$this->people = $person;
		else
			$this->people[] = $person;
	}

	public function getTop(&$x, array & $ret=array()){
		if(count($this->children) > 0){
			// sort by depth
			usort($this->children, 'self::compareDepth');
			
			foreach($this->children as $child){
				$copy = $this->people;
				$child->getTop($x, $copy);
				if(!is_array($copy)) $copy = array($copy);
				$ret[] = array('people' => array_merge($this->people, $copy), 'startTime' => $this->getLower(), 'endTime' => $this->getUpper());
				
				if(count($ret) >= $x)
					break;	
			}
		}
		else{
			if(!is_array($copy)) $copy = array($copy);
			$ret[] = array('people' => array_merge($this->people, $copy), 'startTime' => $this->getLower(), 'endTime' => $this->getUpper());
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

	public function insertInterval(&$name, $lowerBound, $upperBound){
		// if no children
		if(count($this->children) == 0){
			$this->children[] = new IntervalObject($lowerBound, $upperBound, $name);
			$this->depth++;
		}
		// at least 1 child exists
		else{
			$leftNode = $this->getLeftNode($lowerBound);
			//echo "$leftNode \n";
			//if($leftNode == count($this->children)) $leftNode--;
			//var_dump($this);
			//echo 'leftNode => '.$leftNode;
			
			// new is a larger bound OR new is a smaller bound
			if( ($this->children[$leftNode]->getLower() > $lowerBound && $this->children[$leftNode]->getUpper() > $upperBound) || ($this->children[$leftNode]->getLower() < $lowerBound && $this->children[$leftNode]->getUpper() < $upperBound) ){
				array_splice($this->children, $leftNode, 0, -1);
				$this->children[$leftNode] = new IntervalObject($lowerBound, $upperBound, $name);
				//$this->sortedInsertChild(new IntervalObject($lowerBound, $upperBound, $name));
			}
			/*else if(){
				array_splice($this->children, $leftNode+1, 0, -1);
				$this->children[$leftNode+1] = new IntervalObject($lowerBound, $upperBound, $name);
			}*/
			// new is the same interval
			else if($this->children[$leftNode]->getLower() == $lowerBound && $this->children[$leftNode]->getUpper() == $upperBound){
				if(is_array($name))
					$this->people = array_merge($this->people, $name);
				else
					$this->people[] = $name;
			}
			// new is a subset
			else if($this->children[$leftNode]->getLower() <= $lowerBound && $this->children[$leftNode]->getUpper() >= $upperBound){
				//echo 'subset';
				$this->insertIntoChild($leftNode, $name, $lowerBound, $upperBound);
			}
			// new is a superset (or superset multiple)
			else if($this->children[$leftNode]->getLower() >= $lowerBound && $this->children[$leftNode]->getUpper() <= $upperBound){
				//echo 'superset';
				// sorted insert of this properties in new object
				array_splice($this->children, $leftNode, 0, -1);
				$this->children[$leftNode] = new IntervalObject($this->lowerBound, $this->upperBound, $this->people);
				//$this->sortedInsertChild(new IntervalObject($this->lowerBound, $this->upperBound, $this->people));
					
				// replace this properities with the new properties
				$this->lowerBound = $lowerBound;
				$this->upperBound = $upperBound;
			
				if(is_array($person))
					$this->people = $person;
				else
					$this->people[] = $person;
					
				// Check next interval (in case of superset multiple)
				while($this->children[++$leftNode]->getLower() >= $lowerBound && $this->children[$leftNode]->getUpper() <= $upperBound){
					$temp = clone $this->children[$leftNode];
					array_splice($this->children, $leftNode, 0, -1);
					$this->children[$leftNode] = $temp;
					//$this->sortedInsertChild(clone $this->children[$leftNode]);
					$this->children = array_merge(array_slice($this->children,0,$leftNode), array_slice($this->children,$leftNode+1));
				}
			}
			// new is intersection (or intersects multiple)
			else{
				//echo 'intersect';
				// intersection means insert normally no matter what
				array_splice($this->children, $leftNode, 0, -1);
				$this->children[$leftNode] = new IntervalObject($lowerBound, $upperBound, $name);
				
				// check if subinterval is large enough to intersect with left  
				if($this->children[$leftNode]->getUpper() - $lowerBound >= CalIntervalDiff::getLength()){
					$this->insertIntoChild($leftNode, $name, $lowerBound, $this->children[$leftNode]->getUpper());
				}
				
				// check if subinterval is large enough to intersect with right
				if(isset($this->children[$leftNode+2]) && $upperBound - $this->children[$leftNode+2]->getLower() >= CalIntervalDiff::getLength()){
					$this->insertIntoChild($leftNode+2, $name, $this->children[$leftNode+2]->getLower(), $upperBound);
				}
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
		
		return $alpha;
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
    		return ($a->getDepth() < $b->getDepth()) ? -1 : 1;
	}
}

class CalIntervalDiff{
	private $workdayLowerBound;
	private $workdayUpperBound;
	private $rangeLowerBound;
	private $rangeUpperBound;
	private $topSlots;
	
	private static $length;

	private $intervalObjects = [];

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

			foreach($events as $email => $intervals) {
				foreach($intervals as $interval){
					//echo $interval['endTime'].' - '.$interval['startTime'].' = '.($interval['endTime'] - $interval['startTime']).'\n';
					if( $interval['endTime'] - $interval['startTime'] < $length)
						continue;
	
					if( ($interval['startTime'] % 1440) < $workdayLowerBound ){
						$interval['startTime'] = ($interval['startEnd']/1440) + workdayLowerBound;
					}
	
					if( ($interval['endTime'] % 1440) > $workdayUpperBound ){
						$interval['endTime'] = ($interval['endTime']/1440) + workdayUpperBound;
					}
	
					$this->root->insertInterval($email, $interval['startTime'], $interval['endTime']);
					
					var_dump($this->root);
					echo '-------------------------------------';
				}
				echo 'NEXT PERSON +++++++++++++++++';
			}
		}

	}

	public function getTop($x){
		$ret = [];
		$this->root->getTop($x,$ret);
<<<<<<< HEAD
		return array_slice( $ret, 0, 5 );
	}
	
	public static function getLength(){
		return self::$length;
=======
		array_slice($ret, 0, 5 );
>>>>>>> b9194aa338dbcf78da7655c79334cde6603d3ec7
	}
}

?>