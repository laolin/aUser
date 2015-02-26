<?php

require './db.config.php';
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
