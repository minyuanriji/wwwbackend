<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="全部" name="all"></el-tab-pane>
            <div class="table-body">
                <div class="input-item" style="width:300px;margin-bottom:20px;display:inline-block;">
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="手机号/订单号/ID" v-model="search.keyword" clearable @clear="toSearch">
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>

                <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px">

                    <el-table-column prop="id" label="ID" width="100"></el-table-column>

                    <el-table-column label="充值手机号" width="100">
                        <template slot-scope="scope">
                            <div>{{scope.row.mobile}}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="充值金额" width="100">
                        <template slot-scope="scope">
                            <div>{{scope.row.order_price}}元</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="下单用户" width="180">
                        <template slot-scope="scope">
                            <div>{{scope.row.nickname}}[ID:{{scope.row.user_id}}]</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="抵扣" width="230">
                        <template slot-scope="scope"  v-if="scope.row.pay_status != 'unpaid'">
                            <div>余额抵扣：<b style="color:#077a00">{{scope.row.pay_price}}元</b></div>
                            <div>红包抵扣：<b style="color:#cc3311">{{scope.row.integral_deduction_price}}元</b></div>
                        </template>
                    </el-table-column>

                    <el-table-column label="退款金额" width="230">
                        <template slot-scope="scope">
                            <div>余额抵扣：<b style="color:#077a00">{{scope.row.refund_price}}元</b></div>
                            <div>红包抵扣：<b style="color:#cc3311">{{scope.row.refund_integral}}元</b></div>
                        </template>
                    </el-table-column>

                    <el-table-column label="订单状态" width="150">
                        <template slot-scope="scope">
                            <div>{{scope.row.status}}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="时间" width="200">
                        <template slot-scope="scope">
                            <div>下单时间:{{scope.row.created_at}}</div>
                            <div>更新时间:{{scope.row.updated_at}}</div>
                            <div v-if="scope.row.pay_status != 'unpaid'">支付时间:{{scope.row.pay_at}}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="备注" width="200">
                        <template slot-scope="scope">
                            <div>{{scope.row.reason}}</div>
                        </template>
                    </el-table-column>

<!--                    <el-table-column label="操作"></el-table-column>-->
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

            loadData(status = 'all', page = 1) {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/addcredit/mall/order/order/refund-list',
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

                        console.log(e);
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
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
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