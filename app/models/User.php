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

		// Initialize MySQL bindings
		parent::initBinding(array(
			':email' => $this->email,
			':name' => $this->name,
			':userID' => $this->userID
		));
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
		if(!isset($_SESSION['auth_token'])){
			echo 'Currently logged in';
		else
			echo'Not currently logged in';
		}
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
		$stmt = Database::prepareAssoc("INSERT INTO Auth_Token (`auth_token`, `userID`) VALUES(:token, :userID));"
		$stmt->bindParam(':token', $token);
		$stmt->execute();
		return $token;
	}

	public function revokeAuthToken($auth){
		$stmt = Database::prepareAssoc("DELETE FROM Auth_Token WHERE `auth_token`=`:auth`;")
		$stmt->bindParam(':auth', $auth);
		$stmt->execute();
	}

	public function exists(){
			$stmt = Database::prepareAssoc("SELECT `email` FROM User WHERE `email`=':email'", $this->getBinding());
			$stmt->execute();
			return $stmt->fetch() !== false;
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
		password_hash($this->password, PASSWORD_BCRYPT, array('salt' => $salt));
		// Prepare sql statement
		$stmt = Database::prepareAssoc("INSERT INTO User (`email`, `name`, `passwordHash`, `passwordSalt`)
			VALUES(:email, :name, :password, :salt);", parent::getBinding());

		var_dump(parent::getBinding());
		$stmt->debugDumpParams();

		$stmt->bindParam(':password', $this->password);

		$stmt->bindParam(':salt', $salt);

		$stmt->execute();
	}

	public function update(){

	}

	public function delete(){

	}

	public function load(){

	}
}
