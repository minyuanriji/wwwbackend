<?php
Yii::$app->loadComponentView('com-order-detail');
?>
<div id="app" v-cloak>
    <com-order-detail
            get-order-list-url="plugin/mch/mall/order/index"
            :is-show-edit-address="false"
            :is-show-cancel="false"
            :is-show-remark="false"
            :is-show-finish="false"
            :is-show-confirm="false"
            :is-show-print="false"
            :is-show-clerk="false"
            :is-show-send="false">
    </com-order-detail>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {};
        },
        created() {
        },
        methods: {}
    })
</script>