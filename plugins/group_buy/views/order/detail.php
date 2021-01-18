<!-- 这个是拼团订单详情 -->
<?php
/**
 * 引当前模块组价
 */
Yii::$app->loadPluginComponentView('com-order-detail');
?>
<div id="app" v-cloak>
    <com-order-detail></com-order-detail>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {

            };
        },
        created() {
        },
        methods: {

        }
    })
</script>