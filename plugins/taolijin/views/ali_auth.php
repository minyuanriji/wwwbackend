<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1, minimum-scale=1">
<title>淘宝授权</title>
<style type="text/css">
body{
    margin: 0 ;padding: 0;font-size: 0.30rem;
    background:#f7f7f7;
}
</style>
<script type="text/javascript">
    document.documentElement.style.fontSize = document.documentElement.clientWidth / 640 * 100 + 'px';
</script>
</head>
<body>
<noscript>
<strong>Please enable JavaScript to continue.</strong>
</noscript>
<div id="app"></div>
<style type="text/css">

</style>

<div style="text-align: center;margin-top:50px;color:gray;">
    <div style="text-align:center;margin-bottom:10px;">
        <img src="/web/statics/img/mall/tb.jpg" style="width:28%;"/>
    </div>
    <span style="color:green;">您已授权成功</span>
</div>

<script type="text/javascript">
    var userAgent = navigator.userAgent;
    if (userAgent.indexOf('AlipayClient') > -1) {
        // 支付宝小程序的 JS-SDK 防止 404 需要动态加载，如果不需要兼容支付宝小程序，则无需引用此 JS 文件。
        document.writeln('<script src="https://appx/web-view.min.js"' + '>' + '<' + '/' + 'script>');
    } else if (/QQ/i.test(userAgent) && /miniProgram/i.test(userAgent)) {
        // QQ 小程序
        document.write(
            '<script type="text/javascript" src="https://qqq.gtimg.cn/miniprogram/webview_jssdk/qqjssdk-1.0.0.js"><\/script>'
        );
    } else if (/miniProgram/i.test(userAgent) && /micromessenger/i.test(userAgent)) {
        // 微信小程序 JS-SDK 如果不需要兼容微信小程序，则无需引用此 JS 文件。
        document.write('<script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js"><\/script>');
    } else if (/toutiaomicroapp/i.test(userAgent)) {
        // 头条小程序 JS-SDK 如果不需要兼容头条小程序，则无需引用此 JS 文件。
        document.write(
            '<script type="text/javascript" src="https://s3.pstatp.com/toutiao/tmajssdk/jssdk-1.0.1.js"><\/script>');
    } else if (/swan/i.test(userAgent)) {
        // 百度小程序 JS-SDK 如果不需要兼容百度小程序，则无需引用此 JS 文件。
        document.write(
            '<script type="text/javascript" src="https://b.bdstatic.com/searchbox/icms/searchbox/js/swan-2.0.18.js"><\/script>'
        );
    } else if (/quickapp/i.test(userAgent)) {
        // quickapp
        document.write('<script type="text/javascript" src="https://quickapp/jssdk.webview.min.js"><\/script>');
    }
    if (!/toutiaomicroapp/i.test(userAgent)) {
        document.querySelector('.post-message-section').style.visibility = 'visible';
    }
</script>
<!-- uni 的 SDK -->
<!-- 需要把 uni.webview.1.5.2.js 下载到自己的服务器 -->
<script type="text/javascript" src="https://gitee.com/dcloud/uni-app/raw/master/dist/uni.webview.1.5.2.js"></script>
<script type="text/javascript">
    // 待触发 `UniAppJSBridgeReady` 事件后，即可调用 uni 的 API。
    document.addEventListener('UniAppJSBridgeReady', function() {
        uni.postMessage({
            data: {
                action: 'message'
            }
        });
        uni.getEnv(function(res) {
            console.log('当前环境：' + JSON.stringify(res));
        });
        document.getElementById('postMessage').addEventListener('click', function() {
            uni.postMessage({
                data: {
                    action: 'message'
                }
            });
        });
    });
</script>



<script type="text/javascript">
var timer = setInterval(function(){
    var c = document.getElementById("counter").innerHTML;
    if(parseInt(c) <= 0){
        clearInterval(timer);
        return;
    }
    document.getElementById("counter").innerHTML = c - 1;
}, 1000);
</script>
<script>

</script>
</body>
</html>
