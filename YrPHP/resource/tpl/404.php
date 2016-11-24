<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <base href="<?=getUrl();?>"/>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv='Refresh' content='<?php echo $time; ?>;URL=<?php echo $url; ?>'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>提示信息</title>

</head>

<body>

<div class="container">

    <h1><?php echo $msg; ?></h1>
    <h4><span id="cnt"><?php echo $time; ?></span>秒钟后自动跳转！【<a href="<?php echo $url; ?>">立即跳转</a>】</h4>

</div> <!-- /container -->
<script>
    window.onload =function() {
        var i = <?php echo $time-1; ?>;
        setInterval(function(){
            document.getElementById("cnt").innerHTML = i--;

        },1000);
    };
</script>
</body>
</html>