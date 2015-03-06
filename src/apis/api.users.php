<?php
require_once 'catUsers.class.php';

/**
 * 用户表，用来验证用户的
 *
 * @api: users
 * 
 * 参数action: login, auth, a, pushan, reg, password
 * action=reg         功能：注册新用户。
 * action=password    功能：修改密码。
 * action=其他：验证操作数据（是否是用户自己发来的操作数据）
 * 
 *   【注：所有API可用的参数】
 *   jsonp: 可选，有效的可执行函数名，可用于跨域名调用等。
 *     例如:jsonp=alert, jsonp=console.log, jsonp=some_valid_func 。
 *     指定了jsonp以后，返回的header中 Content-type: application/javascript;
 *     其他情况返回header中 Content-type: application/json;
 *     
 * @method: get, post （仅可以使用post或get方式）
 * action参数: login, a, auth, pushan
 *   【功能】 用户帐号密码验证，操作验证。
 * 
 * action参数: reg,  password
 *   【功能】 create 或update。创建一个用户，修改密码。
 *   
 * @method: put
 *   【功能】 无
 *   
 * @ method: delete
 *   【功能】 无（不提供删除功能）
 *
 * 【其他参数】
 * //action: login,   a, auth, pushan,   reg,   password。
 * user: 用户名
 * 
 * otoken: 原先的pass_token，仅在修改密码时 有用。
 * ptoken: 新的pass_token，在新用户注册或修改密码时 有用。
 * email: 用户邮件，在新用户注册时 有用。
 * 
 * atoken: action_token，验证action用的
 * time: 时间
 * 
 */
class users_Handler {
    
    function post( ) {
      $uc= new catUsers();
      
      $action=v('action');
      $ret=e(1001,"Unknow action ($action).");
      
      switch($action) {
        case 'reg':
          $ret=$uc->add_user();
          break;
        case 'chgpwd':
          $ret=$uc->set_pass_token( );
          break;
        case 'login':
        case 'a':
        case 'auth':
        default:
          $ret=$uc->cat( );
          $ret['action']= $action ;
      }
      
      return echoRestfulData($ret);
    }
    
    function get( ) {
      return $this->post ( );
    }
}

