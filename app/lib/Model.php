<?php

require_once __DIR__.'/Binding.php';
require_once __DIR__.'/CRUD.php';

// Base class for models, standardizes constructor interface, reduces likelyhood of errors, makes auto-inialization easier
abstract class Model{
	
	private static $binding;
	
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
	public function __construct(array & $args = array(), array $required = array(), & $fromUserInput=false){
		// Initialize instance properties to match the key => value pairs of $args
		foreach($args as $key => $val){
			// Ensure the key is a valid instance property
			if(property_exists($this, $key))
				$this->$key = $val;//$this->__set($key, $val); // use magic method __set so that private properties in child may be set
			// Key is not valid, delagate to malformedArgs()
			else
				return $this->malformedArgs("The key $key is not a valid property of class: " + get_class($this), $fromUserInput);
		}
		
		// Use the provided properties, if none are provided, load all instance properties
		$properties = count($required) == 0 ? get_object_vars($this) : $required;
		
		// Flags for testing arrays
		$hasValidArray = false;
		$hasArrays = false;
		
		// Ensure that all required properties were successfully initialized
		foreach($properties as $prop){
			// Multiple valid required patterns exist
			if(is_array($prop) && $hasValidArray === false){// hasValidArray check is an optimization to unecessary parsing (consider adding && count($prop) <= count($args) as optimization)
				$hasArrays = true;
				
				// Flag for if current array is valid (assume yes, loop will fix this if it is untrue)
				$tmpValidArray = true;
				
				foreach($prop as $subprop){
					// If the required property was not initialized, delagate to malformedArgs()
					if(!isset($this->$subprop)){
						$tmpValidArray = false;
						break; // Data is invalid, no reason to continue checking	
					}
				}
				
				// Check if array requirements were met
				if($tmpValidArray === true)   
					$hasValidArray = true;
			}
			// Only one valid required pattern exists
			else{
				// If the required property was not initialized, delagate to malformedArgs()
				if(!isset($this->$prop))
					return $this->malformedArgs("The required property $prop was not initialized for class: " + get_class($this), $fromUserInput);
			}
		}
		
		// Delagate error to malformedArgs() if arrays were used and none of them were valid
		if($hasArrays === true && $hasValidArray === false)
			$this->malformedArgs('The required properties were not initialized for class: ' + get_class($this) + 'Requirements are:' + var_export($required), $fromUserInput);
	}
	
	/** 
		Provides a FETCH_INTO interface with access to private properties + error checking. Expects associative array where
		keys correspond to properties defined in the child class
	*/
	protected function loadInto(array & $data = array()){
		foreach($data as $key => $val)
			if(property_exists($key, $this))
				$this->__set($key, $val);
	}
	
	/**
		Provides a FETCH_INTO interface with access to private properties + error checking, but creates arrays instead of 
		assigning scalar values. Expects an array of associative arrays where keys coreespond to properties defined in the 
		child class
	*/
	protected function loadAllInto(array & $data = array(), array & $properties = array()){
		// Temporary arrays that child properties will be assigned with
		$temp = [];
		
		// Ensure the number of sql columns matches the number of properties
		if(count($properties) != count($data[0]))
			malformedArgs('property count must match column count for loadAllInto');
		
		// Validate the array keys have corresponding child properties
		foreach($properties as $key){
			if(property_exists($key, $this))
				$temp[$key] = [];
			else
				malformedArgs("property => $key does not exist" + get_class($this));
		}
		
		// Load into the arrays
		foreach($data as $row)
			for($i = 0; $i < count($row); $i++)
				$temp[$properties[$i]][] = $val;
				
		// Write the temporary arrays to the child
		foreach($tmp as $key => $val)
			$this->__set($key, $val);	
	}
	
	// Function is called when constructor arguments are in
	private function malformedArgs($errorMessage, $fromUserInput=false){
		// Calling function intends on handling the error 
		if($fromUserInput === true)
			return false;
		// No error handling signaled, halt code execution and display the given error message
		else
			die('ERROR: ' + $errorMessage + ' VAR_DUMP -> ' + var_export($this));
	}
	
	//
	protected static function initBinding(array $bindingArgs = array()){
		var_dump($bindingArgs);
		echo '</br></br>';
		if(!isset(self::$binding)){
			self::$binding = new Binding($bindingArgs);
		}
	}
	
	protected static function getBinding(){
		return self::$binding;
	}
	
}

