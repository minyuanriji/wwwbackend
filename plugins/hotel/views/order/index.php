
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="全部" name="all"></el-tab-pane>
            <el-tab-pane label="待确认" name="unconfirmed"></el-tab-pane>
            <el-tab-pane label="已确认" name="confirmed"></el-tab-pane>
            <el-tab-pane label="预订失败" name="fail"></el-tab-pane>
            <el-tab-pane label="待支付" name="unpaid"></el-tab-pane>
            <el-tab-pane label="已结束" name="finished"></el-tab-pane>
            <el-tab-pane label="已取消" name="cancel"></el-tab-pane>
            <div class="table-body">
                <div class="input-item" style="width:300px;margin-bottom:20px;">
                    <el-input @keyup.enter.native="search" size="small" placeholder="请输入昵称搜索" v-model="search.keyword" clearable @clear="search">
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                </div>

                <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px">
                    <el-table-column label="订单信息" width="230">
                        <template slot-scope="scope">
                            <div>订单单号：{{scope.row.booking_arrive_date}}</div>
                            <div>订单金额：{{scope.row.order_price}}元</div>
                            <div>下单用户：{{scope.row.nickname}}[ID:{{scope.row.user_id}}]</div>
                            <div>支付金额：{{scope.row.pay_price}}元</div>
                            <div>红包抵扣：{{scope.row.integral_deduction_price}}元</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="预订信息" width="230">
                        <template slot-scope="scope">
                            <div>酒店名称：{{scope.row.hotel_name}}</div>
                            <div>入住时间：{{scope.row.booking_start_date}}</div>
                            <div>离店时间：2021/07/03</div>
                            <div>到店时间：{{scope.row.booking_arrive_date}}</div>
                            <div>房间数量：{{scope.row.booking_num}}间</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="订单状态" width="150">
                        <template slot-scope="scope">
                            <div>待付款</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="时间" width="200">
                        <template slot-scope="scope">
                            <div>下单时间:{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            <div>更新时间:{{scope.row.updated_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            <div>支付时间:{{scope.row.pay_at}}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="操作"></el-table-column>
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
                search: {
                    keyword: '',
                    status: 'all',
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
                        r: 'plugin/hotel/mall/order/list',
                        status: status,
                        page: page,
                        keyword: this.search.keyword
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
            search() {
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