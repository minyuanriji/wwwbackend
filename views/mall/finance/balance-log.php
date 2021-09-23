<?php
Yii::$app->loadComponentView('com-user-finance-stat');
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>余额收支（<span style="color: #1ed0ff">类型筛选和省市区筛选只支持查询09/22日后的数据</span>）</span>
                <div style="float: right;margin: -5px 0">
                    <com-export-dialog :field_list='export_list' :params="searchData" @selected="exportConfirm"></com-export-dialog>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div style="display: flex;justify-content: space-evenly">
                <div style="width: 25%">
                    <el-date-picker size="small" v-model="date" type="datetimerange"
                                    style="float: left"
                                    value-format="yyyy-MM-dd HH:mm:ss"
                                    range-separator="至" start-placeholder="开始日期"
                                    @change="selectDateTime"
                                    end-placeholder="结束日期">
                    </el-date-picker>
                </div>
                <div style="width: 15%">
                    <el-input @keyup.enter.native="search" size="small" placeholder="请输入昵称、手机号搜索" v-model="keyword" clearable
                              @clear="search">
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                </div>
                <div style="width: 18%">
                    类型
                    <el-tooltip class="item" effect="dark" content="只有选择订单或者商家扫码类型，才能筛选省市区" placement="bottom">
                        <i class="el-icon-question"></i>
                    </el-tooltip>
                    <el-select size="small" v-model="type" @change='searchType' class="select" placeholder="请选择类型">
                        <el-option key="" label="全部" value=""></el-option>
                        <el-option key="order" label="订单" value="order"></el-option>
                        <el-option key="mch_checkout_order" label="商家扫码" value="mch_checkout_order"></el-option>
                        <el-option key="admin" label="管理员充值" value="admin"></el-option>
                        <el-option key="order_refund" label="订单退款" value="order_refund"></el-option>
                        <el-option key="user_cash" label="用户提现余额" value="user_cash"></el-option>
                        <el-option key="giftpacks_order" label="大礼包订单" value="giftpacks_order"></el-option>
                    </el-select>
                </div>
                <div style="width: 16%" v-if="levelShow">
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
            <div style="margin: 30px 0">
                <div style="display: flex;justify-content: space-evenly">
                    <div>
                        <div style="text-align: center">总收入</div>
                        <div id="assets">{{Statistics.income}}元</div>
                    </div>
                    <div>
                        <div style="text-align: center">总支出</div>
                        <div id="assets">{{Statistics.expend}}元</div>
                    </div>
                    <div>
                        <div style="text-align: center">当页收入</div>
                        <div id="assets">{{Statistics.currentIncome}}元</div>
                    </div>
                    <div>
                        <div style="text-align: center">当页支出</div>
                        <div id="assets">{{Statistics.currentExpend}}元</div>
                    </div>
                </div>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">

                <el-table-column prop="id" label="ID" width="80"></el-table-column>

                <el-table-column prop="nickname" label="昵称">
                    <template slot-scope="scope">
                        <com-user-finance-stat :user-id="parseInt(scope.row.uid)">
                            {{scope.row.nickname}}
                        </com-user-finance-stat>
                    </template>
                </el-table-column>

                <el-table-column label="收支情况(元)" width="180">
                    <template slot-scope="scope">
                        <div style="font-size: 18px;"
                             :style="{color: scope.row.type == 1 ? '#68CF3D' : scope.row.type == 2 ? '#F6AA5A' : ''}"
                        >
                            {{scope.row.type == 1 ? '+' : scope.row.type == 2 ? '-' : ''}}
                            {{scope.row.money}}
                        </div>
                    </template>
                </el-table-column>

                <el-table-column prop="desc" label="说明" width="500px"></el-table-column>

                <el-table-column label="备注">
                    <template slot-scope="scope">
                        <div flex="box:first" v-if="scope.row.info_desc">
                            <div style="padding-right: 10px" v-if="scope.row.info_desc.hasOwnProperty('pic_url') && scope.row.info_desc.pic_url.length > 0">
                                <com-image mode="aspectFill" :src="scope.row.info_desc.pic_url"></com-image>
                            </div>
                            <div v-if="scope.row.info_desc.hasOwnProperty('remark')">{{scope.row.info_desc.remark}}
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column prop="scope" width="180" label="充值时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                    </template>
                </el-table-column>

            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next"
                               :page-count="pageCount"></el-pagination>
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
                    date: '',
                    start_date: '',
                    end_date: '',
                },
                date: '',
                keyword: getQuery("user_id"),
                form: [],
                pageCount: 0,
                listLoading: false,
                Statistics: '',
                type: '',
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
                export_list: [],
            };
        },
        methods: {
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.date = this.date;
                this.searchData.type = this.type;
                this.searchData.level = this.level;
                this.searchData.address = this.address;
            },
            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.date == null) {
                    this.searchData.start_date = '';
                    this.searchData.end_date = ''
                }
                this.getList();
            },
            searchType(e) {
                if (e == 'order' || e == 'mch_checkout_order') {
                    this.levelShow=true;
                } else {
                    this.levelShow=false;
                    this.level='';
                }
                this.page = 1;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/finance/balance-log',
                        page: this.page,
                        date: this.date,
                        user_id: getQuery('user_id'),
                        keyword: this.keyword,
                        start_date: this.searchData.start_date,
                        end_date: this.searchData.end_date,
                        type: this.type,
                        level: this.level,
                        address: this.address,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        let {list, Statistics, export_list, pagination} = e.data.data;
                        this.form = list;
                        this.Statistics = Statistics;
                        this.pageCount = pagination.page_count;
                        this.export_list = export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
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

<style>
    #assets {
        font-size: 18px;
        color: #1ed0ff;
        margin-left: 10px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>