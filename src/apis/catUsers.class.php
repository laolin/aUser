<?php
/**
 *
 * class catUsers
 * @author Laolin hi#laolin.com
 *
 *  超轻量级的，高安全性的用户系统
 *
 *   【数据表规定】
 *   id:
 *   user: 用户名。不区分大小写。
 *     至少2字符，首字符要求为字母，后续字母、数字或下划线。
 *   email: 
 *   pass_token: 加密过的密码。应该只在设定密码是在通过网络传输。
 *     =md5("特定的公开的字符串" + 转为全小写的用户名 + 加密前的password )
 *   create_time: 
 *   user_group: 先都=1，预留备用。
 *
 * 
 * 
 * 用户验证方式：
 * 通过 有授权的用户名、密码串，代表用户提交的操作和数据的几个变量，
 * 采用事先约好的计算方法，计算出一个 action_token
 * 密码串不传递，其他数据传递到服务器
 *
 * 服务器接收到用户数据，并从数据库中取得密码串，计算出 action_token
 * 两者相符表示是授权用户的操作
 * 详见函数 cat()
 * 
 */
class catUsers {
    private $_md5Salt='Laolin_user_PUSHAN';
    private $_table='cat_user';
    
    
    public function login($user, $login_time, $action_token) {
      $ret=cat($user,'login', $login_time, $action_token);
      if($ret['err_code']==0)
        return true;
      return false;
    }
    
    /**
     * @function cat (cat = check_action_token的缩写)
     *
     * 验证 action_token 是不是对的
     *
     * @param $user 用户名
     * @param $action_finger 从本次提交的操作和数据中提取的“动作指纹”
     * @param $action_time 函数执行时的时间戳，用于避免数据篡改
     * @param $action_token 从上面3个参数，加上用户自已的密码 pass_token 共4个变量一起计算出来的验证字符串
     */
    public function cat($user, $action_finger, $action_time, $action_token ) {
    //public function check_action_token($user, $action_finger, $action_time, $action_token ) {

      $ret=e(0, 'ok');
      
      //1.时间是否和服务器现在时间差距太大
      //PHP已设东8区，考虑最大时差范围可能是 -20 ~ +4
      //故允许时间差 -21 ~ 5 小时
      $esp=time()-$action_time;
      if($esp> 21 * 3600 || $esp< -5 * 3600)
        return e(2001, "Time Mismatch, yours: $action_time, mine: ".time());
        
      //2.用户是否存在
      $pass_token=$this->get_pass_token($user);
      if(0===$pass_token)
        return e(2002, 'User Verification fail.');
        
      //3.action_finger应该在服务器自己计算对比?
      //  （或不要客户端的，在服务端自己提取）
      
      //4.加上pass_token，计算
      $rit_token=$this->gen_action_token($user,$action_finger,$action_time,$pass_token);
      if($rit_token != $action_token)
        $ret=e(2000, 'Action Authentication fail.');
      return $ret;
    }
    
    /**
     * 3. 由4个变量一起计算出来一个验证字符串
     *  @seealso check_action_token
    */    
    private function gen_action_token($user,$action_finger,$action_time,$pass_token) {
      $ret=md5(strtolower($user) . $action_finger . $action_time . $pass_token);
      return $ret;
    }
    
    /**
     * 2. 根据用户名，密码生成 gen_pass_token
     */
    function gen_pass_token($user,$password) {
      return md5($this->_md5Salt . strtolower($user) . $password  );
    }
    
    /**
     * 1b. 在数据库中 设置用户的 pass_token
     */
    function set_pass_token($user, $token) {
        $where = [  ];
        $where["user"] = $user;//MYSQL自动为大小写不敏感
        $where["LIMIT"] = 1;
        $res=$GLOBALS['db']->update($this->_table,
          [ 'pass_token' => $token ],$where);
        return $res;
    }
    /**
     * 1. 从数据库中 读取用户的 pass_token
     */
    function get_pass_token($user) {
        $where = [  ];
        $where["user"] = $user;//MYSQL自动为大小写不敏感
        $where["LIMIT"] = 1;

        $res=$GLOBALS['db']->select($this->_table,
          ['user','pass_token'],$where);
      return isset($res[0])&&isset($res[0]['pass_token'])?
        $res[0]['pass_token'] : 0 ;
    }
    
}

