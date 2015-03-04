// 在APItest中的API地址中输入 ./users.js，然后get一下即可加载此JS文件。
    
/**
 * 根据用户名，密码生成 提交 reg 的post数据
 */
function gen_reg_data(user,password,email,prefix) {
  prefix=prefix||""
  d=gen_action_data(user,password,'reg',prefix)
  d[prefix+'ptoken']=gen_pass_token(user,password)
  d[prefix+'email']=email
  console.log(JSON.stringify(d,null,"\t"))
  return d;
}
     
/**
 * 根据用户名，密码生成 提交 action 的post数据
 */
function gen_action_data(user,password,action,prefix) {
  prefix=prefix||""
  time=Math.round( (new Date().getTime()/1000))
  atoken=gen_action_token(user, action, time, password)
  d={}
    d['__catusers_prefix']=prefix
    d[prefix+'user']=user
    d[prefix+'action']=action
    d[prefix+'time']=time
    d[prefix+'atoken']=atoken
  
  console.log(JSON.stringify(d,null,"\t"))
  return d
}


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
function gen_action_token(user,action,time,password) {
  pass_token=gen_pass_token(user,password);  
  finger=hex_md5( pass_token + action + time );
  $ret=hex_md5(user.toLowerCase() + finger + time + pass_token);
  return $ret;
}
    