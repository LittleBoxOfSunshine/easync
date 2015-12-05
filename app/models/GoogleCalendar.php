<?php

require_once(__DIR__.'/../lib/Model.php');
require __DIR__ . '/../../vendor/autoload.php';
class GoogleCalendar extends Model{
	const CLIENT_ID = '66468154963-kjvu0u6hohvv59l03d0gr8kcomi4pggd.apps.googleusercontent.com';
	const CLIENT_SECRET = 'oNwvkqFISBzSjxNeWtZcnrAo';
	const REDIRECT_URI = 'http://easync.com/api/v1.0/User/addGoogleCal';
	const PLATFORM_ID = 'Google';

	protected $userID;
	protected $calID;
	protected $token;
	protected $client;
	protected $calendarList;

	public function __construct(array $args = array()){
	  
	    $args = array_merge(array(
	    	'calID' => self::PLATFORM_ID
	    ), $args);

	    parent::__construct($args,
			array(
				'userID',
				'calID'
			)
		);
	    
		$this->client = self::makeGoogleClient($this->userID);
		
		// Refresh the token if it's expired.
		if($this->client->isAccessTokenExpired()){
			$temp = json_decode($this->client->getAccessToken());

			if($temp == NULL)
				die("ERROR: user has not allowed easync to access their Google Calendar");

			$this->client->refreshToken( $this->client->REFRESH_TOKEN_DB );
			$token = $this->client->getAccessToken();
			//save the token!
			$stmt = Database::prepareAssoc("UPDATE `CalendarTokens` SET token=:token WHERE userID=:userID AND platformID=:platformID");
			$stmt->bindParam(':token', $token);
			$stmt->bindParam(':platformID', $this->calID);
			$stmt->bindParam(':userID', $this->userID);
			$stmt->execute();
		}
		
		
		$this->calendarList = new Google_Service_Calendar($this->client);   

	}

	private static function makeGoogleClient($USER_ID){
		$client = new Google_Client();
	    $client->setApplicationName("Easync");
	    $client->setClientId(self::CLIENT_ID);
	    $client->setClientSecret(self::CLIENT_SECRET);
	    $client->setRedirectUri(self::REDIRECT_URI);
	    $client->setAccessType('offline');   // Gets us our refreshtoken
	    //$client->setApprovalPrompt('force');

	    $client->setScopes(array('https://www.googleapis.com/auth/calendar'));

	    $platID = self::PLATFORM_ID;


	    $calToken = self::loadTokens($USER_ID);

	    if( $calToken !== false ){
	    	$client->setAccessToken($calToken['token']);
	    	$client->REFRESH_TOKEN_DB = $calToken['refreshToken'];
	    }

	    return $client;
	}

	private static function loadTokens($USER_ID){
		$platID = self::PLATFORM_ID;

	    $stmt = Database::prepareAssoc("SELECT `token`, `refreshToken` FROM `CalendarTokens` WHERE userID=:userID AND platformID=:platformID");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->bindParam(':platformID', $platID );
		$stmt->execute();
		$allTokens = $stmt->fetch();

		if( $allTokens !== false )
			return $allTokens;
		else
			return false;
	}

	public static function requestAccess($app, $USER_ID){
		$authUrl = self::makeGoogleClient($USER_ID)->createAuthUrl();
 		$app->redirect($authUrl);
	}
	
	public static function acceptAccess($userID){
		$client = self::makeGoogleClient($userID);
		$client->authenticate($_GET['code']);  

		$token = $client->getAccessToken();

		$refreshToken = json_decode($token);
		$refreshToken = $refreshToken->refresh_token;

		$platID = self::PLATFORM_ID;

		$stmt = Database::prepareAssoc("INSERT INTO `CalendarTokens` (token, refreshToken, userID, platformID) VALUES (:token, :refreshToken, :userID, :platformID) ON DUPLICATE KEY UPDATE token=:token, refreshToken=:refreshToken");
		$stmt->bindParam(':token', $token);
		$stmt->bindParam(':refreshToken', $refreshToken);
		$stmt->bindParam(':userID', $userID);
		$stmt->bindParam(':platformID', $platID);
	    $stmt->execute();
	}


	public function getEvents($startTime, $endTime){
		$calendarList  = $this->calendarList->calendarList->listCalendarList();
		
		while(true){
		    foreach($calendarList->getItems() as $calendarListEntry){
			    /*
			     * problems:
			     * timezone would have to be dealt with here
			     */

			    //get all events
			    $events = $this->calendarList->events->listEvents($calendarListEntry->id, array('singleEvents' => 'true', 'timeMin' => $startTime, 'timeMax' => $endTime) );
			    foreach($events->getItems() as $event){
			    	echo $event->getSummary();
			    	echo "<br>";
			        echo $event->getStart()->dateTime;
			        echo "<br>";
			        echo $event->getEnd()->dateTime;
			        echo "<br>-------------<br><br>";
			    }
		    }

		    $pageToken = $calendarList->getNextPageToken();
		    if($pageToken) {
		        $optParams = array('pageToken' => $pageToken);
		        $calendarList = $this->calendarList->calendarList->listEvents($optParams);
		    } 
		    else{
		    	break;
		    }
		}
	  	
	}
	
}



    