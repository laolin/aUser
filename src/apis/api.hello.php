<?php
//WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
/*
api: hello
method: get, post, put, delete
功能：就是测试用的，没有什么别的功能。
*/
class hello_Handler{
    function get() {
      $res=['Welcome'=> "Hello, world! 1",
        'My time'=>$GLOBALS['time']];
      $res['data_GET']=$_GET;
      $res['data_POST']=$_POST;      
      $res['data_INPUT']=$_GET;
      $res['info']='GET OK @'.  $GLOBALS['time'];
      echoRestfulData($res);
    }
    function post() {
      $res=['Welcome'=> "Hello, world! 2",
        'My time'=>$GLOBALS['time']];
      $res['data_GET']=$_GET;
      $res['data_POST']=$_POST;
      $res['data_INPUT']=$_POST;
      $res['info']='POST OK @'.  $GLOBALS['time'];
      echoRestfulData($res);
    }
    function put() {
      $res=['Welcome'=> "Hello, world! 3",
        'My time'=>$GLOBALS['time']];
      $res['data_GET']=$_GET;
      parse_str(file_get_contents('php://input'),$data);
      $res['data_INPUT']=$data;
      $res['info']='PUT OK @'.  $GLOBALS['time'];
      echoRestfulData($res);
    }
    function delete() {
      $res=['Welcome'=> "Hello, world! 4",
        'My time'=>$GLOBALS['time']];
      $res['data_GET']=$_GET;
      parse_str(file_get_contents('php://input'),$data);
      $res['data_INPUT']=$data;
      $res['info']='DELETE OK @'.  $GLOBALS['time'];
      echoRestfulData($res);
    }
}
