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
	
	public static function prepareAssoc($query){
		// Check that the calling class uses the CRUD interface
		$trace = debug_backtrace();
		if(!($trace[1]['class'] instanceof CRUD))
			die('ERROR: Class must implement CRUD to use the database class...');	
		
		// Connect to the database if not already connected
		if(!isset(self::$connection))
			self::init();
		
		$statement = self::$connection->prepare($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		
		return $statement;
	}
}