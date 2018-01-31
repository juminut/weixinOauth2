<?php
/*
 * by zhangshurui 20180121
 *
 * admin@hunnanren.com
 *
 *
 * 微信通过oauth2主要实现的两大功能（微信登录，获取用户资料信息）
 * 1、其他网站可让用户使用微信登录，拉取用户微信信息***在微信开放平台注册开发者帐号，并拥有一个已审核通过的网站应用，并获得相应的AppID和AppSecret，申请微信登录且通过审核后，可开始接入流程。
 * 2、微信内h5页面拉取当前用户信息****仅支持认证的服务号的appid及appsecret
 *
 * 一、微信登录流程
 *
 *  1、展示二维码供要登录的客户扫描。共两种形式
 * --（1）微信官方提供的，整页是一个二维码的页面。调用本类中的getLoginUrl函数获取跳转地址，然后跳转。
 * -------Header("HTTP/1.1 303 See Other");
   -------Header("Location: $url");
 * --（2）在自己的页面上添加扫描登录二维码。
 * ------在自己的页面引入<script src="https://res.wx.qq.com/connect/zh_CN/htmledition/js/wxLogin.js"></script>，支持https
 * ------调用本类中的getLoginStr函数，返回的是js内容，直接输出在页面内
 *
 * 2、第一步用户授权登录，或者取消登录，会跳转至第一步设置的redirectUri地址。并带参数
 * ---授权成功，redirectUri?code=CODE&state=STATE。code是进一步获取用户信息的凭证。state是第一步设置的可以做个验证
 * ---授权失败，redirectUri?state=STATE。state是第一步设置的可以做个验证
 *
 * 3、调用函数access_token，使用上一步获取的code，换取access_token，openID，unionID
 *
 * 4、使用上一步获取的access_token，来拉取用户微信中的详细信息。****需要第一步scope设置为snsapi_userinfo
 *
 * 另外提供如下操作
 * 函数auth，检验授权凭证（access_token）是否有效
 * 函数refresh_token，刷新access_token有效期
 *
 *
 *
 *
 *二、微信内h5页面获取当前用户信息
 *
 * 1、调用authorize函数。得到返回地址。跳转
 * -------Header("HTTP/1.1 303 See Other");
 * -------Header("Location: $url");
 *
 * 2、微信授权后，自动跳回第一步设置的redirectUrl，
 * ---redirectUri?code=CODE&state=STATE。code是进一步获取用户信息的凭证。state是第一步设置的可以做个验证
 *
 * 3、调用函数access_token，使用上一步获取的code，换取access_token，openID，unionID
 *
 * 4、使用上一步获取的access_token，来拉取用户微信中的详细信息。****需要第一步scope设置为snsapi_userinfo
 *
 * 另外提供如下操作
 * 函数auth，检验授权凭证（access_token）是否有效
 * 函数refresh_token，刷新access_token有效期
 *
 */
class WeixinOauth2{
    private $config;

    function __construct(){
        //$this->config=include "config/config.php"; //获取公众号配置文件
        $this->config=array(
            "fuwu"=>array(
                "APPID"=>"wxa8fea24f06df4d20",
                "APPSECRET"=>"7ed49ec34d19fbf68664237f8835e4df"
            )
        );
    }


    //获取微信官方提供整页微信扫描登录页面地址
    //AccountType 对应config配置
    //redirectUri 用户允许授权后，将会重定向到redirectUri的网址上，并且带上code和state参数，若用户禁止授权，则重定向后不会带上code参数，仅会带上state参数
    //scope 应用授权作用域，snsapi_base 只能获取用户openid，snsapi_userinfo （登录确认页提示：获得你的公开信息（昵称、头像等），可通过openid拿到昵称、性别、所在地。）
    //state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节，授权请求后原样带回给第三方。该参数可用于防止csrf攻击（跨站请求伪造攻击），建议第三方带上该参数，可设置为简单的随机数加session进行校验
    public function getLoginUrl($AccountType,$redirectUri,$scope,$state){
        return "https://open.weixin.qq.com/connect/qrconnect?appid=".$this->config[$AccountType]['APPID']."&redirect_uri=".urlencode($redirectUri)."&response_type=code&scope=snsapi_login,".$scope."&state=".$state."#wechat_redirect";
    }

    //获取自定义微信扫描登录页面代码
    //AccountType 对应config配置
    //loginContainer 页面显示二维码的容器id
    //redirectUri 用户允许授权后，将会重定向到redirectUri的网址上，并且带上code和state参数，若用户禁止授权，则重定向后不会带上code参数，仅会带上state参数
    //scope 应用授权作用域，snsapi_base 只能获取用户openid，snsapi_userinfo （登录确认页提示：获得你的公开信息（昵称、头像等），可通过openid拿到昵称、性别、所在地。）
    //state 用于保持请求和回调的状态，授权请求后原样带回给第三方。该参数可用于防止csrf攻击（跨站请求伪造攻击），建议第三方带上该参数，可设置为简单的随机数加session进行校验
    //style 提供"black"、"white"可选，默认为黑色文字描述。
    //href 自定义样式链接，第三方可根据实际需求覆盖默认样式。详见https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN
    public function getLoginStr($AccountType,$loginContainer,$redirectUri,$scope,$state,$style="black",$href=""){
        return '<script language="javascript">var obj = new WxLogin({id:"'.$loginContainer.'", appid: "'.$this->config[$AccountType]['APPID'].'", scope: "snsapi_login,'.$scope.'", redirect_uri: "'.urlencode($redirectUri).'",state: "'.$state.'",style: "'.$style.($href==""?"":('",href: "'.$href)).'"});</script>';
    }

    //使用code换取access_token，openID，unionID
    //AccountType 对应config配置
    //code 微信回调的URL中接的code
    public function access_token($AccountType,$code){
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->config[$AccountType]['APPID']."&secret=".$this->config[$AccountType]['APPSECRET']."&code=".$code."&grant_type=authorization_code";
        return $this->curl($url);
    }

    //使用access_token获取的access_token，来拉取用户微信中的信息。只有scope=snsapi_userinfo时可用
    public function userInfo($access_token,$openid){
        $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        return $this->curl($url);
    }

    /*
	//公众号获取用户信息，只有用户关注了公众号，就能拉取客户资料
	//access_token 公众号的access_token
	public function gongZhongHaouserInfo($access_token,$openid){
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        return $this->curl($url);
    }
    */

    //检验授权凭证（access_token）是否有效
    public function auth($access_token,$openid){
        $url="https://api.weixin.qq.com/sns/auth?access_token=".$access_token."&openid=".$openid;
        return $this->curl($url);
    }

    //刷新access_token有效期
    public function refresh_token($AccountType,$refresh_token){
        $url="https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$this->config[$AccountType]['APPID']."&grant_type=refresh_token&refresh_token=".$refresh_token;
        return $this->curl($url);
    }

    //h5页面获取用户信息入口
    //现微信仅支持服务号
    //redirectUrl 微信服务器将获取用户信息的授权凭证发送的地址。即实例化此类，调用access_token函数的地址。
    //scope 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且， 即使在未关注的情况下，只要用户授权，也能获取其信息 ）
    //重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节，授权请求后原样带回给第三方。该参数可用于防止csrf攻击（跨站请求伪造攻击），建议第三方带上该参数，可设置为简单的随机数加session进行校验
    public function authorize($redirectUrl,$scope,$state){
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->config['fuwu']['APPID']."&redirect_uri=".urlencode($redirectUrl)."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
    }

    private function curl($url,$data="",$verify=true,$timeout=500,$cainfo="/cert/rootca.pem"){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//以文件流的形式返回
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        if($verify){//是否验证访问的域名证书
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
            curl_setopt($curl,CURLOPT_CAINFO,dirname(__FILE__).$cainfo);
        }else{
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        if($data<>""){//data有内容，则通过post方式发送
            curl_setopt($curl,CURLOPT_POST,true);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        if($res){
            curl_close($curl);
            return $res;
        } else {
            $error = curl_errno($curl);
            curl_close($curl);
            throw new Exception("curl出错，错误码:$error");
        }
    }
}
?>