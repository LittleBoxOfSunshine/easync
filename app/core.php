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
    'expires' => '30 days',
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'httponly' => false, 
    'name' => 'slim_session',
    'secret' => 'F3613642856D7E27445E9DBAD0402AD4534ED000C203FC564082652CB5CF7034',
    'cipher' => MCRYPT_RIJNDAEL_256,
    'cipher_mode' => MCRYPT_MODE_CBC
)));

require_once __DIR__.'/lib/Database.php';

$AUTH_MIDDLEWARE = function () use ($app){
    
    global $USER_ID;
    
    return function () use ($app) {
        global $USER_ID;
        // This only needs to run once
        if($USER_ID != NULL)
            return;

        $stmt = Database::prepareAssoc("SELECT userID FROM `Auth_Token` WHERE `auth_token`=:authToken");
        
        $error403 = function () use ($app){
            $app->halt(403, 'ERROR: You must be authenticated to use this api route...');
        };
        
        if(!isset($_SESSION['auth_token']))
            $error403();
        
        $stmt->bindParam(':authToken', $_SESSION['auth_token']);
        $stmt->execute();
            
        if($row = $stmt->fetch()){
            // Define userID global
            $USER_ID = $row['userID'];
        }
        else{
            $error403();
        }
    };
    
};

/*
// Define Authentication Middleware
class Authentication extends SlimMiddleware{
    protected $stmt;
    
    public function __construct(){
        $this->stmt = Database::prepareAssoc("SELECT uid FROM `Auth_Token` WHERE `auth_token`=:authToken");
    }
    
    public function call(){
        if(!isset($_SESSION['auth_Token']))
            $this->error403();
        
        $this->stmt->bindParam(':authToken', $_SESSION['auth_token']);
        $this->stmt->execute();
        
        $row = $this->stmt->fetch();
        
        if(count($row) == 0){
            $this->error403();
        }
        else{
            // Define userID global
            global $USER_ID;
            $USER_ID = $row['uid'];
            $this->next->call();
        }
    }
    
    private function error403(){
            $this->app->response->setStatus(403);
            echo 'ERROR: You must be authenticated to use this api route...';
    }
}
*/

/*
    Import lib
*/
$routers = rglob(__DIR__.'/lib/*');
foreach($routers as $router){
    if(!is_dir($router))
        require_once $router;		
}
    
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
	echo file_get_contents(__DIR__.'/../public/app/index.html');
});

$app->response->headers->set('Access-Control-Allow-Origin', 'http://localhost:9000');
$app->response->headers->set('Access-Control-Allow-Credentials', 'true');

        
// Start the application
$app->run();
