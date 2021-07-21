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

<?php echo $content;?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>