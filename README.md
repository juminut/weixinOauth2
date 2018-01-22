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
 *
 * $this->config=array(
 *          "fuwu"=>array(
 *              "APPID"=>"wxa8fea24f06df4d20", //获取access_token,jsapi_ticket 必要条件
 *              "APPSECRET"=>"7ed49ec34d19fbf68664237f8835e4df", //获取access_token,jsapi_ticket 必要条件
 *          )
 *      );
 *修改函数curl中的默认根证书路径
 *
 *微信公众号的access_token jsapi_ticket管理，支持多地服务器获取：https://github.com/juminut/weixinTokenManager
 *
 *微信登录，获取用户资料信息，类代码：https://github.com/juminut/weixinOauth2
 *
 *
 */