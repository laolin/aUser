<?php
require_once 'catUsers.class.php';

//BBBBBBBBBBBBBBBBBBBBBBBBBBBBB
/*
api: books
api: books/id // :number
  【注：所有API可用的参数】
  jsonp: 可选，有效的可执行函数名，可用于跨域名调用等。
    例如:jsonp=alert, jsonp=console.log, jsonp=some_valid_func 。
    指定了jsonp以后，返回的header中 Content-type: application/javascript;
    其他情况返回header中 Content-type: application/json;
    
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
  【功能】 update
  无id时，非法请求，
  有id时：更新一个数据。
  
method: delete
  【功能】 delete
  无id时，非法请求，
  有id时：删除一个数据。
  
  【参数】
  id: 可选，当api URI中未指定id时，可在此指定id，有效值>=1。

*/
class books_Handler {
    function get($id=0) {
      $res=[];
      $id=intVal($id);
      if($id==0&&isset($_GET['id']))$id=intVal($_GET['id']);
      if($id) { 
        $res['info']="Get the book #$id @".$GLOBALS['time'];
        $res['res']=$GLOBALS['db']->select("book",'*',['id'=>$id]);
      } else {
        $res['info']="Get books @".$GLOBALS['time'];
        
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
      $uc= new catUsers();
      $ret=e(0,'ok.');
      $id=intVal($id);
      if($id) {
        $ret=e(1001,"ERROR: Can't POST with id(#$id)");
      } else {
        $va=$this->dataValidate($_POST);
        
        if($va['errorinfo'])
          $ret=e(1002,$va['errorinfo']);
        else {
          
          //action_finger与客户端算法要一致。防止数据被窃取并篡改后再恶意利用
          $action_finger= md5('books.create' . $va['data']['title']. $va['data']['rating']. $va['data']['price']);
          
          $ret=$uc->cat( $action_finger );
          if($ret['err_code']==0) {
            $ret=e(0,"Create a book");
      
            $rid=$GLOBALS['db']->insert("book",$va['data']);
            $ret['res']=$GLOBALS['db']->get("book",'*',['id'=>$rid]);
          }
        }
      }
      echoRestfulData($ret);
    }
    
    
    function put($id=0) {
      $uc= new catUsers();
      $ret=e(0,'ok.');
      $id=intVal($id);
      
      parse_str(file_get_contents('php://input'),$input);
      
      if($id==0&&isset($input['id']))$id=intVal($input['id']);
      
      if($id) {  
        
        $va=$this->dataValidate($input);
        if($va['count']<=1) { //没有有效数据，只有1个time是系统自动的
          $ret=e(2001,"ERROR: no validate data (id=$id)");
        } else {
          //action_finger与客户端算法要一致。防止数据被窃取并篡改后再恶意利用
          $action_finger= md5('books.update' . $id . $va['data']['title']. $va['data']['rating']. $va['data']['price']);
          $_REQUEST=$input;
          $ret=$uc->cat( $action_finger );
          if($ret['err_code']==0) {
            $ret=e(0,"Update the book #$id @".$GLOBALS['time']);
            $to_upd=$GLOBALS['db']->get("book",'*',['id'=>$id]);
            if($to_upd) {
              $ret['old']=$to_upd;
              $GLOBALS['db']->update("book",$va['data'],['id'=>$id]);
            } else {
              $ret['res']="Nothing updated (id=$id)";
            }
          }
        }
      } else {
        $ret=e(2002,"ERROR: Invalid id to be updated(#$id)");
      }
      echoRestfulData($ret);
    }
    
    function delete($id=0) {
      $uc= new catUsers();
      $ret=e(0,'ok.');
      $id=intVal($id);
      
      parse_str(file_get_contents('php://input'),$input);
      
      if($id==0&&isset($input['id']))$id=intVal($input['id']);
      if($id) {           
        //action_finger与客户端算法要一致。防止数据被窃取并篡改后再恶意利用
        $action_finger= md5('books.delete' . $id );
        $_REQUEST=$input;
        $ret=$uc->cat( $action_finger );
        if($ret['err_code']==0) {
          $ret=e(0,"Delete the book #$id");
          $to_del=$GLOBALS['db']->get("book",'*',['id'=>$id]);
          if($to_del) {
            $ret['deleted']=$to_del;
            $GLOBALS['db']->delete("book",['id'=>$id]);
          } else {
            $ret=e(3001,'Nothing deleted.');
          }
        }
      } else {
        $ret=e(3002,"ERROR: Invalid id to be deleted(#$id)");
      }
      echoRestfulData($ret);
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
}

