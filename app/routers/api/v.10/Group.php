<?php

// This is the group Controller, so define it as group Group
$app->group('/api/v1.0/Group', function() use ($app) {
	
	$app->post('/createGroup', function() use ($app){
		$emails = $app->request()->post('emails');
		$groupName = $app->request()->post('groupName');
		
		// Create the group details (name and date?)
		$stmt = Database::prepareAssoc("INSERT INTO GroupDetails (`creatorID`, `name`) VALUES (:creatorID, ':name');");
		$stmt->bindParam(':creatorID', User::authToUserID($_SESSION['token']));
		$stmt->bindParam(':name', $groupName);
		
		$groupID = Database::lastInsertId();
		
		// Insert each account into the group
		$stmt = Database::prepareAssoc("INSERT INTO Group (`groupID`, `userID`) VALUES (:groupID, :userID)");
		$stmt->bindParam(':groupID', $groupID);
		
		foreach($emails as $email){
			$stmt->bindParam(':userID', User::emailToUser($email));
			$stmt->execute();	
		}
		
	});//)->add($MIDDLEWARE_AUTH);
	
});