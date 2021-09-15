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
                    <div style="color:green;font-size: 20px">+{{total_income}}元</div>
                    <div class="info-item-name">
                        <span>收入</span>
                        <el-tooltip class="item" effect="dark" :content="income_tip" placement="bottom">
                            <i class="el-icon-question"></i>
                        </el-tooltip>
                    </div>
                </div>

                <div class="num-info-item">
                    <div style="color:red;font-size: 20px">{{total_disburse}}元</div>
                    <div class="info-item-name">
                        <span>支出</span>
                        <el-tooltip class="item" effect="dark" :content="disburse_tip" placement="bottom">
                            <i class="el-icon-question"></i>
                        </el-tooltip>
                    </div>
                </div>
                <div class="num-info-item">
                    <div style="font-size: 20px">{{total_profit}}元</div>
                    <div class="info-item-name">
                        <span>毛利</span>
                    </div>
                </div>
            </div>
        </el-card>

        <div class="table-body" style="padding: 20px" v-loading="switch_loading">

            <el-tabs v-model="search.type" type="card" @tab-click="handleClick">
                <el-tab-pane label="总收益" name="TotalRevenue"></el-tab-pane>
                <el-tab-pane label="红包" name="RedEnvelopes"></el-tab-pane>
                <el-tab-pane label="商户" name="Merchant"></el-tab-pane>
                <el-tab-pane label="管理员充值" name="adminRecharge"></el-tab-pane>
            </el-tabs>

            <div v-if="tab_index==0" style="display: flex;justify-content: space-between;flex-wrap: wrap;">
                <div class="num-info-item" style="width: 100%;margin-bottom: 25px">
                    <div style="display: flex;justify-content: space-evenly">
                        <div class="one_class">
                            <div class="two_class">总收益</div>
                            <div id="assets">{{list.count_income}}元</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">待结算</div>
                            <div id="assets">{{list.frozen_income}}元</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">已提现</div>
                            <div id="assets">{{list.cash_income}}元</div>
                        </div>
                    </div>
                </div>
                <div style="width: 100%">
                    <div style="float: left">
                        <el-tabs :tab-position="tabPosition"  @tab-click="TabClick" v-model="search.second_type">
                            <el-tab-pane label="待结算" name="ToSettled"></el-tab-pane>
                            <el-tab-pane label="已提现" name="WithdrawnCash"></el-tab-pane>
                        </el-tabs>
                    </div>
                    <div v-if="red_tab_index" style="float: left;width: 90%">
                        <el-table :data="list.income_list" border style="width: 100%">
                            <el-table-column prop="id" label="ID" width="180"></el-table-column>
                            <el-table-column prop="income" label="收益" width="180"></el-table-column>
                            <el-table-column prop="money" label="变动金额" width="180"></el-table-column>
                            <el-table-column prop="desc" label="说明"  width="650"></el-table-column>
                            <el-table-column prop="created_at" label="时间">
                                <template slot-scope="scope">
                                    <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
            </div>

            <div v-if="tab_index==1" style="display: flex;justify-content: space-between;flex-wrap: wrap;">
                <div class="num-info-item" style="width: 100%;margin-bottom: 25px">
                    <div style="display: flex;justify-content: space-evenly">
                        <div class="one_class">
                            <div class="two_class">总送出</div>
                            <div id="assets" >{{list.total_red_envelope}}红包</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">已使用</div>
                            <div id="assets">{{list.count_envelope}}红包</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">商品</div>
                            <div id="assets">{{list.order_envelope}}红包</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">商家</div>
                            <div id="assets">{{list.mch_envelope}}红包</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">酒店</div>
                            <div id="assets">{{list.hotel_envelope}}红包</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">大礼包</div>
                            <div id="assets">{{list.big_gift_envelope}}红包</div>
                        </div>
                    </div>
                </div>
                <div style="width: 100%">
                    <div style="float: left">
                        <el-tabs :tab-position="tabPosition"  @tab-click="TabClick" v-model="search.second_type">
                            <el-tab-pane label="商品" name="RedEnvelopesGoods">
                                <el-table :data="list.envelope_list" border style="width: 100%">
                                    <el-table-column prop="id" label="ID" width="100"></el-table-column>
                                    <el-table-column prop="order_no" label="订单号（点击可跳转订单）" width="240">
                                        <template slot-scope="scope">
                                           <button @click="$navigate({r: 'mall/order/index', order_no:scope.row.order_no, keyword_1:1})">{{scope.row.order_no}}</button>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="total_goods_original_price" label="订单总原价"  width="200"></el-table-column>
                                    <el-table-column prop="total_pay_price" label="实际支付总费用(含运费）"  width="200"></el-table-column>
                                    <el-table-column prop="integral_deduction_price" label="红包抵扣价"  width="180"></el-table-column>
                                    <el-table-column prop="created_at" label="时间"  width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                            <el-tab-pane label="商家" name="RedEnvelopesMch">
                                <el-table :data="list.envelope_list" border style="width: 100%">
                                    <el-table-column prop="id" label="ID" width="100"></el-table-column>
                                    <el-table-column prop="mch_id" label="商户ID" width="100"></el-table-column>
                                    <el-table-column prop="order_no" label="订单号" width="240"></el-table-column>
                                    <el-table-column prop="order_price" label="订单金额"  width="200"></el-table-column>
                                    <el-table-column prop="pay_price" label="实际支付总费用"  width="200"></el-table-column>
                                    <el-table-column prop="integral_deduction_price" label="红包抵扣价"  width="180"></el-table-column>
                                    <el-table-column prop="created_at" label="时间"  width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                            <el-tab-pane label="酒店" name="RedEnvelopesHotel">
                                <el-table :data="list.envelope_list" border style="width: 100%">
                                    <el-table-column prop="id" label="ID" width="100"></el-table-column>
                                    <el-table-column prop="user_id" label="用户ID" width="100"></el-table-column>
                                    <el-table-column prop="hotel_id" label="酒店ID" width="100"></el-table-column>
                                    <el-table-column prop="order_no" label="订单号" width="240"></el-table-column>
                                    <el-table-column prop="order_price" label="订单金额"  width="200"></el-table-column>
                                    <el-table-column prop="pay_price" label="实际支付总费用"  width="200"></el-table-column>
                                    <el-table-column prop="integral_deduction_price" label="红包抵扣价"  width="180"></el-table-column>
                                    <el-table-column prop="created_at" label="时间"  width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                            <el-tab-pane label="大礼包" name="RedEnvelopesGiftBag">
                                <el-table :data="list.envelope_list" border style="width: 100%">
                                    <el-table-column prop="id" label="ID" width="100"></el-table-column>
                                    <el-table-column prop="pack_id" label="礼包ID" width="100"></el-table-column>
                                    <el-table-column prop="order_sn" label="订单号" width="240"></el-table-column>
                                    <el-table-column prop="order_price" label="订单金额"  width="200"></el-table-column>
                                    <el-table-column prop="pay_price" label="实际支付总费用"  width="200"></el-table-column>
                                    <el-table-column prop="integral_deduction_price" label="红包抵扣价"  width="180"></el-table-column>
                                    <el-table-column prop="created_at" label="时间"  width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                        </el-tabs>
                    </div>
                </div>
            </div>

            <div v-if="tab_index==2" style="display: flex;justify-content: space-between;flex-wrap: wrap;">
                <div class="num-info-item" style="width: 100%;margin-bottom: 25px">
                    <div style="display: flex;justify-content: space-evenly">
                        <div class="one_class">
                            <div class="two_class">总收入</div>
                            <div id="assets">{{list.CheckoutPrice}}元</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">已提现</div>
                            <div id="assets">{{list.withdrawn_cash}}元</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">未提现</div>
                            <div id="assets">{{list.No_cash_withdrawal}}元</div>
                        </div>
                    </div>
                </div>
                <div style="width: 100%">
                    <div style="float: left">
                        <el-tabs :tab-position="tabPosition"  @tab-click="TabClick" v-model="search.second_type">
                            <el-tab-pane label="总收入" name="TotalRevenue">
                                <el-table :data="list.withdrawal_list" border style="width: 100%">
                                    <el-table-column prop="id" label="ID" width="80"></el-table-column>
                                    <el-table-column prop="mch_id" label="商户ID" width="80"></el-table-column>
                                    <el-table-column prop="order_no" label="订单号" width="280"></el-table-column>
                                    <el-table-column prop="order_price" label="订单金额" width="180"></el-table-column>
                                    <el-table-column prop="pay_price" label="实际支付金额" width="180"></el-table-column>
                                    <el-table-column prop="score_deduction_price" label="积分抵扣金额"  width="180"></el-table-column>
                                    <el-table-column prop="integral_deduction_price" label="红包抵扣价"  width="180"></el-table-column>
                                    <el-table-column prop="created_at" label="时间" width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                            <el-tab-pane label="已提现" name="Withdrawal">
                                <el-table :data="list.withdrawal_list" border style="width: 100%">
                                    <el-table-column prop="id" label="ID" width="180"></el-table-column>
                                    <el-table-column prop="mch_id" label="商户ID" width="180"></el-table-column>
                                    <el-table-column prop="fact_price" label="实际到账" width="180"></el-table-column>
                                    <el-table-column prop="service_fee_rate" label="提现手续费"  width="180"></el-table-column>
                                    <el-table-column prop="content" label="说明"  width="180"></el-table-column>
                                    <el-table-column prop="created_at" label="时间" width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                            <el-tab-pane label="未提现" name="NoCashWithdrawal">
                                <el-table :data="list.withdrawal_list" border style="width: 100%">
                                    <el-table-column prop="id" label="商户ID" width="180"></el-table-column>
                                    <el-table-column prop="user_id" label="用户ID" width="180"></el-table-column>
                                    <el-table-column prop="account_money" label="账户余额" width="180"></el-table-column>
                                    <el-table-column prop="created_at" label="时间" width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                        </el-tabs>
                    </div>
                </div>
            </div>

            <div v-if="tab_index==3" style="display: flex;justify-content: space-between;flex-wrap: wrap;">
                <div class="num-info-item" style="width: 100%;margin-bottom: 25px">
                    <div style="display: flex;justify-content: space-evenly">
                        <div class="one_class">
                            <div class="two_class">红包</div>
                            <div id="assets">{{list.integral}}红包</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">收益</div>
                            <div id="assets">{{list.Income}}元</div>
                        </div>
                        <div class="one_class">
                            <div class="two_class">购物券</div>
                            <div id="assets">{{list.ShoppingVoucher}}购物券</div>
                        </div>
                    </div>
                </div>
                <div style="width: 100%">
                    <div style="float: left">
                        <el-tabs :tab-position="tabPosition"  @tab-click="TabClick" v-model="search.second_type">
                            <el-tab-pane label="红包" name="envelopes">
                                <el-table :data="list.oper_list" border style="width: 100%">
                                    <el-table-column prop="id" label="ID" width="180"></el-table-column>
                                    <el-table-column prop="user_id" label="用户ID" width="180"></el-table-column>
                                    <el-table-column prop="integral" label="变动红包" width="180"></el-table-column>
                                    <el-table-column prop="current_integral" label="当前红包"  width="180"></el-table-column>
                                    <el-table-column prop="desc" label="说明"  width="380"></el-table-column>
                                    <el-table-column prop="created_at" label="时间" width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                            <el-tab-pane label="收益" name="NoCashWithdrawal">
                                <el-table :data="list.oper_list" border style="width: 100%">
                                    <el-table-column prop="id" label="ID" width="100"></el-table-column>
                                    <el-table-column prop="user_id" label="用户ID" width="100"></el-table-column>
                                    <el-table-column prop="income" label="收益" width="180"></el-table-column>
                                    <el-table-column prop="money" label="变动金额" width="180"></el-table-column>
                                    <el-table-column prop="desc" label="说明"  width="500"></el-table-column>
                                    <el-table-column prop="created_at" label="时间" width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                            <el-tab-pane label="购物券" name="ShoppingVoucher">
                                <el-table :data="list.oper_list" border style="width: 100%">
                                    <el-table-column prop="id" label="ID" width="180"></el-table-column>
                                    <el-table-column prop="user_id" label="用户ID" width="180"></el-table-column>
                                    <el-table-column prop="money" label="充值购物券" width="180"></el-table-column>
                                    <el-table-column prop="current_money" label="充值前购物券" width="180"></el-table-column>
                                    <el-table-column prop="desc" label="说明"  width="380"></el-table-column>
                                    <el-table-column prop="created_at" label="时间" width="180">
                                        <template slot-scope="scope">
                                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-tab-pane>
                        </el-tabs>
                    </div>
                </div>
            </div>

            <div style="margin-top: 10px;" flex="box:last cross:center">
                <div style="margin: auto">
                    <el-pagination
                            v-if="pagination"
                            style="display: inline-block;float: right;"
                            background
                            :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next"
                            :current-page="pagination.current_page"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </div>
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
                    type:'TotalRevenue',
                    second_type:'ToSettled',
                },
                loading: false,
                switch_loading:false,
                total_income: 0.00,
                total_disburse: 0.00,
                Statistics:'',
                tab_index:0,
                red_tab_index:false,
                tabPosition:'left',
                page:1,
                list:'',
                pagination:'',
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
            },
            total_profit:function(){
                var profit = this.total_income + this.total_disburse;
                profit = profit.toFixed(2);
                return profit;
            }
        },
        methods: {
            getStat(){
                var self = this;
                this.loading = true;
                this.switch_loading = true;
                request({
                    params: {
                        r: 'plugin/finance_analysis/mall/finance/income-stat',
                        date_start: self.search.date_start,
                        date_end: self.search.date_end,
                        time_start: self.time_start,
                        type: self.search.type,
                        second_type: self.search.second_type,
                        page: self.page,
                    },
                    method: 'get',
                }).then(e => {
                    self.loading = false;
                    self.switch_loading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.pagination = e.data.data.pagination;
                        self.total_income = e.data.data.business.total_income;
                        self.total_disburse = e.data.data.business.total_disburse;
                        if(self.total_disburse != 0 ){
                            self.total_disburse = -1 * self.total_disburse;
                        }
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
            handleClick(e) {
                this.switch_loading = true;
                this.tab_index = e.index;
                if (this.tab_index == 0) {
                    this.search.second_type='ToSettled';
                } else if (this.tab_index == 1) {
                    this.search.second_type='RedEnvelopesGoods';
                } else if (this.tab_index == 2) {
                    this.search.second_type='TotalRevenue';
                } else if (this.tab_index == 3) {
                    this.search.second_type='envelopes';
                }
                this.page = 1;
                this.getStat();

            },
            TabClick(e) {
                console.log(e.index)
                this.red_tab_index = true;
                this.page = 1;
                this.switch_loading = true;
                this.getStat();
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getStat();
            },
        },
        created() {
            this.red_tab_index = true;
            this.getStat();
        }
    })
</script>

<style>
    .one_class {
        width: 30%;
    }

    .two_class {
        width: 100%;
        text-align: center!important;
    }


    .com-analysis-income {}

    .com-analysis-income .num-info {
        display: flex;
        width: 100%;
        color: #303133;
        margin-top:15px;
    }

    .com-analysis-income .num-info .num-info-item {
        text-align: center;
        width: 33.3%;
        border-left: 1px dashed #EFF1F7;
    }

    .com-analysis-income .num-info .num-info-item:first-of-type {
        border-left: 0;
    }

    .com-analysis-income .info-item-name {
        font-size: 16px;
        color: #92959B;
    }

    #assets {
        font-size: 20px;
        color: #1ed0ff;
        width: 100%;
        text-align: center;
    }

    .box-card-1 {
        width: 395px;
        height: 300px;
    }

    #make {
        margin-left: 20px;
        font-size: 20px;
    }

    #num_val {
        color: #1ed0ff;
    }
</style>