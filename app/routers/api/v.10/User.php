<?php

// This is the user Controller, so define it as group User
$app->group('/api/v1.0/User', function() use ($app, $AUTH_MIDDLEWARE) {

	$app->get('/home', $AUTH_MIDDLEWARE(), function () use ($app){
		echo "This is the home function.";
    });

	$app->post('/googleSignIn', function () use ($app) {

		$data = json_decode($app->request()->getBody());

		$email = $data->email;
		$firstname = $data->firstname;
		$lastname = $data->lastname;
		$fullName = $firstname.' '.$lastname;
		$google_ID = $data->google_ID;
		$google_token = $data->google_token;

		$user = new User(array('email' => $email));

		$stmt = Database::prepareAssoc("SELECT email from User WHERE email=:email;");
		$stmt->bindParam(':email', $email);
		$stmt->execute();

		try{
			$response = file_get_contents("https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=$google_token");
			$response = json_decode($response);
		}
		catch(Exception $e){
			echo 'Error with Google token';
			return;
		}


		if($stmt->fetch()){
			$stmt = Database::prepareAssoc("SELECT googleID FROM User WHERE email=:email;");
			$stmt->bindParam(':email', $email);
			$stmt->execute();
			$anger = $stmt->fetch();
			if(is_null($anger["googleID"])){
				$stmt = Database::prepareAssoc("UPDATE User SET `googleID` = :google_ID WHERE `email` = :email;");
				$stmt->bindParam(':google_ID', $google_ID);
				$stmt->bindParam(':email', $email);
				$stmt->execute();
				echo 'Inserted tokenID';
			}
			else{
				echo json_encode('Already Used Google Sign In.');
			}
		}

		else {
			$stmt = Database::prepareAssoc("INSERT INTO User (`email`, `name`, `googleID`) VALUES(:email, :name, :google_ID);");
			$stmt->bindParam(':email', $email);
			$stmt->bindParam(':name', $fullName);
			$stmt->bindParam(':google_ID', $google_ID);
			$stmt->execute();

		    if($stmt->errorCode() === '00000'){
			    echo json_encode('Account Created.');
		    }
		    else if($stmt->errorCode() === '23000'){
			    echo json_encode('ERROR: This email is already registered...');
		    }
		    else{
			    echo json_encode('A MySQL error has occurred.');
		    }
	    }

	    $uID = Database::lastInsertId();

		if(isset($_SESSION['auth_token'])){
			$user->revokeAuthToken($_SESSION['auth_token']);
		}

		$_SESSION['auth_token'] = $user->createAuthToken($uID);
	});

	$app->post('/login', function () use ($app){
		/*if($app->request->headers->get('Content-Type') != 'application/json'){
			echo 'ERROR: Request body must be json...';
			return;
		}*/

		$data = json_decode($app->request()->getBody());
		$email = $data->email;
		$password = $data->password;

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

	$app->post('/nearbyJoin', $AUTH_MIDDLEWARE(), function () use ($app){
		global $USER_ID;
		$app->response->headers->set('Content-Type', 'application/json');
		$token = $app->request->get('token');

		$stmt = Database::prepareAssoc("INSERT INTO NearbyAttendees (`token`,`userID`) VALUES (:token, :userID);");
		$stmt->bindParam(':token', $token);
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();

		echo json_encode($token);
	});

	$app->post('/rsvp', function () use ($app){
		global $USER_ID;
		$token = $app->request->post('token');
		$attending = $app->request->post('attending');

		$stmt = Database::prepareAssoc("UPDATE Meeting SET rsvp=:attending  WHERE token=:token;");
		$stmt->bindParam(':token', $token);
		$stmt->bindParam(':attending', $attending);
		$stmt->execute();

		if($stmt->errorCode() === '00000'){
			echo 'Successfully Added to Meeting.';
		}
		else {
				echo 'mySQL error.';
		}

	});

	$app->delete('/logout', $AUTH_MIDDLEWARE(), function () use ($app){
		global $USER_ID;
		$user = new User(array('userID' => $USER_ID));
		$user->logout();
    });

	$app->get('/getMeetings', $AUTH_MIDDLEWARE(), function () use ($app){
        global $USER_ID;

        $app->response->headers->set('Content-Type', 'application/json');

        $email = User::userToEmail($USER_ID);
        $meetings = [];

        $stmt = Database::prepareAssoc("SELECT `meetingID` FROM `Meeting` WHERE `email` = :email;");
        $stmt->bindParam(':email',$email);
        $stmt->execute();

        $mIDs = [];
        while($row = $stmt->fetch())
            $mIDs[] = $row['meetingID'];

        $stmt = Database::prepareAssoc("SELECT * FROM `MeetingDetails` WHERE `meetingID` = :meetingID;");
        $stmt->bindParam(':meetingID', $meetingID);

        foreach($mIDs as $meetingID) {
            $stmt->execute();
            $meetings[] = $stmt->fetch();
        }

        $stmt = Database::prepareAssoc("SELECT email FROM `Meeting` WHERE `meetingID` = :meetingID");
        $stmt->bindParam(':meetingID', $meetingID);

        foreach($mIDs as $meetingID) {
            $stmt->execute();
            $counter = 0;
            foreach($meetings as $meet){
                $emails = $stmt->fetchAll();
                for($i=0;$i<count($emails);$i++)
                    $emails[$i] = $emails[$i]['email'];

                $meetings[$counter]['attendies'] = $emails;
                $counter++;
            }
        }

        echo json_encode($meetings);
    });

	$app->get('/nearbyGetAttendees', $AUTH_MIDDLEWARE(), function () use ($app){
		global $USER_ID;
		$token = $app->request->get('token');
		$counter = 0;
		$app->response->headers->set('Content-Type', 'application/json');

		$stmt = Database::prepareAssoc("SELECT `userID` FROM `NearbyAttendees` WHERE token=:token;");
		$stmt->bindParam(':token', $token);
		$stmt->execute();

		$attendees = [];
		$userIDs = [];
		while($row = $stmt->fetch())
			$userIDs[] = $row['userID'];

		$stmt = Database::prepareAssoc("SELECT `name`,`email` FROM `User` WHERE `userID` = :userID;");
		$stmt->bindParam(':userID', $userID);

		foreach($userIDs as $userID) {
			$stmt->execute();
			$attendees[] = $stmt->fetch();
		}

	    echo json_encode($attendees);
    });

    $app->post('/register', function () use ($app){

        /*if($app->request->headers->get('Content-Type') != 'application/json'){
			echo 'ERROR: Request body must be json...';
			return;
		}*/

        $data = json_decode($app->request()->getBody());
        $email = $data->email;
        $password = $data->password;
        $firstname = $data->firstname;
        $lastname = $data->lastname;

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

        // if($user->login($data->password)) {
        // 	//$app->redirect('/api/v1.0/User/addGoogleCal');
        // } else {
        // 	echo 'Some error';
        // }
		

    });

    $app->get('/addGoogleCal', $AUTH_MIDDLEWARE(), function () use ($app){
    	global $USER_ID;
		$platformID = 'Google';

    	$stmt = Database::prepareAssoc("SELECT `token` FROM `CalendarTokens` WHERE userID=:userID AND platformID=:platformID");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->bindParam(':platformID', $platformID);
		$stmt->execute();
		$calToken = $stmt->fetch();

	    if ( $calToken === false ) {
	    	// Step 1:  The user has not authenticated - redirect them
		    if (!isset($_GET['code'])) {
		    	GoogleCalendar::requestAccess($app, $USER_ID);
		    }
		    // Step 2: The user accepted your access now you need to exchange it.
		    else{
		    	GoogleCalendar::acceptAccess($USER_ID);
		    }
	    }

	   	$app->redirect('/');

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

	$app->get('/hasConnectedGoogleCal', $AUTH_MIDDLEWARE(), function() use ($app) {
		global $USER_ID;
		$app->response->headers->set('Content-Type', 'application/json');

		$stmt = Database::prepareAssoc("SELECT userID from CalendarTokens WHERE userID=:userID");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();

		$dat = $stmt->fetch();

		if($dat !== false)
			echo json_encode(true);
		else
			echo json_encode(false);
	});

	$app->post('/nearbyInIt', $AUTH_MIDDLEWARE(), function () use ($app){
		global $USER_ID;
		$app->response->headers->set('Content-Type', 'application/json');
		$token = uniqid();
		$stmt = Database::prepareAssoc("INSERT INTO NearbyToken (`token`,`creatorUserID`) VALUES (:token, :userID);");
		$stmt->bindParam(':token', $token);
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();
		echo json_encode($token);
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

	$app->get('/getContactsInfo', $AUTH_MIDDLEWARE(), function() use ($app) {
		global $USER_ID;
		$app->response->headers->set('Content-Type', 'application/json');

		$stmt = Database::prepareAssoc("SELECT name, email FROM User WHERE email in 
										(SELECT contactEmail FROM Contacts WHERE userID=:userID)");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();

		$data = [];
		if ($data = $stmt->fetchAll()) {
    		echo json_encode($data);
		}
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

	$app->post('/addContactsCheckEmail', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		$contacts = json_decode($app->request()->getBody());
		$contact = $contacts->email;


		$stmt_one = Database::prepareAssoc("SELECT name, email FROM User WHERE email=:email;");
		$stmt_one->bindParam(':email', $contact);
		$stmt_one->execute();

		$dat = $stmt_one->fetch();
		if ($dat == false) {
			echo 'no user found for email';
			return;
		}

		$stmt = Database::prepareAssoc("INSERT INTO Contacts (`userID`, `contactEmail`) VALUES (:userID, :contactEmail);");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->bindParam(':contactEmail', $contact);

		$stmt->execute();

		if($stmt->errorCode() === '00000'){
			echo json_encode($dat);
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

		/*if($app->request->headers->get('Content-Type') != 'application/json'){
			echo 'ERROR: Request body must be json...';
			return;
		}*/

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

	$app->get('/removeContact', $AUTH_MIDDLEWARE(), function() use ($app) {
		global $USER_ID;

		$email = $app->request->get('email');
		$stmt = Database::prepareAssoc("DELETE FROM Contacts WHERE contactEmail=:email AND userID=:userID;");
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();

		if($stmt->errorCode() === '00000'){
			echo 'Contact Removed';
		} else {
			echo 'A MySQL error has occurred.';
		}
	});

});
