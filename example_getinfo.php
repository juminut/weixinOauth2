<?php
/*用户授权，获取用户信息*/

//*****微信打开***********
//设置微信登录回调地址，为了代码在一个页面里展示，所以以当前页面地址作为回调的地址，通过判断是否设置了state来区分是浏览器访问的，还是微信回调。
//需设置，公众号——公众号设置——功能设置——网页授权域名，域名设置为回调地址的域名

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
    $redirectUri='http'.($_SERVER["HTTPS"] == "on"?'s':'').'://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
    $a=$wxOauth2->authorize($redirectUri,"snsapi_base",time());
    echo '<a href="'.$a.'">应用授权作用域:snsapi_base</a><br>';

    $a=$wxOauth2->authorize($redirectUri,"snsapi_userinfo",time());
    echo '<a href="'.$a.'">应用授权作用域:snsapi_userinfo</a><br>';
}
?>