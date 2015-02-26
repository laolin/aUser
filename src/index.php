<?php

if( isset(LAOLIN_CFG['PATH'] ))
  CFG_PATH=LAOLIN_CFG['PATH'];
else 
  CFG_PATH='.';
if( file_exists(CFG_PATH '/db.config.php' ))
  require CFG_PATH '/db.config.php';
require_once 'medoo/class.laolindb.php';


srand();
$dt=Date('Y-m-d H:i:s');
$db=new laolinDb();



$db->insert("book", [
	"title" => "Hi61! $dt",
  'rating'=>rand()%10,
	"time" => rand(),
  'price'=>rand()%100
]);

$db->update("book",['price'=>rand()%10000/100,'rating'=>rand()%10],
  ['id[~]'=>"%".(rand()%10)]
);

// */

//$db->delete("book", ['time[<]'=>55555] );

echo "<h2>61 @ $dt </h2>";
echo '<pre>';

var_dump($db);
