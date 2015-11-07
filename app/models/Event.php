<?php
require_once(__DIR__.'/../lib/Model.php');

class Event extends Model implements CRUD{
	protected $location;
	protected $startTime;     
	protected $creationTime;  
	protected $updateTime;    
	protected $endTime;       
	protected $name;          
	protected $description;   
	protected $creatorUserID; 
	protected $timeZone;      
	protected $recurrence;    
	protected $attachments;   
	protected $eventID;  

	public function __construct(array $args = array()){
		parent::__construct(
			$args,
			array(
				'eventID',
				'userID',
				'name',
				'creatorUserID',
				'timeZone',
				'startTime',
				'creationtime',
				'endTime'
			));
	}

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
		Database::prepareAssoc("DELETE FROM EventDetails WHERE eventID=:eventID;
			DELETE FROM Event WHERE eventID=:eventID;
			DELETE FROM EventDetails WHERE eventID=:eventID;", self::$bindings)->execute();
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

	public function getLocation(){
		return $this->location;
	}

	public function setLocation($location){
		$this->location = $location;
	}

	public function getStartTime(){
		return $this->startTime;
	}

	public function setStartTime($startTime){
		$this->startTime = $startTime;
	}

	public function getCreationTime(){
		return $this->creationTime;
	}

	public function setCreationTime($creationTime){
		$this->creationTime = $creationTime;
	}

	public function getUpdateTime(){
		return $this->updateTime;
	}

	public function setUpdateTime($updateTime){
		$this->updateTime = $updateTime;
	}

	public function getEndTime(){
		return $this->endTime;
	}

	public function setEndTime($endTime){
		$this->endTime = $endTime;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getCreatorUserID(){
		return $this->creatorUserID;
	}

	public function setCreatorUserID($creatorUserID){
		$this->creatorUserID = $creatorUserID;
	}

	public function getTimeZone(){
		return $this->timeZone;
	}

	public function setTimeZone($timeZone){
		$this->timeZone = $timeZone;
	}

	public function getRecurrence(){
		return $this->recurrence;
	}

	public function setRecurrence($recurrence){
		$this->recurrence = $recurrence;
	}

	public function getAttachments(){
		return $this->attachments;
	}

	public function setAttachments($attachments){
		$this->attachments = $attachments;
	}

	public function getEventID(){
		return $this->eventID;
	}

	public function setEventID($eventID){
		$this->eventID = $eventID;
	}
}