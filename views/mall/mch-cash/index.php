<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
        <el-form size="small" class="export-btn" :inline="true" :model="search">

        </el-form>
        <el-tabs v-model="activeName" @tab-click="handleClick">

            <el-tab-pane label="全部" name="all"></el-tab-pane>
            <el-tab-pane label="未审核" name="no_confirm"></el-tab-pane>
            <el-tab-pane label="待打款" name="no_paid"></el-tab-pane>
            <el-tab-pane label="已打款" name="paid"></el-tab-pane>
            <el-tab-pane label="拒绝" name="refuse"></el-tab-pane>
            <el-tab-pane label="已驳回" name="return"></el-tab-pane>

            <div class="table-body">

                <el-date-picker size="small" v-model="date" type="datetimerange"
                                style="float: left"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至" start-placeholder="开始日期"
                                @change="selectDateTime"
                                end-placeholder="结束日期">
                </el-date-picker>
                <div class="input-item" style="margin-left:15px;display:inline-block;width:300px;">
                    <el-input @keyup.enter.native="goSearch" size="small" placeholder="请输入商户名称搜索" v-model="search.keyword" clearable @clear="goSearch">
                        <el-button slot="append" icon="el-icon-search" @click="goSearch"></el-button>
                    </el-input>
                </div>
                <div style="margin: 30px 0">
                    <div style="display: flex;justify-content: space-evenly">
                        <div>
                            <div style="text-align: center">总申请提现金额</div>
                            <div id="assets">{{Statistics.applyMoney}}元</div>
                        </div>
                        <div>
                            <div style="text-align: center">总实际打款（已转账）</div>
                            <div id="assets">{{Statistics.actualMoney}}元</div>
                        </div>
                        <div>
                            <div style="text-align: center">当页申请提现金额</div>
                            <div id="assets">{{Statistics.currentApply}}元</div>
                        </div>
                        <div>
                            <div style="text-align: center">当页实际打款（已转账）</div>
                            <div id="assets">{{Statistics.currentActual}}元</div>
                        </div>
                    </div>
                </div>

                <el-table :data="list" size="small" border v-loading="loading" style="margin-top:20px;margin-bottom: 15px">
                    <el-table-column label="基本信息">
                        <template slot-scope="scope">
                            <com-image mode="aspectFill" :src="scope.row.cover_url" style="float: left;margin-right: 10px"></com-image>
                            <div>{{scope.row.name}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="账户信息">
                        <template slot-scope="scope">
                            <template v-if="scope.row.type == 'efps_bank'">
                                <div>开户人:{{scope.row.bankUserName}}</div>
                                <div>银行卡号:{{scope.row.bankCardNo}}</div>
                                <div>开户行:{{scope.row.bankName}}</div>
                                <div>账户类型:{{scope.row.bankAccountType}}</div>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column label="提现信息">
                        <template slot-scope="scope">
                            <div>申请提现金额:{{scope.row.money}}元</div>
                            <div>手续费:{{scope.row.service_fee_rate}}元</div>
                            <div>实际打款金额:<span style="color: #ff4544">{{scope.row.fact_price}}</span>元</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status==0">待处理</div>
                            <div v-if="scope.row.status==1">
                                <div v-if="scope.row.transfer_status==0">待转账</div>
                                <div v-if="scope.row.transfer_status==1" style="color:green">已转账</div>
                                <div v-if="scope.row.transfer_status==2" style="color:#cc3311">拒绝转账</div>
                            </div>
                            <div v-if="scope.row.status==2" style="color:#cc3311">已驳回</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="时间">
                        <template slot-scope="scope">
                            <div>申请时间:{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            <div>更新时间:{{scope.row.updated_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="备注">
                        <template slot-scope="scope">
                            <div>{{scope.row.remark}}</div>
                            <div>{{scope.row.content}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">

                            <el-button @click="apply(scope.row, 'confirm')" v-if="scope.row.status == 0" size="mini" circle style="margin-top: 10px">
                                <el-tooltip class="item" effect="dark" content="同意" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>

                            <el-button @click="apply(scope.row, 'refuse')" v-if="scope.row.status != 2 && (scope.row.status != 1 || scope.row.transfer_status != 1)" size="mini" circle style="margin-left: 10px;margin-top: 10px">
                                <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>

                            <el-button @click="apply(scope.row, 'return')" v-if="(scope.row.status == 2 && scope.row.transfer_status == 0)" size="mini" circle style="margin-left: 10px;margin-top: 10px">
                                <el-tooltip class="item" effect="dark" content="退还账户余额" placement="top">
                                    <img src="statics/img/mall/balance.png" alt="">
                                </el-tooltip>
                            </el-button>

                            <el-button @click="apply(scope.row, 'paid')" v-if="scope.row.status == 1 && scope.row.transfer_status == 0" size="mini" circle style="margin-top: 10px">
                                <el-tooltip class="item" effect="dark" content="打款" placement="top">
                                    <img src="statics/img/mall/pay.png" alt="">
                                </el-tooltip>
                            </el-button>

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
                date: '',
                search: {
                    keyword: '',
                    status: '',
                    start_date: '',
                    end_at: ''
                },
                loading: false,
                activeName: '-1',
                list: [],
                pagination: null,
                exportList: [],
                Statistics: '',
            };
        },
        mounted() {
            this.loadData(this.activeName);
        },
        methods: {
            goSearch() {
                if (this.date == null) {
                    this.date = ''
                }
                this.loadData(this.activeName, 1)
            },

            selectDateTime(e) {
                if (e != null) {
                    this.search.start_date = e[0];
                    this.search.end_date = e[1];
                } else {
                    this.search.start_date = '';
                    this.search.end_date = '';
                }
                this.goSearch();
            },

            apply(row, act) {
                this.$prompt('请输入备注', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'mall/mch-cash/apply',
                                },
                                method: 'post',
                                data: {
                                    id: row.id,
                                    act: act,
                                    content: instance.inputValue,
                                }
                            }).then(e => {
                                instance.confirmButtonLoading = false;
                                if (e.data.code === 0) {
                                    this.loadData(this.activeName);
                                    done();
                                } else {
                                    instance.confirmButtonText = '确定';
                                    this.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                done();
                                instance.confirmButtonLoading = false;
                            });
                        }else{
                            done();
                        }
                    }
                });
            },
            confirmSubmit() {
                this.search.status = this.activeName
            },
            loadData(status = -1, page = 1) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/mch-cash/index',
                        status: status,
                        page: page,
                        start_date: this.search.start_date,
                        end_date: this.search.end_date,
                        keyword: this.search.keyword,
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.Statistics = e.data.data.Statistics;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            pageChange(page) {
                this.loadData(this.activeName, page);
            },
            handleClick(tab, event) {
                this.loadData(this.activeName)
            }
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
    #assets {
        font-size: 18px;
        color: #1ed0ff;
        margin-left: 10px;
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