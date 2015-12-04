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
		
		if(!isset($email) || !isset($password) || !isset($firstname) || !isset($lastname)){
			echo 'email, password, firstname, and lastname must be provided...';
			return;
		}

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

	$app->get('/exists', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		$app->response->headers->set('Content-Type', 'application/json');
		
		$email = $app->request->get('email');
		$stmt = Database::prepareAssoc("SELECT email from User WHERE email=:email;");
		$stmt->bindParam(':email', $email);
		$stmt->execute();
		
		$dat = $stmt->fetch();
		
		if($dat !== false)
			echo json_encode(true);
		else
			echo json_encode(false);
	});

	$app->get('/getUserDetails', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		$app->response->headers->set('Content-Type', 'application/json');
		$user = new User(array('userID' => $USER_ID));
		$user->getUserDetails();
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

		//This query should be used to convert group names to userID lists
		//$stmt = Database::prepareAssoc("SELECT DISTINCT(u.email) FROM `User` u, `GroupDetails` gd, `Group` g WHERE gd.creatorUserID = :userID AND u.userID != :userID");
		$stmt = Database::prepareAssoc("SELECT name FROM `GroupDetails` WHERE creatorUserID=:userID");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();

		while($row = $stmt->fetch())
			$data[] = $row['name'];

		echo json_encode($data);

	});

	$app->post('/addContacts', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		$contacts = json_decode($app->request()->getBody());

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
	
	$app->get('/getSettings', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;	
		$app->response->headers->set('Content-Type', 'application/json');
		
		$stmt = Database::prepareAssoc("SELECT `data` FROM `Settings` WHERE userID=:userID");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();
		
		$data = $stmt->fetch();
		
		echo $data['data'];
		
	});
		
	$app->post('/updateSettings', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		
		if($app->request->headers->get('Content-Type') != 'application/json'){
			echo 'ERROR: Request body must be json...';
			return;
		}
		
		$data = $app->request()->getBody();
		
		if(!isset($data)){
			echo 'ERROR: No settings data provided or JSON is malformed...';
			return;
		}
		
		$stmt = Database::prepareAssoc("INSERT INTO `Settings` (userID, data) VALUES (:userID, :data) ON DUPLICATE KEY UPDATE data=:data;");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->bindParam(':data', $data);
		//$stmt->debugDumpParams();
		
		$stmt->execute();
		
		echo 'Settings updated...';
	});
	
});
