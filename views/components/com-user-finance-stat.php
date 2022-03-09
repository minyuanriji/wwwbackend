<template  id="com-user-finance-stat">
    <el-popover
        class="com-user-finance-stat"
        placement="top-start"
        width="350"
        @show="loadStatInfo"
        trigger="hover">
        <div v-loading="loading" style="min-height:50px;">
            <div v-if="!loading">
                <el-row >
                    <el-col :span="24" style="margin-bottom:5px;text-align: center">”{{nickname}}“财务概况</el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">用户ID</el-col>
                    <el-col :span="12"><span style="color:green">{{userId}}</span></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">用户等级</el-col>
                    <el-col :span="12">
                        <span v-if="role_type == 'branch_office'">分公司</span>
                        <span v-if="role_type == 'partner'">合伙人</span>
                        <span v-if="role_type == 'store'">VIP会员</span>
                        <span v-if="role_type == 'user'">普通用户</span>
                    </el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">商品消费总额</el-col>
                    <el-col :span="12"><span style="color:green">{{stat_info.total_goods_paid}}元</span></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">商品金豆抵扣总额</el-col>
                    <el-col :span="12"><span style="color:#cc3311">{{stat_info.total_goods_integral_paid}}元</span></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">店铺消费总额</el-col>
                    <el-col :span="12"><span style="color:green">{{stat_info.total_checkout_paid}}元</span></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">店铺金豆抵扣总额</el-col>
                    <el-col :span="12"><span style="color:#cc3311">{{stat_info.total_checkout_integral_paid}}元</span></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">消费总额</el-col>
                    <el-col :span="12"><b style="color:green">{{stat_info.total_paid}}元</b></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">金豆抵扣总额</el-col>
                    <el-col :span="12"><b style="color:#cc3311">{{stat_info.total_integral_paid}}元</b></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">获得金豆总数</el-col>
                    <el-col :span="12"><b style="color:#cc3311">{{stat_info.total_integral_got}}元</b></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">总收益</el-col>
                    <el-col :span="12"><b style="color:cornflowerblue">{{stat_info.total_income}}元</b></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">提现笔数</el-col>
                    <el-col :span="12"><b style="color:#cc3311">{{stat_info.total_cash_count}}笔</b></el-col>
                </el-row>
                <el-row class="user-finance-stat-row">
                    <el-col :span="12" class="l-label">提现总额</el-col>
                    <el-col :span="12"><b style="color:#cc3311">{{stat_info.total_cash}}元</b></el-col>
                </el-row>
            </div>
        </div>
        <div slot="reference"><slot></slot></div>
    </el-popover>
</template>
<script>
    Vue.component('com-user-finance-stat', {
        template: '#com-user-finance-stat',
        props: {
            userId: Number
        },
        created() {
        },
        data() {
            return {
                is_loaded: false,
                loading: false,
                nickname: 'XXX',
                role_type: '',
                stat_info: {
                    total_goods_paid: 0.00, //商品消费总额
                    total_checkout_paid: 0.00, //店铺消费总额
                    total_goods_integral_paid: 0.00, //商品订单金豆抵扣总额
                    total_checkout_integral_paid:0.00, //店铺金豆抵扣总额
                    total_paid: 0.00, //消费总额
                    total_integral_paid: 0.00, //金豆抵扣总额
                    total_integral_got: 0.00, //金豆获得总数
                    total_income: 0.00, //总收益
                    total_cash_count: 0, //提现总笔数
                    total_cash: 0.00 //提现总额
                }
            }
        },
        methods: {
            loadStatInfo(){
                let params = {
                    r: 'mall/finance/stat-info',
                    user_id: this.userId
                };
                this.loading = true;
                var self = this;
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        self.is_loaded = true;
                        self.loading = false;
                        self.stat_info = e.data.data.stat_info;
                        self.nickname = e.data.data.nickname;
                        self.role_type = e.data.data.role_type;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {

                });
            }
        }
    });
</script>
<style>
.user-finance-stat-row{margin-top:1px}
.user-finance-stat-row .el-col{padding:6px 5px;}
.l-label{background:#f1f1f1;text-align: right}
</style>
