<?php

// This is the user Controller, so define it as group User
$app->group('/api/v1.0/User', function() use ($app, $AUTH_MIDDLEWARE) {

	$app->get('/home', $AUTH_MIDDLEWARE(), function () use ($app){
		echo "This is the home function.";
    });
	
	$app->post('/login', function () use ($app){
		$email = $app->request->post('email');
		$password = $app->request->post('password');

		if(!isset($email) || !isset($password)){
			echo 'Email and password must be provided...';
		}
		else{
			$user = new User(array('email' => $email));
			
			if(isset($_SESSION['auth_token']))
				$user->revokeAuthToken($_SESSION['auth_token']);
			
			if($user->login($password) === true){
				echo 'Login successful';
			}
			else{
				echo 'Incorrect username and/or password';
			}
		}

    });

	$app->delete('/logout', $AUTH_MIDDLEWARE(), function () use ($app){
		global $USER_ID;
		$user = new User(array('userID' => $USER_ID));
		$user->logout();
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
			$user->register($password);
		}

    });

	$app->get('/getUserDetails', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		$user = new User(array('userId' => $USER_ID));
		$user->getUserDetails();
		echo 'This is getUserDetails function';

	});
		
	$app->get('/getContacts', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		$app->response->headers->set('Content-Type', 'application/json');

		$stmt = Database::prepareAssoc("SELECT `contactEmail` FROM `Contacts` WHERE `userID`=:userID;");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();
		
		$data = [];
		while($row = $stmt->fetch())
			$data[] = $row['contactEmail'];
		
		echo json_encode($data);	

	});
	
	$app->post('/addContacts', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		$contacts = json_decode($app->request()->getBody());
		$contacts = $contacts->emails;
		
		Database::beginTransaction();

		$stmt = Database::prepareAssoc("INSERT INTO Contacts (`userID`, `contactEmail`) VALUES (:userID, :contactEmail);");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->bindParam(':contactEmail', $contact);
		
		foreach($contacts as $contact)
			$stmt->execute();
			
		Database::commit();
		
		if($stmt->errorCode() === '00000'){
			echo 'Contacts Added';
		}
		else if($stmt->errorCode() === '23000'){
			echo 'WARNING: These contacts already exist...';
		}
		else{
			echo 'A MySQL error has occurred.';
		}

	});		
		
});
