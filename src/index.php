<?php
if( isset($LAOLIN_CFG['PATH'] ))
  $CFG_PATH=$LAOLIN_CFG['PATH'];
else 
  $CFG_PATH='.';
  

if( file_exists($CFG_PATH . '/db.config.php' ))
  require ($CFG_PATH . '/db.config.php');
require_once 'medoo/class.laolindb.php';

require_once 'Toro/Toro.php';


