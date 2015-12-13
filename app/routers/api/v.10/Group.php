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
	
});