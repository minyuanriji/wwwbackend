<div id="app" v-cloak>
    <el-tabs v-model="activeName" @tab-click="handleClick" style="border-radius: 15px">

        <el-tab-pane v-for="item in header_tab" :label="item.label" :name="item.name"></el-tab-pane>

        <el-card style="border-radius: 15px">
            <div style="float: right;margin-right: 10px;margin-bottom: 10px">
                <com-export-dialog :field_list='export_list'
                                   :params="searchData"
                                   @selected="exportConfirm">
                </com-export-dialog>
            </div>
            <div style="margin-top:10px" v-loading="statisticsLoading">
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
        </el-card>

        <el-card style="border-radius: 15px;margin-top: 15px;">
            <div style="display: flex;justify-content: space-evenly;">
                <div style="width: 30%">
                    <div style="float: left;margin-top: 5px">打款时间：</div>
                    <el-date-picker size="small" v-model="date" type="datetimerange"
                                    style="float: left"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    range-separator="至" start-placeholder="开始日期"
                                    @change="selectDateTime"
                                    end-placeholder="结束日期">
                    </el-date-picker>
                </div>
                <div style="width: 20%">
                    <el-input @keyup.enter.native="goSearch" size="small" placeholder="请输入搜索"
                              v-model="search.keyword" clearable @clear="goSearch" @input="triggeredChange">
                        <el-select slot="prepend" v-model="search.kw_type" placeholder="请选择" size="small" style="width:120px;">
                            <el-option v-for="item in select_keyword_option" :label="item.label" :key="item.value" :value="item.value"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="goSearch"></el-button>
                    </el-input>
                </div>
                <div style="width: 16%">
                    等级
                    <el-select size="small" v-model="level" placeholder="请选择区域等级" @change="levelChange">
                        <el-option
                                v-for="item in level_list"
                                :label="item.name"
                                :value="item.level">
                        </el-option>
                    </el-select>
                </div>
                <div style="width: 20%" v-if="level>0">
                    省市区
                    <el-cascader
                            size="small"
                            @change="addressChange"
                            :options="district"
                            :props="props"
                            v-model="address">
                    </el-cascader>
                </div>
            </div>
        </el-card>

        <el-card shadow="never" style="border:0;border-radius: 15px;margin-top: 15px"
                 body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
            <div class="table-body">
                <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px">
                    <el-table-column label="基本信息">
                        <template slot-scope="scope">
                            <com-image mode="aspectFill" :src="scope.row.cover_url"
                                       style="float: left;margin-right: 10px"></com-image>
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

                            <el-button @click="apply(scope.row, 'confirm')" v-if="scope.row.status == 0" size="mini"
                                       circle style="margin-top: 10px">
                                <el-tooltip class="item" effect="dark" content="同意" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>

                            <el-button @click="apply(scope.row, 'refuse')"
                                       v-if="scope.row.status != 2 && (scope.row.status != 1 || scope.row.transfer_status != 1)"
                                       size="mini" circle style="margin-left: 10px;margin-top: 10px">
                                <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>

                            <el-button @click="apply(scope.row, 'return')"
                                       v-if="(scope.row.status == 2 && scope.row.transfer_status == 0)" size="mini"
                                       circle style="margin-left: 10px;margin-top: 10px">
                                <el-tooltip class="item" effect="dark" content="退还账户余额" placement="top">
                                    <img src="statics/img/mall/balance.png" alt="">
                                </el-tooltip>
                            </el-button>

                            <el-button @click="apply(scope.row, 'paid')"
                                       v-if="scope.row.status == 1 && scope.row.transfer_status == 0" size="mini" circle
                                       style="margin-top: 10px">
                                <el-tooltip class="item" effect="dark" content="打款" placement="top">
                                    <img src="statics/img/mall/pay.png" alt="">
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
                    status: '',
                    start_date: '',
                    end_at: ''
                },
                loading: false,
                statisticsLoading: false,
                activeName: '-1',
                list: [],
                pagination: null,
                exportList: [],
                Statistics: '',
                level_list: [
                    {
                        name: '省',
                        level: 1
                    },
                    {
                        name: '市',
                        level: 2
                    },
                    {
                        name: '区',
                        level: 3
                    },
                ],
                level: '',
                address: null,
                district: [],
                town_list: [],
                province_id: 0,
                city_id: 0,
                district_id: 0,
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
                export_list: [],
                searchData: {
                    keyword: '',
                    kw_type: '',
                    start_date: '',
                    end_date: '',
                    status: '',
                    level: '',
                    address: null,
                },
                header_tab:[
                    {
                        label:'全部',
                        name:'all'
                    },
                    {
                        label:'未审核',
                        name:'no_confirm'
                    },
                    {
                        label:'待打款',
                        name:'no_paid'
                    },
                    {
                        label:'已打款',
                        name:'paid'
                    },
                    {
                        label:'拒绝',
                        name:'refuse'
                    },
                    {
                        label:'已驳回',
                        name:'return'
                    },
                ],
                select_keyword_option:[
                    {
                        label:'店铺名',
                        value:'store_name',
                    },
                    {
                        label:'商户ID',
                        value:'mch_id',
                    },
                    {
                        label:'手机号',
                        value:'mobile',
                    },
                ],
            };
        },
        mounted() {
            this.loadData(this.activeName);
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
                        } else {
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
                        kw_type: this.search.kw_type,
                        level: this.level,
                        address: this.address,
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.export_list = e.data.data.export_list;
                        this.statisticsLoadData(status, page);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            statisticsLoadData(status = -1, page = 1) {
                this.statisticsLoading = true;
                request({
                    params: {
                        r: 'mall/mch-cash/statistics',
                        status: status,
                        page: page,
                        start_date: this.search.start_date,
                        end_date: this.search.end_date,
                        keyword: this.search.keyword,
                        kw_type: this.search.kw_type,
                        level: this.level,
                        address: this.address,
                    },
                    method: 'get'
                }).then(e => {
                    this.statisticsLoading = false;
                    if (e.data.code == 0) {
                        this.Statistics = e.data.data.Statistics;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.statisticsLoading = false;
                });
            },
            pageChange(page) {
                this.loadData(this.activeName, page);
            },
            handleClick(tab, event) {
                this.loadData(this.activeName)
            },
            exportConfirm() {
                this.searchData.keyword = this.search.keyword;
                this.searchData.kw_type = this.search.kw_type;
                this.searchData.start_date = this.search.start_date;
                this.searchData.end_date = this.search.end_date;
                this.searchData.level = this.level;
                this.searchData.address = this.address;
                this.searchData.status = this.status;
            },
            levelChange(e) {
                this.getDistrict(e);
            },
            // 获取省市区列表
            getDistrict(level) {
                if (level == 1) {
                    level1 = 1;
                } else if (level == 2) {
                    level1 = 2;
                } else if (level == 3) {
                    level1 = 3;
                } else {
                    level1 = 4;
                }
                request({
                    params: {
                        r: 'district/index',
                        level: level1
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {
                });
            },
            addressChange(e) {
                this.town_list = []
                this.page = 1;
                this.loadData();
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

    #assets {
        font-size: 18px;
        color: #1ed0ff;
        margin-left: 10px;
    }

    .table-body {
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>