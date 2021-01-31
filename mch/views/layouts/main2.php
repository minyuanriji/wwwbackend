<?php
$siteName = '商城管理';

?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="keywords" content="<?= isset($indSetting['keywords']) ? $indSetting['keywords'] : '' ?>"/>
    <meta name="description" content="<?= isset($indSetting['description']) ? $indSetting['description'] : '' ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <title><?= $this->title ? ($this->title . ' - ') : '' ?><?= $siteName ?></title>
    <link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/element-ui@2.12.0/lib/theme-chalk/index.css">
    <link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/statics/css/flex.css">
    <link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/statics/css/common.css">
    <link href="<?= Yii::$app->request->baseUrl ?>/../favicon.ico" mce_href="<?= Yii::$app->request->baseUrl ?>/../favicon.ico" rel="shortcut icon"/>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/jquery@3.3.1/dist/jquery.min.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vue@2.6.10/dist/vue.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/element-ui@2.12.0/lib/index.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/qs@6.5.2/dist/qs.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/axios@0.18.0/dist/axios.min.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vue-line-clamp@1.2.4/dist/vue-line-clamp.umd.js"></script>
    <script>
        let _layout = null;
        const _csrf = '<?=Yii::$app->request->csrfToken?>';
        const _scriptUrl = '<?=Yii::$app->request->scriptUrl?>';
        const _baseUrl = '<?= \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl ?>';
    </script>

    <script>let _siteLogo = _baseUrl + '/statics/img/admin/login-logo.png';</script>

    <script src="<?= Yii::$app->request->baseUrl ?>/statics/js/common.js"></script>
    <style>
        html, body {
            height: 100%;
            padding: 0;
            margin: 0;
        }

        #app {
            height: 100%;
        }

        [v-cloak] {
            display: none !important;
        }
    </style>

</head>
<body>
<?php $this->beginBody() ?>
<div id="_layout"></div>
<?= $this->renderFile('@app/views/components/index.php') ?>
<?= $content ?>
<script>
    _layout = new Vue({
        el: '#_layout',
    });
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
