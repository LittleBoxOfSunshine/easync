<?php

$app->group('/api/v1.0/Meeting', function() use ($app, $AUTH_MIDDLEWARE) {
	
	//for giving list of meeting possibilities
	$app->post('/planMeeting', $AUTH_MIDDLEWARE(), function () use ($app){
		
		/*

		required:
			startTime, endTime, name, creatorUserID (global, not input), timeZone
		
		json object consists of:
			equireds ^ 
			array of emails
			length
			day range (how early/late in day)
			all attendees required(bool)

		output: array of the ranges, sorted by rank;# of people can't attend each one
		*/

		/*
		steps: (a lot of these will be combined/moved)
			! copy emails, prune those w/out googlecal acess
			! load any tokens, construct any API objects
			pull events
			diff by all attendees - look up group scheduling algorithm 
				get the free times of each person (sort.php)
					(not necessary to remember event names)
				find the max overpallping of $length

				if impossibr and required
					give failure message
				else if not required
					move on

			diff, rank by max #tendies
			give top options or failure if no optoins exist (store copy of results in session)
			
		*/
		$app->response->headers->set('Content-Type', 'application/json');

		//json or form request?

		$meeting = json_decode($app->request()->getBody());

		//required for calendar diffing
		$emails = $meeting->emails;
		$length = $meeting->length;
		$dayEnd = $meeting->dayEnd;
		$dayStart = $meeting->dayStart;
		$allRequired = $meeting->allRequired;
		$startTime =  new DateTime($meeting->EventDetails->startTime);
		$endTime = new DateTime($meeting->EventDetails->endTime);
		
		//not required - passed as json encoded cookie
		$meetingDetails = [];
		$meetingDetails['name'] = $meeting->EventDetails->name;
		$meetingDetails['description'] = $meeting->EventDetails->description;
		$meetingDetails['creatorEmail'] = $meeting->EventDetails->creatorEmail;
		$meetingDetails['location'] = $meeting->EventDetails->location;
		$meetingDetails['attachments'] = $meeting->EventDetails->attachments;

		$_SESSION['meetingDetails'] = $meetingDetails;


		$startTime = $startTime->format('Y-m-d\TH:i:sP');
		$startTime = substr($startTime, 0, -6);
		$startTime = $startTime . "-06:00";

		$endTime = $endTime->format('Y-m-d\TH:i:sP');
		$endTime = substr($endTime, 0, -6);
		$endTime = $endTime . "-06:00";

		$allEvents = [];


		foreach($emails as $email){
			$stmt = Database::prepareAssoc("SELECT `userID` FROM `User` WHERE email=:email");
			$stmt->bindParam(':email', $email);
			$stmt->execute();
			$userID = $stmt->fetch();

			if($userID == false)
				echo "ERROR: The user: " . $email . " does not have a userID\n";
			else{
				$userID = $userID['userID'];
				
				$cal = new GoogleCalendar(array('userID' => $userID));
				//$allEvents[] = $cal->getEvents($startTime, $endTime);
				$mergedEvents = $cal->merge_ranges( $cal->getEvents($startTime, $endTime) );
				$invertedEvents = $cal->invertEvents($mergedEvents);
				$minuteEvents = $cal->convertToMinutes( $invertedEvents, $startTime );

				$allEvents[$email] = $minuteEvents;
			} 

		}

		$start = explode(':', $dayStart);
		$minutes = $start[0] * 60;
		$minutes += $start[1];

		$dayStart = $minutes;

		$end = explode(':', $dayEnd);
		$minutes = $end[0] * 60;
		$minutes += $end[1];

		$dayEnd = $minutes;

		$length = explode(':', $length);
		$minutes = $length[0] * 60;
		$minutes += $length[1];

		$length = $minutes;

		$tree = new CalIntervalDiff($allEvents, 0, 9999999999, $dayStart, $dayEnd, $length);

		$meetingTimes = $tree->getTop(5);
/*
		$meetingTimes = array(
			array(
				'people' => array('smitheric95@gmail.com', 'cahenk95@gmail.com', 'newtest@gmail.com'),
				'startTime' => '0',
				'endTime' => '480'
			),
			array(
				'people' => array('smitheric95@gmail.com', 'cahenk95@gmail.com'),
				'startTime' => '481',
				'endTime' => '3360'
			)
		);
*/	

		//make sure everyone can attend
		if($allRequired){
			if(count($emails) != count($meetingTimes[0]['people'])){
				echo "A meeting time is not possible for all members.";
				return;
			}
		}

		//store info (sessionMeetings) in a session 
		//sessionMeetings contains same info as finalMeetings with the same indexes
		//but its information is in a different format for the database

		$sessionMeetings = [];


		foreach($meetingTimes as $meet) {
			$start = new DateTime($startTime);

			$newTime = $start->add(new DateInterval('PT' . $meet['startTime'] . 'M'));
			$newTime = $newTime->format('Y-m-d H:i:s');

			$meet['startTime'] = $newTime;

			$start = new DateTime($startTime);

			$newTime = $start->add(new DateInterval('PT' . $meet['endTime'] . 'M'));
			$newTime = $newTime->format('Y-m-d H:i:s');

			$meet['endTime'] = $newTime;

			$sessionMeetings[] = $meet;
		}


		$_SESSION['meetings'] = $sessionMeetings;

		//convert to non 0 indexed and change email to names
		$finalMeetings = [];

		foreach($meetingTimes as $meet) {
			$start = new DateTime($startTime);

			$newTime = $start->add(new DateInterval('PT' . $meet['startTime'] . 'M'));
			$newTime = $newTime->format('Y-m-d\TH:i:sP');
			$newTime = substr($newTime, 0, -6);
			$newTime = date("D, M d g:i A", strtotime($newTime));
			
			$meet['startTime'] = $newTime;

			$start = new DateTime($startTime);

			$newTime = $start->add(new DateInterval('PT' . $meet['endTime'] . 'M'));
			$newTime = $newTime->format('Y-m-d\TH:i:sP');
			$newTime = substr($newTime, 0, -6);
			$newTime = date("D, M d g:i A", strtotime($newTime));

			$meet['endTime'] = $newTime;

			$newPeople = [];

			$stmt = Database::prepareAssoc("SELECT `name` FROM `User` WHERE email=:person");
			$stmt->bindParam(':person', $person);
			
			foreach($meet['people'] as $person){
				$stmt->execute();

				$newPeople[] = $stmt->fetch()['name'];
			}

			$meet['people'] = $newPeople;

			$finalMeetings[] = $meet;
		}

		echo json_encode( $finalMeetings );
		
	});

	//if exists, update rather than insert
	$app->post('/finalizeMeeting', $AUTH_MIDDLEWARE(), function () use ($app){
		/*
		* change email cookie to all json 
		* should contain stuff for meetingDetails like location, etc.
		*/

		if( isset($_SESSION['meetings']) ){
			//load meeting details from session using index (time range) given as input	
			$index = json_decode($app->request()->getBody());

			if($index === NULL)
				die("Index was not sent in json.");

			$index = $index->index;

			$meetingDetails = $_SESSION['meetingDetails'];
			$name = $meetingDetails['name'];
			$description = $meetingDetails['description'];
			$creatorEmail = $meetingDetails['creatorEmail'];
			$location = $meetingDetails['location'];
			$attachments = $meetingDetails['attachments'];

			$creationTime = date('Y-m-d H:i:s');

			//holds attendees and start/end times
			$meeting = $_SESSION['meetings'][$index];

			$creatorUserID = User::emailToUser($creatorEmail);
			
			
			//insert into meetingDetails then meeting
			$stmt = Database::prepareAssoc("INSERT INTO `MeetingDetails` (location, startTime, creationTime, endTime, name, description, creatorUserID, attachments)
				VALUES (:location, :startTime, :creationTime, :endTime, :name, :description, :creatorUserID, :attachments);");
			$stmt->bindParam(':location', $location);	
			$stmt->bindParam(':startTime', $meeting['startTime']);
			$stmt->bindParam(':creationTime', $creationTime);
			$stmt->bindParam(':endTime', $meeting['endTime']);
			$stmt->bindParam(':name', $name);			
			$stmt->bindParam(':description', $description);
			$stmt->bindParam(':creatorUserID', $creatorUserID);
			$stmt->bindParam(':attachments', $attachments);
			$stmt->execute();

			$meetingID = Database::lastInsertId();

			$stmt = Database::prepareAssoc("SELECT `name` FROM `User` WHERE email=:email");
			$stmt->bindParam(':email', $creatorEmail);
			$stmt->execute();

			$creatorName = $stmt->fetch();			
			$creatorName = $creatorName['name'];

			$stmt = Database::prepareAssoc("INSERT INTO `Meeting` (`email`, `meetingID`) VALUES(:email, :meetingID);");
			$stmt->bindParam(':email', $email);
			$stmt->bindParam(':meetingID', $meetingID);

			foreach($meeting['people'] as $email){
				$stmt->execute();

				$message = '<html><body>' . $creatorName . ' has invited you to join their meeting<br>';
				$message .= "<a href='easync.com/api/v1.0/Meeting/rsvp'>Click here</a>" . ' to RSVP.<br>';
				$message .= "Sincerely, your friends at Easync.";
				$message .= '</body></html>';

				$subject = "Easync: invitation from ". $creatorName;

				$headers = "From: bot@easync.com";
				// send email
				mail($email,$subject,$message, $headers);
			}

			echo "Your meeting has been scheduled.";
			
		}

		else{
			die('No meetings cookie was set.. planMeeting was never called?');
		}

		/*
		
		output: success/failure msgs

		steps:
			load meeting details from session using index (time range) given as input
			create meeting (insert db)
			add attendees (insert db)
			send confimartion/rsvp emails to list of emails
			echo successs/failure
		*/
	});	
	
	$app->post('/rsvp', $AUTH_MIDDLEWARE(), function() use ($app){
		$rsvpToken = $app->request->post('rsvpToken');
		
		// modify attendie entry to acception
		
	});
});