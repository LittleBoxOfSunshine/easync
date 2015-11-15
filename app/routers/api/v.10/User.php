<?php

// This is the user Controller, so define it as group User
$app->group('/api/v1.0/User', function() use ($app) {

	$app->get('/home', function () use ($app){
		echo "This is the home function.";
    });//)->add($MIDDLEWARE_AUTH);

	$app->post('/login', function () use ($app){
		$email = $app->request->post('email');
		$password = $app->request->post('password');

		if(!isset($email) || !isset($password)){
			echo 'Email and password must be provided...';
		}
		else{
			$user = new User(array('email' => $email, 'password' => $password));
			if($user->login()){
				echo 'Login successful';
			}
			else{
				echo 'Incorrect username and/or password';
			}
		}

    });

	$app->delete('/logout', function () use ($app){
		echo "This is the delete function.";
    });

	$app->post('/register', function () use ($app){
		$email = $app->request->post('email');
		$password = $app->request->post('password');
		$firstname = $app->request->post('firstname');
		$lastname = $app->request->post('lastname');

		$user = new User(array(
			'email' => $email,
			'name' => $firstname.' '.$lastname
			), true);

		if($user === false){
			//handle input error here
			echo 'user is malformed';
		}
		else{
			if(!$user->exists()){
				$user->register($password);
			}
			else
				echo "ERROR: the email $email is already registered...";
		}

    });

		$app->get('/getUserDetails', function() use ($app){
			echo 'This is getUserDetails function';
		}
		
		});//)->add($MIDDLEWARE_AUTH);

});
