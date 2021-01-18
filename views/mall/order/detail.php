<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/29 15:59
 */
Yii::$app->loadComponentView('com-order-detail');
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