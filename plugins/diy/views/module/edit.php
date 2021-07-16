<?php

Yii::$app->loadComponentView("com-edit", __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template');
?>
<div id="app" v-cloak>
    <com-edit type="module" request-url="plugin/diy/mall/module/edit"></com-edit>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {};
        },
    });
</script>