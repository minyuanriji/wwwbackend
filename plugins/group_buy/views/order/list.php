<!-- <h1>hello world</h1> -->
<!-- 这个是拼团订单列表 -->

<?php
Yii::$app->loadPluginComponentView('com-orders-list');
?>
<style>

</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <com-orders-list :is-show-order-plugin="true"></com-orders-list>
    </el-card>
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
    });
</script>