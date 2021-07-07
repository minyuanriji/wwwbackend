<?php

Yii::$app->loadComponentView("com-edit", __DIR__);
?>
<div id="app" v-cloak>
    <com-edit></com-edit>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {};
        },
    });
</script>