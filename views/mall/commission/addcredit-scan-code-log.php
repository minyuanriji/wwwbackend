<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>话费充值分佣记录</span>
            </div>
        </div>
        <div class="table-body">
            <div style="float: left">
                <span>状态</span>
                <el-select size="small" v-model="searchData.status" class="select" @change="change">
                    <el-option key="-2" label="全部" value="-2"></el-option>
                    <el-option key="-1" label="无效" value="-1"></el-option>
                    <el-option key="0" label="待结算" value="0"></el-option>
                    <el-option key="1" label="已结算" value="1"></el-option>
                </el-select>
            </div>
            <el-date-picker size="small" v-model="searchData.date" type="datetimerange"
                            style="float: left;margin-left: 10px"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至" start-placeholder="开始日期"
                            @change="selectDateTime"
                            end-placeholder="结束日期">
            </el-date-picker>

            <div style="margin-bottom: 20px;">请选择搜索方式
                <el-input style="width: 350px" size="small" v-model="searchData.keyword" placeholder="请输入搜索内容" clearable
                          @clear="clearSearch"
                          @change="search"
                          @input="triggeredChange"
                >
                    <el-select style="width: 100px" slot="prepend" v-model="searchData.keyword_1">
                        <el-option v-for="item in selectList" :key="item.value"
                                   :label="item.name"
                                   :value="item.value">
                        </el-option>
                    </el-select>
                </el-input>
            </div>

            <el-table :data="form" border style="width: 100%" v-loading="listLoading">

                <el-table-column prop="id" label="ID" width="80"></el-table-column>

                <el-table-column label="充值手机号" width="300">
                    <template slot-scope="scope">
                        <div>{{scope.row.mobile}}</div>
                    </template>
                </el-table-column>

                <el-table-column label="订单信息" width="350">
                    <template slot-scope="scope">
                        <div>支付用户昵称：<b>{{scope.row.pay_user_name}}</b></div>
                        <div>订单编号：<b style="font-size: 14px">{{scope.row.order_no}}</b></div>
                        <div>订单金额：<b style="color:#cc3311">{{scope.row.order_price}}元</b></div>
                        <div>扣除红包：<b style="color:#cc3311">{{scope.row.integral_deduction_price}}</b></div>
                        <div>实际支付费用：<b style="color:#cc3311">{{scope.row.pay_price}}元</b></div>
                    </template>
                </el-table-column>

                <el-table-column prop="user.nickname" label="收益人信息" width="350">
                    <template slot-scope="scope">
                        <com-image mode="aspectFill"
                                   style="float: left;margin-right: 8px"
                                   :src="scope.row.avatar_url">
                        </com-image>
                        <div>昵称：{{scope.row.nickname}}(ID:{{scope.row.user_id}})</div>
                        <div v-if="scope.row.role_type=='store'">身份：VIP会员</div>
                        <div v-if="scope.row.role_type=='partner'">身份：合伙人</div>
                        <div v-if="scope.row.role_type=='branch_office'">身份：分公司</div>
                        <div v-if="scope.row.role_type=='user'">身份：普通用户</div>
                        <div>手机号：{{scope.row.mobile}}</div>
                    </template>
                </el-table-column>

                <el-table-column label="收益(元)" width="180">
                    <template slot-scope="scope">{{scope.row.price}}</template>
                </el-table-column>

                <el-table-column label="状态" width="130">
                    <template slot-scope="scope">
                        <div v-if="scope.row.status == -1" style="color: red">无效</div>
                        <div v-if="scope.row.status == 0">待结算</div>
                        <div v-if="scope.row.status == 1" style="color: green">已结算</div>
                    </template>
                </el-table-column>

                <el-table-column prop="scope" label="添加时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                    </template>
                </el-table-column>

            </el-table>
            <div style="text-align: center;margin-top: 20px">
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
                    keyword_1: '',
                    date: '',
                    start_date: '',
                    end_date: '',
                    status: '',
                },
                selectList: [
                    {value: '1', name: '充值手机号'},
                    {value: '2', name: '受益人手机号'},
                    {value: '3', name: '受益人昵称'},
                    {value: '4', name: '订单编号'},
                ],
                form: [],
                pageCount: 0,
                listLoading: false,
            };
        },
        methods: {
            clearSearch() {
                this.page = 1;
                this.searchData.keyword = '';
                this.getList();
            },
            triggeredChange (){
                if (this.searchData.keyword.length>0 && this.searchData.keyword_1.length<=0) {
                    alert('请选择搜索方式');
                    this.searchData.keyword='';
                }
            },
            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.searchData.date == null) {
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
                        r: 'mall/commission/addcredit-scan-code-log',
                        page: this.page,
                        keyword: this.searchData.keyword,
                        start_date: this.searchData.start_date,
                        end_date: this.searchData.end_date,
                        status: this.searchData.status,
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