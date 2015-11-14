<?php
    
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');    
    
// Import Dependencies
require __DIR__ . '/../vendor/autoload.php';

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
$routers = rglob(__DIR__.'/routers/*');
foreach($routers as $router){
    if(!is_dir($router))
        require_once $router;		
}

/*
    Import models
*/

// Automatically load model files
$routers = rglob(__DIR__.'/models/*');
foreach($routers as $router){
    if(!is_dir($router))
        require_once $router;		
}

// Define index route
$app->get('/', function () use ($app){
	echo file_get_contents(__DIR__.'/../public/index.html');
});//)->add($MIDDLEWARE_AUTH);
        
// Start the application
$app->run();