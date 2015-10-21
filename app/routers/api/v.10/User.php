<?php

// Import Dependencies
require __DIR__ . '/vendor/autoload.php';

// This is the user Controller, so define it as group User
$app->group('/api/v1.0/User', function() use ($app) {

	$app->get('/home', function () use ($app){

    });//)->add($MIDDLEWARE_AUTH);

	$app->post('/login', function () use ($app){
		$email = $app->request->post('email');
		$password = $app->request->post('password');
		$user = new \User($email);

		if(\Token::tokenExists($email))
			\Token::revokeTokenByEmail($email);

		if($user->checkLogin($password)){
			$_SESSION['auth_token'] = \Token::generateToken($email);
			echo "$email logged in";
		}
		else{
			echo "Invalid login";
		}


    });

	$app->delete('/logout', function () use ($app){

    });

	$app->post('/register', function () use ($app){
		$email = $app->request->post('email');
		$password = $app->request->post('password');
		$user = new \User($email);
		if($user->create($email, $password, $firstname.$lastname, $phoneNumber)){
			echo 'Account Created. Logging in... ';
			self::signIn($email, $password);
		}
		else{
			echo 'Account already exists';
		}

    });

});
