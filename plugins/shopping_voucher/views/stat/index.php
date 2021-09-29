<?php defined('YII_ENV') or exit('Access Denied');?>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>数据概况</span>
        </div>
        <el-card shadow="never">
            <div slot="header">
                <span>收支汇总</span>
                <div class="tab-pay">
                    <el-tabs>
                        <el-tab-pane label="昨日" name="one"></el-tab-pane>
                        <el-tab-pane label="7日" name="seven"></el-tab-pane>
                    </el-tabs>
                </div>
            </div>
        </el-card>
    </el-card>
</div>

<script>
const app = new Vue({
    el: '#app',
    data() {
        return {

        };
    },
    methods: {

    }
})
</script>