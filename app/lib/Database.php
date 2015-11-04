<?php

class Database{
	
	private static $connection;
	
	private static function init(){
		try {
			self::$connection = new PDO('mysql:host=localhost;dbname=easync', 'easync', 'crayalaeasync');
			self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
	}
	
	public static function getConnection(){
		// Check that the calling class uses the CRUD interface
		$trace = debug_backtrace();
		if(!($trace[1]['class'] instanceof CRUD))
			die('ERROR: Class must implement CRUD to use the database class...');
			
		// Connect to the database if not already connected
		if(!isset(self::$connection))
			self::init();
		
		return self::$connection;
	}
	
	public static function prepare(&$query, Binding & $binding, $fetchMode=PDO::FETCH_NUM){
		// Check that the calling class uses the CRUD interface
		$trace = debug_backtrace();
		if(!($trace[1]['class'] instanceof CRUD))
			die('ERROR: Class must implement CRUD to use the database class...');	
		
		// Connect to the database if not already connected
		if(!isset(self::$connection))
			self::init();
		
		// Prepare query and set fetch mode to associative
		$statement = self::$connection->prepare($query);
		$statement->setFetchMode($fetchMode);
		
		// If bindings were provided, apply them
		if(isset($binding))
			$binding->applyBindings($statement);
		
		return $statement;
		
	}
	
	public static function prepareAssoc(&$query, Binding & $binding){
		self::prepare($query, Binding & $binding, PDO::FETCH_ASSOC);
	}

	public static function lastInsertId(){
		if(isset(self::$connection))
			return self::$connection->lastInsertId();
		else
			die('ERROR: No connection to get lastInsertId from...');
	}

}

class Binding{
	
	private $bindings;
	
	public function __construct(array $bindings = array()){
		$this->bindings = $bindings;	
	}
	
	public function applyBindings(PDOStatement & $statement){
		// Apply the bindings to the statement (if given)
		if(isset($statement)){
			foreach($bindings as $key => $val){
				switch(count($val)){
					case 1:
					$statement->bindParam($val[0]);
					break;
					
					case 2:
					$statement->bindParam($val[0], $val[1]);
					break;
					
					case 3:
					$statement->bindParam($val[0], $val[1], $val[2]);
					break;
					
					case 4:
					$statement->bindParam($val[0], $val[1], $val[2], $val[3]);
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