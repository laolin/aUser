<?php
require_once 'catUsers.class.php';

/**
 * 用户表，用来验证用户的
 *
 * @api: users/(:action) 
 * 
 * 参数action: login, auth, regist, password
 * 
 *   【注：所有API可用的参数】
 *   jsonp: 可选，有效的可执行函数名，可用于跨域名调用等。
 *     例如:jsonp=alert, jsonp=console.log, jsonp=some_valid_func 。
 *     指定了jsonp以后，返回的header中 Content-type: application/javascript;
 *     其他情况返回header中 Content-type: application/json;
 *     
 * @method: get
 * 可用action参数: login, auth
 *   【功能】 用户帐号密码验证，操作验证。
 * 
 * @method: post
 * 可用action参数: regist,  password
 *   【功能】 create 或update。创建一个用户，修改密码。
 *   
 * @method: put
 *   【功能】 无（请统一使用post或get方式）
 *   
 * @ method: delete
 *   【功能】 无（不提供删除功能）
 *
 * 【其他参数】
 * 注：参数action要放在api地址中。
 * user: 用户名
 * otoken: 原先的pass_token，仅在修改密码时会接收到。
 * ptoken: 新的pass_token，在新用户注册或修改密码时会接收到。
 *
 * atoken: action_token，验证action用的
 * finger: 本次提交的操作和数据中提取的“动作指纹”
 * time: 时间
 * 
 */
class users_Handler {
    
    function get($action=0) {
      $ret=e(1001,'Unknow action (get).');
      $uc= new catUsers();
      
      $uname=v('user');
      $atoken=v('atoken');
      $finger=v('finger');
      $time=v('time');
      switch($action) {
        case 'login':
          $ret=$uc->cat($uname,$finger,$time,$atoken);
          break;
        case 'auth':
          $ret=$uc->cat($uname,$finger,$time,$atoken);
      }
      
      return echoRestfulData($ret);
    }
    
    function post($action=0) {
      $ret=e(1001,'Unknow action (post).');
      $uc= new catUsers();
      
      $uname=v('user');
      $otoken=v('otoken');
      $ptoken=v('ptoken');
      
      switch($action) {
        case 'regist':
          break;
        case 'password':
          $rt=$this->setPassword($uc,$uname,$otoken,$ptoken);
          if($rt) $ret=e(0,'Reset password success.');
          else $ret=e(1002,'Reset password FAILED!');
          break;
        //default:
        //  $ret;
      }
      echoRestfulData($ret);
    }
    
    function setPassword($uc, $uname,$otoken,$ptoken) {
      $right_ot=$uc->get_pass_token($uname);
      if($right_ot === $otoken)
        return $uc->set_pass_token($uname,$ptoken);
      return false;
    }
}

