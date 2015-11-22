<?php

require_once __DIR__.'/../lib/Model.php';
require_once __DIR__.'/../lib/Database.php';

class User extends Model implements CRUD{

	protected $email;
	protected $name;
	protected $userID;

	public function __construct(array $args = array(), $fromUserInput=false){
		parent::__construct(
			$args,
			array(
				array('userID'),
				array('email')
			),
			$fromUserInput
		);
	}

	public function login($password, $googleAuth=false){
		if($googleAuth){
			echo 'Goog Auth is not yet enabled...';
			return false;
		}
		else{
			// Load the user's salt from the database
			$stmt = Database::prepareAssoc("SELECT `passwordHash`, `passwordSalt` FROM User WHERE `email`=':email';");
			$stmt->execute();

			// Try to load the data
			if($data = $stmt->fetch()){
				// Hash the password
				$options = array('salt' => $data['passwordSalt']);
				$_SESSION['auth_token'] = $this->createAuthToken();
				return strcmp(password_hash($password, PASSWORD_BCRYPT, $options), $data['passwordHash']) == 0;
			}
			else{
				return false;
			}
		}
	}

	public function getUserDetails(){

	}

	public function getContacts(){

	}

	public function get(){

	}

	public function addGoogleCal(){

	}

	public function getMeetings(){

	}

	public function getSettings(){

	}

	public function updateSettings(){

	}

	public function isLoggedIn(){
		// check if auth token exists (in session)
		if(!isset($_SESSION['auth_token']))
			echo 'Currently logged in';
		else
			echo'Not currently logged in';
	
	}

	public function logout(){
		if(isset($_SESSION['auth_token'])){
			revokeAuthToken($_SESSION['auth_token']);
			unset($_SESSION['auth_token']);
			echo 'Logout completed';
		}
		else{
			echo 'User is already logged out';
		}

	}

	public function createAuthToken(){
		$token = bin2hex(random_bytes(5));
		$stmt = Database::prepareAssoc("INSERT INTO Auth_Token (`auth_token`, `userID`) VALUES(:token, :userID);");
		$stmt->bindParam(':token', $token);
		$stmt->execute();
		return $token;
	}

	public function revokeAuthToken($auth){
		$stmt = Database::prepareAssoc("DELETE FROM Auth_Token WHERE `auth_token`=':auth';");
		$stmt->bindParam(':auth', $auth);
		$stmt->execute();
	}
	
	public static function authToUserID($authToken){
		$stmt = Database::prepareAssoc("SELECT userID FROM Auth_Token WHERE `auth_token`=':auth';");
		$stmt->bindParam(':auth', $auth);
		$stmt->execute();
		$ret = $stmt->fetch();
		return $ret['userID'];
	}
	
	public static function emailToUser($email){
		$stmt = Database::perepareAssoc("SELECT userID FROM User WHERE email=:email;");
		$stmt->bindParam(':email', $email);
		$stmt->execute();
		$ret = $stmt->fetch();
		return $ret['userId'];
	}
	
	public static function userToEmail($userID){
		$stmt = Database::perepareAssoc("SELECT email FROM User WHERE userID=:userID;");
		$stmt->bindParam(':userID', $userID);
		$stmt->execute();
		$ret = $stmt->fetch();
		return $ret['email'];
	}

	public function exists(){
			$stmt = Database::prepareAssoc("SELECT `email` FROM User WHERE `email`=':email'", $this->getBinding());
			$stmt->execute();
			$ret = $stmt->fetch();
			return $ret['email'] !== false;
	}

	public function register($password){
		$this->password = $password;
		$this->create();
		unset($this->password);
	}

	public function create(){
		// Create salt
		//$size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
		$salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
		// Hash the password
		$this->password = password_hash($this->password, PASSWORD_BCRYPT, array('salt' => $salt));
		// Prepare sql statement
		$stmt = Database::prepareAssoc("INSERT INTO User (`email`, `name`, `passwordHash`, `passwordSalt`) 
			VALUES(:email, :name, :password, :salt);");
		
		$stmt->bindParam(':email', $this->email);
		$stmt->bindParam(':name', $this->name);	

		$stmt->bindParam(':password', $this->password);

		$stmt->bindParam(':salt', $salt);

		$stmt->execute();
		
		if($stmt->errorCode() === '00000'){
			echo 'Account Created.';
		}
		else if($stmt->errorCode() === '23000'){
			echo 'ERROR: This email is already registered...';
		}
		else{
			echo 'A MySQL error has occurred.';
		}
	}

	public function update(){

	}

	public function delete(){

	}

	public function load(){

	}
}
