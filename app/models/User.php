<?php

require_once __DIR__.'/../lib/Model.php';
require_once __DIR__.'/../lib/Database.php';

class User extends Model implements CRUD{

	private $email;
	private $name;

	public function __construct(array $args = array(), $fromUserInput=false){
		parent::__construct(
			$args,
			array(
				array('userID'),
				array('email', 'password')
			),
			$fromUserInput
		);

		// Initialize MySQL bindings
		parent::initBinding(array(
			':email' => $this->email,
			':name' => $this->name
		));
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
			$stmt = \Database::prepareAssoc("SELECT `email` FROM User WHERE `email`=':email'");
			$stmt->execute();
			return $stmt->fetch() !== false;
	}

	public function create($password){
		// Create salt
		$size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
		$salt = mcrypt_create_iv($size, MCRYPT_DEV_RANDOM);

		// Hash the password
		password_hash($password, PASSWORD_BCRYPT, array('salt' => $salt));

		// Prepare sql statement
		$stmt = Database::prepareAssoc("INSERT INTO User (`email`, `name`, `passwordHash`, `passwordSalt`)
			VALUES(:email, :name, :password, :salt);", self::$binding);

		$stmt->bindParam(':password', $password);
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
