

<template id="com-analysis-income">
    <div class="com-analysis-income">
        <div flex="cross:center" >
            <el-date-picker
                    size="small"
                    style="width: 350px"
                    v-model="search.date_range"
                    @change="changeTime"
                    type="daterange"
                    value-format="yyyy-MM-dd"
                    range-separator="至"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期">
            </el-date-picker>
            <el-tabs @tab-click="tabTotal" v-model="search.s_date" style="margin-left:15px;">
                <el-tab-pane label="1小时内" name="hour"></el-tab-pane>
                <el-tab-pane label="1天内" name="day"></el-tab-pane>
                <el-tab-pane label="最近一周" name="week"></el-tab-pane>
                <el-tab-pane label="1个月" name="month"></el-tab-pane>
                <el-tab-pane label="1年" name="year"></el-tab-pane>
            </el-tabs>

        </div>

        <el-card class="box-card" style="margin-top:5px;" v-loading="loading">

            <!-- 数据总览 -->
            <div class="num-info">
                <div class="num-info-item">
                    <div style="color:green">+{{total_income}}元</div>
                    <div class="info-item-name">
                        <span>收入</span>
                        <el-tooltip class="item" effect="dark" :content="income_tooltip" placement="bottom">
                            <i class="el-icon-question"></i>
                        </el-tooltip>
                    </div>
                </div>

                <div class="num-info-item">
                    <div style="color:red">-{{total_disburse}}元</div>
                    <div class="info-item-name">
                        <span>支出</span>
                        <el-tooltip class="item" effect="dark" :content="disburse_tip" placement="bottom">
                            <i class="el-icon-question"></i>
                        </el-tooltip>
                    </div>
                </div>

            </div>

        </el-card>
    </div>

</template>

<script>
    Vue.component('com-analysis-income', {
        template: '#com-analysis-income',
        data() {
            return {
                search:{
                    s_date: 'hour',
                    date_range: null,
                    date_start: null,
                    date_end: null,
                },
                loading: false,
                total_income: 0.00,
                total_disburse: 0.00
            }
        },
        computed:{
            income_tip:function(){
                return '平台现金收入，例如用户支付商品订单的收入';
            },
            disburse_tip:function(){
                return '平台现金支出，例如用户收益的提现支出';
            },
            time_start:function(){
                var s = {hour:3600};
                s['day'] = s.hour * 24;
                s['week'] = s.day * 7;
                s['month'] = s.week * 4;
                s['year'] = s.month * 12;
                if(this.search.s_date){
                    return s[this.search.s_date];
                }
                return 0;
            }
        },
        methods: {
            getStat(){
                var self = this;
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/finance_analysis/mall/finance/income-stat',
                        date_start: self.search.date_start,
                        date_end: self.search.date_end,
                        time_start: self.time_start
                    },
                    method: 'get',
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.total_income = e.data.data.total_income;
                        self.total_disburse = e.data.data.total_disburse;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.loading = false;
                });
            },
            tabTotal() {
                this.getStat();
            },
            // 自定义时间
            changeTime() {
                this.search.s_date = null;
                if (this.search.date_range) {
                    this.search.date_start = this.search.date_range[0];
                    this.search.date_end = this.search.date_range[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.getStat();
            },
        },
        created() {
            this.getStat();
        }
    })
</script>

<style>
    .com-analysis-income {}

    .com-analysis-income .num-info {
        display: flex;
        width: 100%;
        font-size: 28px;
        color: #303133;
        margin-top:15px;
    }

    .com-analysis-income .num-info .num-info-item {
        text-align: center;
        width: 50%;
        border-left: 1px dashed #EFF1F7;
    }

    .com-analysis-income .num-info .num-info-item:first-of-type {
        border-left: 0;
    }

    .com-analysis-income .info-item-name {
        font-size: 16px;
        color: #92959B;
    }

</style>