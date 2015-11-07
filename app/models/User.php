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
	}

	public function login(){
		
	}
	
	public function logout(){
		
	}
	
	public function create(){
		
	}
	
	public function update(){
		
	}
	
	public function delete(){
		
	}

	public function load(){
		
	}
}