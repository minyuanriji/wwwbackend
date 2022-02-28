<template id="com-wrong-report">
    <el-card class="box-card">
        <div slot="header" class="clearfix">
            <span>异常警告</span>
        </div>
        <el-tabs v-model="activeName" type="card">
            <el-tab-pane label="全部" name="all">赠送金豆异常</el-tab-pane>
            <el-tab-pane label="赠送金豆" name="integral">赠送金豆异常</el-tab-pane>
            <el-tab-pane label="用户提现" name="user_cash">用户提现</el-tab-pane>
            <el-tab-pane label="用户收益" name="user_income">用户收益异常</el-tab-pane>
            <el-tab-pane label="商户提现" name="mch_cash">商户提现异常</el-tab-pane>
            <el-tab-pane label="二维码收款" name="checkout">商品订单异常</el-tab-pane>
            <el-tab-pane label="商品订单" name="order">商品订单异常</el-tab-pane>
        </el-tabs>
    </el-card>
</template>

<script>
    Vue.component('com-wrong-report', {
        template: '#com-wrong-report',
        data() {
            return {
                activeName: 'all'
            }
        },
        methods: {

        },
        created() {

        }
    })
</script>