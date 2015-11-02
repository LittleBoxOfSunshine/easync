<?php

// Base class for models, standardizes constructor interface, reduces likelyhood of errors, makes auto-inialization easier
abstract class Model{
	/**
		This generic constructor enforces behaviours that improve code readability and reduce the likelyhood
		of errors caused by dynamic typing mistmatches. It enforces a "c" style constructor that provides
		some of the functionally that would normally be achieved by overloading the constructor.
		
		The $args parameter contains an array of key => value pairs where the keys correspond to NULL properties
		of the child class and the values to initialize them to. Should a key be given that doesn't have a 
		corresponding property to store it in, code execution is halted. This prevents errorneous extra data from
		being added to an instance.
		
		If the $required parameter is provided, it is an array of strings that correspond to instance properities
		that MUST be initialized for the constructor to run correctly. If any of the required inputs are not found
		in $args code execution is halted. By default/if no $required parameter is provided, the constructor will
		require that ALL instance variables be instantiated. Multiple valid required patterns may be provided (this
		allows the CRUD interface load function to be defined more flexibly)
		
		The $fromUserInput parameter is an optional flag that signals the data contained in args comes from some
		form of user input, rather than class definitions. Consequently, it is possible and expected that the data
		may be malformed, but the calling code is equipped to handle that exception. So, the constructor returns
		false as an error message instead of halting code execution
	*/
	public function __construct(array & $args = array(), array & $required = array(), & $fromUserInput=false){
		// Initialize instance properties to match the key => value pairs of $args
		foreach($args as $key => $val){
			// Ensure the key is a valid instance property
			if(property_exists($this, $key))
				$this->{$key} = $val;
			// Key is not valid, delagate to malformedArgs()
			else
				return $this->malformedArgs($fromUserInput, "The key $key is not a valid property of class: " + get_class($this));
		}
		
		// Use the provided properties, if none are provided, load all instance properties
		$properties = count($required) == 0 ? get_object_vars($this) : $required;
		
		// Ensure that all required properties were successfully initialized
		foreach($properties as $prop)
			// Multiple valid required patterns exist
			if(is_array($prop)){
				foreach($prop as $subprop){
					// If the required property was not initialized, delagate to malformedArgs()
					if(!isset($this->$prop))
						return $this->malformedArgs($fromUserInput, "The required property $prop was not initialized for class: " + get_class($this));
				}
			}
			// Only one valid required pattern exists
			else{
				// If the required property was not initialized, delagate to malformedArgs()
				if(!isset($this->$prop))
					return $this->malformedArgs($fromUserInput, "The required property $prop was not initialized for class: " + get_class($this));
			}
	}
	
	// Function is called when constructor arguments are in
	private function malformedArgs(& $fromUserInput, & $errorMessage){
		// Calling function intends on handling the error 
		if($fromUserInput)
			return false;
		// No error handling signaled, halt code execution and display the given error message
		else
			die('ERROR: ' + $errorMessage + 'VAR_DUMP -> ' + var_export());
	}
	
}

// This interface is required to use the database object, distinguishes between actions 
// that involve just a memory object and those that involve the database
interface CRUD{
	
	// Uses instance properties to create a representation of the memory object in the database
	public abstract function create();
	
	// Uses instance properties to update the database representation of the object to match its current state
	public abstract function update();
	
	// Deletes the database representation of the memory object
	public abstract function delete();
	
	// Loads data from the database into instance properties using some minimum subset of instance properties
	// The different combinations of properties that can be used is implementation specific. Multiple valid
	// combinations are allowed
	public abstract function load();
}