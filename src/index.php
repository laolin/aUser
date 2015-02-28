<?php
if( isset($LAOLIN_CFG['PATH'] ))
  $CFG_PATH=$LAOLIN_CFG['PATH'];
else 
  $CFG_PATH='.';
  

if( file_exists($CFG_PATH . '/db.config.php' ))
  require ($CFG_PATH . '/db.config.php');
require_once 'medoo/class.laolindb.php';

require_once 'Toro/Toro.php';




srand();
$GLOBALS['db']=new laolinDb();
date_default_timezone_set('Asia/Hong_Kong');
$GLOBALS['time']=Date('Y-m-d H:i:s');


//111111111111111111111111111

function echoRestfulData($data,$jsonp='') {
  if( !headers_sent() ) {
    if(strlen($jsonp) == 0 && isset($_REQUEST['jsonp']) && strlen($_REQUEST['jsonp'])>0)
      $jsonp=$_REQUEST['jsonp'];
    if(strlen($jsonp)>0)
      header('Content-type: application/javascript; charset=utf-8');
    else header('Content-type: application/json; charset=utf-8');
    header("Expires: Thu, 01 Jan 1970 00:00:01 GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
  }
  
  if(strlen($jsonp)>0) {
    echo $jsonp.' ( ';
  }
  
  echo json_encode($data,JSON_PRETTY_PRINT);
  
  if(strlen($jsonp)>0) {
    echo ' ); ';
  }
}

ToroHook::add("404",  function() {
  $err=['error_code'=> 404,
    'error'=> 'Error API parameters.'];
  echoRestfulData($err);
});


require_once 'apis/api.hello.php';
require_once 'apis/api.books.php';


Toro::serve([
    
    "/books/([0-9]+)" => "books_Handler",
    "/books" => "books_Handler",
    
    "/hello" => "hello_Handler"
]);

