
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
                <div style="display:flex;align-items: center">
                    <el-select v-model="search.kw_type" placeholder="请选择" style="width:150px;">
                        <el-option key="bsh_mch_id" value="bsh_mch_id" label="补商汇-商户ID"></el-option>
                    </el-select>
                    <el-input v-model="search.keyword" placeholder="请输入"  style="margin-left:10px;width:350px;"></el-input>
                    <el-button @click="searchGo" type="primary" style="margin-left:10px;">查询</el-button>
                </div>
            </el-card>
            <el-table v-loading="loading" :data="list" border style="margin-top:20px;width: 100%">
                <el-table-column prop="bsh_mch_id" label="商户ID" width="90" align="center"></el-table-column>
                <el-table-column label="商户信息" width="180" >
                    <template slot-scope="scope">
                        <div style="display:flex;align-items: center;">
                            <com-image width="25" height="25" :src="scope.row.cover_url"></com-image>
                            <div style="padding-left:10px;">
                                <div>{{scope.row.name}}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="折扣(补商汇)" width="90" align="center" prop="zk"></el-table-column>
                <el-table-column label="智慧门店" width="180" >
                    <template slot-scope="scope">
                        {{scope.row.detail.merchant_name}}
                        <span style="color:gray;">（{{scope.row.detail.store_name}}）</span>
                    </template>
                </el-table-column>
                <el-table-column label="支付方式" width="100" align="center">
                    <template slot-scope="scope">
                        {{scope.row.detail.pay_type == 1 ? '微信' : '支付宝'}}
                    </template>
                </el-table-column>

                <el-table-column label="分账状态" width="140" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status == 0" style="color:darkred">待分账</span>
                        <span v-if="scope.row.status == 1" style="color:steelblue">处理中</span>
                        <span v-if="scope.row.status == 2" style="color:darkgreen">已分账</span>
                        <span v-if="scope.row.status == 3" style="color:gray">已取消</span>
                    </template>
                </el-table-column>

                <el-table-column label="用户信息" width="350" align="center">
                    <template slot-scope="scope">
                        <el-table :show-header="false" :data="userInfos(scope.row)" border size="small" style="width: 100%">
                            <el-table-column prop="name" width="100" align="right"></el-table-column>
                            <el-table-column prop="value" align="left"></el-table-column>
                        </el-table>
                    </template>
                </el-table-column>
                <!--
                <el-table-column label="分账信息" width="350" align="center">
                    <template slot-scope="scope">
                        <el-table :show-header="false" :data="scope.row.split_data.receivers" border size="small" style="width: 100%">
                            <el-table-column prop="account" width="100" align="right"></el-table-column>
                            <el-table-column prop="amount" align="left"></el-table-column>
                        </el-table>
                    </template>
                </el-table-column>
                -->
                <el-table-column label="订单信息" align="center" width="350">
                    <template slot-scope="scope">
                        <el-table :show-header="false" :data="orderInfos(scope.row)" border size="small" style="width: 100%">
                            <el-table-column prop="name" width="130" align="right"></el-table-column>
                            <el-table-column prop="value" align="left"></el-table-column>
                        </el-table>
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
                        <el-button v-if="scope.row.status != 0" @click="unfreeze(scope.row)"  type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="解冻资金" placement="top">
                                <img src="statics/img/mall/balance.png" alt="">
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

        <el-dialog title="确认分账" :visible.sync="dialogVisible" :close-on-click-modal="false" :close-on-press-escape="false" width="30%" >
            <div v-if="splitLoading" style="text-align: center;margin-top:30px;">加载中</div>
            <table v-if="!splitLoading && splitInfo" class="grid-i" style="width:100%;">
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
                    <td>
                        {{splitInfo.unsplit_amount}}元
                    </td>
                </tr>
                <tr class="c2">
                    <td class="label">本次分账信息：</td>
                    <td>
                        <div v-for="account in splitInfo.split_account" style="margin-bottom:1px;display:flex;align-items: center;">
                            <span style="padding:6px 10px;color:gray;display:inline-block;background:#f7f7f7;width:150px;text-align: right;">{{account.name}}</span>
                            <span style="margin-left:10px;">{{account.amount}}元</span>
                        </div>
                    </td>
                </tr>
                <tr class="c2">
                    <td class="label"></td>
                    <td>
                        <el-button :loading="splitDoing" @click="splitGo" type="danger">确认分账</el-button>
                    </td>
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
                splitDoing: false,
                splitInfo: '',
                list: [],
                pagination: null,
                loading: false,
                search: {
                    page: 1,
                    kw_type: 'bsh_mch_id',
                    keyword: ''
                }
            };
        },
        computed: {
            userInfos(item){
                return function(item){
                    let infos = [];
                    infos.push({name: "用户ID", value: item.user ? item.user.id : '-'});
                    infos.push({name: "用户昵称", value: item.user ? item.user.nickname : '-'});
                    infos.push({name: "手机号码", value: item.pay_user_mobile});
                    infos.push({name: "用户身份", value: item.user ? this.roleText(item.user.role_type) : '-'});
                    infos.push({name: "上级ID", value: item.parent ? item.parent.id : '-'});
                    infos.push({name: "上级昵称", value: item.parent ? item.parent.nickname : '-'});
                    infos.push({name: "上级手机号", value: item.parent ? item.parent.mobile : '-'});
                    infos.push({name: "上级身份", value: item.parent ? this.roleText(item.parent.role_type) : '-'});
                    return infos;
                }
            },
            orderInfos(item){
                return function(item){
                    let infos = [], zk = parseInt(item.transfer_rate) > 0 ? (10 - (parseInt(item.transfer_rate)/100) * 10) : '-';
                    infos.push({name: "订单号", value: item.detail.order_no});
                    infos.push({name: "日期", value: item.created_at});
                    infos.push({name: "折扣", value: !isNaN(zk) ? zk.toFixed(1) : zk});
                    infos.push({name: "订单金额", value: item.detail.total_price});
                    infos.push({name: "实际支付", value: item.detail.pay_price});
                    infos.push({name: "分账金额", value: item.split_amount});
                    infos.push({name: "赠送红包", value: item.shopping_voucher});
                    infos.push({name: "赠送积分", value: item.send_score});
                    infos.push({name: "门店推荐+分佣", value: item.commision_amount});
                    return infos;
                }
            }
        },
        methods: {
            roleText(type){
                let values = {
                    store: 'VIP代理商',
                    partner: '区域服务商',
                    branch_office: '城市服务商',
                    user: 'VIP会员'
                };
                return values[type] ? values[type] : '';
            },
            unfreeze(item){
                this.$confirm('确定操作吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.loading = true;
                    request({
                        params: {
                            r: 'plugin/smart_shop/mall/order/unfreeze',
                        },
                        method: 'post',
                        data: {
                            id: item.id
                        }
                    }).then(e => {
                        this.loading = false;
                        if (e.data.code === 0) {
                            this.$message.success("操作成功");
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.$message.error("网络异常");
                        this.loading = false;
                    });
                }).catch(() => {});
            },
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
            splitGo(){
                this.splitDoing = true;
                request({
                    params: {
                        r: 'plugin/smart_shop/mall/order/do-split',
                    },
                    method: 'post',
                    data: {
                        id: this.splitInfo.id
                    }
                }).then(e => {
                    this.splitDoing = false;
                    if (e.data.code == 0) {
                        this.$message.success("分账成功");
                        this.dialogVisible = false;
                        this.getList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.splitDoing = false;
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