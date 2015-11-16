<?php

require_once(__DIR__.'/../lib/Model.php');
require __DIR__ . '/../../vendor/autoload.php';

class Google_Calendar extends Model implements CRUD{
private $buttonCreated;
public function __construct(){$this->buttonCreated = false;}
public function getButton(){return $this->buttonCreated;}
	public function testCal(){
    echo "infunc";
		 // ********************************************************  //
	    // Get these values from https://console.developers.google.com
	    // Be sure to enable the Analytics API
	    // ********************************************************    //
	    $client_id = '66468154963-kjvu0u6hohvv59l03d0gr8kcomi4pggd.apps.googleusercontent.com';
	    $client_secret = 'oNwvkqFISBzSjxNeWtZcnrAo';
	    $redirect_uri = 'http://easync.com/';

	    $client = new Google_Client();
	    $client->setApplicationName("Client_Library_Examples");
	    $client->setClientId($client_id);
	    $client->setClientSecret($client_secret);
	    $client->setRedirectUri($redirect_uri);
	    $client->setAccessType('offline');   // Gets us our refreshtoken

	    $client->setScopes(array('https://www.googleapis.com/auth/calendar'));


	    //For loging out.
	    if (isset($_GET['logout'])) {
	      unset($_SESSION['token']);
	    }


	    // Step 2: The user accepted your access now you need to exchange it.
	    if (isset($_GET['code'])) {
	      $client->authenticate($_GET['code']);  
	      $_SESSION['token'] = $client->getAccessToken();
	      $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	      header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	    }

	    // Step 1:  The user has not authenticated we give them a link to login    
	    if (!isset($_SESSION['token'])) {
	      $authUrl = $client->createAuthUrl();

	      print "<a class='login' href='$authUrl'>Connect</a>";
        $this->buttonCreated = true;
	    }    


	 
	}

  public function test2(){
    echo "intest2";
     // Step 3: We have access we can now create our service
      if (isset($_SESSION['token'])) {
      $client->setAccessToken($_SESSION['token']);
      print "<a class='logout' href='http://www.daimto.com/Tutorials/PHP/GCOAuth.php?logout=1'>LogOut</a><br>"; 
      
      $service = new Google_Service_Calendar($client);    
      
      $calendarList  = $service->calendarList->listCalendarList();

      while(true) {
          foreach ($calendarList->getItems() as $calendarListEntry) {

            echo $calendarListEntry->getSummary()."<br>\n";


            // get events 
            $events = $service->events->listEvents($calendarListEntry->id);


            foreach ($events->getItems() as $event) {
                echo "-----".$event->getSummary()."<br>";
            }
          }
          $pageToken = $calendarList->getNextPageToken();
          if ($pageToken) {
            $optParams = array('pageToken' => $pageToken);
            $calendarList = $service->calendarList->listCalendarList($optParams);
          } else {
            break;
          }
      }
    } 
  }


	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */

	public function getClient() {
	  $client = new Google_Client();
	  $client->setApplicationName(APPLICATION_NAME);
	  $client->setScopes(SCOPES);
	  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
	  $client->setAccessType('offline');
	
	  // Load previously authorized credentials from a file.
	  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
	  if (file_exists($credentialsPath)) {
	    $accessToken = file_get_contents($credentialsPath);
	  } else {
	    // Request authorization from the user.
	    $authUrl = $client->createAuthUrl();
	    printf("Open the following link in your browser:\n%s\n", $authUrl);
	    print 'Enter verification code: ';
	    $authCode = trim(fgets(STDIN));
	
	    // Exchange authorization code for an access token.
	    $accessToken = $client->authenticate($authCode);
	
	    // Store the credentials to disk.
	    if(!file_exists(dirname($credentialsPath))) {
	      mkdir(dirname($credentialsPath), 0700, true);
	    }
	    file_put_contents($credentialsPath, $accessToken);
	    printf("Credentials saved to %s\n", $credentialsPath);
	  }
	  $client->setAccessToken($accessToken);
	
	  // Refresh the token if it's expired.
	  if ($client->isAccessTokenExpired()) {
	    $client->refreshToken($client->getRefreshToken());
	    file_put_contents($credentialsPath, $client->getAccessToken());
	  }
	  return $client;
	}
	
	/**
	 * Expands the home directory alias '~' to the full path.
	 * @param string $path the path to expand.
	 * @return string the expanded path.
	 */
	
	public function expandHomeDirectory($path) {
	  $homeDirectory = getenv('HOME');
	  if (empty($homeDirectory)) {
	    $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
	  }
	  return str_replace('~', realpath($homeDirectory), $path);
	}
	
	/*
	 * TO DO !
	 */
	

	public function create(){
		// Prepare sql statement
		$stmt = Database::prepareAssoc("INSERT INTO EventDetails VALUES(:location, :startTime, :creationTime, :updateTime, :endTime, :name,: description, :creatorUserID, :timeZone, :recurrence, :attachments);", self::$binding);
		
		// Run the initial query and store the autoincremented event id
		$stmt->execute();
		$this->eventID = Database::lastInsertId();
		
		$stmt = Database::prepareAssoc("INSERT INTO Event VALUES(:eventID, :creatorUserID);", self::$binding);
  
  		// Run the secondary queries
  		$stmt->execute();
	}
	

	public function update(){
		Database::prepareAssoc("UPDATE EventDetails SET creatorUserID=:creatorUserID, location=:location,
			startTime=:startTime, creationTime=:creationTime, updateTime=:updateTime, 
			endTime=:endTime, name=:name, description:=description, timeZone:=timeZone, recurrence:=recurrence, attachments:=attachments
			WHERE eventID=:eventID", 
			self::$bindings)->execute();
	}
	
	public function delete(){
		// Remove any data linking back to the event id then remove the group itself (from EventDetails)
		Database::prepareAssoc("DELETE FROM EventDetails WHERE eventID=:eventID;", self::$bindings)->execute();
	}

	public function load(){
		// Load event details
		$stmt = Database::prepareAssoc("SELECT * FROM EventDetails WHERE eventID=:eventID", self::$bindings);
		$stmt->execute();
		$this->loadInto($stmt->fetch());

		// Load event user
		$stmt = Database::prepareAssoc("SELECT `userID` FROM Event WHERE eventID=:eventID", self::$bindings);
		$stmt->execute();
		$this->loadInto($stmt->fetch());
	}
	
}


/*
// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);

// Print the next 10 events on the user's calendar.
$calendarId = 'primary';
$optParams = array(
  'maxResults' => 10,
  'orderBy' => 'startTime',
  'singleEvents' => TRUE,
  'timeMin' => date('c'),
);
$results = $service->events->listEvents($calendarId, $optParams);

if (count($results->getItems()) == 0) {
  print "No upcoming events found.\n";
} else {
  print "Upcoming events:\n";
  foreach ($results->getItems() as $event) {
    $start = $event->start->dateTime;
    if (empty($start)) {
      $start = $event->start->date;
    }
    printf("%s (%s)\n", $event->getSummary(), $start);
  }
}
	
*/

    