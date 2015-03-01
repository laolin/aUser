<?php

//BBBBBBBBBBBBBBBBBBBBBBBBBBBBB
/**
 * @api pinyin/:q 
 * 
 * 根据拼音查汉字，根据汉字查拼音。
 * @method : get 或 post
 *
 * 根据拼音查所有的同音汉字，拼音可指定音调也可不指定音调
 * 根据汉字查所有的拼音，含音调。
 * 1~4声，5代表轻声
 *
 * @param string q 用查询的拼音或汉字
 *   必须输入有效的拼音，或1个汉字。
 *   可以直接跟在API地址 pinyin的后面，例 /pinyin/lin
 *   也可以用变量q表示，例/pinyin?q=lin
 *
 * @param string jsonp : 【注：所有API可用的参数】
 *  可选，有效的可执行函数名，可用于跨域名调用等。
 *    例如:jsonp=alert, jsonp=console.log, jsonp=some_valid_func 。
 *  指定了jsonp以后，返回的header中 Content-type: application/javascript;
 *  其他情况返回header中 Content-type: application/json;
 *
 * @return Object Obj
 *   Obj.code (int) : 出错信息代码，0表示无错。
 *   Obj.msg (string) : 文本信息
 *   Obj.data (Object) :  查询的结果对象
 *     Obj.data.ans (mix) : 查询结果 
 *     Obj.data.ansType (string 'py'|'hz') : 结果的类型
 *     Obj.data.q (string) : 所查询的输入数据，已清理过的。
 * 注，拼音后面的数字代表声调，1~4声，5代表轻声
 *  
 * @example GET /pinyin/中            // 中的拼音
 * @example GET /pinyin/lin          // 拼音为lin的字
 * @example GET /pinyin?q=liN        // 拼音为lin的字（同上）
 * @example GET /pinyin/LiN2          // 拼音为lin第2声调的字
 * @example GET /pinyin/A          // 拼音为a的字
 * @example GET /pinyin/a5          // 拼音为a，轻声调的字
 *  
 * @example GET /pinyin/a55     //无效
 * @example GET /pinyin/5       //无效
 * @example GET /pinyin/b       //无效
 * @example GET /pinyin/G       //无效
 * @example GET /pinyin/lin6    //无效
 * @example GET /pinyin/中国    //无效（多字）
 * @example GET /pinyin/℃      //无效（不知道拼音）
 * @example GET /pinyin/lng     //无效
 */
class pinyin_Handler {
  private $table='pinyin';
  
    function get($para='') {
      $ret=[ 'code'=>0, 'msg'=>'ok.', 'data'=>false ];
      if($para)$_REQUEST['q']=$para;
      
      $inp=$this->_q();//取得用户输入
      if($inp['code']>0) {
        $ret['code']=$inp['code'];
        $ret['msg']=$inp['msg'];
        return echoRestfulData($ret);
      }
      $q=$inp['data'];
      if($inp['type']=='hz') {//由汉字查拼音的
        $ans=$GLOBALS['db']->select($this->table,['pyd','hz'],['hz[~]'=>$q]);
        ////输入的是汉字 ，则输出pyd(拼音带音调)
        $ret['data']['q']=$q;
        $ret['data']['anstype']='py';
        if($ans) {
          $ret['data']['ans']=$ans;
        } else { 
          $ret['code']=4001;
          $ret['msg']="囧，这个怎么拼啊[ $q ]";
        }
      } else {
        if($inp['type']=='pyd') //由带音调的拼音查汉字的
          $ans=$GLOBALS['db']->select($this->table,['pyd','hz'],['pyd'=>$q,'LIMIT'=>1]);
        else //由不带音调拼音查汉字的
          $ans=$GLOBALS['db']->select($this->table,['pyd','hz'],['py'=>$q,'LIMIT'=>5]);
        
        $ret['data']['q']=$q;
        if($ans) {
          $ret['data']['ans']=$ans;
          $ret['data']['anstype']='hz';
        } else { 
          $ret['code']=3002;
          $ret['msg']="无效的拼音[ $q ]";
        }
      }
      echoRestfulData($ret);
    }
    
    function post($id=0) {
      $this->get($id);
    }
    
    //同音字重排序，尽量按常用
    protected function _reSortTyz($res) {
    
      $ansstr='';      
      for($j=0;$j<500 ;$j++) {//最多的同音字没有超过500个
        $step='';
        $eof=true;
        for($i=0;$i<$length;$i++) {
          $hz1=mb_substr($data['items'][$i][$anskey],$j,1,'UTF-8');
          if($hz1){
            $eof=false;//只要本循环还有字，就继续
            if(strpos($ansstr,$hz1)===false && strpos($step,$hz1)===false)//重复的字不要了
              $step.= $hz1.',' ;
          }
        }
        if($eof)
          break;
        $ansstr.=$step;
      }
      
     } 
/**
 * 取得用户要查询拼音的输入数据
 *
 * 查询拼音request参数名：*'q'*
 * 判断用户查询的数据有效性，
 * 字母全部变为小写的，
 * 并判断是否是汉字、
 * 是否*可能*是拼音。
 * 
 * @return Object Obj
 *   Obj.code (int) : 出错信息，0表示无出错。
 *   Obj.msg (string) : 出错信息，空字符串表示无出错。
 *   Obj.data (mix) : 返回的数据
 *   Obj.type (string) : 'py'表示返回的数据*可能*是拼音(可能是无效的拼音)，'hz'表示返回的数据是汉字
 */
  protected function _q() {   
    $ques=trim(v('q'));
    $kickchars=array( '+',  '%', '#', '=', '&', '?', '/', '\\', '<', '>', ';', ',', '.',
             // '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
             '`', '(', ')', '[', ']');
    $ques=strtolower(str_replace($kickchars,'',$ques));//删掉非法字符
    $ret=[ 'code'=>0, 'msg'=>'ok.', 'data'=>$ques, 'type'=>'' ];
    
    //简单检查收到的是汉字，还是拼音
    $n=mb_strlen($ques,'UTF-8');

    if($n<=0) {
      $ret['code']=2001;
      $ret['msg']='请输入拼音或汉字。';
    } else if ($n==1) {
      if(eregi('[^\x00-\x7F]', $ques)) $ret['type']='hz'; //'有汉字'1个汉字，OK;
      else 
        if(eregi('[aoe]', $ques))$ret['type']='py';//长=1拼音，OK
        else{
          $ret['code']=2002;
          $ret['msg']='无效拼音。';
        }
    } else if ($n>7) {
      $ret['code']=2007;
      $ret['msg']='输入字符太长。';
    } else if (eregi('[^\x00-\x7F]', $ques)) {//有汉字时，长度是不能大于1的
      $ret['code']=2008;
      $ret['msg']='请输入1个汉字 或 1个字的拼音。';
    } else if(eregi('^[a-z]+[1-5]$', $ques))
      $ret['type']='pyd';//带音调的拼音，有可能是无效拼音，管不了啦
    else if(eregi('^[a-z]+$', $ques))
      $ret['type']='py';//无音调的拼音，有可能是无效拼音，管不了啦
    else { 
      $ret['code']=3001;
      $ret['msg']="无效的拼音[ $ques ]";
    }
    
    return $ret;
  }
}

