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
			$user = new User(array('email' => $email));
			if($user->login($password)){
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
			//if(!$user->exists()){
				$user->register($password);
			//}
			//else
				//echo "ERROR: the email $email is already registered...";
		}

    });

    $app->get('/addGoogleCal', function () use ($app){
    	$stmt = Database::prepareAssoc("SELECT `token` FROM `CalendarTokens` WHERE userID=:userID AND platformID=:platformID");
		$stmt->execute();
		$calToken = $stmt->fetch();

	    if ( count($calToken) == 0 ) {
	    	// Step 1:  The user has not authenticated - redirect them  
		    if (!isset($_GET['code'])) {
		    	GoogleCalendar::requestAccess($app);
		    }
		    // Step 2: The user accepted your access now you need to exchange it.
		    else{
		    	GoogleCalendar::acceptAccess();
		    }
	    }
	    
	    $test = new GoogleCalendar();
	   	$test->getEvents();
		
	});//)->add($MIDDLEWARE_AUTH);

	$app->get('/getSettings', function () use ($app){
		
	});

	$app->get('/getUserDetails', function() use ($app){
		echo 'This is getUserDetails function';
	
	});//)->add($MIDDLEWARE_AUTH);
});
