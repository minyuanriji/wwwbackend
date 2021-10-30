<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>账单记录</span>
                <div  v-loading="statisticsLoading">
                    <div style="display: flex;justify-content: space-evenly">
                        <div>
                            <div style="text-align: center;">总收入</div>
                            <div id="assets"><span style="color: #1ed0ff;font-size: 20px">{{Statistics.income}}</span>元</div>
                        </div>
                        <div>
                            <div style="text-align: center">当页收入</div>
                            <div id="assets"><span style="color: #1ed0ff;font-size: 20px">{{Statistics.currentIncome}}</span>元</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-body">

            <div style="display: flex;margin-bottom: 20px">
                <div>
                    支付状态
                    <el-select v-model="searchData.pay_status" placeholder="请选择" size="small" @change="search" style="width:110px;">
                        <el-option
                                v-for="item in pay_options"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </div>

                <div style="margin-left: 20px">
                    支付方式
                    <el-select v-model="searchData.pay_mode" placeholder="请选择" size="small" @change="search" style="width:110px;">
                        <el-option
                                v-for="item in pay_mode"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </div>

                <div style="margin-left: 20px">
                    <el-date-picker size="small" v-model="searchData.date" type="datetimerange"
                                    style="float: left"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    range-separator="至" start-placeholder="支付开始日期"
                                    @change="selectDateTime"
                                    end-placeholder="支付结束日期">
                    </el-date-picker>
                </div>

                <div style="margin-left: 20px">
                    <el-input style="width: 250px" size="small" v-model="searchData.keyword" placeholder="请输入搜索内容" clearable
                              @clear="clearSearch"
                              @change="search"
                              @input="triggeredChange">
                        <el-select style="width: 100px" slot="prepend" v-model="searchData.keyword_1">
                            <el-option v-for="item in selectList" :key="item.value"
                                       :label="item.name"
                                       :value="item.value">
                            </el-option>
                        </el-select>
                    </el-input>
                </div>

                <div style="margin-left: 20px">
                    <el-select size="small" v-model="level" placeholder="请选择区域" @change="levelChange" style="width: 120px">
                        <el-option
                                v-for="item in level_list"
                                :label="item.name"
                                :value="item.level">
                        </el-option>
                    </el-select>
                </div>
                <div style="margin-left: 20px" v-if="level>0">
                    省市区
                    <el-cascader
                            size="small"
                            @change="addressChange"
                            :options="district"
                            :props="props"
                            v-model="address">
                    </el-cascader>
                </div>

                <el-button @click="clearWhere" style="color: #1ed0ff;margin-left: 20px" size="small">清空筛选条件</el-button>

            </div>

            <el-table v-loading="listLoading" :data="list" border style="width: 100%">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="order_no" label="订单号" width="270"></el-table-column>
                <el-table-column :show-overflow-tooltip="true" label="店铺信息" width="280">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <com-image width="25" height="25" :src="scope.row.cover_url"></com-image>
                            <div style="margin-left: 10px;overflow:hidden;text-overflow: ellipsis;">
                                {{scope.row.name}}
                                （<span style="color: #1ed0ff">{{scope.row.discount}}折</span>）
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column label="支付状态" width="260">
                    <template slot-scope="scope">
                        <div v-if="scope.row.is_pay==1">
                            <div>支付用户：{{scope.row.nickname}}(ID：{{scope.row.pay_user_id}})</div>
                            <div>支付时间：{{scope.row.format_pay_time}}</div>
                        </div>
                        <div v-else style="color: red">未支付</div>
                    </template>
                </el-table-column>

                <el-table-column label="资金明细" width="160">
                    <template slot-scope="scope">
                        <div>支付现金：{{scope.row.pay_price}}</div>
                        <div>支付红包：{{scope.row.integral_deduction_price}}</div>
                        <div>总支付：{{scope.row.order_price}}</div>
                    </template>
                </el-table-column>

                <el-table-column label="赠送明细" width="260">
                    <template slot-scope="scope">
                        <div>
                            赠送购物券：{{scope.row.send_money}}
                            <span v-if="scope.row.send_status == 'invalid' || scope.row.send_status == ''" style="color: red">(无效)</span>
                            <span v-if="scope.row.send_status == 'success'" style="color: green">(已发送)</span>
                            <span v-if="scope.row.send_status == 'waiting'" style="color: red">(待发送)</span>
                        </div>
                        <div>
                            赠送积分：{{scope.row.score_money}}
                            <span v-if="scope.row.score_status == 'invalid' || scope.row.score_status == ''" style="color: red">(无效)</span>
                            <span v-if="scope.row.score_status == 'success'" style="color: green">(已发送)</span>
                            <span v-if="scope.row.score_status == 'waiting'" style="color: red">(待发送)</span>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column label="佣金明细">
                    <template slot-scope="scope">
                        <div>
                            直推：<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                昵称：{{scope.row.direct_push_user_nickname}}({{scope.row.direct_push_user_id}})&nbsp;&nbsp;
                                {{scope.row.direct_push_price}}&nbsp;&nbsp;
                                <span v-if="scope.row.direct_push_status == -1" style="color: red">无效</span>
                                <span v-if="scope.row.direct_push_status == 0">待结算</span>
                                <span v-if="scope.row.direct_push_status == 1" style="color: #13ce66">已结算</span>
                        </div>
                        <span>
                            消费：<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <span v-for="(item,index) in scope.row.consumption">
                                昵称：{{item.nickname}}({{item.user_id}})&nbsp;&nbsp;
                                {{item.price}}&nbsp;&nbsp;
                                <span v-if="item.status == -1" style="color: red">无效</span>
                                <span v-if="item.status == 0">待结算</span>
                                <span v-if="item.status == 1" style="color: #13ce66">已结算</span>
                                <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </span>
                        </span>
                    </template>
                </el-table-column>

                <!--<el-table-column :show-overflow-tooltip="true" label="操作">
                    <template slot-scope="scope">
                        <el-button @click="view(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="详情" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>-->

            </el-table>

            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                searchData: {
                    keyword: '',
                    keyword_1: '',
                    date: '',
                    start_date: '',
                    end_date: '',
                    pay_status: '',
                    pay_mode: '',
                },
                selectList: [
                    {value: '1', name: '支付用户昵称'},
                    {value: '2', name: '店铺名'},
                    {value: '3', name: '订单号'},
                ],
                Statistics:'',
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
                levelShow:false,
                list: [],
                listLoading: false,
                statisticsLoading: false,
                page: 1,
                pageCount: 0,
                id: null,
                pay_options: [
                    {label: "全部",   value: ""},
                    {label: "已支付", value: "paid"},
                    {label: "未支付", value: "unpaid"}
                ],
                pay_mode: [
                    {label: "全部",   value: ""},
                    {label: "红包", value: "red_packet"},
                    {label: "余额", value: "balance"}
                ]
            };
        },
        methods: {
            triggeredChange (){
                if (this.searchData.keyword.length>0 && this.searchData.keyword_1.length<=0) {
                    alert('请选择搜索方式');
                    this.searchData.keyword='';
                }
            },

            clearSearch() {
                this.page = 1;
                this.searchData.keyword = '';
                this.searchData.keyword_1 = '';
                this.getList();
            },

            selectDateTime(e) {
                if (e != null) {
                    this.searchData.start_date = e[0];
                    this.searchData.end_date = e[1];
                } else {
                    this.searchData.start_date = '';
                    this.searchData.end_date = '';
                }
                this.search();
            },

            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },

            clearWhere() {
                this.searchData.keyword = '';
                this.searchData.keyword_1 = '';
                this.searchData.pay_status = '';
                this.searchData.start_date = '';
                this.searchData.date = '';
                this.searchData.end_date = '';
                this.searchData.pay_mode = '';
                this.address = '';
                this.level = '';
                this.getList();
            },

            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/checkout-order/index',
                        page: self.page,
                        keyword: self.searchData.keyword,
                        keyword_1: self.searchData.keyword_1,
                        pay_status: self.searchData.pay_status,
                        start_date: self.searchData.start_date,
                        end_date: self.searchData.end_date,
                        pay_mode: self.searchData.pay_mode,
                        address: self.address,
                        level: self.level,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.list = e.data.data.list;
                        self.pageCount = e.data.data.pagination.page_count;
                        self.billStatistics();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            billStatistics() {
                let self = this;
                self.statisticsLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/checkout-order/bill-statistics',
                        page: self.page,
                        keyword: self.searchData.keyword,
                        keyword_1: self.searchData.keyword_1,
                        pay_status: self.searchData.pay_status,
                        start_date: self.searchData.start_date,
                        end_date: self.searchData.end_date,
                        pay_mode: self.searchData.pay_mode,
                        address: self.address,
                        level: self.level,
                    },
                    method: 'get',
                }).then(e => {
                    self.statisticsLoading = false;
                    if (e.data.code === 0) {
                        self.Statistics = e.data.data.Statistics;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            view(id) {
                navigateTo({
                    r: 'plugin/mch/mall/checkout-order/detail',
                    id: id,
                });
            },
            search() {
                this.page = 1;
                this.getList();
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
                this.getList();
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
