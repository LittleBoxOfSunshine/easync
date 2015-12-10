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
		if($this->client->isAccessTokenExpired()) {
			$this->client->refreshToken();
			//save the token!
			$stmt = Database::prepareAssoc("UPDATE `token` SET `CalendarTokens`='$refresh' WHERE userID=:userID AND platformID=:platformID");
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
	    	$client->setAccessToken($calToken);
	   		//$client->refreshToken($calToken->refresh_token);
	    }

	    return $client;
	}

	private static function loadTokens($USER_ID){
		$platID = self::PLATFORM_ID;

	    $stmt = Database::prepareAssoc("SELECT `token` FROM `CalendarTokens` WHERE userID=:userID AND platformID=:platformID");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->bindParam(':platformID', $platID );
		$stmt->execute();
		$allTokens = $stmt->fetch();

		if( $allTokens !== false )
			return $allTokens['token'];
		else
			return false;
	}

	public static function requestAccess($app, $USER_ID){
		//$app->response->headers->set('Content-Type', 'application/javascript');
		$authUrl = self::makeGoogleClient($USER_ID)->createAuthUrl();
 		$app->redirect($authUrl);
	}
	
	public static function acceptAccess($userID){
		$client = self::makeGoogleClient($userID);
		$client->authenticate($_GET['code']);  

		$token = $client->getAccessToken();
		$platID = self::PLATFORM_ID;

		$stmt = Database::prepareAssoc("INSERT INTO `CalendarTokens` (token, userID, platformID) VALUES (:token, :userID, :platformID) ON DUPLICATE KEY UPDATE token=:token");
		$stmt->bindParam(':token', $token);
		$stmt->bindParam(':userID', $userID);
		$stmt->bindParam(':platformID', $platID);
		$stmt->execute();
	}


	public function getEvents(){
		    $calendarList  = $this->calendarList->calendarList->listCalendarList();
				  while(true) {
				      foreach ($calendarList->getItems() as $calendarListEntry) {
				        echo $calendarListEntry->getSummary()."<br>\n";
				        // get events 
				        $events = $this->calendarList->events->listEvents($calendarListEntry->id);
				        foreach ($events->getItems() as $event) {
				            echo "-----".$event->getSummary()."<br>";
				        }
				      }
				      $pageToken = $calendarList->getNextPageToken();
				      if ($pageToken) {
				        $optParams = array('pageToken' => $pageToken);
				        $calendarList = $this->calendarList->calendarList->listCalendarList($optParams);
				      } else {
				        break;
				      }
				  }
	  	
	}
	
}



    