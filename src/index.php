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
    if(strlen($jsonp)>0) header('Content-type: application/javascript; charset=utf-8');
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


//WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
class SplashHandler{
    function get() {
      $res=['Welcome'=> "Hello, world!",
        'My time'=>$GLOBALS['time']];
      $res['data_GET']=$_GET;
      $res['data_POST']=$_POST;      
      $res['data_INPUT']=$_GET;
      $res['info']='GET OK @'.  $GLOBALS['time'];
      echoRestfulData($res);
    }
    function post() {
      $res=['Welcome'=> "Hello, world!",
        'My time'=>$GLOBALS['time']];
      $res['data_GET']=$_GET;
      $res['data_POST']=$_POST;
      $res['data_INPUT']=$_POST;
      $res['info']='POST OK @'.  $GLOBALS['time'];
      echoRestfulData($res);
    }
    function put() {
      $res=['Welcome'=> "Hello, world!",
        'My time'=>$GLOBALS['time']];
      $res['data_GET']=$_GET;
      parse_str(file_get_contents('php://input'),$data);
      $res['data_INPUT']=$data;
      $res['info']='PUT OK @'.  $GLOBALS['time'];
      echoRestfulData($res);
    }
    function delete() {
      $res=['Welcome'=> "Hello, world!",
        'My time'=>$GLOBALS['time']];
      $res['data_GET']=$_GET;
      parse_str(file_get_contents('php://input'),$data);
      $res['data_INPUT']=$data;
      $res['info']='DELETE OK @'.  $GLOBALS['time'];
      echoRestfulData($res);
    }
}
class bookHandler {
    function get($name=0) {
      $res=[];
      $id=intVal($name);
      if($id==0&&isset($_GET['id']))$id=intVal($_GET['id']);
      if($id) { 
        $res['info']="You see the book #$id @".$GLOBALS['time'];
        $res['res']=$GLOBALS['db']->select("book",'*',['id'=>$id]);
      } else {
        $res['info']="You list books @".$GLOBALS['time'];
        
        $perpage=isset($_GET['perpage'])  &&
            intval($_GET['perpage'])>1 && intval($_GET['perpage'])<200 ?
          intval($_GET['perpage']) : 10;
        $page=isset($_GET['page']) && intval($_GET['page'])>0 ?
          (intval($_GET['page'])-1)*$perpage : 0;
        $where = [ "ORDER" => "id DESC" ];
        $where["LIMIT"] = [$page,$perpage];

        $res['res']=$GLOBALS['db']->select("book",'*',$where);
      }
      echoRestfulData($res);
    }
    
    function post($name) {
      $id=intVal($name);
      if($id) {
        $res['info']=$res['error']="ERROR: Can't POST with id(#$id)";
      } else {
        $error='';
        if(strlen($_POST['title'])<2)
          $error.=' Invalid title.';
        if(intval($_POST['rating'])<=0||intval($_POST['rating'])>10)
          $error.=' Valid rating:(0,10].';
        if(floatval($_POST['price'])<=0)
          $error.=' Invalid price.';
        
        $dat['title']=$_POST['title'];
        $dat['rating']=intval($_POST['rating']);
        $dat['price']=floatval($_POST['price']);
        $dat['time']=time();
        if($error)
          $res['error']=$error;
        else {
          $res['info']="You create a book";
          $rid=$GLOBALS['db']->insert("book",$dat);
          $res['res']=$GLOBALS['db']->get("book",'*',['id'=>$rid]);
        }
      }
      echoRestfulData($res);
    }
    
    
    function abc(){
    
      $db->insert("book", [
        "title" => "$dt",
        'rating'=>rand()%10,
        "time" => rand(),
        'price'=>rand()%100
      ]);

      $db->update("book",['price'=>rand()%10000/100,'rating'=>rand()%10],
        ['id[~]'=>"%".(rand()%10)]
      );

      // */

      //$db->delete("book", ['time[<]'=>55555] );

    }
}

Toro::serve([
    
    "/books/([0-9]+)" => "bookHandler",
    "/books" => "bookHandler",
    
    "/hello" => "SplashHandler"
]);

