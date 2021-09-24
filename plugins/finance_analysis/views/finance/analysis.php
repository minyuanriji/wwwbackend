<?php defined('YII_ENV') or exit('Access Denied');

echo $this->render("com-analysis-income");
echo $this->render("com-wrong-report");
?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>财务概况</span>
        </div>
        <div class="table-body">
            <com-analysis-income></com-analysis-income>
        </div>
        <div style="margin-top:20px;">
            <com-wrong-report></com-wrong-report>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {

            };
        },
        methods: {}
    })
</script>
<style>

    .table-body {
        background-color: #fff;
        position: relative;
        border: 1px solid #EBEEF5;
        padding-bottom: 30px;
    }

</style>