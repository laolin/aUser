// 在APItest中的API地址中输入 ./users.js，然后get一下即可加载此JS文件。
    
/**
 * 根据用户名，密码生成 gen_pass_token
 */
function gen_pass_token(user,password) {
  _md5Salt='Laolin_user_PUSHAN';
  return hex_md5(_md5Salt + user.toLowerCase()  + password  );
}

//Math.round( (new Date().getTime()/1000))

/**
 * 由4个变量一起计算出来一个验证字符串
*/    
function gen_action_token(user,finger,time,password) {
  pass_token=gen_pass_token(user,password);
  $ret=hex_md5(user.toLowerCase() + finger + time + pass_token);
  return $ret;
}
    