<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>淘宝授权</title>
</head>
<body>
<noscript>
<strong>Please enable JavaScript to continue.</strong>
</noscript>
<div id="app"></div>
</body>
<div style="text-align: center;margin-top:30px;">授权成功！请稍后...
    <span id="counter">5</span>
</div>
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
</html>