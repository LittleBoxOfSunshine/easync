<?php
    
// Import Dependencies
require __DIR__ . '/vendor/autoload.php';

// Performs glob recursively
function rglob($pattern, &$flags = 0) {
	// Run the initial glob
	$files = glob($pattern, $flags);
		
	// Append each file result, recursively call rglob with each directory result  
	foreach(glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
		$files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
	}
		
	// Return the complete list of files
	return $files;
}

$app = new \Slim\Slim(array(
    'mode' => self::MODE_DEV,
    'debug' => true
));

/*
    Add App Wide Middleware
*/

// Enable client side sessions
$app->add(new \Slim\Middleware\SessionCookie(array(
    'expires' => '20 minutes',
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'httponly' => false,
    'name' => 'slim_session',
    'secret' => 'F3613642856D7E27445E9DBAD0402AD4534ED000C203FC564082652CB5CF7034',
    'cipher' => MCRYPT_RIJNDAEL_256,
    'cipher_mode' => MCRYPT_MODE_CBC
)));

/*
    Define Route Specific Middleware
*/

    
/*
    Define routes
*/

// Automatically load router files
$routers = rglob('/routers/*');
foreach($routers as $router){
    require $router;		
}

/*
    Import models
*/

// Automatically load model files
$routers = rglob('/models/*');
foreach($routers as $router){
    require $router;		
}

// Automatically load the model files
        
// Start the application
$app()->run();