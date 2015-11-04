<?php

class Group extends Model implements CRUD{
	protected $creationTime;
	protected $name;
	protected $description;
	protected $creatorUserID;
  	protected $logo;
  	protected $groupID;
	protected $userIDs;
	protected $permissions;  
	  
	private static $binding;

	public function __construct(array $args = array()){
		parent::__construct($args, array(
			array('groupID'),
			array('name','creatorID')
			));
			
		// Initialize MySQL bindings
		if(!isset(self::$binding))
			self::$binding = Binding(array(
				':creatorID' => $this->creatorID,
				':name' => $this->name,
				':description' => $this->description,
				':creationTime' => $this->creationTime,
				':logo' => $this->logo
			));
			
		// Initialize Permission Arrays
		$this->permissions = array(
			Permission::OWNER => array(),
			Permission::ADMIN => array(),
			Permission::MODERATOR => array()	
		);
	}

	public function getCreationTime(){
		return $this->creationTime;
	}

	public function setCreationTime($creationTime){
		$this->creationTime = $creationTime;
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

	public function getLogo(){
		return $this->logo;
	}

	public function setLogo($logo){
		$this->logo = $logo;
	}

	public function getGroupID(){
		return $this->groupID;
	}

	public function setGroupID($groupID){
		$this->groupID = $groupID;
	}

	public function getUserIDs(){
		return $this->userIDs;
	}

	public function setUserIDs(array $userIDs = array()){
		$this->userIDs = $userIDs;
	}
	
	/**
		TODO: These functions aren't performing any level of error checking. Add at later date
	*/
	
	public function create(){
		// Prepare sql statement
		$stmt = Database::prepareAssoc("INSERT INTO GroupDetails VALUES(:creatorID, :name, :description, :creationTime, :logo);", self::$binding);
		
		// Run the initial query and store the autoincremented group id
		$stmt->execute();
		$this->groupID = Database::lastInsertId();
		
		$stmt = Database::prepareAssoc("INSERT INTO Group VALUES(:groupID, :creatorID);
			INSERT INTO Permissions VALUES(:groupID, :creatorID, '".Permission::OWNER."');", self::$binding);
  
  		// Run the secondary queries
  		$stmt->execute();
	}
	
	public function update(){
		Database::prepareAssoc("UPDATE GroupDetails SET creatorID=:creatorID, name=:name,
			description=:description, creationTime=:creationTime, logo=:logo WHERE groupID=:groupID", 
			self::$bindings)->execute();
	}
	
	public function delete(){
		// Remove any data linking back to the group id then remove the group itself (from GroupDetails)
		Database::prepareAssoc("DELETE FROM Permissions WHERE groupID=:groupID;
			DELETE FROM EventGroups WHERE groupID=:groupID;
			DELETE FROM Group WHERE groupID=:groupID;
			DELETE FROM GroupDetails WHERE groupID=:groupID;", self::$bindings)->execute();
	}

	public function load(){
		// Load group details
		$stmt = Database::prepareAssoc("SELECT * FROM GroupDetails WHERE groupID=:groupID;", self::$bindings);
		$stmt->execute();
		$this->loadInto($stmt->fetch());
		
		// Load group members
		$stmt = Database::prepareAssoc("SELECT `userID` FROM Group WHERE groupID=:groupID", self::$bindings);
		$stmt->execute();
		$this->loadAllInto($stmt->fetchAll(), array('userIDs'));
		
		// Load group member permissions
		$stmt = Database::prepareAssoc("SELECT `userID`, `permission` FROM Permissions WHERE groupID=:groupID", self::$bindings);
		$stmt->execute();
		$data = $stmt->fetchAll();
		foreach($data as $row)
			$this->permissions[$row['permission']][] = $row['userID'];
		
		// Load the group's events
		$stmt = Database::prepareAssoc("SELECT `eventID` FROM EventGroups WHERE groupID=:groupID", self::$bindings);
		$stmt->execute();
		$this->loadAllInto($stmt->fetchAll(), array('eventIDs'));
		
	}
}

class Permission{
	
	const OWNER = 'OWNER';
	const ADMIN = 'ADMIN';
	const MODERATOR = 'MODERATOR';
	
}
