<?php

require_once __DIR__.'/Binding.php';
require_once __DIR__.'/CRUD.php';

class Database{
	
	private static $connection;
	
	private static function init(){
		try {
			self::$connection = new PDO('mysql:host=54.69.194.54;dbname=easync', 'easync', 'crayaladb15');
			self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
			die();
		}
	}
	
	public static function getConnection(){
		// Check that the calling class uses the CRUD interface
		$trace = debug_backtrace();
		//if(!($trace[1]['class'] instanceof CRUD))
			//die('ERROR: Class must implement CRUD to use the database class...');
			
		// Connect to the database if not already connected
		if(!isset(self::$connection))
			self::init();
		
		return self::$connection;
	}
	
	public static function prepare($query, Binding $binding, $fetchMode=PDO::FETCH_NUM){
		// Check that the calling class uses the CRUD interface
		$trace = debug_backtrace();
		//if(!($trace[1]['class'] instanceof CRUD))
			//die('ERROR: Class must implement CRUD to use the database class...');	
		
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
	
	public static function prepareAssoc($query, Binding $binding){
		return self::prepare($query, $binding, PDO::FETCH_ASSOC);
	}

	public static function lastInsertId(){
		if(isset(self::$connection))
			return self::$connection->lastInsertId();
		else
			die('ERROR: No connection to get lastInsertId from...');
	}

}