
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>门店支付单记录</span>
                <div style="float: right;margin-top: -5px"></div>
            </div>
        </div>
        <div class="table-body">

            <el-tabs v-model="activeName" @tab-click="handleTab">
                <el-tab-pane label="已支付" name="paid"></el-tab-pane>
                <el-tab-pane label="未支付" name="unpaid"></el-tab-pane>
            </el-tabs>

            <div style="display:flex;align-items: center;margin-top:10px;">
                <el-select v-model="search.kw_type" placeholder="请选择" style="width:150px;">
                    <el-option value="order_no" label="订单号"></el-option>
                    <el-option value="store_name" label="门店名称"></el-option>
                    <el-option value="pay_user_id" label="支付用户ID"></el-option>
                    <el-option value="pay_user_kw" label="支付用户昵称、手机"></el-option>
                </el-select>
                <el-input v-model="search.keyword" placeholder="门店名称"  style="margin-left:10px;width:200px;"></el-input>
                <el-button @click="searchGo" type="primary" style="margin-left:10px;">查询</el-button>
            </div>
            <el-table v-loading="loading" :data="list" border style="margin-top:20px;width: 100%">
                <el-table-column align="center" prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="order_no" label="订单号"></el-table-column>
                <el-table-column prop="store_name" label="门店名称"></el-table-column>
                <el-table-column align="center" label="类型" width="120">
                    <template slot-scope="scope">
                        <span v-if="scope.row.business_scene == 'shopping_voucher'">红包储值</span>
                    </template>
                </el-table-column>
                <el-table-column align="center" label="订单状态" width="100">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.order_status == 'unconfirmed'" >未确认</el-tag>
                        <el-tag v-if="scope.row.order_status == 'success'"  type="success">已完成</el-tag>
                        <el-tag v-if="scope.row.order_status == 'unpaid'" type="info">未支付</el-tag>
                        <el-tag v-if="scope.row.order_status == 'fail'" type="danger">支付失败</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="center"  prop="order_price" label="订单金额" width="100"></el-table-column>
                <el-table-column label="支付状态" width="100">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.pay_status == 'paid'"  type="success">已支付</el-tag>
                        <el-tag v-if="scope.row.pay_status == 'unpaid'" type="info">未支付</el-tag>
                        <el-tag v-if="scope.row.pay_status == 'refund'" type="danger">已退款</el-tag>
                        <el-tag v-if="scope.row.pay_status == 'refunding'" type="danger">退款中</el-tag>
                    </template>
                </el-table-column>
                <el-table-column align="center" label="支付信息" width="300">
                    <template slot-scope="scope">
                        <span v-if="scope.row.pay_status == 'unpaid'" >-</span>
                        <el-table v-else size="mini" :show-header="false" :data="cPayData(scope.row)" border >
                            <el-table-column align="right" width="100">
                                <template slot-scope="scope">
                                    {{scope.row.name}}
                                </template>
                            </el-table-column>
                            <el-table-column>
                                <template slot-scope="scope">
                                    {{scope.row.value}}
                                </template>
                            </el-table-column>
                        </el-table>
                    </template>
                </el-table-column>
                <el-table-column align="center" prop="created_at" label="订单日期" ></el-table-column>
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


    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: "paid",
                dialogVisible: false,
                list: [],
                pagination: null,
                loading: false,
                search: {
                    page: 1,
                    keyword: '',
                    kw_type: 'order_no'
                }
            };
        },
        computed: {
            cPayData(item){
                return function(item){
                    return [
                        {name: '支付用户', value: item.nickname},
                        {name: '手机号', value: item.mobile},
                        {name: '支付日期', value: item.pay_time},
                        {name: '支付金额', value: item.pay_price},
                        {name: '支付方式', value: item.pay_type}
                    ];
                }
            },
        },
        methods: {
            handleTab(e){
                this.search.page = 1;
                this.getList();
            },
            searchReset(){
                this.search = {page:1};
                this.getList();
            },
            searchGo(){
                this.page = 1;
                this.getList();
            },
            getList() {
                let self = this;
                self.loading = true;
                let params = {
                    r: 'plugin/smart_shop/mall/store-pay-order/index'
                };
                params = Object.assign(params, this.search);
                params['status'] = this.activeName;
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.pagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.loading = false;
                });
            },
            pageChange(page) {
                this.search.page = page;
                this.getList();
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
<style>

    .fin-detail{display:flex;align-items: center}
    .fin-detail > span:first-child{width:100px;text-align: right}

    .grid-i th{padding:5px 0px 5px 0px;}
    .grid-i th,.grid-i td{text-align:left;}
    .grid-i td{padding:10px 10px;border:1px solid #ddd;border-bottom:none;}
    .grid-i tr:last-child td{border-bottom:1px solid #ddd;}
    .grid-i .label{border-left:none;font-weight:bold;padding:6px 6px 6px 0px;border-right:none;text-align:right;background:#f1f1f1;}
    .grid-i td:first-child{border-left:1px solid #ddd;}
    .grid-i .c2 td{width:70%}
    .grid-i .label{width:30% !important;}

    .search-merchant td > div{display: flex;align-items: center}
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
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

    .el-input-group__append .el-button {
        margin: 0;
    }

    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }
</style>