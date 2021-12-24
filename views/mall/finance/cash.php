<?php
Yii::$app->loadComponentView('com-user-finance-stat');
?>

<div id="app" v-cloak>
    <el-tabs v-model="activeName" @tab-click="handleClick">
        <el-tab-pane v-for="item in header_tab" :name="item.name" :label="item.label"></el-tab-pane>

        <el-card style="border-radius: 15px;margin-bottom: 10px">
            <div style="float: right">
                <com-export-dialog :field_list='exportList' :params="searchData" @selected="exportConfirm"></com-export-dialog>
            </div>
            <div >
                <div style="display: flex;justify-content: space-evenly">
                    <div>
                        <div style="text-align: center">总申请提现金额</div>
                        <div id="assets">{{Statistics.applyMoney}}元</div>
                    </div>
                    <div>
                        <div style="text-align: center">总实际打款（已打款）</div>
                        <div id="assets">{{Statistics.actualMoney}}元</div>
                    </div>
                    <div>
                        <div style="text-align: center">当页申请提现金额</div>
                        <div id="assets">{{Statistics.currentApply}}元</div>
                    </div>
                    <div>
                        <div style="text-align: center">当页实际打款（已打款）</div>
                        <div id="assets">{{Statistics.currentActual}}元</div>
                    </div>
                </div>
            </div>
        </el-card>

        <el-card style="border-radius: 15px;margin-top: 15px;margin-bottom: 10px">
            <div style="float: left;margin-top: 5px">打款时间：</div>
            <el-date-picker size="small" v-model="date" type="datetimerange"
                            style="float: left"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至" start-placeholder="开始日期"
                            @change="selectDateTime"
                            end-placeholder="结束日期">
            </el-date-picker>
            <div class="input-item" style="margin-left:15px;display:inline-block;width:350px;">
                <el-input @keyup.enter.native="goSearch" size="small" placeholder="请输入搜索" v-model="search.keyword" clearable @clear="goSearch" @input="triggeredChange">
                    <el-select slot="prepend" v-model="search.kw_type" placeholder="请选择" size="small" style="width:120px;">
                        <el-option v-for="item in select_keyword_option" :label="item.label" :key="item.value" :value="item.value"></el-option>
                    </el-select>
                    <el-button slot="append" icon="el-icon-search" @click="goSearch"></el-button>
                </el-input>
            </div>
        </el-card>

        <el-card shadow="never" style="border:0;" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
            <div class="table-body">
                <el-table :data="list" size="small" border v-loading="loading" style="margin-top:20px;margin-bottom: 15px">
                    <el-table-column label="基本信息">
                        <template slot-scope="scope">
                            <com-image mode="aspectFill" :src="scope.row.user.avatar" style="float: left;margin-right: 10px"></com-image>
                            <com-user-finance-stat :user-id="parseInt(scope.row.user_id)">
                                {{scope.row.user.nickname}}
                            </com-user-finance-stat>
                            <img src="statics/img/mall/wx.png" v-if="scope.row.user.platform == 'wxapp'" alt="">
                            <img src="statics/img/mall/ali.png" v-else-if="scope.row.user.platform == 'aliapp'" alt="">
                            <img src="statics/img/mall/toutiao.png" v-else-if="scope.row.user.platform == 'ttapp'" alt="">
                            <img src="statics/img/mall/baidu.png" v-else-if="scope.row.user.platform == 'bdapp'" alt="">
                        </template>
                    </el-table-column>
                    <el-table-column label="账户信息">
                        <template slot-scope="scope">
                            <template v-if="scope.row.type == 'wechat'">
                                <div style="text-align: center"> 微信收款码
                                    <br>
                                    <img :src="scope.row.extra.wechat_qrcode" alt=""></div>
                            </template>
                            <template v-if="scope.row.type == 'alipay'">
                                <div>支付宝姓名:{{scope.row.extra.name}}</div>
                                <div>支付宝账号:{{scope.row.extra.mobile}}</div>
                            </template>
                            <template v-if="scope.row.type == 'bank'">
                                <div>开户人:{{scope.row.extra.name}}</div>
                                <div>银行卡号:{{scope.row.extra.bank_account}}</div>
                                <div>开户行:{{scope.row.extra.bank_name}}</div>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column label="提现信息">
                        <template slot-scope="scope">
                            <div>用户申请提现金额:{{scope.row.cash.price}}元</div>
                            <div>手续费:{{scope.row.cash.service_fee_rate}}元</div>
                            <div>实际打款金额:<span style="color: #ff4544">{{scope.row.cash.fact_price}}</span>元</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态" prop="status_text">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status==0">待审核</div>
                            <div v-if="scope.row.status==1">待打款</div>
                            <div v-if="scope.row.status==2">已打款</div>
                            <div v-if="scope.row.status==3">已驳回</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="时间">
                        <template slot-scope="scope">
                            <div>申请时间:{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            <div v-if="scope.row.status == 1">审核时间:{{scope.row.updated_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            <div v-if="scope.row.status == 2">打款时间:{{scope.row.updated_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            <div v-if="scope.row.status == 3">驳回时间:{{scope.row.updated_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                        </template>
                    </el-table-column>
                 <el-table-column label="备注">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status == 1 || scope.row.status == 2">审核备注:{{scope.row.content.validate_content}}</div>
                            <div v-if="scope.row.status == 2">打款备注:{{scope.row.content.remittance_content}}</div>
                            <div v-if="scope.row.status == 3">驳回备注:{{scope.row.content.reject_content}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button size="mini" circle style="margin-top: 10px" v-if="scope.row.status == 0" @click="apply(scope.row, 1)">
                                <el-tooltip class="item" effect="dark" content="同意" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button size="mini" circle style="margin-top: 10px" v-if="scope.row.status == 2 && scope.row.is_transmitting == 1" @click="applyDetail(scope.row)">
                                <el-tooltip class="item" effect="dark" content="核实" placement="top">
                                    <img src="statics/img/mall/detail.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button size="mini" circle v-if="scope.row.status == 1" style="margin-top: 10px" @click="apply(scope.row, 2)">
                                <el-tooltip class="item" effect="dark" content="打款" placement="top">
                                    <img src="statics/img/mall/pay.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button size="mini" circle style="margin-left: 10px;margin-top: 10px" v-if="scope.row.status < 2" @click="apply(scope.row, 3)">
                                <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div style="text-align: center">
                    <el-pagination
                        v-if="list.length > 0"
                        style="margin-top:20px;"
                        background :page-size="pagination.pageSize"
                        @current-change="pageChange"
                        layout="prev, pager, next" :current-page="pagination.current_page"
                        :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </el-card>
    </el-tabs>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                date: '',
                search: {
                    keyword: '',
                    kw_type: '',
                    status: -1,
                    start_date: '',
                    end_at: ''
                },
                loading: false,
                activeName: '-1',
                list: [],
                pagination: null,
                exportList: [],
                Statistics: '',
                searchData: {
                    keyword: '',
                    kw_type: '',
                    start_date: '',
                    end_date: '',
                    status: '',
                },
                header_tab: [
                    {
                        label:'全部',
                        name: -1,
                    },
                    {
                        label:'未审核',
                        name: 0,
                    },
                    {
                        label:'待打款',
                        name: 1,
                    },
                    {
                        label:'已打款',
                        name: 2,
                    },
                    {
                        label:'驳回',
                        name: 3,
                    },
                ],
                select_keyword_option:[
                    {
                        label:'手机号',
                        value:'mobile',
                    },
                    {
                        label:'昵称',
                        value:'nickname',
                    },
                    {
                        label:'用户ID',
                        value:'user_id',
                    },
                ],
            };
        },
        mounted() {
            this.loadData();
        },
        methods: {
            triggeredChange (){
                if (this.search.keyword.length>0 && this.search.kw_type.length<=0) {
                    alert('请选择搜索方式');
                    this.search.keyword='';
                }
            },
            goSearch() {
                if (this.date == null) {
                    this.date = ''
                }
                this.loadData(this.activeName, 1)
            },
            exportConfirm() {
                this.searchData.keyword = this.search.keyword;
                this.searchData.kw_type = this.search.kw_type;
                this.searchData.start_date = this.search.start_date;
                this.searchData.end_date = this.search.end_date;
                this.searchData.status = this.search.status;
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
            confirmSubmit() {
                this.search.status = this.activeName
            },
            loadData(status = -1, page = 1) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/finance/cash',
                        status: status,
                        page: page,
                        start_date: this.search.start_date,
                        end_date: this.search.end_date,
                        keyword: this.search.keyword,
                        kw_type: this.search.kw_type,
                        user_id: getQuery('user_id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.Statistics = e.data.data.Statistics;
                        this.pagination = e.data.data.pagination;
                        this.exportList = e.data.data.export_list;
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
            },
            apply(cash, status) {
                this.$prompt('请输入备注', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'mall/finance/cash-apply',
                                },
                                method: 'post',
                                data: {
                                    id: cash.id,
                                    status: status,
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
                        } else {
                            done();
                        }
                    }
                }).then(value => {

                }).catch(e => {
                    this.$message({
                        type: 'info',
                        message: '取消输入'
                    });
                });
            },
            applyDetail(cash){
                this.loading = true;
                var self = this;
                request({
                    params: {
                        r: 'mall/finance/cash-transmit-check',
                    },
                    method: 'post',
                    data: {
                        id: cash.id
                    }
                }).then(e => {
                    self.loading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                    self.loadData(self.activeName);
                }).catch(e => {
                    self.loading = false;
                });
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

    #assets {
        font-size: 18px;
        color: #1ed0ff;
        margin-left: 10px;
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