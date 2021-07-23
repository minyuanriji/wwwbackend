<?php

?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <meta name="format-detection" content="telephone=no,email=no,address=no">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>

    <link href="https://cdn.jsdelivr.net/npm/ant-design-vue@1.7.6/dist/antd.css" rel="stylesheet" type="text/css"/>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/ant-design-vue@1.7.6/dist/antd.js"></script>

    <title><?= $this->title ?></title>
</head>
<body>
<?php $this->beginBody(); ?>

<div id="userLayout" class="user-layout-wrapper">
    <div class="container">
        <div class="user-layout-lang">
            <select-lang class="select-lang-trigger" />
        </div>
        <div class="user-layout-content">
            <div class="top">
                <div class="header">
                    <img src="https://preview.pro.antdv.com/assets/logo.f3103225.svg" class="logo" alt="logo">
                    <span class="title">Ant Design</span>
                </div>
                <div class="desc">

                </div>
            </div>

            <?php echo $content;?>

            <div class="footer">
                <div class="links">
                    <a href="_self">帮助</a>
                    <a href="_self">隐私</a>
                    <a href="_self">条款</a>
                </div>
                <div class="copyright">
                    Copyright &copy; 2018 vueComponent
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    new Vue({
        el: '#userLayout',
        data() {
            return {

            }
        },
        created() {
        },
        methods: {

        },
    });
</script>
<style type="text/css">
    #userLayout.user-layout-wrapper{height: 100%;}
    #userLayout.user-layout-wrapper .container .main{
        max-width: 368px;
        width: 98%;
    }
    #userLayout.user-layout-wrapper .container{
        width: 100%;
        min-height: 100%;
        background: #f0f2f5 url(https://preview.pro.antdv.com/assets/background.5825f033.svg) no-repeat 50%;
        background-size: 100%;
        position: relative;
    }
    #userLayout.user-layout-wrapper .container .user-layout-lang{
        width: 100%;
        height: 40px;
        line-height: 44px;
        text-align: right;
    }
    #userLayout.user-layout-wrapper .container .user-layout-lang .select-lang-trigger{
        cursor: pointer;
        padding: 12px;
        margin-right: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        vertical-align: middle;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content{
        padding: 32px 0 24px;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .top{
        text-align: center;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .top .header{
        height: 44px;
        line-height: 44px;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .top .header .badge{
        position: absolute;
        display: inline-block;
        line-height: 1;
        vertical-align: middle;
        margin-left: -12px;
        margin-top: -10px;
        opacity: 0.8;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .top .header .logo{
        height: 44px;
        vertical-align: top;
        margin-right: 16px;
        border-style: none;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .top .header .title{
        font-size: 33px;
        color: rgba(0, 0, 0, .85);
        font-family: Avenir, 'Helvetica Neue', Arial, Helvetica, sans-serif;
        font-weight: 600;
        position: relative;
        top: 2px;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .top .desc {
        font-size: 14px;
        color: rgba(0, 0, 0, 0.45);
        margin-top: 12px;
        margin-bottom: 40px;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .main{
        min-width: 260px;
        width: 368px;
        margin: 0 auto;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .footer{
        width: 100%;
        bottom: 0;
        padding: 0 16px;
        margin: 48px 0 24px;
        text-align: center;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .footer .links{
        margin-bottom: 8px;
        font-size: 14px;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .footer .links a{
        color: rgba(0, 0, 0, 0.45);
        transition: all 0.3s;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .footer .links a &:not(:last-child) {
        margin-right: 40px;
    }
    #userLayout.user-layout-wrapper .container .user-layout-content .footer .copyright{
        color: rgba(0, 0, 0, 0.45);
        font-size: 14px;
    }
    #userLayout.user-layout-wrapper .container a{
        text-decoration: none;
    }
</style>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>