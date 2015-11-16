<?php

class Binding{
	
	private $bindings;
	
	public function __construct(array $bindings = array()){
		$this->bindings = $bindings;	
	}
	
	public function applyBindings(PDOStatement & $statement){
		echo '<br><br>';
		var_dump($this->bindings);
		// Apply the bindings to the statement (if given)
		if(isset($statement)){
			foreach($this->bindings as $key => $val){
				switch(count($val)){
					case 0:
					break;
					
					case 1:
					$statement->bindParam($key, $val);
					break;
					
					case 2:
					$statement->bindParam($key, $val[0], $val[1]);
					break;
					
					case 3:
					$statement->bindParam($key, $val[0], $val[1], $val[2]);
					break;
					
					case 4:
					$statement->bindParam($key, $val[0], $val[1], $val[2], $val[3]);
					break;
					
					default:
					die('ERROR: No bindings were given....');
				}
			}
		}
		else{
			die('ERROR: Valid PDOStatement not given...');
		}
	}
}