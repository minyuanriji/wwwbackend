<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>商品消费分佣记录</span>
            </div>
        </div>
        <div class="table-body">
            <div style="float: left">
                <span>状态</span>
                <el-select size="small" v-model="status" class="select" @change="change">
                    <el-option key="-2" label="全部" value="-2"></el-option>
                    <el-option key="-1" label="无效" value="-1"></el-option>
                    <el-option key="0" label="待结算" value="0"></el-option>
                    <el-option key="1" label="已结算" value="1"></el-option>
                </el-select>
            </div>
            <el-date-picker size="small" v-model="date" type="datetimerange"
                            style="float: left;margin-left: 10px"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至" start-placeholder="开始日期"
                            @change="selectDateTime"
                            end-placeholder="结束日期">
            </el-date-picker>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入昵称搜索" v-model="keyword" clearable
                          @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">

                <el-table-column prop="id" label="ID" width="80"></el-table-column>

                <el-table-column label="商品名称" width="300">
                    <template slot-scope="scope">{{scope.row.goods_name}}</template>
                </el-table-column>

                <el-table-column label="订单信息" width="230">
                    <template slot-scope="scope">
                        <div>支付用户昵称：<b>{{scope.row.buy_user_name}}</b></div>
                        <div>订单编号：<b style="font-size: 12px">{{scope.row.order_no}}</b></div>
                        <div>购买数量：<b style="color:#cc3311">{{scope.row.num}}</b></div>
                        <div>商品原总价(优惠前)：<b style="color:#cc3311">{{scope.row.total_original_price}}元</b></div>
                        <div>扣除积分：<b style="color:#cc3311">{{scope.row.use_score_price}}</b></div>
                        <div>扣除红包：<b style="color:#cc3311">{{scope.row.integral_price}}</b></div>
                        <div>商品总价(优惠后)：<b style="color:#cc3311">{{scope.row.total_price}}元</b></div>
                        <div>实际支付（含运费）：<b style="color:#cc3311">{{scope.row.total_pay_price}}元</b></div>
                    </template>
                </el-table-column>

                <el-table-column prop="user.nickname" label="收益人信息" width="200">
                    <template slot-scope="scope">
                        <div>收益人昵称：{{scope.row.user.nickname}}</div>
                        <div>收益人身份：{{scope.row.identity}}</div>
                    </template>
                </el-table-column>

                <el-table-column label="收益(元)" width="180">
                    <template slot-scope="scope"><b style="color: red">{{scope.row.price}}</b></template>
                </el-table-column>

                <el-table-column label="状态" width="180">
                    <template slot-scope="scope">
                        <div v-if="scope.row.status == -1" style="color: red">无效</div>
                        <div v-if="scope.row.status == 0">待结算</div>
                        <div v-if="scope.row.status == 1" style="color: green">已结算</div>
                    </template>
                </el-table-column>

                <el-table-column prop="scope" width="180" label="添加时间">
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
                    status: '',
                },
                date: '',
                status: '',
                keyword: '',
                form: [],
                pageCount: 0,
                listLoading: false,
            };
        },
        methods: {
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.date = this.date;
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
            change() {
                this.page = 1;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/commission/goods-consume-log',
                        page: this.page,
                        date: this.date,
                        keyword: this.keyword,
                        start_date: this.searchData.start_date,
                        end_date: this.searchData.end_date,
                        status: this.status,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        let {list, pagination} = e.data.data;
                        this.form = list;
                        this.pageCount = pagination.page_count;
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
            }
        },
        mounted: function () {
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