<?php
Yii::$app->loadComponentView('com-dialog-select');
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>红包记录</span>
                <div style="float: right;margin: -5px 0">
                    <el-button @click="handleRecharge" type="primary" size="small">充值红包</el-button>
                </div>
                <div style="margin-top: 15px" v-loading="statisticsLoading">
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
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索" v-model="searchData.keyword" clearable @clear="search" @input="triggeredChange">
                    <el-select slot="prepend" v-model="searchData.kw_type" placeholder="请选择" size="small"
                               style="width:120px;">
                        <el-option v-for="item in item_type_options"
                                   :key="item.value"
                                   :label="item.label"
                                   :value="item.value">
                        </el-option>
                    </el-select>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <div style="float: left; margin-left: 15px">
                类型
                <el-tooltip class="item" effect="dark" content="只有选择订单或者商家扫码类型，才能筛选省市区" placement="bottom">
                    <i class="el-icon-question"></i>
                </el-tooltip>
                <el-select style="width: 150px;" size="small" v-model="searchData.source_type" @change='typeChange'>
                    <el-option v-for="item in type_options" :key="item.value" :value="item.value" :label="item.label"></el-option>
                </el-select>
            </div>
            <el-table :data="list" border style="width: 100%" v-loading="loading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column label="用户信息" width="280">
                    <template slot-scope="scope">
                        <com-image mode="aspectFill" style="float: left;margin-right: 8px"
                                   :src="scope.row.avatar_url"></com-image>
                        <div>{{scope.row.nickname}}</div>
                        <div>ID:{{scope.row.user_id}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="收支情况" width="150">
                    <template slot-scope="scope">
                        <div style="font-size: 18px;color: #68CF3D" v-if="scope.row.type == 1">+{{scope.row.money}}</div>
                        <div style="font-size: 18px;color: #F6AA5A" v-if="scope.row.type == 2">-{{scope.row.money}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="current_money" label="当前红包（变动前）" width="200"></el-table-column>
                <el-table-column prop="desc" label="说明" width="400"></el-table-column>
                <el-table-column prop="scope" label="日期">
                       <template slot-scope="scope">
                           {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                       </template>
                </el-table-column>
            </el-table>

            <!--工具条 批量操作和分页-->
            <el-pagination
                    background
                    layout="prev, pager, next"
                    @current-change="pageChange"
                    :page-size="pagination.pageSize"
                    :total="pagination.total_count"
                    style="margin:15px;text-align: center"
                    v-if="pagination">
            </el-pagination>
        </div>

        <!-- 充值收益 -->
        <el-dialog title="充值红包" :visible.sync="dialogRecharge" width="30%">
            <el-form :model="rechargeForm" label-width="80px" :rules="rechargeFormRules" ref="rechargeForm">
                <el-form-item label="操作" prop="type">
                    <el-radio v-model="rechargeForm.type" label="1">充值</el-radio>
                    <el-radio v-model="rechargeForm.type" label="2">扣除</el-radio>
                </el-form-item>
                <el-form-item label="用户" prop="user_id">
                    <el-input style="display: none;" v-model="rechargeForm.user_id"></el-input>
                    <el-input disabled v-model="rechargeForm.nickname">
                        <template slot="append">
                            <el-button @click="getUsers" type="primary">选择</el-button>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="数量" prop="number" size="small">
                    <el-input type="number" v-model="rechargeForm.number"></el-input>
                </el-form-item>
                <el-form-item label="备注" prop="remark" size="small">
                    <el-input v-model="rechargeForm.remark"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogRecharge = false">取消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="rechargeSubmit">充值</el-button>
            </div>
        </el-dialog>

        <com-dialog-select
                @close="closeDlgSelect"
                @selected="selectUser"
                :url="forDlgSelect.url"
                :multiple="forDlgSelect.multiple"
                :title="forDlgSelect.title"
                :list-key="forDlgSelect.listKey"
                :params="forDlgSelect.params"
                :columns="forDlgSelect.columns"
                :visible="forDlgSelect.visible"></com-dialog-select>

    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                btnLoading: false,
                statisticsLoading: false,
                rechargeForm: {
                    type: "1",
                    user_id: '',
                    number: 0.00,
                    nickname: '',
                    remark: '',
                    is_manual: 1,
                },
                forDlgSelect:{
                    visible: false,
                    multiple: false,
                    title: "选择用户",
                    params: {},
                    columns: [
                        {label:"红包", key:"shop_voucher_money"},
                        {label:"手机号", key:"mobile"},
                        {label:"等级", key:"role_type_text"}
                    ],
                    listKey: 'nickname',
                    url: "mall/user/index",
                },
                dialogRecharge: false,
                rechargeFormRules: {
                    user_id: [
                        {required: true, message: '请选择用户', trigger: 'blur'},
                    ],
                    type: [
                        {required: true, message: '操作不能为空', trigger: 'blur'},
                    ],
                    number: [
                        {required: true, message: '数量不能为空', trigger: 'blur'},
                    ]
                },
                searchData: {
                    keyword: '',
                    kw_type: '',
                    start_date: '',
                    end_at: '',
                    source_type: '',
                },
                date: '',
                Statistics: '',
                list: [],
                pagination: null,
                loading: false,
                item_type_options: [
                    {
                        value: 'mobile',
                        label: '手机号'
                    },
                    {
                        value: 'user_id',
                        label: '用户ID'
                    },
                    {
                        value: 'nickname',
                        label: '昵称'
                    },
                ],
                type_options:[
                    {
                        label:'全部',
                        value:''
                    },
                    {
                        label:'商城订单-消费',
                        value:'target_order'
                    },
                    {
                        label:'订单取消',
                        value:'from_order_cancel'
                    },
                    {
                        label:'管理员操作',
                        value:'admin'
                    },
                    {
                        label:'订单退款',
                        value:'from_order_refund'
                    },
                    {
                        label:'商家扫码订单',
                        value:'from_mch_checkout_order'
                    },
                    {
                        label:'1688订单',
                        value:'target_alibaba_distribution_order'
                    },
                    {
                        label:'1688订单退款',
                        value:'1688_distribution_order_detail_refund'
                    },
                    {
                        label:'酒店订单',
                        value:'from_hotel_order'
                    },
                    {
                        label:'话费订单',
                        value:'from_addcredit_order'
                    },
                    {
                        label:'大礼包订单',
                        value:'from_giftpacks_order'
                    },
                    {
                        label:'商城订单-获取',
                        value:'from_order_detail'
                    },
                ],
            };
        },
        methods: {
            triggeredChange (){
                if (this.searchData.keyword.length>0 && this.searchData.kw_type.length<=0) {
                    alert('请选择搜索方式');
                    this.searchData.keyword='';
                }
            },
            typeChange(e) {
                console.log(e);
                this.page = 1;
                this.searchData.source_type = e;
                this.getList();
            },
            getUsers(){
                this.forDlgSelect.visible = true;
            },
            selectUser(row){
                this.rechargeForm.user_id = row.user_id;
                this.rechargeForm.nickname = row.nickname;
            },
            closeDlgSelect(){
                this.forDlgSelect.visible = false;
            },
            handleRecharge() {
                this.dialogRecharge = true;
                this.rechargeForm.user_id = '';
                this.rechargeForm.nickname = '';
            },
            rechargeSubmit(){
                var self = this;
                this.$refs.rechargeForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, self.rechargeForm);
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/shopping_voucher/mall/shopping-voucher-log/recharge',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                                self.dialogRecharge = false;
                            } else {
                                self.$message.error(e.data.msg);
                            }
                            self.btnLoading = false;
                        }).catch(e => {
                            self.btnLoading = false;
                        });
                    }
                });
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
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
            getList() {
                let params = {
                    r: 'plugin/shopping_voucher/mall/shopping-voucher-log/list',
                    page: this.page,
                    start_date: this.searchData.start_date,
                    end_date: this.searchData.end_date,
                    keyword: this.searchData.keyword,
                    kw_type: this.searchData.kw_type,
                    source_type: this.searchData.source_type,
                };
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.getStatistics();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.loading = false;
                }).catch(e => {
                    this.loading = false;
                });
                this.loading = true;
            },
            getStatistics() {
                let params = {
                    r: 'plugin/shopping_voucher/mall/shopping-voucher-log/statistics',
                    page: this.page,
                    start_date: this.searchData.start_date,
                    end_date: this.searchData.end_date,
                    keyword: this.searchData.keyword,
                    kw_type: this.searchData.kw_type,
                    source_type: this.searchData.source_type,
                };
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