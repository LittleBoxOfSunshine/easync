<?php

require_once __DIR__.'/../lib/Model.php';
require_once __DIR__.'/../lib/Database.php';

class User extends Model implements CRUD{

	private $email;
	private $firstname;
	private $lastname;
	private $creditCardProvider;
	private $creditCardNo;

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
				':password' => $this->password,
				':firstname' => $this->firstname,
				':lastname' => $this->lastname
			));
	}

	public function login(){
			if(checkLogin() != false) {
				echo "successful login"
			}
			else {
				echo "Login failed."
			}
	}

	public function checkLogin(){
		$stmt = \Database::prepareAssoc("SELECT `email` FROM User WHERE
			`email`='$email' AND `password`=''$password'");
		$stmt->execute();
		return $stmt->fetch() !== false;
	}

	public function logout(){

	}

	public function exists(){
			$stmt = \Database::prepareAssoc("SELECT `email` FROM User WHERE `email`='$email'");
			$stmt->execute();
			return $stmt->fetch() !== false;
	}

	public function create(){
		// Prepare sql statement
		$stmt = Database::prepareAssoc("INSERT INTO User VALUES(:email, :password, :firstname, :lastname);", self::$binding);
		$stmt->execute();

	}

	public function update(){

	}

	public function delete(){

	}

	public function load(){

	}
}
