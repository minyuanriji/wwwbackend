<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>积分记录</span>
                <div style="float: right;">
                    <com-export-dialog :field_list='export_list' :params="searchData" @selected="exportConfirm"></com-export-dialog>
                </div>
                <div style="margin: 30px 0" v-loading="statisticsLoading">
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
            </div>
        </div>
        <div class="table-body">
            <el-date-picker size="small" v-model="date" type="datetimerange"
                            style="float: left"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至" start-placeholder="开始日期"
                            @change="selectDateTime"
                            end-placeholder="结束日期">
            </el-date-picker>
            <div class="input-item" style="float: left">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入昵称、手机号搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <div style="float: left; margin-left: 15px">
                类型
                <el-tooltip class="item" effect="dark" content="只有选择订单或者商家扫码类型，才能筛选省市区" placement="bottom">
                    <i class="el-icon-question"></i>
                </el-tooltip>
                <el-select style="width: 120px;" size="small" v-model="source_type" @change='typeChange'>
                    <el-option key="" label="全部" value=""></el-option>
                    <el-option key="order" label="订单" value="order"></el-option>
                    <el-option key="order_cancellation" label="订单取消" value="order_cancellation"></el-option>
                    <el-option key="sign_in" label="签到" value="sign_in"></el-option>
                    <el-option key="admin" label="管理员操作" value="admin"></el-option>
                    <el-option key="give" label="下单赠送积分" value="give"></el-option>
                    <el-option key="new_user" label="新人领积分" value="new_user"></el-option>
                    <el-option key="giftpacks_order" label="大礼包订单增送" value="giftpacks_order"></el-option>
                    <el-option key="from_mch_checkout_order" label="商家扫码赠送" value="from_mch_checkout_order"></el-option>
                </el-select>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="nickname" label="昵称"></el-table-column>
                <el-table-column label="收支情况(积分)" width="150">
                    <template slot-scope="scope">
                        <div style="font-size: 18px;color: #68CF3D" v-if="scope.row.type == 1">+{{scope.row.score}}</div>
                        <div style="font-size: 18px;color: #F6AA5A" v-if="scope.row.type == 2">-{{scope.row.score}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="desc" label="说明" width="400"></el-table-column>
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

            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        @current-change="pageChange"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="float:right;margin:15px"
                        v-if="pagination">
                </el-pagination>
            </el-col>
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
                    end_at: '',
                    source_type: '',
                },
                date: '',
                keyword: '',
                form: [],
                pagination: null,
                listLoading: false,
                statisticsLoading: false,
                export_list: [],
                source_type:'',
                Statistics: '',
            };
        },
        methods: {
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.start_date = this.date[0];
                this.searchData.end_date = this.date[1];
                this.searchData.source_type = this.source_type;
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.date == null) {
                    this.date = ''
                }
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
                this.page = 1;
                this.search();
            },

            typeChange(e) {
                console.log(e);
                this.page = 1;
                this.form = '';
                this.pagination = '';
                this.export_list = [];
                if (this.date == null) {
                    this.date = ''
                }
                this.getList();
            },

            getList() {
                let params = {
                    r: 'mall/finance/score-log',
                    page: this.page,
                    date: this.date,
                    user_id: getQuery('user_id'),
                    keyword: this.keyword,
                    source_type: this.source_type,
                };
                if (this.date) {
                    Object.assign(params, {
                        start_date: this.date[0],
                        end_date: this.date[1],
                    });
                }
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.export_list = e.data.data.export_list;
                        this.pagination = e.data.data.pagination;
                        this.getStatistics();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
                this.listLoading = true;
            },

            getStatistics() {
                let params = {
                    r: 'mall/finance/statistics',
                    page: this.page,
                    date: this.date,
                    user_id: getQuery('user_id'),
                    keyword: this.keyword,
                    source_type: this.source_type,
                };
                if (this.date) {
                    Object.assign(params, {
                        start_date: this.date[0],
                        end_date: this.date[1],
                    });
                }
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.Statistics = e.data.data.Statistics;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.statisticsLoading = false;
                }).catch(e => {
                    this.statisticsLoading = false;
                });
                this.statisticsLoading = true;
            },
        },
    mounted: function() {
        this.getList();
    }
});
</script>

<style>
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

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
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
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>