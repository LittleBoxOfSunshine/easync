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
		// Connect to the database if not already connected
		if(!isset(self::$connection))
			self::init();
		
		return self::$connection;
	}
	
	public static function prepareAssoc($query){
		// Connect to the database if not already connected
		if(!isset(self::$connection))
			self::init();
		
		$statement = self::$connection->prepare($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		
		return $statement;
	}
}