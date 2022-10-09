
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>门店账户明细</span>
                <div style="float: right;margin-top: -5px"></div>
            </div>
        </div>
        <div class="table-body">

            <div style="display:flex;align-items: center;margin-top:10px;">
                <el-select v-model="search.kw_type" placeholder="请选择" style="width:150px;">
                    <el-option value="bsh_name" label="平台商户"></el-option>
                </el-select>
                <el-input v-model="search.keyword" placeholder="请输入"  style="margin-left:10px;width:200px;"></el-input>
                <el-button @click="searchGo" type="primary" style="margin-left:10px;">查询</el-button>
            </div>
            <el-table v-loading="loading" :data="list" border style="margin-top:20px;width: 100%">
                <el-table-column align="center" prop="id" label="编号" width="100"></el-table-column>
                <el-table-column prop="bsh_name" label="平台商户" width="200"></el-table-column>
                <el-table-column label="变动金额" width="150">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type == 1" style="color:green">+ {{scope.row.num}}</span>
                        <span v-if="scope.row.type == 2" style="color:darkred">- {{scope.row.num}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="before_num" label="当前余额" width="150"></el-table-column>
                <el-table-column prop="desc" label="描述" width="200"></el-table-column>
                <el-table-column align="center" prop="created_at" label="日期" width="180" ></el-table-column>

                <el-table-column align="center" label="业务信息">
                    <template slot-scope="scope">

                        <el-table v-if="scope.row.source_type == 'store_pay_order'"  size="mini" :show-header="false" :data="cPayOrderData (scope.row)" border >
                            <el-table-column prop="name" width="100" align="right"></el-table-column>
                            <el-table-column prop="value"></el-table-column>
                        </el-table>
                        <el-table v-if="scope.row.source_type == 'cyorder'"  size="mini" :show-header="false" :data="cCyorderData (scope.row)" border >
                            <el-table-column prop="name" width="100" align="right"></el-table-column>
                            <el-table-column prop="value"></el-table-column>
                        </el-table>
                        <span v-else> - </span>

                        <div v-if="scope.row.source_type == 'cyorder'">
                            <div style="padding:10px 0;color:gray;">分佣</div>
                            <el-table size="mini" :show-header="false" :data="cCommission (scope.row)" border >
                                <el-table-column prop="name" width="100" align="right"></el-table-column>
                                <el-table-column prop="value"></el-table-column>
                            </el-table>
                        </div>
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


    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                dialogVisible: false,
                list: [],
                pagination: null,
                loading: false,
                search: {
                    page: 1,
                    keyword: '',
                    kw_type: 'bsh_name'
                }
            };
        },
        computed: {
            cCommission(item){
                return function(item){
                    let info = item.source_info ? item.source_info : '',
                        commission = info && info.commission ? info.commission : '',
                        commission_3r = info && info.commission_3r ? info.commission_3r : '';
                    let list = [
                        {name: '直推', value: commission ? commission.price : '-'}
                    ];
                    if(commission_3r){
                        let i, role_type;
                        for(i=0; i < commission_3r.length; i++){
                            role_type = commission_3r[i].role_type;
                            if(role_type == "branch_office"){
                                role_type = "分公司";
                            }else if(role_type == "partner"){
                                role_type = "合伙人";
                            }else if(role_type == "store"){
                                role_type = "VIP代理商";
                            }
                            list.push({name: role_type, value: commission_3r[i].price});
                        }
                    }
                    return list;
                }
            },
            cCyorderData(item){
                return function(item){
                    let info = item.source_info ? item.source_info : '',
                        shopping_voucher = info && info.shopping_voucher ? info.shopping_voucher : '',
                        score = info && info.score ? info.score : '';
                    return [
                        {name: '支付金额', value: info ? info.pay_price : '-'},
                        {name: '支付用户', value: info ? info.pay_user_mobile : '-',},
                        {name: '赠送红包', value: shopping_voucher ? shopping_voucher.shopping_voucher_num : '-' },
                        {name: '赠送积分', value: score ? score.integral_num : '-' },
                        {name: '赠送积分', value: score ? score.integral_num : '-' },
                    ]
                }
            },
            cPayOrderData(item){
                return function(item){
                    let info = item.source_info ? item.source_info : '';
                    return [
                        {name: '订单号', value: (info ? info.order_no : '-')},
                        {name: '支付用户', value: (info ? info.nickname : '-')},
                        {name: '手机手机', value: (info ? info.mobile : '-')},
                        {name: '订单金额', value: (info ? info.order_price : '-')},
                        {name: '支付方式', value: (info ? info.pay_type : '-')}
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
                    r: 'plugin/smart_shop/mall/store-account/log'
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