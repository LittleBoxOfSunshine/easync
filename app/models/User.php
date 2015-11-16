<?php

require_once __DIR__.'/../lib/Model.php';
require_once __DIR__.'/../lib/Database.php';

class User extends Model implements CRUD{

	protected $email;
	protected $name;

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
			$stmt = \Database::prepareAssoc("SELECT `passwordHash`, `passwordSalt` FROM User WHERE `email`=':email';");	
			$stmt->execute();
			
			// Try to load the data
			if($data = $stmt->fetch()){
				// Hash the password
				$options = array('salt' => $data['passwordSalt']);
				$this->createAuthToken();
				return strcmp(password_hash($password, PASSWORD_BCRYPT, $options), $data['passwordHash']) == 0;
			}
			else{
				return false;
			}
		}
	}
	
	public function isLoggedIn(){
		// check if auth token exists (in session)
	}

	public function logout(){

	}
	
	public function createAuthToken(){
		
	}
	
	public function revokeAuthToken(){
		
	}

	public function exists(){
			$stmt = \Database::prepareAssoc("SELECT `email` FROM User WHERE `email`=':email'", $this->getBinding());
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
