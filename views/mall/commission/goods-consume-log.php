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

                <el-table-column label="商品名称" width="350">
                    <template slot-scope="scope">
                        <com-image mode="aspectFill"
                                   style="float: left;margin-right: 8px"
                                   :src="scope.row.cover_pic">
                        </com-image>
                        {{scope.row.goods_name}}
                    </template>
                </el-table-column>

                <el-table-column label="订单信息" width="350">
                    <template slot-scope="scope">
                        <div>支付用户昵称：<b>{{scope.row.buy_user_name}}</b></div>
                        <div>订单编号：<b style="font-size: 14px">{{scope.row.order_no}}</b></div>
                        <div>购买数量：<b style="color:#cc3311">{{scope.row.num}}</b></div>
                        <div>商品原总价(优惠前)：<b style="color:#cc3311">{{scope.row.total_original_price}}元</b></div>
                        <div>扣除积分：<b style="color:#cc3311">{{scope.row.use_score_price}}</b></div>
                        <div>扣除红包：<b style="color:#cc3311">{{scope.row.integral_price}}</b></div>
                        <div>商品总价(优惠后)：<b style="color:#cc3311">{{scope.row.total_price}}元</b></div>
                        <div>实际支付（含运费）：<b style="color:#cc3311">{{scope.row.total_pay_price}}元</b></div>
                    </template>
                </el-table-column>

                <el-table-column prop="nickname" label="收益人信息" width="350">
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

                <el-table-column label="收益(元)" width="130">
                    <template slot-scope="scope"><b style="color: red">{{scope.row.price}}</b></template>
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
                form: [],
                pageCount: 0,
                listLoading: false,
                selectList: [
                    {value: '1', name: '昵称'},
                    {value: '2', name: '手机号'},
                    {value: '3', name: '订单编号'},
                    {value: '4', name: '商品名称'}
                ],
            };
        },
        methods: {
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
            clearSearch() {
                this.page = 1;
                this.searchData.keyword = '';
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
                        r: 'mall/commission/goods-consume-log',
                        page: this.page,
                        keyword: this.searchData.keyword,
                        keyword_1: this.searchData.keyword_1,
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
</style>