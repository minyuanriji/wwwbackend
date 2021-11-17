<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="全部" name="all"></el-tab-pane>
            <el-tab-pane label="支付成功" name="paid"></el-tab-pane>
            <el-tab-pane label="未支付" name="unpaid"></el-tab-pane>
            <el-tab-pane label="已退款" name="refund"></el-tab-pane>
            <div class="table-body">
                <span style="height: 32px;">支付时间：</span>
                <el-date-picker
                        class="item-box"
                        size="small"
                        @change="changeTime"
                        v-model="search.time"
                        type="datetimerange"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                </el-date-picker>

                <div class="input-item" style="width:300px;margin-bottom:20px;display:inline-block;">
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="订单ID/订单编号/大礼包名称" v-model="search.keyword" clearable @clear="toSearch">
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>

                <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px">

                    <el-table-column prop="id" label="ID" width="100"></el-table-column>

                    <el-table-column label="订单信息" width="650">
                        <template slot-scope="scope">
                            <div class="com-order-head" flex="cross:center">
                                <div class="com-order-time">创建时间：{{ scope.row.created_at|dateTimeFormat('Y-m-d H:i:s') }}</div>
                                <div class="com-order-user">
                                    <span class="com-order-time">订单号：</span>{{scope.row.order_sn}}
                                    <span class="com-order-pay-user" style="margin-left: 20px">支付用户：</span>{{scope.row.nickname}}({{scope.row.user_id}})
                                </div>
                            </div>
                            <div>
                                <com-image style="float: left;margin-right: 10px;height: 80px;width: 80px"
                                           :src="scope.row.cover_pic"></com-image>
                                <div style="margin: 10px 0">{{ scope.row.title }}（ID：{{scope.row.pack_id}}）</div>
                                小计：{{ scope.row.pay_type == 'integral' ? scope.row.integral_deduction_price : scope.row.pay_price}}
                                <span style="margin-left: 30px">数量：×1</span>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="支付状态" width="130">
                        <template slot-scope="scope">
                            <div v-if="scope.row.pay_status=='refund'" style="color: red">已退款</div>
                            <div v-if="scope.row.pay_status=='refunding'" style="color: red">退款中</div>
                            <div v-if="scope.row.pay_status=='paid'" style="color: green">已支付</div>
                            <div v-if="scope.row.pay_status=='unpaid'">未支付</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="实付金额" width="130">
                        <template slot-scope="scope">
                            <div>红包：{{scope.row.integral_deduction_price ?? 0}}</div>
                            <div v-if="scope.row.pay_type=='balance'">账户余额：{{scope.row.pay_price ?? 0}}</div>
                            <div v-if="scope.row.pay_type=='money'">现金：{{scope.row.pay_price ?? 0}}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="赠送" width="170">
                        <template slot-scope="scope">
                            <div v-if="scope.row.integral_enable == 1 && scope.row.is_integral == 1 && scope.row.pay_status == 'paid'">红包：{{scope.row.integral_give_num}}</div>
                            <div v-if="scope.row.score_enable == 1 && scope.row.pay_status == 'paid'">
                                <span v-if="scope.row.score_give_settings.is_permanent == 1">永久积分：{{scope.row.score_give_settings.integral_num}}</span>
                                <span v-else>
                                    限时积分：{{scope.row.score_give_settings.integral_num}}<br/>
                                    {{scope.row.score_give_settings.period}}月<br/>
                                    {{scope.row.score_give_settings.expire}}（天）有效期<br/>
                                </span>
                            </div>
                            <div>
                                购物券：{{scope.row.voucher_num}}&nbsp;&nbsp;&nbsp;
                                <span v-if="scope.row.voucher_status == 'success'" style="color: #13ce66">已发送</span>
                                <span v-if="scope.row.voucher_status == 'invalid'" style="color: red">无效</span>
                                <span v-if="scope.row.voucher_status == 'waiting'" style="color: orange">等待中</span>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="分润信息" width="420">
                        <template slot-scope="scope">
                            <div v-for="(nav,index) in scope.row.share_profit">
                                {{index}} : 昵称：{{nav.nickname}}(ID:{{nav.user_id}})
                                <span style="margin-left: 10px">佣金：{{ nav.price }}</span>
                                <span v-if="nav.status == -1">状态：无效</span>
                                <span v-if="nav.status == 0">状态：待结算</span>
                                <span v-if="nav.status == 1">状态：已结算</span>
                            </div>
                        </template>
                    </el-table-column>

                    <!--<el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button @click="toDetail(scope.row.id)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="查看订单详情" placement="top">
                                    <img src="statics/img/mall/order/detail.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button @click="destroy(scope.row.id, scope.$index)" circle type="text" size="mini">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>-->
                </el-table>
                <div flex="box:last cross:center">
                    <div></div>
                    <div>
                        <el-pagination
                                v-if="list.length > 0"
                                style="display: inline-block;float: right;"
                                background :page-size="pagination.pageSize"
                                @current-change="pageChange"
                                layout="prev, pager, next" :current-page="pagination.current_page"
                                :total="pagination.total_count">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </el-tabs>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                passengersData: [],
                search: {
                    keyword: '',
                    status: 'all',
                    start_time: '',
                    end_time: '',
                    time: null,
                },
                loading: false,
                activeName: 'all',
                list: [],
                pagination: null,
                exportList: [],
            };
        },
        mounted() {
            this.loadData();
        },
        methods: {
            // 日期搜索
            changeTime() {
                if (this.search.time) {
                    this.search.start_time = this.search.time[0];
                    this.search.end_time = this.search.time[1];
                } else {
                    this.search.start_time = null;
                    this.search.end_time = null;
                }
                this.loadData();
            },

            loadData(status = 'all', page = 1) {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/giftpacks/mall/giftpacks-order/index',
                        status: status,
                        page: page,
                        keyword: this.search.keyword,
                        start_time: this.search.start_time,
                        end_time: this.search.end_time,
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },

            toSearch() {
                this.page = 1;
                this.loadData(this.activeName);
            },

            showPassengers(row){
                this.passengersData = row.passengers;
                console.log(this.passengersData);
            },

            pageChange(page) {
                this.loadData(this.activeName, page);
            },

            handleClick(tab, event) {
                this.loadData(this.activeName)
            },

            toDetail(id){
                navigateTo({
                    r: 'plugin/baopin/mall/store/goods-list',
                    id: id
                })
            },
        }
    })
</script>
<style>
    .item {
        margin-top: 10px;
        margin-right: 40px;
    }
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .com-order-user {
        margin-left: 30px;
    }

    .com-order-head .com-order-time {
        color: #909399;
    }

    .export-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>