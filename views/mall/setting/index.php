<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-10
 * Time: 12:36
 */
Yii::$app->loadComponentView('com-test');
?>
<div id="app" v-cloak>
    <com-test></com-test>
    <div v-loading="loading">
        <template v-if="mall">
            <h1>{{mall.name}}</h1>
            <el-button @click="$navigate({r:'mall/demo/index'})">列表样式规范</el-button>
            <el-button @click="$navigate({r:'mall/demo/edit'})">表单样式</el-button>
        </template>
    </div>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                mall: null,
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'mall/setting/index',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.mall = e.data.data.mall;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
        }
    });
</script>
