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

$dt="B3," . rand() . ' - ' . Date('Y-m-d H:i:s');
echo "<h2> $dt </h2><pre>";


//WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
class SplashHandler{
    function get() {
        echo "Hello, world";
    }
}
class ProductHandler {
    function get($name) {
      echo "You want to see product: $name";
      if(!$name)$name='%';
      $res=$GLOBALS['db']->select("book",'*',['id[~]'=>$name]);
      var_dump($res);
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
    "/hello" => "SplashHandler",
    "/list/([a-zA-Z0-9-_]+)" => "ProductHandler",
    "/list" => "ProductHandler"
]);

//WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW


