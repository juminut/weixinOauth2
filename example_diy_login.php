<?php
/*在自己页面上添加登录验证二维码，自定义样式*/
//电脑打开，微信扫码登录
//设置微信登录回调地址，为了代码在一个页面里展示，所以以当前页面地址作为回调的地址，通过判断是否设置了state来区分是浏览器访问的，还是微信回调。

include 'weixinOauth2.class.php';
$wxOauth2=new weixinOauth2();

if(isset($_GET['state'])){
    //是回调后的
    if(isset($_GET['code'])){
        $code=$_GET['code'];
        echo "获得的code:<br>".$code."<br><hr><br>";

        echo "通过code，调用函数access_token返回结果：<br>";
        $access_token=$wxOauth2->access_token("fuwu",$code);
        echo $access_token;
        echo "<br><hr><br>";

        $access_token=json_decode($access_token,true);

        echo "检验授权凭证access_token，调用函数auth(如有需要)：<br>";
        $auth=$wxOauth2->auth($access_token['access_token'],$access_token['openid']);
        echo $auth;
        echo "<br><hr><br>";

        echo "刷新access_token，调用函数refresh_token（如有需要）：<br>";
        $refresh_token=$wxOauth2->refresh_token("fuwu",$access_token['refresh_token']);
        echo $refresh_token;
        echo "<br><hr><br>";

        echo "获取用户资料信息，调用函数userInfo：<br>";
        $userInfo=$wxOauth2->userInfo($access_token['access_token'],$access_token['openid']);
        echo $userInfo;
        echo "<br><hr><br>";

    }else{
        echo "取消登录";
    }
}else{
    //是回调前
    ?>
    <script src="https://res.wx.qq.com/connect/zh_CN/htmledition/js/wxLogin.js"></script>
    应用授权作用域:snsapi_base<br>
    <div id="login_2"></div>
<br><br><hr><br>
    应用授权作用域:snsapi_userinfo<br>
    <div id="login_1"></div>
    <?php
    $redirectUri='http'.($_SERVER["HTTPS"] == "on"?'s':'').'://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
    $a=$wxOauth2->getLoginStr("fuwu","login_1",$redirectUri,"snsapi_userinfo",time(),"black","");
    echo $a;

    $a=$wxOauth2->getLoginStr("fuwu","login_2",$redirectUri,"snsapi_base",time(),"white","");
    echo $a;

}
?>
