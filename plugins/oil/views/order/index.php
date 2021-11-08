<?php

?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="全部" name="all"></el-tab-pane>
            <el-tab-pane label="已支付" name="paid"></el-tab-pane>
            <el-tab-pane label="未支付" name="unpaid"></el-tab-pane>
            <div class="table-body">
                <span style="height: 32px;">下单时间：</span>
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
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="手机号/订单号/用户ID" v-model="search.keyword" clearable @clear="toSearch">
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>

                <el-table :data="list" size="medium" border v-loading="loading" style="margin-bottom: 15px">

                    <el-table-column prop="id" label="ID" width="100" align="center"></el-table-column>

                    <el-table-column label="订单信息" width="300">
                        <template slot-scope="scope">
                            <div><b>编号：</b>{{scope.row.order_no}}</div>
                            <div><b>日期：</b>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            <div><b>金额：</b><span style="color:darkgreen">100元</span></div>
                        </template>
                    </el-table-column>

                    <el-table-column label="产品信息" width="130">
                        <template slot-scope="scope">
                            <div>平台：{{scope.row.plat_name}}</div>
                            <div>面额：<span style="color:darkgreen">{{scope.row.product_price}}</span></div>
                        </template>
                    </el-table-column>

                    <el-table-column label="状态" width="100" align="center">
                        <template slot-scope="scope">
                            <span v-if="scope.row.real_status == 'unconfirmed'" style="color:royalblue">{{scope.row.status_text}}</span>
                            <span v-if="scope.row.real_status == 'wait'" style="color:green">{{scope.row.status_text}}</span>
                            <span v-if="scope.row.real_status == 'refund' || scope.row.real_status == 'refunding' || scope.row.real_status == 'fail'" style="color:darkred">{{scope.row.status_text}}</span>
                            <span v-if="scope.row.real_status == 'finished' || scope.row.real_status == 'expired' || scope.row.real_status == 'invalid'" style="color:gray">{{scope.row.status_text}}</span>
                            <span v-if="scope.row.real_status == 'unpaid'">{{scope.row.status_text}}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="支付信息" width="260" >
                        <template slot-scope="scope">
                            <span v-if="scope.row.pay_status == 'paid'">
                                <div>支付日期：{{scope.row.pay_at}}</div>
                                <div>使用现金：<span style="color:darkgreen">{{scope.row.pay_price}}元</span></div>
                                <div>红包抵扣：<span style="color:darkred">{{scope.row.integral_deduction_price}}</span></div>
                            </span>
                            <span v-else>-</span>
                        </template>
                    </el-table-column>


                    <el-table-column label="用户信息" width="230">
                        <template slot-scope="scope">
                            <div>昵称：<span style="color:steelblue">{{scope.row.nickname}}</span>[ID:{{scope.row.user_id}}]</div>
                            <div>手机：{{scope.row.mobile}}</div>
                        </template>
                    </el-table-column>


                    <el-table-column label="位置" width="230">
                        <template slot-scope="scope">
                            <div>{{scope.row.province}}{{scope.row.city}}{{scope.row.district}}</div>
                            <div>{{scope.row.address}}</div>
                        </template>
                    </el-table-column>


                    <el-table-column label="操作">
                        <template slot-scope="scope">

                        </template>
                    </el-table-column>
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
                    date_start: '',
                    date_end: '',
                    time: null,
                },
                loading: false,
                activeName: 'all',
                list: [],
                pagination: null
            };
        },
        mounted() {
            this.loadData();
        },
        methods: {
            // 日期搜索
            changeTime() {
                if (this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.loadData();
            },
            loadData(status = 'all', page = 1) {
                this.loading = true;
                let that = this;
                request({
                    params: {
                        r: 'plugin/oil/mall/order/list',
                        status: status,
                        page: page,
                        keyword: this.search.keyword,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        //that.queryStatus(0, e.data.data.list);
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
            pageChange(page) {
                this.loadData(this.activeName, page);
            },
            handleClick(tab, event) {
                this.loadData(this.activeName)
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