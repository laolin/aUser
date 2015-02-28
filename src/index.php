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
/*
api: hello
method: get, post, put, delete
功能：就是测试用的，没有什么别的功能。
*/
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
//BBBBBBBBBBBBBBBBBBBBBBBBBBBBB
/*
api: books
api: books/id // :number

method: get
  【功能】 列表
  无id时，列出一页。
  有id时，列出一个数据。
  
  【参数】
  id: 可选，当api URI中未指定id时，可在此指定id，有效值>=1。
  page: 可选，有效值>=1，默认1
  perpage: 可选，有效值[2,200] 默认10
  
  【返回值】
  data['res']是查询到的数据内容。
  
  【说明】
  默认倒排序，所以第一页显示的是最后几个数据。
  
  【示例】
  GET /books            //列出第一页
  GET /books/8          //列出id=8的
  GET /books?id=8       //列出id=8的
  
  GET /books?page=3     //列出第3页(第21~30个数据)
  GET /books?page=3&perpage=100     //列出第3页(第201~300个数据)
  
  
method: post
  【功能】 create
  无id时，创建一个数据。
  有id时：非法请求。
  
  【参数】
  title: 必选，字符串。长度应大于2
  rating: 必选，整数。有效范围：(0,10]
  price:  必选，浮点数。 有效范围：(0,无穷大)
  
  【返回值】
  data['res']是创建的数据内容。
  
  
method: put
  【功能】 updata
  （未完成）
  
method: delete
  【功能】 delete
  无id时，非法请求，
  有id时：删除一个数据。
  
  【参数】
  id: 可选，当api URI中未指定id时，可在此指定id，有效值>=1。

*/
class bookHandler {
    function get($id=0) {
      $res=[];
      $id=intVal($id);
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
    
    function post($id=0) {
      $res=[];
      $id=intVal($id);
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
    
    
    function put($id=0) {
      $res=[];
      $id=intVal($id);
      
      parse_str(file_get_contents('php://input'),$input);
      
      if($id==0&&isset($input['id']))$id=intVal($input['id']);
      
      if($id) {  
        
        $va=$this->dataValidate($input);
        if($va['count']<=1) { //没有有效数据，只有1个time是系统自动的
          $res['info']=$res['error']="ERROR: no validate data (id=$id)";
        } else {
          $res['info']="You want to update the book #$id @".$GLOBALS['time'];
          $to_upd=$GLOBALS['db']->get("book",'*',['id'=>$id]);
          if($to_upd) {
            $res['res']=$to_upd;
            $GLOBALS['db']->update("book",$va['data'],['id'=>$id]);
          } else {
            $res['res']="Nothing updated (id=$id)";
          }
        }
      } else {
        $res['info']=$res['error']="ERROR: Invalid id to be deleted(#$id)";
      }
      echoRestfulData($res);
    }
    
    function delete($id=0) {
      $res=[];
      $id=intVal($id);
      
      parse_str(file_get_contents('php://input'),$input);
      
      if($id==0&&isset($input['id']))$id=intVal($input['id']);
      if($id) { 
        $res['info']="You want to delete the book #$id @".$GLOBALS['time'];
        
        $to_del=$GLOBALS['db']->get("book",'*',['id'=>$id]);
        if($to_del) {
          $res['res']=$to_del;
          $GLOBALS['db']->delete("book",['id'=>$id]);
        } else {
          $res['res']='Nothing deleted.';
        }
      } else {
        $res['info']=$res['error']="ERROR: Invalid id to be deleted(#$id)";
      }
      echoRestfulData($res);
    }
    
    //功能：检查输入的数据，并返回能用于数据库操作的数据
    //参数:$in 输入的数据
    //返回：
    //  $ret['errorinfo']：数据检查出错信息，空字符串表示没有出错。
    //  $ret['count']：可用的数据数量
    //  $ret['data']：可用的数据
    function dataValidate($in){
      $dat=[];
      $error='';
      $ok=0;
      if(strlen($in['title'])<2)
        $error.=' Invalid title.';
      else {
        $ok++;
        $dat['title']=$in['title'];
      }
      if(intval($in['rating'])<=0||intval($in['rating'])>10)
        $error.=' Valid rating:(0,10].';
      else {
        $ok++;
        $dat['rating']=intval($in['rating']);
      }
      if(floatval($in['price'])<=0)
        $error.=' Invalid price.';
      else {
        $ok++;
        $dat['price']=floatval($in['price']);
      }
      $ok++;
      $dat['time']=time();
      return [ 'errorinfo'=> $error, 'count'=>$ok,'data'=>$dat ];
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

