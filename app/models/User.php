<?php

class User{

	private $email;

	private $firstname;
	private $lastname;
	private $creditCardProvider;
	private $creditCardNo;

	public function __construct($data, $isEmail=true){
		if($isEmail)
			$this->email = $data;
		else{
			$this->email = \Token::tokenToEmail($data);
		}
	}

	public function create($email, $password, $name, $phoneNumber){
		//Verify that the account doesn't already exist
		if(!$this->exists($email)){
			$this->firstname = $name;
			$this->phoneNumber = $phoneNumber;

			//Configure password settings
			//$password_hash = $password;
			// $hashAlgorithm = 'NONE';
			// $numberHashIter = 0;
			// $salt = 'pepper';

			Database::query("INSERT INTO User
				(`name`, `email`, `passwordHash`,`phoneNumber`) VALUES (
				'$name',
				'$email',
				'%s',
				'$phoneNumber'
				);"
			password_hash($password, PASSWORD_DEFAULT));
			return true;
		}
		else{
			return false;
		}
	}

	public function checkLogin($password){
		// return count(\Database::query("SELECT `email` FROM User WHERE
		// 	`email`='$this->email' AND `passwordHash`='$password' LIMIT 1;")) > 0;
		return count(\Database::query("SELECT `email` FROM User WHERE
			`email`='$this->email' AND `passwordHash`='$password' LIMIT 1;")) > 0;
	}

	public function exists($email = ''){
		if($email != '')
			return count(\Database::query("SELECT `email` FROM User WHERE `email`='$email' LIMIT 1;")) > 0;
		else if(! isset($this->email))
			return count(\Database::query("SELECT `email` FROM User WHERE `email`='".$this->email."' LIMIT 1;")) > 0;
		else
			return false;
	}

	public function getLastOrder(){
		$userID = \Database::queryItem("SELECT `UID` FROM User WHERE `email`='$this->email'");
		return \Database::queryItem("SELECT `orderID` FROM `OrderDetails` WHERE `userID`='$userID' ORDER BY `orderID` DESC LIMIT 1;");
	}

	public function getUserDetails(){
		//retrieve and return credit details from user
		$userID = \Database::queryItem("SELECT `UID` FROM User WHERE `email`='$this->email';");
		$userDetails =  \Database::queryRow("SELECT `firstName`, `lastName`, `creditProvider`, `creditNumber` FROM User WHERE `UID`=$userID;");
		$data = [];
		$data['firstname'] = $userDetails[0];
		$data['lastname'] = $userDetails[1];
		$data['creditprovider'] = $userDetails[2];
		$tempNum = strval($userDetails[3]);
		for($i=0; $i<strlen($tempNum) - 4; $i++)
			$tempNum[$i] = '*';
		$data['creditnumber'] = $tempNum;

		echo json_encode($data);
	}

	public function getEmail(){
		return $this->email;
	}
}
	/*
		Author: Chris Henk
		October 2015

		Interface to simply interacting with the mysql db, also forces queries to
		be sanitized
	*/

	class Database{

		//Database Settings
		private static $DB_SERVER = '54.69.194.54';
		private static $DB_DATABASE = 'easync';

		//Database User
		private static $DB_USER = 'easync';
		private static $DB_PASSWORD = 'crayalaeasync';

		//Connection
		private static $CONNECTION;

		private static function executeQuery(&$query){
			if(!isset(self::$CONNECTION))
				self::init();

			//Sanatize the input (prevent injection attacks)
			mysqli_real_escape_string(self::$CONNECTION, $query);

			//Run the query
			return mysqli_query(self::$CONNECTION, $query);
		}

		private static function init(){
			//Connect to the database
			self::$CONNECTION = mysqli_connect(self::$DB_SERVER, self::$DB_USER, self::$DB_PASSWORD, self::$DB_DATABASE);

			if (mysqli_connect_errno())
  				echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		public static function query($query){
			$result = [];

			//Execute the query
			$rows = self::executeQuery($query);

			if(is_bool($rows) || get_class($rows) != 'mysqli_result')
				return NULL;

			//Parse rows one at a time
			while ($row = $rows->fetch_row())
				$result[] = $row;

			//Return the parsed rows
			return $result;
		}

		public static function queryRow($query){
			//Execute the query and return the first row
			$temp = self::executeQuery($query);

			if(is_bool($temp) || get_class($temp) != 'mysqli_result')
				return NULL;

			$temp = $temp->fetch_row();

			return $temp;
		}

		public static function queryItem($query){
			//Execute the query and return the first column of the first row
			$temp = self::executeQuery($query);

			if(is_bool($temp) || get_class($temp) != 'mysqli_result')
				return NULL;

			$temp = $temp->fetch_row();

			if(count($temp[0]) > 1)
				return $temp[0][0];
			else
				return $temp[0];
		}

		public static function rawQuery($query){
			return self::executeQuery($query);
		}

		public static function assocQuery($query){
			$result = [];

			$rows = self::executeQuery($query);

			if(is_bool($rows) || get_class($rows) != 'mysqli_result')
				return NULL;

			//Parse rows one at a time
			while ($row = mysqli_fetch_assoc($rows))
				$result[] = $row;

			//Return the parsed rows
			return $result;
		}

	}

	class Token{

		public static function tokenToEmail($token){
			return Database::queryItem("SELECT `email` FROM Tokens WHERE `auth_token`=$token;");
		}

		public static function generateToken($email){
			$token = rand();
			Database::query("INSERT INTO Tokens (`auth_token`, `email`) VALUES ($token, '$email');");
			return $token;
		}

		public static function revokeToken($auth){
			Database::query("DELETE FROM Tokens WHERE `auth_token`='$auth';");
		}

		public static function revokeTokenByEmail($email){
			Database::query("DELETE FROM Tokens WHERE `email`='$email';");
		}

		public static function tokenExists($email){
			return count(Database::query("SELECT `email` FROM Tokens WHERE `email`='$email';")) > 0;
		}
	}
