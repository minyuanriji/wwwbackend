
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>分账订单管理</span>
                <div style="float: right;margin-top: -5px"></div>
            </div>
        </div>
        <div class="table-body">
            <el-card class="box-card">

            </el-card>
            <el-table v-loading="loading" :data="list" border style="margin-top:20px;width: 100%">
                <el-table-column prop="bsh_mch_id" label="商户ID" width="100" ></el-table-column>
                <el-table-column label="商户信息" width="200" >
                    <template slot-scope="scope">
                        <div style="display:flex;align-items: center;">
                            <com-image width="25" height="25" :src="scope.row.cover_url"></com-image>
                            <div style="padding-left:10px;">
                                <div>{{scope.row.name}}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="智慧门店" width="200" >
                    <template slot-scope="scope">
                        {{scope.row.detail.merchant_name}}
                        <span style="color:gray;">（{{scope.row.detail.store_name}}）</span>
                    </template>
                </el-table-column>
                <el-table-column label="支付方式" width="110" align="center">
                    <template slot-scope="scope">
                        {{scope.row.detail.pay_type == 1 ? '微信' : '支付宝'}}
                    </template>
                </el-table-column>
                <el-table-column prop="pay_user_mobile" label="支付手机" width="130" align="center"></el-table-column>
                <el-table-column label="分账状态" width="150" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status == 0" style="color:darkred">待分账</span>
                        <span v-if="scope.row.status == 1" style="color:darkgreen">已分账</span>
                        <span v-if="scope.row.status == 2" style="color:gray">已取消</span>
                    </template>
                </el-table-column>
                <el-table-column label="分账信息" width="500" align="center">
                    <template slot-scope="scope">
                        <el-table :data="scope.row.split_data" border size="small" style="width: 100%">
                            <el-table-column label="收益人" width="180"></el-table-column>
                            <el-table-column label="金额"></el-table-column>
                            <el-table-column label="日期"></el-table-column>
                        </el-table>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" width="150" label="日期" align="center">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column label="订单号" width="200" align="center">
                    <template slot-scope="scope">
                        {{scope.row.detail.order_no}}
                    </template>
                </el-table-column>
                <el-table-column label="订单金额" width="100" align="center">
                    <template slot-scope="scope">
                        {{scope.row.detail.total_price}}
                    </template>
                </el-table-column>
                <el-table-column label="实际支付" width="100" align="center">
                    <template slot-scope="scope">
                        {{scope.row.detail.pay_price}}
                    </template>
                </el-table-column>
                <el-table-column label="操作" fixed="right" width="150" align="center">
                    <template slot-scope="scope">
                        <el-button @click="showSplitDialog(scope.row)" type="text" circle size="mini" v-if="scope.row.status == 0">
                            <el-tooltip class="item" effect="dark" content="确认分账" placement="top">
                                <img src="statics/img/mall/pass.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" circle size="mini" v-if="scope.row.status == 0">
                            <el-tooltip class="item" effect="dark" content="取消分账" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
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

        <el-dialog title="确认分账" :visible.sync="dialogVisible" width="30%" >
            <div v-if="splitLoading" style="text-align: center;margin-top:30px;">加载中</div>
            <table v-if="!splitLoading" class="grid-i" style="width:100%;">
                <tr class="c2">
                    <td class="label">订单金额：</td>
                    <td>{{splitInfo.total_price}}元</td>
                </tr>
                <tr class="c2">
                    <td class="label">实际支付：</td>
                    <td>{{splitInfo.pay_price}}元</td>
                </tr>
                <tr class="c2">
                    <td class="label">账户类型：</td>
                    <td>
                        <span v-if="splitInfo.pay_type == 1">微信</span>
                        <span v-if="splitInfo.pay_type == 2">支付宝</span>
                    </td>
                </tr>
                <tr class="c2">
                    <td class="label">可分账金额：</td>
                    <td>100</td>
                </tr>
                <tr class="c2">
                    <td class="label">本次分账信息：</td>
                    <td>100</td>
                </tr>
            </table>
        </el-dialog>


    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                dialogVisible: false,
                splitLoading: false,
                splitInfo: {},
                list: [],
                pagination: null,
                loading: false,
                search: {
                    page: 1
                }
            };
        },
        methods: {
            showSplitDialog(item){
                this.dialogVisible = true;
                this.splitLoading = true;
                request({
                    params: {
                        r: 'plugin/smart_shop/mall/order/split-info',
                        id: item.id
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        this.splitLoading = false;
                        this.splitInfo = e.data.data.info;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error("请求失败");
                });
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
                    r: 'plugin/smart_shop/mall/order/index'
                };
                params = Object.assign(params, this.search);
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