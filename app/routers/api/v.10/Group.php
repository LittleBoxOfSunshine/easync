<?php

// This is the group Controller, so define it as group Group
$app->group('/api/v1.0/Group', function() use ($app, $AUTH_MIDDLEWARE) {
	
	$app->post('/createGroup', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		$input = json_decode($app->request()->getBody());
		
		/*if(!isset($input->groupName) || !isset($input->emails)){
			echo 'ERROR: Malformed data...';
			return;
		}*/
		
		// Create the group details (name and date?)
		$stmt = Database::prepareAssoc("INSERT INTO GroupDetails (`creatorUserID`, `name`) VALUES (:creatorID, :name);");
		$stmt->bindParam(':creatorID', $USER_ID);
		$stmt->bindParam(':name', $input->groupName);
		
		$stmt->execute();
		
		$groupID = Database::lastInsertId();
		
		for($i = 0; $i < count($input->emails); $i++)
			$input->emails[$i] = User::emailToUser($input->emails[$i]);
		
		Database::beginTransaction();
		
		// Insert each account into the group
		$stmt = Database::prepareAssoc("INSERT INTO `Group` (`groupID`, `userID`) VALUES (:groupID, :userID);");
		$stmt->bindParam(':groupID', $groupID);
		$stmt->bindParam(':userID', $email);

		foreach($input->emails as $email)
			$stmt->execute();
		
		Database::commit();
		
		if($stmt->errorCode() === '00000'){
			echo 'Group Created';
		}
		else if($stmt->errorCode() === '23000'){
			echo 'ERROR: Group already exists';
		}
		else{
			echo 'A MySQL error has occurred.';
		}
	});

	$app->get('/getGroups', $AUTH_MIDDLEWARE(), function() use ($app) {
		global $USER_ID;

		$app->response->headers->set('Content-Type', 'application/json');

		//$stmt = Database::prepareAssoc("SELECT userID FROM `Group` WHERE groupID=(SELECT groupID FROM GroupDetails WHERE creatorUserID=:userID);");
		$stmt = Database::prepareAssoc("SELECT User.name, email, g.name as groupName FROM (SELECT userID, name FROM `Group` LEFT JOIN GroupDetails ON `Group`.`groupID`=GroupDetails.groupID WHERE `Group`.groupID in (SELECT groupID FROM `Group` WHERE userID=:userID)) as g JOIN User on User.userID=g.userID WHERE g.userID != :userID;");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();

		$data = [];
		if ($data = $stmt->fetchAll()) {
    		echo json_encode($data);
		}

	});

	$app->post('/addContactToGroup', $AUTH_MIDDLEWARE(), function() use ($app) {
		global $USER_ID;
		$app->response->headers->set('Content-Type', 'application/json');

		//get new name, new email and group to be added to
		$data = json_decode($app->request()->getBody());
		$email = $data->email;
		$name = $data->name;
		$groupname = $data->groupName;

		if(!isset($email) || !isset($name) || !isset($groupname)) {
			echo 'email, name, and groupname must be provided...';
			return;
		}

		//get the group number using group name
		$stmt = Database::prepareAssoc("SELECT groupID FROM `GroupDetails` WHERE name=:gname;");
		$stmt->bindParam(':gname', $groupname);
		$stmt->execute();

		$g_number = $stmt->fetch();
		//convert group id to string
		$g_string = $g_number["groupID"];


		//get user id
		$stmt_one = Database::prepareAssoc("SELECT userID FROM `User` WHERE email=:email;");
		$stmt_one->bindParam(':email', $email);
		$stmt_one->execute();

		$u_number = $stmt_one->fetch();
		$u_string = $u_number["userID"];

		//insert person into group
		$stmt_two = Database::prepareAssoc("INSERT INTO `Group` (`groupID`, `userID`) VALUES (:groupID, :userID);");
		$stmt_two->bindParam(':userID', $u_string);
		$stmt_two->bindParam(':groupID', $g_string);
		$stmt_two->execute();

		if($stmt_two->errorCode() === '00000'){
			echo json_encode('Person Added to Group');
			return;
		} else {
			echo json_encode('A MySQL error has occurred.');
			return;
		}

	});

	$app->get('/getGroupNames', $AUTH_MIDDLEWARE(), function() use ($app) {
		global $USER_ID;
		$app->response->headers->set('Content-Type', 'application/json');

		//get the groups
		$stmt = Database::prepareAssoc("SELECT name FROM GroupDetails WHERE groupID in (SELECT groupID FROM `Group` WHERE userID=:userID);");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->execute();

		echo json_encode($stmt->fetchall());
	});
	

});